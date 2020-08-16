<?php
  // Session starten
  // Sitzungsdaten löschen
  session_start();
  session_destroy();

  header('Location: ../index.php');
?>
