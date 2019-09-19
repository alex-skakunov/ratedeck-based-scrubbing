<?php

header('Content-Type: application/json');

$userId = (int)$_POST['user_id'];
if (empty($userId)) {
  exit(json_encode(array('success' => 0, 'error_message' => 'No user ID given')));
}
$maxPrice = (float)$_POST['max_price'];
$data = array('success' => 1);
try {
  query(
    'UPDATE user SET `max_price` = :max_price WHERE `id` = ' . $userId,
    array(
      ':max_price' => !empty($maxPrice) ? $maxPrice : null,
    )
  );
  $data['max_price'] = !empty($maxPrice) ? number_format($maxPrice, 2) : null;
}
catch(Exception $e) {
  $data['success'] = 0;
  $data['error_message'] = $e->getMessage();
}
exit(json_encode($data));