<?php
// Security check - delete after use
$secret = $_GET['key'] ?? '';
if ($secret !== 'raushan2024') {
    die('Unauthorized');
}

$host = 'localhost';
$user = 'comp_wp103';
$pass = 'S9u8]CI(p9';
$db   = 'comp_wp103';

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) die('Connect Error: ' . $mysqli->connect_error);

// Read the SQL file
$sql_file = __DIR__ . '/push_to_live.sql';
if (!file_exists($sql_file)) die('SQL file not found: ' . $sql_file);

$sql = file_get_contents($sql_file);
if (!$sql) die('Could not read SQL file');

echo '<pre>';
echo 'SQL file size: ' . number_format(strlen($sql)) . " bytes\n";

// Split by semicolons and execute
$mysqli->multi_query($sql);
$count = 0;
do {
    $count++;
    if ($result = $mysqli->store_result()) {
        $result->free();
    }
} while ($mysqli->more_results() && $mysqli->next_result());

if ($mysqli->errno) {
    echo "Error: " . $mysqli->error . "\n";
} else {
    echo "Import completed successfully! Queries: $count\n";
}

$mysqli->close();
echo '</pre>';

// Clean up
unlink(__FILE__);
unlink($sql_file);
echo 'Done. Files deleted.';
?>
