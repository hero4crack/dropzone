<?php
require_once '../includes/session.php';

$sessionManager = new SessionManager();
$sessionManager->destroySession();

header('Location: login.php');
exit();
?>