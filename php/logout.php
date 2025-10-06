<?php
session_start();
session_destroy();
header("Location: ../into/login.html");
exit;
?>
