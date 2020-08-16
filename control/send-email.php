<?php
  // HTML-Header
  unset($headers);
  $headers[] = 'Content-type: text/html; charset=utf-8';
  $headers[] = 'From: Stundenrapport Rapperswil-Jona <noreply@srrj.ch>';

  if ($rule) {
    if (preg_match('/permission/', $rule)) {
      $subject = 'Informationen zu Ihrem neuen Benutzerkonto';
      $message = '...';
    }
  } else {
    if ($_POST['action'] === 'accept') {
      $subject = 'Ihr Zeiterfassung wurde bestätigt';
      $message = '...';
    } else {
      $subject = 'Ihr Zeiterfassung wurde zurückgewiesen';
      $message = '...';
    }
  }

  mail($to, $subject, $message, implode("\r\n", $headers));
?>
