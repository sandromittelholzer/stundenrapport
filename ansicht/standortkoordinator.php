<?php
  require '../include/permission.php';

  if (isset($permission)) {
    if ($permission !== 'standortkoordinator') {
      header('location: ' . $permission . '.php');
      exit();
    }
  } else {
    header('location: ../index.php');
    exit();
  }
?>

<!DOCTYPE html>
<html lang="de" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/master.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css" rel="stylesheet"> <!-- Icons -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <title>Stundenrapport Rapperswil-Jona</title>
  </head>
  <body>
    <div class="header">
    </div>

    <div class="side-navigation">
      <h2>Menu</h2>

      <h3>Zeiterfassungen</h3>
      <ul>
        <li class="selected">
          <a href="#offen">Überprüfung offen</a>
        </li>
        <li>
          <a href="#bestaetigt">Bestätigt</a>
        </li>
        <li>
          <a href="#zurueckgewiesen">Zurückgewiesen</a>
        </li>
      </ul>

      <h3>Mein Konto</h3>
      <ul>
        <li>
          <i class="fas fa-cog"></i>
          <a href="#einstellungen">Einstellungen</a>
        </li>
      </ul>
      <a href="../control/log-out.php" class="log-out">Log-out</a>
    </div>


    <div class="replacing-content">
      <!-- Offene Zeiterfassung -->
      <div class="content offen">
        <div class="heading">
          <div class="helper">
            <h1>Überprüfung ausstehend</h1>
            <h2>0</h2>
          </div>
        </div>

        <!-- sortieren, filtern -->
        <div class="filter">
          <div class="dropdown">
            <input type="text" name="sort" placeholder="Sortieren nach" disabled>
            <ul>
              <li>Datum absteigend</li>
              <li>Sit amet</li>
            </ul>
          </div>

          <div class="control">
            <button type="button" class="edit" disabled>Überarbeiten</button>
            <button type="button" class="reject negative" disabled>Zurückweisen</button>
            <button type="button" class="accept positive" disabled>Bestätigen</button>
            <button type="button" class="undo">Rückgängig</button>
            <button type="button" class="submit positive">Speichern und bestätigen</button>
          </div>

          <button type="button" class="filter-options">
            <i class="fas fa-align-left"></i>
            Filter
          </button>
        </div>

        <div class="enable-scroll">
          <table class="time-record">
            <tr>
              <th>
                <input type="checkbox" name="">
              </th>
              <th class="member">
                <span>Mitarbeiter</span>
              </th>
              <th class="task">
                <span>Tätigkeit</span>
              </th>
              <th class="task-no">
                <span>Lohnart</span>
              </th>
              <th class="date">
                <span>Datum</span>
              </th>
              <th class="start-time">
                <span>Startzeit</span>
              </th>
              <th class="end-time">
                <span>Endzeit</span>
              </th>
              <th class="total">
                <span>Total</span>
              </th>
              <th class="sum">
                <span>Betrag</span>
              </th>
              <th class="comment">
                <span>Bemerkung</span>
              </th>
              <th class="created">
                <span>Erstellt</span>
              </th>
              <th class="last-modified">
                <span>Zuletzt geändert</span>
              </th>
            </tr>
          </table>
        </div>
      </div>

      <!-- Bestätigte Zeiterfassung -->
      <div class="content bestaetigt">
        <div class="heading">
          <div class="helper">
            <h1>Bestätigte Zeiterfassungen</h1>
            <h2>0</h2>
          </div>
        </div>

        <!-- sortieren, filtern -->
        <div class="filter">
          <div class="dropdown">
            <input type="text" name="sort" placeholder="Sortieren nach" disabled>
            <ul>
              <li>Datum absteigend</li>
              <li>Sit amet</li>
            </ul>
          </div>

          <div class="control">
            <button type="button" class="edit" disabled>Überarbeiten</button>
            <button type="button" class="reject negative" disabled>Zurückweisen</button>
            <button type="button" class="undo">Rückgängig</button>
            <button type="button" class="submit positive">Speichern</button>
          </div>

          <button type="button" class="filter-options">
            <i class="fas fa-align-left"></i>
            Filter
          </button>
        </div>

        <div class="enable-scroll">
          <table class="time-record">
            <tr>
              <th>
                <input type="checkbox" name="">
              </th>
              <th class="member">
                <span>Mitarbeiter</span>
              </th>
              <th class="task">
                <span>Tätigkeit</span>
              </th>
              <th class="task-no">
                <span>Lohnart</span>
              </th>
              <th class="date">
                <span>Datum</span>
              </th>
              <th class="start-time">
                <span>Startzeit</span>
              </th>
              <th class="end-time">
                <span>Endzeit</span>
              </th>
              <th class="total">
                <span>Total</span>
              </th>
              <th class="sum">
                <span>Betrag</span>
              </th>
              <th class="comment">
                <span>Bemerkung</span>
              </th>
              <th class="created">
                <span>Erstellt</span>
              </th>
              <th class="last-modified">
                <span>Zuletzt geändert</span>
              </th>
            </tr>
          </table>
        </div>
      </div>

      <!-- Zurückgewiesen Zeiterfassungen -->
      <div class="content zurueckgewiesen">
        <div class="heading">
          <div class="helper">
            <h1>Zurückgewiesen Zeiterfassungen</h1>
            <h2>0</h2>
          </div>
        </div>

        <!-- sortieren, filtern -->
        <div class="filter">
          <div class="dropdown">
            <input type="text" name="sort" placeholder="Sortieren nach" disabled>
            <ul>
              <li>Datum absteigend</li>
              <li>Sit amet</li>
            </ul>
          </div>

          <div class="control">
            <button type="button" class="edit" disabled>Überarbeiten</button>
            <button type="button" class="accept positive" disabled>Bestätigen</button>
            <button type="button" class="undo">Rückgängig</button>
            <button type="button" class="submit positive">Speichern und bestätigen</button>
          </div>

          <button type="button" class="filter-options">
            <i class="fas fa-align-left"></i>
            Filter
          </button>
        </div>

        <div class="enable-scroll">
          <table class="time-record">
            <tr>
              <th>
                <input type="checkbox" name="">
              </th>
              <th class="member">
                <span>Mitarbeiter</span>
              </th>
              <th class="task">
                <span>Tätigkeit</span>
              </th>
              <th class="task-no">
                <span>Lohnart</span>
              </th>
              <th class="date">
                <span>Datum</span>
              </th>
              <th class="start-time">
                <span>Startzeit</span>
              </th>
              <th class="end-time">
                <span>Endzeit</span>
              </th>
              <th class="total">
                <span>Total</span>
              </th>
              <th class="sum">
                <span>Betrag</span>
              </th>
              <th class="comment">
                <span>Bemerkung</span>
              </th>
              <th class="created">
                <span>Erstellt</span>
              </th>
              <th class="last-modified">
                <span>Zuletzt geändert</span>
              </th>
            </tr>
          </table>
        </div>
      </div>

      <!-- Mein Konto (Benutzereinstellungen) -->
      <div class="content einstellungen">
        <div class="heading">
          <h1>Einstellungen</h1>
        </div>

        <div class="description">
          <h3>Benutzerdaten</h3>
          <p>Sollten Sie Ihr Passwort ändern, beachten Sie bitte, dass Ihr Neues 8 oder mehr Zeichen, mindestens einen Klein- und einen Grossbuchstaben und eine Zahl enthalten muss, um den Sicherheitsrichtlinien zu entsprechen. Optional können Sie auch noch Sonderzeichen nutzen.</p>
        </div>

        <form class="settings">
          <div class="section form-of-address">
            <input type="radio" id="settings-ms" name="form_of_address" value="Frau" hidden checked>
            <label for="settings-ms">Frau</label>
            <input type="radio" id="settings-mr" name="form_of_address" value="Herr" hidden>
            <label for="settings-mr">Herr</label>
          </div>

          <div class="section personal-details">
            <div class="first-name">
              <h3>Name*</h3>
              <input type="text" name="first_name">
            </div>
            <div class="last-name">
              <h3>Nachname*</h3>
              <input type="text" name="last_name">
            </div>
          </div>

          <div class="helper">
            <div class="section username">
              <h3>Benutzername</h3>
              <input type="text" name="username" disabled>
            </div>

            <div class="section e-mail">
              <h3>E-Mail*</h3>
              <input type="text" name="e_mail">
            </div>
          </div>

          <div class="helper">
            <div class="section locations">
              <h3>Standorte</h3>
            </div>
          </div>

          <div class="section change-password">
            <h3>Neues Passwort</h3>
            <input type="text" name="password" hidden>
            <h3>Neues Passwort bestätigen</h3>
            <input type="text" name="repeat_password" hidden>

            <div class="flex">
              <label for="change-password">
                <input type="checkbox" id="change-password">
                <span>Passwort ändern</span>
              </label>
            </div>
          </div>

          <button type="button" id="save">Speichern</button>
        </form>
      </div>
    </div>

    <script src="../js/dropdown.js" charset="utf-8"></script>
    <script src="../js/table.js" charset="utf-8"></script>
    <script src="../js/prefetching-database.js" charset="utf-8"></script>
    <script src="../js/switch-content.js" charset="utf-8"></script>
    <script src="../js/form.js" charset="utf-8"></script>
    <script src="../js/client-side.js" charset="utf-8"></script>
  </body>
</html>
