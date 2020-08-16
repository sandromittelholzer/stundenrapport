<?php
  require '../include/permission.php';
  require '../libs/fpdf/fpdf.php';

  setlocale(LC_TIME, 'de_CH');

  class PDF extends FPDF {
    function header() {
      // Schriftart, Schriftstärke ('' = normal) und Schriftgrösse
      $this->setFont('Helvetica', '', 10);

      // Header
      // Breite (0 = 100 %), Höhe, Text, Rand (0 = kein Rand), Position nächste Zelle (2 = darunter)
      $this->cell(0, 5, 'Rapperswil-Jona', 0, 2);
      $this->cell(0, 5, 'Bildung, Familie', 0, 2);
      $this->cell(0, 5, 'Personaldienst Schule', 0, 2);
      $this->Ln(10);

      $this->setFont('Helvetica', '', 8);
      $this->cell(105, 5, 'Lohnarten Tagesstruktur', 0, 0);
      $this->cell(0, 5, 'Monat ' . strftime('%B'), 0, 2);
      $this->Ln();

      $columns = [
        ['Mitarbeiternr.', 20], ['Name', 25], ['Nachname', 25], ['Lohnart', 15], ['Monat', 20], ['Konto', 25], ['Std.', 15], ['Stds.', 15], ['Pensum (in %)', 25], ['Betrag', 15]
      ];

      foreach ($columns as $i => $column) {
        // Spalten platzieren
        if ($i !== 0) {
          $startPosition += $columns[$i - 1][1];
          $this->setXY($this->getX() + $startPosition, $this->getY() - 8);
        }

        // MultiCell erstellen (= Zeilenumbruch)
        $this->multiCell($column[1], 8, $column[0], 1, 'L');
      }
    }

    function tableData($pdo) {
      $this->setFont('Helvetica', '', 8);

      if ($_GET['user']) {
        $filter = 'tbl_benutzer.id in (' . implode(',', $_GET['user']) . ')';
      } else {
        $filter = true;
      }

      $sql = "SELECT name,
                     nachname,
                     lohnart,
                     mitarbeiternummer,
                     konto,
                     TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(endzeit) - TIME_TO_SEC(startzeit))), '%H:%i') as std,
                     SUM(betrag) as betrag,
                     eintrittsdatum
                FROM tbl_benutzer
          inner join tbl_zeiterfassungen
                  on tbl_zeiterfassungen.fs_benutzer = tbl_benutzer.id
           left join tbl_taetigkeiten
                  on tbl_taetigkeiten.id = tbl_zeiterfassungen.fs_taetigkeit
           left join tbl_lohnarten
                  on tbl_zeiterfassungen.fs_lohnart = tbl_lohnarten.id
               where datum between :f_date and :l_date and ueb_standortkoordinator = 1 and ueb_superuser = 1 and $filter
            group by tbl_benutzer.id, lohnart, konto";

      $statement = $pdo->prepare($sql);

      // Erstes und letztes Datum des letzten Monats
      $statement->bindParam(':f_date', date('Y-m-d', mktime(0, 0, 0, date('m') - 1, 1)));
      $statement->bindParam(':l_date', date('Y-m-d', mktime(0, 0, 0, date('m'), 0)));

      $statement->execute();

      for ($i = 0; $i < $statement->rowCount(); $i++) {
        $f[] = $statement->fetch();
      }

      // Tabellenreih(e) generieren
      $columns = ['mitarbeiternummer', 'name', 'nachname', 'lohnart', 'monat', 'konto', 'std', 'stds', 'pensum', 'betrag'];

      if ($f) { // Mindestens ein Eintrag
        foreach ($f as $n => $tr) {
          // Multidimensional
          for ($i = 0; $i < 10; $i++) {
            $export[$n][] = $tr[$columns[$i]];
          }

          // Monat ausgeben
          $export[$n][4] = strftime('%B', strtotime('-1 month'));

          // Tage seit Eintrittsdatum berechnen
          $interval = date_diff(new DateTime($tr['eintrittsdatum']), new DateTime())->format('%r%Y');

          if ($interval < 3) {
            $export[$n][count($columns) - 3] = 'CHF 30.-';
          } else if ($interval < 5) {
            $export[$n][count($columns) - 3] = 'CHF 32.50.-';
          } else {
            $export[$n][count($columns) - 3] = 'CHF 35.-';
          }

          // Pensum berechnen
          $export[$n][8] = round(((date('i', strtotime($export[$n][6])) + date('h', strtotime($export[$n][6])) * 60) / 10080) * 100, 2);
        }

        // Tabellenreihe(n) anzeien
        $columns = [20, 25, 25, 15, 20, 25, 15, 15, 25, 15]; // Spaltengrössen

        foreach ($export as $i => $tr) {
          $c[0] = 5; // Start

          // Y-Koordinate ermitteln
          if (!$nextTr) { // Erste Reihe
            $c[1] = $this->getY();

            $paddingBottom = 1;
            $marginTop = 1;
          } else {
            $c[1] = max($nextTr); // Höchste Koordinate nehmen, um allfällige Zeilenumbrüche entgegenzuwirken
            $marginTop = 2;
            $paddingBottom = 0;
          }

          // Zellen positionieren
          foreach ($tr as $i => $td) {
            if ($i !== 0) {
              $c[0] += $columns[$i - 1]; // X-Position laufend addieren (= nächste Zellen)
            }

            // Koordinaten für alle Zellen speichern
            $coordinates[$i] = [
              $c[0],
              $c[1]
            ];

            $this->setXY($c[0], $c[1] + $marginTop);
            $this->multiCell($columns[$i], 3, utf8_decode($td), 0, 'L'); // Breite, Höhe (= Zeilenabstand), Inhalt, Rand (0 = kein Rand), linksbündig

            $nextTr[] = $this->getY(); // Y-Koordinaten sammeln, um allfälligen Zeilenumbruch zu erkennen
          }

          // Raster zeichnen, um Grössenunterschiede der Zellen auszugleichen
          for ($i = 0; $i < count($tr); $i++) {
            $this->Rect($coordinates[$i][0], $coordinates[$i][1] + $marginTop - 1, $columns[$i], max($nextTr) - $coordinates[$i][1] + $paddingBottom); // X, Y, Breite, Höhe (nächste Y-Koordinate - aktuelle Y-Koordinate + padding unten)
          }

          // Seitenumbruch falls nötig
          if ($this->getY() + 5 > $this->PageBreakTrigger) {
            $this->addPage(); // Neue Seite anhängen

            // Neue Koordinaten ermitteln und nächste Y-Koordinate löschen (= ausserhalb der Seite)
            $c[1] = $this->getY();
            unset($nextTr);

            // Padding und Margin für erste Reihe
            $marginTop = 1;
            $paddingBottom = 1;
          }
        }
      }
    }

    function footer() {
      // Seitenanzahl unten anzeigen (= Seite 1 von 1)
      $this->setY($this->getPageHeight() - 5);
      $this->cell(0, 5, 'Seite ' . $this->pageNo() . ' von {nb}', 0, 0, 'C');
    }
  }

  $pdf = new PDF();
  $pdf->SetMargins(5, 5, 5);

  $pdf->addPage();
  $pdf->tableData($pdo);

  $pdf->aliasNbPages(); // Gesamtzahl der Seiten

  $pdf->output();
?>
