<?php

if (empty($_POST)) {
    return;
}
    
if (!empty($_POST['user_submit'])) {
    $newPassword = trim($_POST['user_password']);
    if (empty($newPassword)) {
        $errorMessage = 'The password should not be empty';
        return;
    }
    try {
        query(
          'UPDATE user SET
          `password` = :pass
          WHERE `id` = ' . $_SESSION['user']['id'],
          array(
            ':pass'     => md5(SALT . $newPassword),
          )
        );
        $message = 'The password has been successfully updated';
    }
    catch(Exception $e) {
        return $errorMessage = 'We could not set a new password to you due to an error.';
    }
}
