<?php

/**
 * Ожидание готовности MySQL перед миграциями.
 * Пробует подключиться до N секунд, затем завершается с ошибкой,
 * чтобы orchestrator (podman compose) перезапустил контейнер.
 */

$host = getenv('DB_HOST') ?: 'mysql';
$port = (int) (getenv('DB_PORT') ?: '3306');
$database = getenv('DB_DATABASE') ?: 'vitteza';
$user = getenv('DB_USERNAME') ?: 'vitteza';
$password = getenv('DB_PASSWORD') ?: 'secret';

$timeout = (int) (getenv('DB_WAIT_TIMEOUT') ?: '60');
$started = time();

while (true) {
    mysqli_report(MYSQLI_REPORT_OFF);
    $mysqli = @new mysqli($host, $user, $password, $database, $port);
    if ($mysqli->connect_error === null) {
        $mysqli->close();
        fwrite(STDOUT, "[wait-for-db] MySQL is ready ({$host}:{$port})\n");
        exit(0);
    }
    if (time() - $started >= $timeout) {
        fwrite(STDERR, "[wait-for-db] MySQL did not become ready in {$timeout}s: " . $mysqli->connect_error . "\n");
        exit(1);
    }
    fwrite(STDOUT, "[wait-for-db] waiting for MySQL ({$host}:{$port})...\n");
    sleep(2);
}
