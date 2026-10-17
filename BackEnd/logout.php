<?php

session_start();

$_SESSION = [];

session_destroy();

header("Location: ../FrontEnd/pages/login.html?logout=1");

exit;