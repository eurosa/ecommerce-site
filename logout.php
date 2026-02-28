<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

logoutUser();
header('Location: index.php?logged_out=1');
exit;
