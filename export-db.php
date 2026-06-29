<?php
// Quick DB export via HTTP
$host = 'localhost';
$user = 'comp_wp103';
$pass = 'S9u8]CI(p9';
$db   = 'comp_wp103';

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) die('Connect Error: ' . $mysqli->connect_error);

header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="live_export.sql"');

$tables = [];
$result = $mysqli->query("SHOW TABLES");
while ($row = $result->fetch_row()) $tables[] = $row[0];

foreach ($tables as $table) {
    // Structure
    $res = $mysqli->query("SHOW CREATE TABLE `$table`");
    $row = $res->fetch_row();
    echo "DROP TABLE IF EXISTS `$table`;\n";
    echo $row[1] . ";\n\n";

    // Data
    $res = $mysqli->query("SELECT * FROM `$table`");
    while ($row = $res->fetch_row()) {
        $vals = array_map(function($v) use ($mysqli) {
            return $v === null ? 'NULL' : "'" . $mysqli->real_escape_string($v) . "'";
        }, $row);
        echo "INSERT INTO `$table` VALUES (" . implode(',', $vals) . ");\n";
    }
    echo "\n";
}

$mysqli->close();
unlink(__FILE__);
?>
