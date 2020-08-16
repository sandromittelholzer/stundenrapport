<?php
  require 'config.php';
  session_start();

  $sql = "SELECT berechtigung
            from tbl_benutzer
      inner join tbl_berechtigungen
              on tbl_benutzer.fs_berechtigung = tbl_berechtigungen.id
           where tbl_benutzer.id = {$_SESSION['id']}";

  $statement = $pdo->prepare($sql);
  $statement->execute();

  $f = $statement->fetch();

  if ($statement->rowCount()) {
    $permission = strtolower($f['berechtigung']);
    unset($f); // Reset, um in anderen Dokumenten erneut nutzen zu können
  }
?>
