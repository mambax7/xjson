<?php
require_once dirname(dirname(__DIR__)) . '/mainfile.php';
if (empty($_POST) && empty($_GET)) {
    header('Location: ' . XOOPS_URL);
    exit;
} else {
    error_reporting(0);
    require_once __DIR__ . '/include/server.php';
    exit;
}
