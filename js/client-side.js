let rules;
let error;

// Clientseitige Formular- und Tabellenüberprüfung
function clientSide(obj) {
  // Lohnarten
  let taskNos = ['1280', '1282', '1284', '1285'];
  let assignedTaskNos = {
    '1280': ['mittagsverpflegung'],
    '1282': ['mittagsbetreuung'],
    '1284': ['nachmittagsbetreuung', 'ferienbetreuung', 'betreuungslektionen'],
    '1285': ['morgenbetreuung']
  }

  if (obj === 'form') {
    // Kategorisierte Regeln
    rules = [
      ['username', 'password', 'repeat_password', 'first_name', 'last_name', 'task'],
      ['e_mail'],
      ['date_of_joining', 'date'],
      ['no', 'task_no'],
      ['locations[]'],
      ['start_time', 'end_time']
    ];
    // Reset
    error = [
      [],
      {}
    ];
    let characters = [
      '0-9',
      'a-z'
    ];
    let pattern = '(?=.*[' + characters[0] + '])(?=.*[' + characters[1] + '])(?=.*[' + characters[1].toUpperCase() + '])';

    form.each(function() {
      if (rules[0].includes(this.name)) {
        if (this.id === 'task-m') {
          $(this).val($('.manual-input input[name="task"]').val());
        }

        if ($(this).val().length === 0) {
          error[0].push(this.name);
        }

        if (rule === 'settings' && this.name.match(/password/)) {
          let data = [
            $('[name="password"]').val(),
            $('[name="repeat_password"]').val()
          ];

          if (!error[0].includes(this.name) && this.name === 'repeat_password' && data[0] !== data[1]) {
            error = [[...error[0], this.name], {...error[1], [this.name]: 'Bitte bestätigen Sie Ihr neues Passwort'}];
          }

          if (this.name === 'password') {
            if (data[0].length !== 0) {
              if (data[0].length < 8) {
                error = [[...error[0], this.name], {...error[1], [this.name]: 'Das Passwort muss mindestens 8 Zeichen enthalten'}];
              } else if (!data[0].match(new RegExp(pattern))) {
                error = [[...error[0], this.name], {...error[1], [this.name]: 'Das Passwort entspricht nicht unseren Sicherheitsrichtlinien'}];
              }
            }
          }
        }
      } else if (rules[1].includes(this.name)) {
        if (!$(this).val().toLowerCase().match(/^[a-z0-9]+(?:[a-z0-9._-]+[a-z0-9]+)?@+[a-z0-9]+(?:[._-][a-z0-9]+)*\.[a-z0-9]+$/)) {
          error[0].push(this.name);
        } else {
          let s = [
            $(this).val().split('@'),
            $(this).val().split('@')[1].split('.')
          ];

          if ($(this).val().length > 254 || s[0][0].length > 64) {
            error[0].push(this.name);
          } else {
            for (let i = 0; i < s[1].length; i++) {
              if (s[1][i].length > 64) {
                error[0].push(this.name);
                break;
              } else if (i === s[1].length - 1) {
                if (s[1][i].length < 2) {
                  error[0].push(this.name);
                }
              }
            }
          }
        }
      } else if (rules[2].includes(this.name)) {
        // Datum konvertieren und auf Gültigkeit prüfen
        let s = $(this).val().split('.');
        let date = new Date(s[2] + '-' + s[1] + '-' + s[0]);

        if (s[2] !== undefined && s[2].toString().length !== 4 || isNaN(date)) {
          error[0].push(this.name);
        }
      } else if (rules[3].includes(this.name)) {
        if (!$(this).val().match(/^[0-9]+$/)) {
          error[0].push(this.name);
        } else if (this.name === 'task_no') {
          if (!taskNos.includes($('input[name="task_no"]').val())) {
            error = [[...error[0], this.name], {...error[1], [this.name]: 'Bitte gültige Lohnart eingegebenen (1280, 1282, 1284 oder 1285)'}];
          } else {
            let c = 0;

            $.each(assignedTaskNos, function() {
              if (!this.includes($('input[name="task"]:checked').val().toLowerCase())) {
                c++;
              }
            });

            if (c !== Object.entries(assignedTaskNos).length) {
              if (!assignedTaskNos[$(this).val()].includes($('input[name="task"]:checked').val().toLowerCase())) {
                error = [[...error[0], this.name], {...error[1], [this.name]: 'Die Lohnart für die eingegebene Tätigkeit ist ungültig'}];
              }
            }
          }
        }
      } else if (rules[4].includes(this.name)) {
        if ($('input[name="locations[]"]:checked').length === 0 && !error[0].includes('locations[]')) {
          error[0].push(this.name);
        }
      } else if (rules[5].includes(this.name)) {
        if (this.id.split('-')[1] == 6) {
          $(this).val($('input[name="' + this.name + '"]:not(:hidden)').val());

          if (!$(this).val().match(/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/)) {
            error[0].push(this.name);
          }
        }

        // Start- und Endzeit abgleichen
        if (this.name === 'end_time' && !error[0].includes('start_time') && !error[0].includes('end_time')) {
          if ($('input[name="start_time"]:checked').val() >= $('input[name="end_time"]:checked').val()) {
            error = [[...error[0], this.name], {...error[1], [this.name]: 'Endzeit muss nach der Startzeit liegen'}];
          }
        }
      }
    });
  } else {
    // Kategorisierte Regeln
    rules = [
      ['first_name[]', 'last_name[]', 'task[]'],
      ['e_mail[]'],
      ['date_of_joining[]', 'date[]'],
      ['no[]', 'task_no[]'],
      ['start_time[]', 'end_time[]']
    ];
    // Reset
    error = [
      [],
      {}
    ];
    trTd = [];

    cSelected[0].forEach((tr, i) => {
      trTd[i] = tr.map(function() {
        return $(this).find('td:not(:first-child) input');
      });
    });

    for (let i = 0; i < trTd.length; i++) {
      trTd[i][0].each(function() {
        if (rules[0].includes(this.name)) {
          if ($(this).val().length === 0) {
            error[0].push(this);
          }
        } else if (rules[1].includes(this.name)) {
          if (!$(this).val().toLowerCase().match(/^[a-z0-9]+(?:[a-z0-9._-]+[a-z0-9]+)?@+[a-z0-9]+(?:[._-][a-z0-9]+)*\.[a-z0-9]+$/)) {
            error[0].push(this);
          } else {
            let s = [
              $(this).val().split('@'),
              $(this).val().split('@')[1].split('.')
            ];

            if ($(this).val().length > 254 || s[0][0].length > 64) {
              error[0].push(this);
            } else {
              for (let i = 0; i < s[1].length; i++) {
                if (s[1][i].length > 64) {
                  error[0].push(this);
                  break;
                } else if (i === s[1].length - 1) {
                  if (s[1][i].length < 2) {
                    error[0].push(this);
                  }
                }
              }
            }
          }
        } else if (rules[2].includes(this.name)) {
          // Datum konvertieren und auf Gültigkeit prüfen
          let s = $(this).val().split('.');
          let date = new Date(s[2] + '-' + s[1] + '-' + s[0]);

          if (s[2] !== undefined && s[2].toString().length !== 4 || isNaN(date)) {
            error[0].push(this);
          }
        } else if (rules[3].includes(this.name)) {
          if (!$(this).val().match(/^[0-9]+$/)) {
            error[0].push(this);
          } else {
            if (this.name === 'task_no[]') {
              if (!taskNos.includes($(this).val())) {
                error = [[...error[0], this], {...error[1], [this.name]: 'Bitte gültige Lohnart eingegebenen (1280, 1282, 1284 oder 1285)'}];
              } else {
                // Überprüfen, ob Lohnart für eingegebene Tätigkeit gültig ist
                let c = 0;

                $.each(assignedTaskNos, function() {
                  if (!this.includes(cSelected[0][i].find('input[name="task[]"]').val().toLowerCase())) {
                    c++;
                  }
                });

                if (c !== Object.entries(assignedTaskNos).length) {
                  if (!assignedTaskNos[$(this).val()].includes(cSelected[0][i].find('input[name="task[]"]').val().toLowerCase())) {
                    error = [[...error[0], this], {...error[1], [this.name]: 'Die Lohnart für die eingegebene Tätigkeit ist ungültig'}];
                  }
                }
              }
            }
          }
        } else if (rules[4].includes(this.name)) {
           // Ungültiges Format (hh:mm)
          if (!$(this).val().match(/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/)) {
            error[0].push(this);
          } else if (this.name === 'end_time[]' && !error[0].includes(cSelected[0][i].find('input[name="start_time[]"]')[0]) && !error[0].includes(cSelected[0][i].find('input[name="end_time[]"]')[0])) { // Start- und Endzeit abgleichen
            if (cSelected[0][i].find('input[name="start_time[]"]').val() >= cSelected[0][i].find('input[name="end_time[]"]').val()) {
              error = [[...error[0], this], {...error[1], [this.name]: 'Endzeit muss nach der Startzeit liegen'}];
            }
          }
        }
      });
    }
  }

  errorHandling(obj);
  return $.Deferred().resolve(error);
}

