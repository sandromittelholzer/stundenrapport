<?php
  require '../include/permission.php';

  if (isset($permission)) {
    if ($permission !== 'betreuungsperson') {
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
            <button type="button" class="delete negative" disabled>Löschen</button>
            <button type="button" class="edit" disabled>Überarbeiten</button>
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
          <a href="#zeiterfassung-erstellen"><i class="fas fa-plus"></i></a>
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
            <button type="button" class="delete negative" disabled>Löschen</button>
            <button type="button" class="edit positive" disabled>Überarbeiten</button>
            <button type="button" class="undo">Rückgängig</button>
            <button type="button" class="submit positive">Speichern und neu einreichen</button>
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
            <button type="button" class="delete negative" disabled>Löschen</button>
            <button type="button" class="edit" disabled>Überarbeiten</button>
            <button type="button" class="undo">Rückgängig</button>
            <button type="button" class="submit positive">Speichern und neu einreichen</button>
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

            <div class="section debug">
              <div class="date-of-joining">
                <h3>Eintrittsdatum</h3>
                <input type="text" name="date_of_joining" disabled>
              </div>
              <div class="abc">
                <h3>Stundenansatz</h3>
                <input type="text" name="abc" disabled>
              </div>
            </div>
          </div>

          <div class="section no">
            <h3>Mitarbeiternummer</h3>
            <input type="text" name="no" disabled>
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

      <!-- Zeiterfassung erstellen -->
      <div class="content zeiterfassung-erstellen">
        <div class="heading">
          <h1>Zeiterfassung erstellen</h1>
          <a href="#offen" class="cancel">Zurück</a>
        </div>

        <div class="description">
          <p>Bitte füllen Sie das unten stehende Formular aus, um eine Zeiterfassung zu erstellen. Beachten Sie dabei, dass Sie die Zeiterfassung jederzeit bearbeiten oder löschen können.</p>
        </div>

        <form class="time-record">
          <div class="section task">
            <h3>Tätigkeit*</h3>

            <!-- *Bereits definierte Tätigkeiten angehängt von JavaScirpt* -->
            <input type="radio" id="task-m" name="task" hidden>
            <label for="task-m">Manuell</label>

            <div class="manual-input">
              <h3>Manuelle Eingabe*</h3>
              <input type="text" name="task" placeholder="Tätigkeit eingeben" hidden>
              <input type="text" name="task_no" placeholder="Lohnartnummer eingeben" hidden>
            </div>
          </div>

          <div class="section locations">
            <h3>Standort(e)*</h3>
            <!-- *Standorte angehängt von JavaScirpt* -->
          </div>

          <div class="section date">
            <h3>Datum*</h3>
            <input type="text" name="date" placeholder="z. B. 01.01.2020">
          </div>

          <div class="section start-time">
            <h3>Startzeit*</h3>

            <!-- Radiobuttons -->
            <input type="radio" id="start-1" name="start_time" value="08:00" hidden checked>
            <label for="start-1">08:00 Uhr</label>
            <input type="radio" id="start-2" name="start_time" value="10:00" hidden>
            <label for="start-2">10:00 Uhr</label>
            <input type="radio" id="start-3" name="start_time" value="12:00" hidden>
            <label for="start-3">12:00 Uhr</label>
            <input type="radio" id="start-4" name="start_time" value="14:00" hidden>
            <label for="start-4">14:00 Uhr</label>
            <input type="radio" id="start-5" name="start_time" value="16:00" hidden>
            <label for="start-5">16:00 Uhr</label>
            <input type="radio" id="start-6" name="start_time" hidden>
            <label for="start-6">...</label>

            <input type="text" name="start_time" placeholder="Startzeit eingeben" hidden>
          </div>

          <div class="section end-time">
            <h3>Endzeit*</h3>

            <!-- Radiobuttons -->
            <input type="radio" id="end-1" name="end_time" value="08:00" hidden checked>
            <label for="end-1">08:00 Uhr</label>
            <input type="radio" id="end-2" name="end_time" value="10:00" hidden>
            <label for="end-2">10:00 Uhr</label>
            <input type="radio" id="end-3" name="end_time" value="12:00" hidden>
            <label for="end-3">12:00 Uhr</label>
            <input type="radio" id="end-4" name="end_time" value="14:00" hidden>
            <label for="end-4">14:00 Uhr</label>
            <input type="radio" id="end-5" name="end_time" value="16:00" hidden>
            <label for="end-5">16:00 Uhr</label>
            <input type="radio" id="end-6" name="end_time" hidden>
            <label for="end-6">...</label>

            <input type="text" name="end_time" placeholder="Endzeit eingeben" hidden>
          </div>

          <div class="section">
            <h3>Bemerkung (optional)</h3>
            <textarea name="comment" maxlength="500"></textarea>
          </div>

          <button type="button" id="create">Erstellen</button>
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
