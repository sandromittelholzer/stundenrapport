let cSelected; // Markierte Checkboxen
let beforeEditing = []; // Einstellungen zwischenspeichern
let trTd = [];
let filter;

function changeButtons() {
  let elements = $(filter).find('.control button, .control a');
  cSelected = [
    [],
    []
  ];
  let uSelected = [];

  // "Speichern" nicht anzeigen
  $(filter).find('.control button').show();
  $(filter).find('.control button:nth-last-child(-n+2)').not(':nth-child(-n+2)').hide();

  // Markierte Reihe(n) finden
  $('td input:checked').each(function(i) {
    cSelected[0].push($(this).closest('tr:nth-child(n+2)'));
    cSelected[1].push($(this).closest('tr:nth-child(n+2)').attr('id')); // Um Daten zu serialisieren
  });

  // URL generieren (Export)
  $.each(cSelected[1], function(i, id) {
    uSelected.push($.param({'user[]': id}));
  });

  $(filter).find('.control a').attr('href', '../export/lohnarten-tagesstruktur.php' + (uSelected.length ? '?' + uSelected.join('&') : ''));

  for (let i = 0; i < elements.length; i++) {
    if (elements.length !== 1) {
      startText = $(elements[i]).html().replace(/[(0-9)]/g, '');
    } else {
      startText = 'Exportieren (alle)';
    }

    if (cSelected[0].length) {
      $(elements[i]).html(startText.replace(/\(alle\)/, '') + ' (' + cSelected[0].length + ')').prop('disabled', false);
    } else {
      $(elements[i]).html(startText).prop('disabled', true);
    }
  }
}

function toDefault() {
  // Checkboxen aktivieren und markierte Checkbox(en) abwählen
  $('th:first-child input, td:first-child input').prop('disabled', false);
  $('table input:checked').prop('checked', false);

  // Inhalt neu positionieren
  $('.align-fix').removeClass();

  // Fehlermeldung(en) entfernen
  $('.error-input').remove();

  changeButtons();
  error = undefined; // Reset
}

function undo() {
  if (cSelected) {
    // Gespeicherte Daten laden
    cSelected[0].forEach((tr, i) => {
      tr.find('td:nth-child(n+2) input').each(function(n) {
        $(this).prop('disabled', true); // Eingabefeler deaktivieren

        // Nicht gepsicherte Einträge zurücksetzen
        if (beforeEditing.length) {
          if ($(this).val() !== beforeEditing[i][n]) {
            $(this).val(beforeEditing[i][n]);
          }
        }
      });
    });

    toDefault();
  }
}

$('.edit').click(function() {
  // "Rückgängig" und "Speichern" anzeigen
  $('.filter .control button').hide();
  $('.filter .control button:nth-last-child(-n+2)').show();

  // Eingabefeler aktivieren und Einstellungen zwischenspeichern
  cSelected[0].forEach((tr, i) => {
    beforeEditing[i] = [];

    tr.find('td:nth-child(n+2) input').each(function() {
      beforeEditing[i].push($(this).val());

      $(this).prop('disabled', false);

      // Bindestrich entfernen
      if ($(this).val().substr($(this).val().length - 1) === '-') {
        $(this).val('');
      }
    });

    // Inhalt neu positionieren
    $(tr).find('td:first').addClass('align-fix');
    $(tr).find('span').parent().addClass('align-fix');
  });

  $('th input, td:first-child input').prop('disabled', true); // Checkboxen deaktivieren
});

// Eingabeänderungen
$(document).on('change', 'input', function() {
  if (error !== undefined) { // Eingaben nach erstem Klick laufend überprüfen
    clientSide('table');
  }
});

// Checkboxänderungen
$(document).on('change', 'th input, td:first-child input', function () {
  if ($(this).is(':checked')) {
     // Checkboxen aktivieren
    if ($(this).closest('tr').is(':first-child')) {
      $(this).closest('table').find('td:first-child input').prop('checked', true);
    }
  } else {
    // Checkboxen deaktivieren
    if ($(this).closest('tr').is(':first-child')) {
      $('td:first-child input').prop('checked', false);
    } else {
      $('tr:first-child input').prop('checked', false); // Erste Checkbox deaktivieren
    }
  }

  filter = $(this).closest('.content').find('.filter');
  changeButtons();
});

$('.undo').click(undo);

$('.submit').click(function() {
  clientSide('table').then(function() {
    if (!error[0].length) {
      let formData = 'action=submit';

      if (!error[0].length) {
        // Markierte Reihe(n) zuweisen
        $.each(cSelected[1], function(i, id) {
          formData += '&id[]=' + id;
        });

        formData = 'rule=edit-' + $('input:checked').closest('table').attr('class') + '&' + formData + '&' + $('input:checked').closest('tr').find('td:nth-child(n+2) input').serialize();

        $.post('../control/update-database.php', formData, function(result) {
          // Keine Fehler
          if (result) {
            updateTables();
          }

          error = undefined; // Reset
        });
      }
    }
  });
});

$('.delete').click(function() {
  let formData = 'action=delete';

  // Markierte Reihe(n) zuweisen
  $.each(cSelected[1], function(i, id) {
    formData += '&id[]=' + id;
  });

  $.post('../control/update-database.php', formData, function(result) {
    // Keine Fehler
    if (result) {
      updateTables();
    }
  });
});

$('.accept').click(function() {
  let formData = 'action=accept';

  // Markierte Reihe(n) zuweisen
  $.each(cSelected[1], function(i, id) {
    formData += '&id[]=' + id;
  });

  $.post('../control/update-database.php', formData, function(result) {
    // Keine Fehler
    if (result) {
      updateTables();
    }
  });
});

$('.reject').click(function() {
  let formData = 'action=reject';

  // Markierte Reihe(n) zuweisen
  $.each(cSelected[1], function(i, id) {
    formData += '&id[]=' + id;
  });

  $.post('../control/update-database.php', formData, function(result) {
    // Keine Fehler
    if (result) {
      updateTables();
    }
  });
});

$('.reset-password').click(function() {
  let formData = 'action=reset-password';

  // Markierte Reihe(n) zuweisen
  $.each(cSelected[1], function(i, id) {
    formData += '&id[]=' + id;
  });

  $.post('../control/update-database.php', formData, function(result) {
    // Keine Fehler
    if (result) {
      updateTables();
    }
  });
});
