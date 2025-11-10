<?php
// Executes init.sql against the local MySQL server using credentials matching db.php

$host = 'localhost';
$user = 'root';
$pass = '';
$sqlFile = __DIR__ . DIRECTORY_SEPARATOR . 'init.sql';

if (!file_exists($sqlFile)) {
    echo "init.sql not found at $sqlFile\n";
    exit(1);
}

$sql = file_get_contents($sqlFile);
if ($sql === false) {
    echo "Failed to read init.sql\n";
    exit(1);
}

$mysqli = new mysqli($host, $user, $pass);
if ($mysqli->connect_error) {
    echo "Connection failed: " . $mysqli->connect_error . PHP_EOL;
    exit(1);
}

if ($mysqli->multi_query($sql)) {
    do {
        // flush results
        if ($res = $mysqli->store_result()) {
            $res->free();
        }
    } while ($mysqli->more_results() && $mysqli->next_result());

    if ($mysqli->errno) {
        echo "SQL error: " . $mysqli->error . PHP_EOL;
        exit(1);
    }

    // verify
    if ($mysqli->select_db('enrollment_db')) {
        $r = $mysqli->query("SELECT COUNT(*) AS c FROM users");
        if ($r) {
            $row = $r->fetch_assoc();
            echo "Database initialized. users table contains: " . ($row['c'] ?? 0) . " row(s)\n";
            exit(0);
        }
    }

    echo "Database initialized (could not verify users count).\n";
    exit(0);
} else {
    echo "Failed to execute init.sql: " . $mysqli->error . PHP_EOL;
    exit(1);
}
