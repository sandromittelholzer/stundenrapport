<?php
  require '../include/config.php';
  require 'request.php';

  $sql = "SELECT eintrittsdatum, fs_berechtigung from tbl_benutzer where tbl_benutzer.id = {$_SESSION['id']}";

  $statement = $pdo->prepare($sql);
  $statement->execute();

  $f = $statement->fetch();
  $permission = $f['fs_berechtigung'];

  if ($rule === 'settings' && !$error) {
    // Multidimensional entfernen
    foreach ($_POST as $name => $data) {
      $_POST[$name] = $data[0];
    }

    // Unnötige Leerzeichen und Grossbuchstaben ersetzen
    $ignore = ['password'];

    foreach ($_POST as $k => $data) {
      $_POST[$k] = preg_replace('/\s+/', ' ', $data);

      if ($k === 'e_mail') {
        $_POST[$k] = strtolower($data);
      } else if (!preg_match('/' . $k . '/', implode($ignore))) {
        $_POST[$k] = mb_convert_case(trim(preg_replace('/\s+/', ' ', $data)), MB_CASE_TITLE);
      }
    }

    // Datenbank aktualisieren
    $sql = "UPDATE tbl_benutzer
               set anrede = :form_of_address,
                   name = :first_name,
                   nachname = :last_name,
                   e_mail = :e_mail
             where id = {$_SESSION['id']}";

    $statement = $pdo->prepare($sql);

    // Paramter finden und an $_POST binden
    preg_match_all('/:([_a-z]+)/', $sql, $parameters);

    foreach ($parameters[1] as $name) {
      $statement->bindParam($name, $_POST[$name]);
    }

    $e = $statement->execute();

    if ($e) {
      if ($_POST['password']) {
        // Neues Passwort setzen
        $sql = "UPDATE tbl_benutzer set passwort = :password where id = {$_SESSION['id']}";

        $statement = $pdo->prepare($sql);

        $statement->bindParam(':password', password_hash($_POST['password'], PASSWORD_DEFAULT));
        $statement->execute();

        $e = $statement->execute();

        if ($e) {
          echo true;
        }
      } else {
        echo true;
      }
    }
  }

  // Reihe(n) speichern
  if ($_POST['action'] === 'submit' && !$error) {
    // Unnötige Leerzeichen und Grossbuchstaben ersetzen
    $ignore = ['task', 'comment'];

    for ($i = 0; $i < count($_POST['id']); $i++) {
      foreach ($_POST as $k => $data) {
        $_POST[$k][$i] = preg_replace('/\s+/', ' ', $data[$i]);

        if ($k === 'e_mail') {
          $_POST[$k][$i] = strtolower($data[$i]);
        } else if (!preg_match('/' . $k . '/', implode($ignore))) {
          $_POST[$k][$i] = mb_convert_case(trim(preg_replace('/\s+/', ' ', $data[$i])), MB_CASE_TITLE);
        }
      }
    }

    if ($rule === 'edit-user') {
      unset($f); // Reset

      for ($i = 0; $i < count($_POST['id']); $i++) {
        $_POST['date_of_joining'][$i] = date('Y-m-d', strtotime($_POST['date_of_joining'][$i])); // Datum umwandeln

        // Eintrittsdatum abgleichen
        $sql = "SELECT eintrittsdatum,
                       fs_berechtigung,
                       tbl_zeiterfassungen.id,
                       datum,
                       startzeit,
                       endzeit
                  from tbl_benutzer
             left join tbl_zeiterfassungen
                    on tbl_zeiterfassungen.fs_benutzer = tbl_benutzer.id
                 where tbl_benutzer.id = :id";

        $statement = $pdo->prepare($sql);

        $statement->bindParam(':id', $_POST['id'][$i]);
        $statement->execute();

        for ($n = 0; $n < $statement->rowCount(); $n++) {
          $f[$i][] = $statement->fetch();
        }

        // Benutzerkontoeinstellungen aktualisieren
        if ($f[$i][0]['fs_berechtigung'] == 1) {
          $sql = "UPDATE tbl_benutzer
                     set name = :first_name,
                         nachname = :last_name,
                         e_mail = :e_mail,
                         eintrittsdatum = :date_of_joining,
                         mitarbeiternummer = :no
                   where id = :id";
        } else {
          $sql = "UPDATE tbl_benutzer
                     set name = :first_name,
                         nachname = :last_name,
                         e_mail = :e_mail
                   where id = :id";
        }

         $statement = $pdo->prepare($sql);

         // Paramter finden und an $_POST binden
         preg_match_all('/:([_a-z]+)/', $sql, $parameters);

         foreach ($parameters[1] as $name) {
           $statement->bindParam($name, $_POST[$name][$i]);
         }

         $e = $statement->execute();

        // Betrag in Zeiterfassung(en) ändern
        if ($e && $f[$i][0]['datum'] && $_POST['date_of_joining'][$i] !== $f[$i][0]['eintrittsdatum']) { // Benutzerkontodaten gespeichert, mindestens eine Zeiterfassung und Eingabefeld anders als Datenbank
          for ($n = 0; $n < count($f[$i]); $n++) {
            // Tage seit Eintrittsdatum berechnen
            // Zeitdifferenz berechnen
            $interval = [
              date_diff(new DateTime($_POST['date_of_joining'][$i]), new DateTime($f[$i][$n]['datum']))->format('%r%Y'),
              (strtotime($f[$i][$n]['endzeit']) - strtotime($f[$i][$n]['startzeit']))
            ];

            // Stundenansatz berechnen und Betrag konvertieren (z.B. 25.26 zu 25.25)
            if ($interval[0] < 3) {
              $hr = 30;
            } else if ($interval[0] < 5) {
              $hr = 32.50;
            } else {
              $hr = 35;
            }

            $sum = number_format((round(($hr / 60) * $interval[1] / 60 * 2, 1) / 2), 2, '.', '');

            // Zeiterfassung aktualisieren
            $sql = "UPDATE tbl_zeiterfassungen set betrag = $sum where id = :id";

            $statement = $pdo->prepare($sql);

            $statement->bindParam(':id', $f[$i][$n]['id']);
            $statement->execute();
          }
        }
      }

      echo true;
    } else {
       // Heutiges Datum für "Zuletzt geändert"
      $date = date('Y-m-d');

      for ($i = 0; $i < count($_POST['id']); $i++) {
        if ($permission == 1) {
          $ueb_standortkoordinator = 0;
          $ueb_superuser = 0;
        } else {
          // Eintrittsdatum des Benutzers aus der ausgewählten Reihe auslesen
          $sql = "SELECT eintrittsdatum
                    from tbl_benutzer
              inner join tbl_zeiterfassungen
                      on tbl_zeiterfassungen.fs_benutzer = tbl_benutzer.id
                   where tbl_zeiterfassungen.id = :id";

          $statement = $pdo->prepare($sql);

          $statement->bindParam(':id', $_POST['id'][$i]);
          $statement->execute();

          $f = $statement->fetch();

          if ($permission == 2) {
            $ueb_standortkoordinator = 1;
            $ueb_superuser = 0;
          } else {
            $ueb_standortkoordinator = 0;
            $ueb_superuser = 1;
          }
        }

        $_POST['date'][$i] = date('Y-m-d', strtotime($_POST['date'][$i])); // Datum umwandeln

        // Tage seit Eintrittsdatum berechnen
        // Zeitdifferenz berechnen
        $interval = [
          date_diff(new DateTime($f['eintrittsdatum']), new DateTime($_POST['date'][$i]))->format('%r%Y'),
          (strtotime($_POST['end_time'][$i]) - strtotime($_POST['start_time'][$i]))
        ];
        
        // Stundenansatz berechnen und Betrag konvertieren (z.B. 25.26 zu 25.25)
        if ($interval[0] < 3) {
          $hr = 30;
        } else if ($interval[0] < 5) {
          $hr = 32.50;
        } else {
          $hr = 35;
        }

        $sum = number_format((round(($hr / 60) * $interval[1] / 60 * 2, 1) / 2), 2, '.', '');

        // Manuelle Eingabe(n) erkennen
        $sql = "SELECT id from tbl_taetigkeiten where taetigkeit = :task";

        $statement = $pdo->prepare($sql);

        $statement->bindParam(':task', $_POST['task'][$i]);
        $statement->execute();

        $f = $statement->fetch();

        if ($f) {
          $task_no = $f['id'];
          $_POST['task'][$i] = null;
        }

        // Zeiterfassung aktualisieren
        $sql = "UPDATE tbl_zeiterfassungen
                   set taetigkeit = :task,
                       datum = :date,
                       startzeit = :start_time,
                       endzeit = :end_time,
                       betrag = $sum,
                       bemerkung = nullif(:comment, ''),
                       zuletzt_geaendert = '$date',
                       ueb_standortkoordinator = $ueb_standortkoordinator,
                       ueb_superuser = $ueb_superuser,
                       fs_taetigkeit = nullif('$task_no', ''),
                       fs_lohnart = (SELECT id from tbl_lohnarten where lohnart = :task_no)
                 where id = :id";
        $statement = $pdo->prepare($sql);

        // Paramter finden und an $_POST binden
        preg_match_all('/:([_a-z]+)/', $sql, $parameters);

        foreach ($parameters[1] as $name) {
          $statement->bindParam($name, $_POST[$name][$i]);
        }

        $e = $statement->execute();

        if ($e) {
          echo true;
        }
      }
    }
  }

  if ($_POST['action'] === 'delete') {
    for ($i = 0; $i < count($_POST['id']); $i++) {
      if ($permission == 1) {
        $sql = "DELETE from tbl_zeiterfassungen where id = :id and fs_benutzer = {$_SESSION['id']}";
      } else if ($permission == 3) {
        $sql = "DELETE from tbl_benutzer where id = :id and not id = {$_SESSION['id']}"; // Gegenprüfen, ob nicht aktuell angemeldeter Benutzer
      }

      $statement = $pdo->prepare($sql);

      $statement->bindParam(':id', $_POST['id'][$i]);
      $e = $statement->execute();

      if ($e) {
        echo true;
      }
    }
  }

  if ($_POST['action'] === 'accept') {
    for ($i = 0; $i < count($_POST['id']); $i++) {
      // Eintrag bestätigen
      if ($permission == 2) {
        $sql = "UPDATE tbl_zeiterfassungen set ueb_standortkoordinator = 1 where id = :id";
      } else if ($permission == 3) {
        $sql = "UPDATE tbl_zeiterfassungen set ueb_superuser = 1 where id = :id";
      }

      $statement = $pdo->prepare($sql);

      $statement->bindParam(':id', $_POST['id'][$i]);
      $e = $statement->execute();

      if ($e) {
        $sql = "SELECT ueb_standortkoordinator, ueb_superuser from tbl_zeiterfassungen where id = :id";

        $statement = $pdo->prepare($sql);

        $statement->bindParam(':id', $_POST['id'][$i]);
        $statement->execute();

        $f = $statement->fetch();

        if ($f['ueb_standortkoordinator'] == 1 && $f['ueb_superuser'] == 1) {
          require 'send-email.php';
        }

        echo true;
      }
    }
  }

  if ($_POST['action'] === 'reject') {
    for ($i = 0; $i < count($_POST['id']); $i++) {
      // Eintrag zurückweisen
      if ($permission == 2) {
        $sql = "UPDATE tbl_zeiterfassungen set ueb_standortkoordinator = 2 where id = :id";
      } else if ($permission == 3) {
        $sql = "UPDATE tbl_zeiterfassungen set ueb_superuser = 2 where id = :id";
      }

      $statement = $pdo->prepare($sql);

      $statement->bindParam(':id', $_POST['id'][$i]);
      $e = $statement->execute();

      if ($e) {
        require 'send-email.php';
        echo true;
      }
    }
  }

  if ($_POST['action'] === 'reset-password') {
    if ($permission == 3) { // Berechtigung überprüfen
      for ($i = 0; $i < count($_POST['id']); $i++) {
        $password = password_hash('123abc456!', PASSWORD_DEFAULT); // Standardpasswort

        $sql = "UPDATE tbl_benutzer set passwort = '$password' where id = :id";

        $statement = $pdo->prepare($sql);

        $statement->bindParam(':id', $_POST['id'][$i]);
        $statement->execute();

        $e = $statement->execute();

        if ($e) {
          echo true;
        }
      }
    }
  }
?>
