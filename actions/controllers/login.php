<?php

if(empty($_POST)) {
  return;
}

if (empty($_POST['email'])) {
  $errorMessage = 'Please enter the email address';
  return;
}

if (empty($_POST['password'])) {
  $errorMessage = 'Please enter the password';
  return;
}

$email = strtolower(trim($_POST['email']));
$password = strtolower(trim($_POST['password']));

$userRecord = query(
  'SELECT `id`, `name`, `is_admin`
   FROM `user`
   WHERE `email` = :email
     AND `password` = :pass',
  array(
    ':email' => $email,
    ':pass'  => md5($password)
  ))->fetch();

if (empty($userRecord)) {
  $errorMessage = 'The password is not correct. Please check and try again.';
  return;
}

$_SESSION['authenticated'] = true;
$_SESSION['user']['id'] = $userRecord['id'];
$_SESSION['user']['name'] = $userRecord['name'];
$_SESSION['user']['level'] = !empty($userRecord['is_admin']) ? 'admin' : 'user';

header('Location: index.php');