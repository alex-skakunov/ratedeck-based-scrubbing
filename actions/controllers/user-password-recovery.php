<?php

if (empty($_GET['email']) || empty($_GET['hash'])) {
  $errorMessage = 'This is a wrong password recovery link.';
  return;
}

$emailAddress = strtolower(trim($_GET['email']));
$hash = trim($_GET['hash']);

$userRecord = query(
  'SELECT `id`, `name`
   FROM `user`
   WHERE `email` = :email',
  array(
    ':email' => $emailAddress
  ))->fetch();

if (empty($userRecord)) {
  sleep(rand(0, 100));
  $errorMessage = 'This is a wrong password recovery link.';
  return;
}

if ($hash != md5(SALT . $emailAddress)) {
  sleep(rand(0, 100));
  $errorMessage = 'This is a wrong password recovery link.';
}

$newPassword = dechex(rand(10000, 100000));

try {
  query(
    'UPDATE user SET
    `password` = :pass
    WHERE `id` = ' . $userRecord['id'],
    array(
      ':pass'     => md5(SALT . $newPassword),
    )
  );
}
catch(Exception $e) {
  return $errorMessage = 'We could not set a new password to you due to an error.';
}

$baseUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'];
$loginUrl = $baseUrl . '?page=login';
$settingsUrl = $baseUrl . '?page=settings';

sendEmail(
  'Your new password',
  'Hello, ' . $userRecord['name']
    . "!<br/><br/>\n"
    . "Your new password is: <code>$newPassword</code>"
    . "<br/><br/>\n"
    . 'You can login here — <a href="' . $loginUrl . '">' . $loginUrl . '</a>'
    . "<br/><br/>\n"
    . 'You can change password at the Settings page after you login — <a href="' . $settingsUrl . '">' . $settingsUrl . '</a>'
    ,
  $emailAddress,
  $userRecord['name']);

$message = "An email with a new password was sent to <b>$emailAddress</b>.<br/>Check your mail.";