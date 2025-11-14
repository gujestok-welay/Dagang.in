<?php
$hash = '$2y$10$4Zd.6cGBXifDnyOtNzpjV.LxLMIaNQR/mk9LqtXDm.jLn2NBNguC.';
$password = '123';
echo 'Hash: ' . $hash . PHP_EOL;
echo 'Password: ' . $password . PHP_EOL;
echo 'Verify: ' . (password_verify($password, $hash) ? 'True' : 'False') . PHP_EOL;
?>