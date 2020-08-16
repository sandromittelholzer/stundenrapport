function close() {
  $('.dropdown ul').hide();
}

$('.dropdown').click(function() {
  if ($(this).children('ul').css('display') === 'none') {
    $(this).attr('tabindex', 1).focus();
    $(this).children('ul').show();
  } else {
    close();
  }
});

$('.dropdown').focusout(function() {
  close();
});

$('.dropdown ul li').click(function() {
  $(this).parent().prev().val($(this).text());
});
