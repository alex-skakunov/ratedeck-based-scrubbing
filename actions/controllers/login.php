<?php

if(empty($_POST)) {
  return;
}

if (empty($_POST['password'])) {
  $errorMessage = 'Please enter the password';
  return;
}

$password = strtolower(trim($_POST['password']));

$passwordFromSettings = query('SELECT `value` FROM `settings` WHERE `name`="password"')->fetchColumn();

if ($passwordFromSettings != md5($password)) {
  $errorMessage = 'The password is not correct. Please check and try again.';
  return;
}

$_SESSION['authenticated'] = true;
header('Location: index.php');