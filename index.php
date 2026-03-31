<?php
include_once 'config/db_conn.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    $_SESSION['main_animation_played'] = null;
    $redirect_url = BASE_URL . "dashboard/"; 
} else {
    $redirect_url = BASE_URL . "auth/login.php"; 
}

header("Location: " . $redirect_url);
exit();
?>
<!--
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="2;url=<?php // echo $redirect_url; ?>">
    <title>Welcome to TraceMo</title>
    <link rel="icon" type="image/png" href="/resources/img/favicon.png">
    <link rel="stylesheet" href="../ncash-tracemo/resources/css/colors.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            flex-direction: column;
            background-color: var(--main-content); 
            font-family: 'Inter', sans-serif;
        }

        .loader {
            width: 48px;
            height: 48px;
            border: 5px solid var(--main-content);
            border-bottom-color: var(--red-dark);
            border-radius: 50%;
            display: inline-block;
            box-sizing: border-box;
            animation: rotation 1s linear infinite;
            margin-bottom: 20px;
        }

        @keyframes rotation {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        p {
            font-size: 18px;
            color: #333;
        }
    </style>
</head>
<body>
  <div class="loader"></div>
  <p>Please wait, redirecting...</p>
</body>
</html>