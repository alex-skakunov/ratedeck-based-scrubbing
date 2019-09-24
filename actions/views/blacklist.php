<?php

if ('admin' == $_SESSION['user']['level']) {
  include_once 'blacklist-admin.php';
}
else {
  include_once 'blacklist-user.php';
}