// Error handling (Fehlermeldung(en) ausgeben)
function errorHandling(obj) {
  if (obj === 'form') {
    form.each(function() {
      let c = $('input[name="' + this.name + '"]').not(':hidden');
      let l = $(this).parent().find(':last');

      if (error[0].includes(this.name)) {
        // Standardfehlermeldung definieren
        if (!error[1][this.name]) {
          error[1][this.name] = 'Bitte Eingabefeld überprüfen';
        }

        if (!rules[4].includes(this.name)) {
          if (!rules[5].includes(this.name)) {
            if (!c.next().hasClass('error-input')) {
              c.after('<span class="error-input">' + error[1][this.name] + '</span>');
            } else {
              c.next().text(error[1][this.name]); // Fehlermeldung aktualisieren
            }
          } else { // Start- und Endzeit
            if (!l.hasClass('error-input')) {
              l.after('<span class="error-input">' + error[1][this.name] + '</span>');
            } else {
              l.text(error[1][this.name]);
            }
          }
        } else { // Checkboxen
          c.css('border-color', '#FF0404');
        }
      } else {
        if (!rules[4].includes(this.name)) {
          if (!rules[5].includes(this.name)) {
            c.next('.error-input').remove();
          } else {
            $(this).parent().find('.error-input').remove();
          }
        } else { // Checkboxen
          c.css('border-color', '');
        }
      }
    });
  } else {
    for (let i = 0; i < trTd.length; i++) {
      trTd[i][0].each(function() {
        let c = $(this);

        if (error[0].includes(this)) {
          // Standardfehlermeldung definieren
          if (!error[1][this.name]) {
            error[1][this.name] = 'Bitte Eingabefeld überprüfen';
          }

          if (!c.next().hasClass('error-input')) {
            c.after('<span class="error-input">' + error[1][this.name] + '</span>');
          } else {
            c.parent().find('span').text(error[1][this.name]); // Fehlermeldung aktualisieren
          }
        } else {
          c.next('.error-input').remove();
        }
      });
    }
  }
}
