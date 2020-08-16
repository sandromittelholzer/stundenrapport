<?php
  require 'include/permission.php';

  // Benutzrer ist bereits angemeldet
  if (isset($permission)) {
    header('location: ansicht/' . $permission . '.php');
    exit();
  }
?>

<!DOCTYPE html>
<html lang="de" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/master.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css" rel="stylesheet"> <!-- Icons -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <title>Stundenrapport Rapperswil-Jona</title>
  </head>
  <body>
    <div class="header">
    </div>

    <form class="log-in">
      <h1>Melden Sie sich hier an</h1>
      <p>Um das Zeiterfassungstool Rapperswil-Jona nutzen zu können, müssen Sie sich mit Ihrem persönlichen Benutzerkonto anmelden.</p>
      <input type="text" name="username" placeholder="Benutzername oder E-Mail">
      <input type="password" name="password" placeholder="Passwort">

      <button type="button" id="log-in">Log-in</button>
    </form>

    <script src="js/client-side.js" charset="utf-8"></script>
    <script src="js/form.js" charset="utf-8"></script>
  </body>
</html>
