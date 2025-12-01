<?php 
session_start();
session_destroy();
header("Location: index.php?module=auth&action=index&msg=logged_out");
exit;
