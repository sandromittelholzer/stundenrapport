<?php
  require '../include/config.php';

  if (isset($_POST['username'], $_POST['password'])) {
    // Benutzerangaben auslesen
    $sql = "SELECT tbl_benutzer.id,
                   benutzername,
                   e_mail,
                   passwort,
                   berechtigung
              from tbl_benutzer
        inner join tbl_berechtigungen
                on tbl_benutzer.fs_berechtigung = tbl_berechtigungen.id
             where benutzername = :username or e_mail = :username";

    $statement = $pdo->prepare($sql);

    $statement->bindParam(':username', $_POST['username']);
    $statement->execute();

    $f = $statement->fetch();

    // Log-in-Daten überprüfen
    if (password_verifY($_POST['password'], $f['passwort'])) {
      session_start();
      $_SESSION['id'] = $f['id'];

      $result = [
        0,
        '../ansicht/' . strtolower($f['berechtigung']) . '.php'
      ];

      echo json_encode($result);
    } else {
      $result = [
        1,
        'Das Benutzernamen oder das Passwort ist ungültig.'
      ];

      echo json_encode($result);
    }
  }
?>
