$('#log-in').on('click', function() {
  // Regel definieren und Eingabefelder anbinden
  rule = $(this).closest('form').attr('class');
  form = $('form input');

  clientSide('form').then(function() {
    if (!error[0].length) {
      formData = form.serialize();
      logIn()();
    }
  });
});

$('.log-in').on('keypress',function(e) {
  // Regel definieren und Eingabefelder anbinden
  rule = $(this).closest('form').attr('class');
  form = $('form input');

  if(e.which == 13) {
    clientSide('form').then(function() {
      if (!error[0].length) {
        formData = form.serialize();
        logIn();
      }
    });
  }
});

function logIn() {
  $.post('control/log-in.php', formData, function(result) {
    result = JSON.parse(result); // In JavaScript-Objeke umwandeln

    if (result[0]) {
      $('input').css('border-color', '#F2380F');
      $('input:last').after('<span class="error-input">' + result[1] + '</span>');
    } else {
      window.location.href = result[1];
    }
  });
}

$('#create, #save').on('click', function() {
  // Regel definieren
  rule = $(this).closest('form').attr('class');
  heading = $(this).closest('.content').find('.heading');

  if (rule === 'settings') {
    form = $(this).closest('form').find('input[name="form_of_address"], input:not(:hidden):not(:disabled):not([id="change-password"])');
  } else {
    if (rule === 'user') {
      // Regel genauer definieren
      rule = $('.permission input:checked').attr('id');
    }

    form = $(this).closest('form').find('input[name="permission"], input[name="form_of_address"], input[name="locations"], input[name="location"]:checked, input[name="task"]:checked, input[name="start_time"]:checked, input[name="end_time"]:checked, :input:not(:hidden)');
  }

  clientSide('form').then(function() {
    if (!error[0].length) {
      insertData();
    }
  });
});

$(document).on('change', 'form input', function() {
  // Eingaben nach erstem Klick laufend überprüfen
  if (error !== undefined) {
    if (rule === 'settings') {
      form = $(this).closest('form').find('input[name="form_of_address"], input:not(:hidden):not(:disabled):not([id="change-password"])');
    } else {
      form = $(this).closest('form').find('input[name="permission"], input[name="form_of_address"], input[name="locations"], input[name="location"]:checked, input[name="task"]:checked, input[name="start_time"]:checked, input[name="end_time"]:checked, :input:not(:hidden)');
    }

    clientSide('form');
  }
});

// Formular an Berechtigung anpassen
$('.permission input').click(function() {
  if (this.id === 'permission-1') {
    $('.section').show();
  } else if (this.id === 'permission-2') {
    $('form .locations').show();
    $('form .date-of-joining, form .no').hide();
  } else {
    $('form .date-of-joining, form .no, .locations').hide();
  }
});

$(document).on('click', 'label', function() {
  // Passwort ändern
  if ($(this).attr('for') === 'change-password') {
    // Passwort ändern
    if ($(this).find('input').prop('checked')) {
      $(this).closest('.section').find('h3').show();
      $(this).closest('.section').find('input:not(:last)').attr('hidden', false);
    } else {
      $(this).closest('.section').find('h3').hide();
      $(this).closest('.section').find('input:not(:last)').attr('hidden', true);
      $(this).closest('.section').find('.error-input').remove();
    }
  } else { // Manuelle Eingaben
    if ($(this).attr('for') === 'task-m') {
      $('.manual-input').addClass('flex');
      $('.manual-input input').attr('hidden', false);
    } else if ($(this).attr('for').includes(6)) {
      $(this).parent().find('input:last').attr('hidden', false);
    } else {
      // Manuelle Eingaben deaktivieren
      $(this).parent().find('input:not(:hidden)').attr('hidden', true);
      $(this).parent().find('.error-input').remove();

      if ($(this).attr('for').match(/^task-[0-9]+$/)) {
        $('.manual-input').removeClass('flex');
      }
    }
  }
});

function insertData() {
  if (rule === 'settings') {
    path = '../control/update-database.php';
    msg = 'Benutzerkontoeinstellungen erfolgreich gespeichert';
  } else {
    path = '../control/insert.php';
    msg = 'Zeiterfassung erfolgreich erstellt';
  }

  formData = 'rule=' + rule + '&' + form.serialize();

  $.post(path, formData, function(result) {
    $('.content').scrollTop(0);

    // Keine Fehler
    if (result) {
      if (rule.match(/permission/)) {
        msg = $('#' + rule).val() + ' für ' + $('.user input[name="first_name"]').val()[0].toUpperCase() + $('.user input[name="first_name"]').val().toLowerCase().slice(1) + ' ' + $('.user input[name="last_name"]').val()[0].toUpperCase() + $('.user input[name="last_name"]').val().toLowerCase().slice(1) + ' erfolgreich erstellt';
      }

      $(heading).after('<span class="message">' + msg + '</span>');

      // Formular zurücksetzen
      $('form').trigger('reset');

      if (rule === 'settings') {
        $('.change-password').find('h3').hide();
        $('.change-password').find('h3, input:not(:last)').attr('hidden', true);

        userSettings();
      } else {
        $('.section').show(); // Betreuungsperson anzeigen
        $('.task, .start-time, .end-time').find('input:not(:hidden)').attr('hidden', true); // Manuelle Eingabefelder (Tätigkeit, Start- und Endzeit) deaktivieren
        $('.manual-input').removeClass('flex'); // Manuelle Tätigkeit entfernen
      }

      setTimeout(function () {
        $('.message').fadeOut(250);

        setTimeout(function() {
          $('.message').remove();
        }, 250);
      }, 5000);
    } else {
      $(heading).after('<span class="message error-color">Es gab leider ein Problem. Bitte laden Sie die Seite neu.</span>');
    }

    error = undefined; // Reset
  });
}
