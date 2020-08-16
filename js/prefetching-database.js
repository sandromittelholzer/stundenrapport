defaults();
updateTables();

function defaults() {
  $.getJSON('../control/load-assigned-data.php', function(result) {
    let permission = result[0]['fs_berechtigung']; // Berechtigung auslesen
    let html = [];

    if (permission.match(/(1|2)/)) {
      if (permission == 1) {
        // Tätigkeiten aus Datenbank
        result[1].forEach((task, i) => {
          html.push('<input type="radio" id="task-' + (i + 1) + '" name="task" value="' + task[0] + '" hidden><label for="task-' + (i + 1) + '">' + task[0] + '</label>');
        });

        $('.time-record .task #task-m').before(html);
        $('.time-record .task input:first').attr('checked', true);
      }

      // Zugewiesene(r) Standort(e)
      html = [
        [],
        []
      ];

      result[2].forEach((location, i) => {
        html = [
          [...html[0], '<span>' + location[0] + '</span>'],
          [...html[1], '<input type="radio" id="location-' + (i + 1) + '" name="location" value="' + location[0] + '" hidden><label for="location-' + (i + 1) + '">' + location[0] + '</label>']
        ];
      });

      $('.content:nth-child(4) .locations').append(html[0]);
      $('.content:nth-child(5) .locations').append(html[1]);

      $('.locations input:first').attr('checked', true);
    } else if (permission == 3) {
      // Standorte aus Datenbank
      result[1].forEach((location, i) => {
        html.push('<div class="flex"><label for="locations-' + (i + 1) + '"><input type="checkbox" id="locations-' + (i + 1) + '" name="locations[]" value="' + location[0] + '"><span>' + location[0] + '</span></label></div>');
      });

      $('.user .locations').append(html);
    }
  });
}


function userSettings() {
  $.getJSON('../control/load-assigned-data.php', function(result) {
    // Paramter übersetzen
    let translation = {'form_of_address': 'anrede', 'first_name': 'name', 'last_name': 'nachname', 'username': 'benutzername', 'e_mail': 'e_mail', 'date_of_joining': 'eintrittsdatum-formatiert', 'abc': 'stundenansatz', 'no': 'mitarbeiternummer'}

    // Einstellungen laden
    if (result[0]['anrede'] === 'Herr') {
      $('#settings-mr').prop('checked', true);
    }

    $('.settings').find('.section:not(.form-of-address):not(.change-password) input').each(function() {
      $(this).val(result[0][translation[this.name]]);
    });
  });
}

