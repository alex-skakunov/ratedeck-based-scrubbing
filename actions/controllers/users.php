<?php

check_admin_access();

if (!empty($_POST)) {
  $user = query('SELECT * FROM `user` WHERE `email` = :email',
  array(
    ':email'    => strtolower(trim($_POST['email']))
  ))->fetch();

  if (!empty($user)) {
    $errorMessage = 'User with this email already exists: <i>' . trim($_POST['email']) . '</i>';
  }
  else {
    query(
      'INSERT INTO user VALUES (NULL, :email, :pass, :nome, :is_admin)',
      array(
        ':email'    => strtolower(trim($_POST['email'])),
        ':pass'     => md5(trim($_POST['password'])),
        ':nome'     => trim($_POST['name']),
        ':is_admin' => !empty($_POST['is_admin']) ? 1 : 0,
      )
    );
  }
}

$usersList = query('SELECT * FROM user');