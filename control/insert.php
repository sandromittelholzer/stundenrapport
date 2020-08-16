<?php
  require '../include/config.php';
  require 'request.php';

  // Sonderzeichen zu normalen Zeichen konvertieren (z. B. ä zu a)
  function cSpecialChars($s, $n) {
    return substr(preg_replace('/[^a-z]/', '', strtolower(iconv('utf-8', 'ascii//TRANSLIT', $s))), 0, $n);
  }

  // Nächsten Benutzernamen ermitteln
  function checkUsername($pdo, $username, $n) {
    $sql = "SELECT benutzername from tbl_benutzer where benutzername = '$username'";

    $statement = $pdo->prepare($sql);
    $statement->execute();

    if ($statement->rowCount()) {
      $n++;
      $username = preg_replace('/[0-9]+/', '', $username) . $n;

      return checkUsername($pdo, $username, $n);
    } else {
      return $username;
    }
  }

  // Multidimensional entfernen
  foreach ($_POST as $name => $data) {
    $_POST[$name] = $data[0];
  }

  if (!$error) {
    // Unnötige Leerzeichen und Grossbuchstaben ersetzen
    $ignore = ['locations', 'task', 'comment'];

    foreach ($_POST as $k => $data) {
      $_POST[$k] = preg_replace('/\s+/', ' ', $data);

      if ($k === 'e_mail') {
        $_POST[$k] = strtolower($data);
      } else if (!preg_match('/' . $k . '/', implode($ignore))) {
        $_POST[$k] = mb_convert_case(trim(preg_replace('/\s+/', ' ', $data)), MB_CASE_TITLE);
      }
    }

    if ($rule === 'time-record') {
      $date = date('Y-m-d'); // Erstellungsdatum
      $_POST['date'] = date('Y-m-d', strtotime($_POST['date'])); // Datum umwandeln

      // Eintrittsdatum abfragen, um Stundenansatz zu berechnen
      $sql = "SELECT eintrittsdatum from tbl_benutzer where id = {$_SESSION['id']}";

      $statement = $pdo->prepare($sql);
      $statement->execute();

      $f = $statement->fetch();

      // Tage seit Eintrittsdatum berechnen
      // Zeitdifferenz berechnen
      $interval = [
        date_diff(new DateTime($f['eintrittsdatum']), new DateTime($_POST['date']))->format('%r%Y'),
        (strtotime($_POST['end_time']) - strtotime($_POST['start_time']))
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

      // Manuelle Eingabe erkennen
      $sql = "SELECT id from tbl_taetigkeiten where taetigkeit = :task";

      $statement = $pdo->prepare($sql);

      $statement->bindParam(':task', $_POST['task']);
      $statement->execute();

      $f = $statement->fetch();

      if ($f) {
        $task_no = $f['id'];
        $_POST['task'] = null;
      }

      $sql = "INSERT INTO tbl_zeiterfassungen (taetigkeit, datum, startzeit, endzeit, betrag, bemerkung, erstellt, fs_benutzer, fs_taetigkeit, fs_lohnart, fs_standort) values (:task, :date, :start_time, :end_time, $sum, nullif(:comment, ''), '$date', {$_SESSION['id']}, nullif('$task_no', ''), (SELECT fs_lohnart from tbl_taetigkeiten inner join tbl_lohnarten on tbl_taetigkeiten.fs_lohnart = tbl_lohnarten.id where tbl_taetigkeiten.id = '$task_no' or lohnart = :task_no), (SELECT id from tbl_standorte where standort = :location))";
      $statement = $pdo->prepare($sql);

      // Paramter finden und an $_POST binden
      preg_match_all('/:([_a-z]+)/', $sql, $parameters);

      foreach ($parameters[1] as $n) {
        $statement->bindParam($n, $_POST[$n]);
      }

      $e = $statement->execute();

      if ($e) {
        echo true;
      }
    } else { // Neues Benutzerkonto
      $username = checkUsername($pdo, cSpecialChars($_POST['last_name'], 3) . cSpecialChars($_POST['first_name'], 2), 0);
      $password = password_hash('123abc456!', PASSWORD_DEFAULT); // Standardpasswort

      $_POST['date_of_joining'] = $_POST['date_of_joining'] ? date('Y-m-d', strtotime($_POST['date_of_joining'])) : null; // Datum umwandeln falls nötig (nur Betreuungsperson)

      $sql = "INSERT INTO tbl_benutzer (anrede, name, nachname, benutzername, passwort, e_mail, eintrittsdatum, mitarbeiternummer, fs_berechtigung) values (:form_of_address, :first_name, :last_name, '$username', '$password', nullif(:e_mail, ''), :date_of_joining, :no, (SELECT id from tbl_berechtigungen where berechtigung = :permission))";
      $statement = $pdo->prepare($sql);

      // Paramter finden und an $_POST binden
      preg_match_all('/:([_a-z]+)/', $sql, $parameters);

      foreach ($parameters[1] as $n) {
        $statement->bindParam($n, $_POST[$n]);
      }

      $e = $statement->execute();

      if ($e) {
        if ($_POST['permission'] === 'Superuser') {
          require_once 'send-email.php';
          echo true;
        } else { // Standort(e) zuweisen
          $sql = "INSERT INTO tbl_benutzer_standorte (fs_benutzer, fs_standort) values";

          foreach ($_POST['locations'] as $location) {
            $sql .= " ((SELECT id from tbl_benutzer where benutzername = '$username'), (SELECT id from tbl_standorte where standort = '$location'))" . ',';
          }

          $sql = substr_replace($sql, ';', -1); // Letztes Komma entfernen
          $statement = $pdo->prepare($sql);

          $e = $statement->execute();

          if ($e) {
            require_once 'send-email.php';
            echo true;
          }
        }
      }
    }
  }
?>
