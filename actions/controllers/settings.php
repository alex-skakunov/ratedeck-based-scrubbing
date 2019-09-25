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
    query('UPDATE `settings` SET `value`="' . md5(SALT . $newPassword) . '" WHERE `name`="password"');
    $message = 'The password has been successfully updated';
}
