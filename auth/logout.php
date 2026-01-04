<?php
session_start();
$_SESSION = [];
session_unset();
session_destroy();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

header("Clear-Site-Data: \"cache\", \"cookies\", \"storage\", \"executionContexts\"");

header("Location: ../auth/login.php");
exit();
?>
