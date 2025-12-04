<?php
session_start();
session_unset();
session_destroy();
if (isset($_COOKIE["remember"])) {
    setcookie("remember", "", time() - 3600, "/", false, true);
}
echo json_encode(['status' => 'success']);
exit;
