<?php

if ('admin' == $_SESSION['user']['level']) {
  include_once 'scrubbing-admin.php';
}
else {
  include_once 'scrubbing-user.php';
}