function updateTables() {
  $.getJSON('../control/load-assigned-data.php', function(result) {
    // Tabellenreihe(n) entfernen
    $('table tr:nth-child(n+2)').html('');

    $('tr').each(function() {
      if(!$(this).html().length) {
        $(this).remove();
      }
    });

    if (result && result[3]) {
      let permission = result[0]['fs_berechtigung']; // Berechtigung auslesen
      let html = [
        [],
        [],
        []
      ];

      // Tabelle generieren
      result[3].forEach((tr) => {
        let dates = ['datum', 'erstellt', 'zuletzt_geaendert'];
        let cellNull = ['bemerkung', 'zuletzt_geaendert'];

        // Status ermitteln
        if (permission == 1) { // Betreuungsperson
          if (tr['ueb_standortkoordinator'] == 0 && tr['ueb_superuser'] != 2 || tr['ueb_superuser'] == 0 && tr['ueb_standortkoordinator'] != 2) { // Überprüfung ausstehend (min. 1 Benutzer nocht nicht bestätigt)
            state = 0;
          } else if (tr['ueb_standortkoordinator'] == 1 && tr['ueb_superuser'] == 1) { // Zeiterfassung bestätigt
            state = 1;
          } else { // Zeiterfassung zurückgewiesen
            state = 2;
          }
        } else if (permission == 2) { // Standortkoordinator
          if (tr['ueb_standortkoordinator'] == 0) {
            state = 0;
          } else if (tr['ueb_standortkoordinator'] == 1) {
            state = 1;
          } else {
            state = 2;
          }
        } else { // Superuser
          if (tr['ueb_superuser'] == 0) {
            state = 0;
          } else if (tr['ueb_superuser'] == 1) {
            state = 1;
          } else {
            state = 2;
          }
        }

        // Start- und Endzeit ohne Sekunden
        tr['startzeit'] = tr['startzeit'].slice(0, -3);
        tr['endzeit'] = tr['endzeit'].slice(0, -3);

        // Zeitdifferenz Start- und Endzeit berechnen
        let timestamp = (new Date(tr['datum'] + 'T' + tr['endzeit']) - new Date(tr['datum'] + 'T' + tr['startzeit'])) / 1000;
        let minutes = timestamp / 60;
        let hours = Math.floor(minutes / 60);
        minutes = minutes % 60;

        // Zeitdifferenz umwandeln (z. B. 8:5 in 8 h 5 min)
        hours = hours ? hours + ' h ' : '';
        minutes = minutes ? minutes + ' min' : '';
        tr['total'] = $.trim(hours + minutes); // Unnötige Leerzeichen entfernen

        // Datum umwandeln
        dates.forEach((date) => {
          if (tr[date] !== null) {
            s = tr[date].split('-');
            tr[date] = s[2] + '.' +  s[1] + '.' + s[0];
          }
        });

        // Leere Zelle ersetzen
        cellNull.forEach((cell) => {
          tr[cell] = tr[cell] ? tr[cell] : '-';
        })

        // Tabellenreihe(n) generieren
        if (permission == 1) {
          html[state].push('<tr id="' + tr['id'] + '"><td><input type="checkbox"></td><td><input type="text" name="task[]" value="' + tr['taetigkeit'] + '" disabled></td><td><input type="text" name="task_no[]" value="' + tr['lohnart'] +'" disabled></td><td><input type="text" name="date[]" value="' + tr['datum'] +'" disabled></td><td><input type="text" name="start_time[]" value="' + tr['startzeit'] +'" disabled></td><td><input type="text" name="end_time[]" value="' + tr['endzeit'] +'" disabled></td><td><span>' + tr['total'] + '</span></td><td><span>CHF ' + tr['betrag'] + '.-' + '</span></td><td><input type="text" name="comment[]" value="' + tr['bemerkung'] + '" maxlength="500" disabled>     </td><td><span>' + tr['erstellt'] + '</span></td><td><span>' + tr['zuletzt_geaendert'] + '</span></td></tr>');
        } else {
          html[state].push('<tr id="' + tr['id'] + '"><td><input type="checkbox"></td><td><span>' + tr['name'] + ' ' + tr['nachname'] + '</span></td><td><input type="text" name="task[]" value="' + tr['taetigkeit'] +'" disabled></td><td><input type="text" name="task_no[]" value="' + tr['lohnart'] +'" disabled></td><td><input type="text" name="date[]" value="' + tr['datum'] +'" disabled></td><td><input type="text" name="start_time[]" value="' + tr['startzeit'] +'" disabled></td><td><input type="text" name="end_time[]" value="' + tr['endzeit'] +'" disabled></td><td><span>' + tr['total'] + '</span></td><td><span>CHF ' + tr['betrag'] + '.-' + '</span></td><td>    <input type="text" name="comment[]" value="' + tr['bemerkung'] + '" maxlength="500" disabled>     </td><td><span>' + tr['erstellt'] + '</span></td><td><span>' + tr['zuletzt_geaendert'] + '</span></td></tr>');
        }
      });

      // Tabellen einsetzen
      html.forEach((table, i) => {
        $('.content:nth-child(' + (i + 1) + ') table').append(table);
        $('.content:nth-child(' + (i + 1) + ') .heading h2').text(table.length);
      });
    } else {
      $('.content .heading h2').text('0'); // Keinen Tabelleninhalt
    }

    // Benutzerkontos (nur für Superuser)
    if (result && result[4]) {
      let html = [
        [],
        []
      ];

      result[4].forEach((tr) => {
        let dates = ['eintrittsdatum'];
        let cellNull = ['e_mail', 'eintrittsdatum', 'mitarbeiternummer'];

        // Datum umwandeln
        dates.forEach((date) => {
          if (tr[date] !== null) {
            s = tr[date].split('-');
            tr[date] = s[2] + '.' +  s[1] + '.' + s[0];
          }
        });

        // Leere Zelle ersetzen
        cellNull.forEach((cell) => {
          tr[cell] = tr[cell] ? tr[cell] : '-';
        });

        // Tabellenreihe(n) generieren
        if (tr['berechtigung'] === 'Betreuungsperson') {
          html = [
            [...html[0], '<tr id="' + tr['id'] + '"><td><input type="checkbox"></td></td><td><input type="text" name="first_name[]" value="' + tr['name'] + '" disabled></td><td><input type="text" name="last_name[]" value="' + tr['nachname'] + '" disabled></td><td><span>' + tr['benutzername'] + '</span></td><td><input type="text" name="e_mail[]" value="' + tr['e_mail'] + '" disabled></td><td><span>' + tr['berechtigung'] + '</span></td><td><input type="text" name="date_of_joining[]" value="' + tr['eintrittsdatum'] + '" disabled></td><td><input type="text" name="no[]" value="' + tr['mitarbeiternummer'] + '" disabled></td></tr>'],
            [...html[1], '<tr id="' + tr['id'] + '"><td><input type="checkbox"></td><td><span>' + tr['name'] + ' ' + tr['nachname'] + '</span></td><td><span>' + tr['benutzername'] + '</span></td><td><span>' + tr['e_mail'] + '</span></td><td><span>' + tr['eintrittsdatum'] + '</span></td><td><span>' + tr['mitarbeiternummer'] + '</span></td></tr>']
          ];
        } else {
          html[0].push('<tr id="' + tr['id'] + '"><td><input type="checkbox"></td></td><td><input type="text" name="first_name[]" value="' + tr['name'] + '" disabled></td><td><input type="text" name="last_name[]" value="' + tr['nachname'] + '" disabled></td><td><span>' + tr['benutzername'] + '</span></td><td><input type="text" name="e_mail[]" value="' + tr['e_mail'] + '" disabled></td><td><span>' + tr['berechtigung'] + '</span></td><td><span>' + tr['eintrittsdatum'] + '</span></td><td><span>' + tr['mitarbeiternummer'] + '</span></td></tr>');
        }
      });

      // Tabellen einsetzen
      $('.content:nth-child(5) table').append(html[0]);
      $('.content:nth-child(6) table').append(html[1]);
      $('.content:nth-last-child(-n+3):not(:last-child) .heading h2').text(html[0].length);
    }
  });

   // Tabelle zurücksetzen und Benutzerkontoeinstellungen (neu)laden
  toDefault();
  userSettings();
}
