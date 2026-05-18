<?php
session_start();

// Destroy the session
session_destroy();

// Redirect to signin page
header('Location: ../php/signIn.php');
exit;
?>
