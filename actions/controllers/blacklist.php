<?php

if (!empty($_GET['table_erased'])) {
  $message = ucfirst($_GET['table_erased']) . ' DNC list was erased';
}


if ('admin' == $_SESSION['user']['level']) {
  include_once 'blacklist-admin.php';
}
else {
  include_once 'blacklist-user.php';
}