<?php
session_start();
require_once '../config/db_conn.php';
$_SESSION = [];
session_unset();
session_destroy();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

header("Clear-Site-Data: \"cache\", \"cookies\", \"storage\", \"executionContexts\"");

header("Location: " . BASE_URL . "auth/login.php");
exit();
?>