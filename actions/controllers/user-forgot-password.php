<?php

if (empty($_POST)) {
  return;
}

if (empty($_POST['email'])) {
  $errorMessage = 'Please enter the email address';
  return;
}

$emailAddress = strtolower(trim($_POST['email']));

$userRecord = query(
  'SELECT `id`, `name`
   FROM `user`
   WHERE `email` = :email',
  array(
    ':email' => $emailAddress
  ))->fetch();

if (empty($userRecord)) {
  sleep(rand(0, 100));
  $errorMessage = 'There is no user with such email address';
  return;
}

$url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . '?page=user-password-recovery&email='
  . $emailAddress
  . '&hash=' . md5(SALT . $emailAddress);
sendEmail(
  'A password recovery',
  'Hello, ' . $userRecord['name']
    . "!<br/><br/>\n"
    . 'It seems you want to change your password. To do that, <a href="' . $url . '"><b>click this link</b></a>.'
    . "<br/><br/>\n"
    . '<small>If you did not plan to recover your password, then someone probably tries to hack your account.</small>'
    ,
  $emailAddress,
  $userRecord['name']);

$message = "The recovery email was sent to address <b>$emailAddress</b>. Check your mail.";