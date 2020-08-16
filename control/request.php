<?php
  require '../include/config.php';
  session_start();

  $rules = [
    // Benutzerkontoeinstellungen
    'settings' => [
      0 => ['form_of_address', 'first_name', 'last_name', 'password', 'repeat_password'],
      1 => ['e_mail']
    ],
    // Betreuungsperson
    'permission-1' => [
      0 => ['permission', 'form_of_address', 'first_name', 'last_name'],
      1 => ['e_mail'],
      2 => ['date_of_joining'],
      3 => ['no'],
      4 => ['locations']
    ],
    // Standortkoordinator
    'permission-2' => [
      0 => ['permission', 'form_of_address', 'first_name', 'last_name'],
      1 => ['e_mail'],
      4 => ['locations']
    ],
    // Superuser
    'permission-3' => [
      0 => ['permission', 'form_of_address', 'first_name', 'last_name'],
      1 => ['e_mail'],
    ],
    // Zeiterfassung
    'time-record' => [
      0 => ['task', 'location'],
      2 => ['date'],
      5 => ['start_time', 'end_time'],
      6 => ['comment']
    ],
    // Tabellen überarbeiten
    // Benutzerkonto
    'edit-user' => [
      [
        0 => ['first_name', 'last_name'],
        1 => ['e_mail'],
        2 => ['date_of_joining'],
        3 => ['no']
      ],
      [
        0 => ['first_name', 'last_name'],
        1 => ['e_mail']
      ]
    ],
    // Zeiterfassung
    'edit-time-record' => [
      0 => ['task'],
      2 => ['date'],
      5 => ['start_time', 'end_time'],
      6 => ['comment']
    ]
  ];
  $rule = $_POST['rule'];
  $characters = [
    '0-9',
    'a-z'
  ];
  $pattern = '(?=.*[' . $characters[0] . '])(?=.*[' . $characters[1] . '])(?=.*[' . strtoupper($characters[1]) . '])';
  // Lohnarten
  $task_nos = [
    '1280' => true,
    '1282' => true,
    '1284' => true,
    '1285' => true
  ];

  function searchDatabase($pdo, $sql, $parameter) {
    // Datenbank durchsuchen
    $statement = $pdo->prepare($sql);
    $statement->execute();

    for ($i = 0; $i < $statement->rowCount(); $i++) {
      $f[] = $statement->fetch();
    }

    // Ergebnis als Index speichern
    foreach ($f as $t) {
      $match[$t[$parameter]] = true;
    }

    return $match;
  }

  // Multidimensional
  if (!preg_match('/edit/', $rule) && !is_null($rule)) {
    foreach ($_POST as $name => $data) {
      $_POST[$name] = [$data];
    }
  }

  // Counter für Schleife definieren
  if ($_POST['id']) {
    $count = count($_POST['id']);
  } else {
    $count = 1;
  }

  if ($rules[$rule]) {
    // Regeln zwischenspeichern
    $cashe = $rules[$rule];

    for ($i= 0; $i < $count; $i++) {
      if ($rule === 'edit-user') {
        $sql = "SELECT fs_berechtigung from tbl_benutzer where id = :id";

        $statement = $pdo->prepare($sql);

        $statement->bindParam(':id', $_POST['id'][$i]);
        $statement->execute();

        $f = $statement->fetch();

        if ($f['fs_berechtigung'] == 1) {
          $rules[$rule] = $cashe[0];
        } else {
          $rules[$rule] = $cashe[1];
        }
      }

      foreach ($rules[$rule] as $r => $name) {
        foreach ($name as $name) {
          if (preg_match('/password/', $name)) {
            if ($_POST['password'][$i]) {
              if (preg_match('/password/', $name)) {
                if ($_POST['password'][$i] !== $_POST['repeat_password'][$i] || !preg_match('/' . $pattern . '/', $_POST['password'][$i])) {
                  $error[] = $name;
                }
              }
            }
          } else {
            if ($r === 0 && preg_match('/[' . $name . ']/', implode($rules[$rule][0]))) {
              if (!strlen($_POST[$name][$i])) {
                $error[] = $name;
              } else if ($name === 'permission') {
                // Prüfen, ob Berechtigung zulässig ist
                $sql = "SELECT berechtigung FROM tbl_berechtigungen";
                $parameter = explode(' ', $sql)[1];

                if (!searchDatabase($pdo, $sql, $parameter)[$_POST[$name][$i]]) {
                  $error[] = $name;
                }
              } else if ($name === 'form_of_address') {
                if (!preg_match('/(Frau|Herr)/', $_POST[$name][$i])) {
                  $error[] = $name;
                }
              } else if ($name === 'task') {
                // Prüfen, ob Tätigkeit zulässig ist
                $sql = "SELECT taetigkeit FROM tbl_taetigkeiten";
                $parameter = explode(' ', $sql)[1];

                if (searchDatabase($pdo, $sql, $parameter)[$_POST[$name][$i]]) {
                  if ($rule === 'edit') {
                    // Prüfen, ob Tätigkeit und Lohnartnummer übereinstimmen
                    $sql = "SELECT taetigkeit,
                                  lohnart
                             from tbl_taetigkeiten
                       inner join tbl_lohnarten
                               on tbl_taetigkeiten.fs_lohnart = tbl_lohnarten.id
                            where taetigkeit = :task and lohnart = :task_no";

                    $statement = $pdo->prepare($sql);

                    // Paramter finden und an $_POST binden
                    preg_match_all('/:([_a-z]+)/', $sql, $parameters);

                    foreach ($parameters[1] as $name) {
                      $statement->bindParam($name, $_POST[$name][$i]);
                    }

                    $statement->execute();

                    if (!$statement->rowCount()) {
                      $error[$_POST['id'][$i]][] = 'task_no';
                    }
                  }
                } else {
                  if (!$task_nos[$_POST['task_no'][$i]]) {
                    $error[] = 'task_no';
                  }
                }
              } else if ($name === 'location') {
                // Prüfen, ob Benutzer für Standort berechtigt
                $sql = "SELECT standort
                          from tbl_standorte
                    inner join tbl_benutzer_standorte
                            on tbl_standorte.id = tbl_benutzer_standorte.fs_standort
                    inner join tbl_benutzer
                            on tbl_benutzer.id = tbl_benutzer_standorte.fs_benutzer
                         where tbl_benutzer.id = {$_SESSION['id']}";
                $parameter = trim(explode(' ', $sql)[1]); // Für mehrzeiliges Statement

                if (!searchDatabase($pdo, $sql, $parameter)[$_POST[$name][$i]]) {
                  $error[] = $name;
                }
              }
            } else if ($r === 1 && preg_match('/[' . $name . ']/', implode($rules[$rule][1]))) {
              // if (strlen($_POST[$name][$i]) !== 0) {
                if (!preg_match('/^[a-z0-9]+(?:[a-z0-9._-]+[a-z0-9]+)?@+[a-z0-9]+(?:[._-][a-z0-9]+)*\.[a-z0-9]+$/', strtolower($_POST[$name][$i]))) {
                  $error[] = $name;
                } else {
                  $e = [
                    explode('@', $_POST[$name][$i]),
                    explode('.', explode('@', $_POST[$name][$i])[1])
                  ];

                  if (strlen($_POST[$name][$i]) > 254 || strlen($e[0][0]) > 64) {
                    $error[] = $name;
                  } else {
                    for ($n = 0; $n < count($e[1]); $n++) {
                      if (strlen($e[1][$n]) > 64) {
                        $error[] = $name;
                        break;
                      } else if ($n === count($e[1]) - 1) {
                        if (strlen($e[1][$n]) < 2) {
                          $error[] = $name;
                        }
                      }
                    }
                  }
                }
              // }
            } else if ($r === 2 && preg_match('/[' . $name . ']/', implode($rules[$rule][2]))) {
              // Datum auf Gültigkeit prüfen
              if (!(date('d.m.Y', strtotime($_POST[$name][$i])) === $_POST[$name][$i])) {
                $error[] = $name;
              }
            } else if ($r === 3 && preg_match('/[' . $name . ']/', implode($rules[$rule][3]))) {
              if (!preg_match('/^[0-9]+$/', $_POST[$name][$i])) {
                $error[] = $name;
              }
            } else if ($r === 4 && preg_match('/[' . $name . ']/', implode($rules[$rule][4]))) {
              if (!isset($_POST[$name][$i])) {
                $error[] = $name;
              } else {
                // Prüfen, ob Standort zulässig ist
                $sql = "SELECT standort FROM tbl_standorte";
                $parameter = explode(' ', $sql)[1];

                foreach ($_POST[$name][$i] as $selected) {
                  if (!searchDatabase($pdo, $sql, $parameter)[$selected]) {
                    $error[] = $name;
                  }
                }
              }
            } else if ($r === 5 && preg_match('/[' . $name . ']/', implode($rules[$rule][5]))) {
              // Format überprüfen und Start- und Endzeit abgleichen
              if (!preg_match('/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/', $_POST[$name][$i])) {
                $error[] = $name;
              } else if ($name === 'end_time' && $_POST['start_time'][$i] >= $_POST['end_time'][$i]) {
                $error[] = $name;
              }
            } else if ($r === 6 && preg_match('/[' . $name . ']/', implode($rules[$rule][6]))) {
              if (strlen($_POST[$name][$i]) > 500) {
                $error[] = $name;
              }
            }
          }
        }
      }
    }
  } else { // Keine Regel gefunden
    $error = true;
  }
?>
