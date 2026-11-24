<?php
require_once __DIR__ . '/config/auth.php';
Auth::logout();
header("Location: index.php");
exit;
