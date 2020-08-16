$('a:not(.export)').click(function() {
  // Inhaltseinstellungen zurücksetzen
  $('.content').hide().removeClass('grid');
  $('input').css('border-color', '');
  $('.error-input, .message').remove();
  $('.change-password h3').hide();
  $('.change-password input:not(:last)').attr('hidden', true);
  $('.change-password input:last').prop('checked', false);

  // Tabellen aktualisieren
  updateTables();

  // Inhalt ändern und Navigation markieren
  if ($(this).closest('li')[0]) {
    $('li').removeClass('selected');
    $(this).closest('li').addClass('selected');

    $('.' + $(this).attr('href').replace('#', '')).addClass('grid');
  } else {
    $('.' + $(this).attr('href').replace('#', '')).show();
  }
});
