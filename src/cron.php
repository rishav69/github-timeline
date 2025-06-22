<?php
echo "// trigger commit";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

file_put_contents(__DIR__ . '/cron_debug.txt', date('Y-m-d H:i:s') . " — cron.php started\n", FILE_APPEND);

require __DIR__ . '/functions.php';

file_put_contents(__DIR__ . '/cron_debug.txt', "Functions loaded\n", FILE_APPEND);

sendGitHubUpdatesToSubscribers();

file_put_contents(__DIR__ . '/cron_debug.txt', "Function call finished\n", FILE_APPEND);
