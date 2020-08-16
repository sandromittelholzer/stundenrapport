<?php
  require '../include/config.php';
  require '../include/permission.php';

  setlocale(LC_TIME, 'de_CH');

  if ($_SESSION['id']) {
    // Benutzerdaten
    $sql = "SELECT anrede,
                   name,
                   nachname,
                   benutzername,
                   e_mail,
                   eintrittsdatum,
                   mitarbeiternummer,
                   fs_berechtigung
              from tbl_benutzer
             where id = {$_SESSION['id']}";

    $statement = $pdo->prepare($sql);
    $statement->execute();

    for ($i = 0; $i < $statement->rowCount(); $i++) {
      $f[0] = $statement->fetch();
    }

    if ($f[0]['eintrittsdatum']) {
      // Datum formatieren (z. B. 01.01.2020 zu 01. Januar 2020)
      $f[0]['eintrittsdatum-formatiert'] = strftime('%d. ', strtotime($f[0]['eintrittsdatum'])) . strftime('%B ', strtotime($f[0]['eintrittsdatum'])) . strftime('%Y', strtotime($f[0]['eintrittsdatum']));

      // Tage seit Eintrittsdatum berechnen
      $interval = date_diff(new DateTime($f[0]['eintrittsdatum']), new DateTime())->format('%r%Y');

      if ($interval < 3) {
        $f[0]['stundenansatz'] = 'CHF 30.-';
      } else if ($interval < 5) {
        $f[0]['stundenansatz'] = 'CHF 32.50.-';
      } else {
        $f[0]['stundenansatz'] = 'CHF 35.-';
      }
    }

    // Bereits definierte Tätigkeiten oder Standorte abfragen
    if ($permission === 'betreuungsperson') {
      $sql = "SELECT taetigkeit FROM tbl_taetigkeiten";
    } else if ($permission === 'superuser') {
      $sql = "SELECT standort FROM tbl_standorte";
    }

    $statement = $pdo->prepare($sql);
    $statement->execute();

    for ($i = 0; $i < $statement->rowCount(); $i++) {
      $f[1][] = $statement->fetch();
    }

    // Zugewiesene(r) Standort(e)
    $sql = "SELECT standort
              from tbl_standorte
        inner join tbl_benutzer_standorte
                on tbl_standorte.id = tbl_benutzer_standorte.fs_standort
        inner join tbl_benutzer
                on tbl_benutzer.id = tbl_benutzer_standorte.fs_benutzer
             where tbl_benutzer.id = {$_SESSION['id']}";

    $statement = $pdo->prepare($sql);
    $statement->execute();

    for ($i = 0; $i < $statement->rowCount(); $i++) {
      $f[2][] = $statement->fetch();
    }

    if ($permission === 'betreuungsperson') {
      // Zeiterfassungen
      $sql = "SELECT tbl_zeiterfassungen.id,
                     coalesce(tbl_zeiterfassungen.taetigkeit, tbl_taetigkeiten.taetigkeit) taetigkeit,
                     lohnart,
                     standort,
                     datum,
                     startzeit,
                     endzeit,
                     betrag,
                     bemerkung,
                     erstellt,
                     zuletzt_geaendert,
                     ueb_standortkoordinator,
                     ueb_superuser
                from tbl_zeiterfassungen
          inner join tbl_benutzer
                  on tbl_zeiterfassungen.fs_benutzer = tbl_benutzer.id
           left join tbl_taetigkeiten
                  on tbl_taetigkeiten.id = tbl_zeiterfassungen.fs_taetigkeit
          inner join tbl_lohnarten
                  on tbl_zeiterfassungen.fs_lohnart = tbl_lohnarten.id
          inner join tbl_standorte
                  on tbl_zeiterfassungen.fs_standort = tbl_standorte.id
               where tbl_benutzer.id = {$_SESSION['id']}";
     } else if ($permission === 'standortkoordinator') {
       $sql = "SELECT tbl_zeiterfassungen.id,
                      tbl_benutzer.name,
                      tbl_benutzer.nachname,
                      coalesce(tbl_zeiterfassungen.taetigkeit, tbl_taetigkeiten.taetigkeit) taetigkeit,
                      lohnart,
                      standort,
                      datum,
                      startzeit,
                      endzeit,
                      betrag,
                      bemerkung,
                      erstellt,
                      zuletzt_geaendert,
                      ueb_standortkoordinator,
                      ueb_superuser
                 from tbl_benutzer b
           inner join tbl_benutzer_standorte
                   on tbl_benutzer_standorte.fs_benutzer = b.id
           inner join tbl_benutzer_standorte bs
                   on bs.fs_standort = tbl_benutzer_standorte.fs_standort
           inner join tbl_benutzer
                   on tbl_benutzer.id = bs.fs_benutzer
           inner join tbl_standorte
                   on tbl_standorte.id = bs.fs_standort
            left join tbl_zeiterfassungen
                   on tbl_zeiterfassungen.fs_benutzer = tbl_benutzer.id and tbl_zeiterfassungen.fs_standort = tbl_standorte.id
            left join tbl_taetigkeiten
                   on tbl_taetigkeiten.id = tbl_zeiterfassungen.fs_taetigkeit
           inner join tbl_lohnarten
                   on tbl_zeiterfassungen.fs_lohnart = tbl_lohnarten.id
                where b.id = {$_SESSION['id']}";
     } else {
       $sql = "SELECT tbl_zeiterfassungen.id,
                      name,
                      nachname,
                      coalesce(tbl_zeiterfassungen.taetigkeit, tbl_taetigkeiten.taetigkeit) taetigkeit,
                      lohnart,
                      standort,
                      datum,
                      startzeit,
                      endzeit,
                      betrag,
                      bemerkung,
                      erstellt,
                      zuletzt_geaendert,
                      ueb_standortkoordinator,
                      ueb_superuser
                 from tbl_zeiterfassungen
           inner join tbl_benutzer
                   on tbl_zeiterfassungen.fs_benutzer = tbl_benutzer.id
            left join tbl_taetigkeiten
                   on tbl_taetigkeiten.id = tbl_zeiterfassungen.fs_taetigkeit
           inner join tbl_lohnarten
                   on tbl_zeiterfassungen.fs_lohnart = tbl_lohnarten.id
           inner join tbl_standorte
                   on tbl_zeiterfassungen.fs_standort = tbl_standorte.id";
     }

    $statement = $pdo->prepare($sql);
    $statement->execute();

    for ($i = 0; $i < $statement->rowCount(); $i++) {
      $f[3][] = $statement->fetch();
    }

    // Benutzerkontos
    if ($permission === 'superuser') {
      $sql = "SELECT tbl_benutzer.id,
                     name,
                     nachname,
                     benutzername,
                     e_mail,
                     berechtigung,
                     eintrittsdatum,
                     mitarbeiternummer
                from tbl_benutzer
          inner join tbl_berechtigungen
                  on tbl_benutzer.fs_berechtigung = tbl_berechtigungen.id
           where not tbl_benutzer.id = {$_SESSION['id']}";

      $statement = $pdo->prepare($sql);
      $statement->execute();

      for ($i = 0; $i < $statement->rowCount(); $i++) {
        $f[4][] = $statement->fetch();
      }
    }

    echo json_encode($f);
  }
?>
