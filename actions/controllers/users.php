<?php
check_admin_access();

if (!empty($_POST)) {
  $emailAddress = strtolower(trim($_POST['email']));
  $user = query('SELECT * FROM `user` WHERE `email` = :email',
  array(
    ':email'    => $emailAddress
  ))->fetch();

  if (!empty($user)) {
    $errorMessage = 'User with this email already exists: <i>' . $emailAddress . '</i>';
  }
  else {
    try {
      $name = trim($_POST['name']);
      $password = trim($_POST['password']);
      query(
        'INSERT INTO user VALUES (NULL, :email, :pass, :nome, DEFAULT, :is_admin, :user_id, NOW(), NULL)',
        array(
          ':email'    => $emailAddress,
          ':pass'     => md5(SALT . $password),
          ':nome'     => $name,
          ':is_admin' => !empty($_POST['is_admin']) ? 1 : 0,
          ':user_id'  => (int)$_SESSION['user']['id'],
        )
      );
      $userId = $db->lastInsertId();
      query("CREATE TABLE `blacklist_user_$userId` LIKE `blacklist_master`");

      $url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'];
      sendEmail(
        'You have got an account',
        'Hello, ' . $name
          . "!<br/><br/>\n"
          . 'You have been registered at <a href="' . $url . '">' . $url . '</a>'
          . "<br/><br/>\n"
          . 'Here are the log in credentials:'
          . "<br/><br/>\n"
          . 'Email: <code>' . $emailAddress . '</code>'
          . "<br/>\n"
          . 'Password: <code>' . $password . '</code>'
          . "<br/><br/>\n"
          . 'Click the link above and enter these details into the login form!'
          ,
        $emailAddress,
        $name);
    }
    catch(Exception $e) {
      $errorMessage = $e->getMessage();
    }
  }
}

$usersList = query('SELECT * FROM user');