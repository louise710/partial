<?php
echo '<meta http-equiv="refresh" content="60">';

$conn = new mysqli("localhost", "root", "", "test");
if ($conn->connect_error) {
    die("DB error: " . $conn->connect_error);
}

date_default_timezone_set("Asia/Manila");

/* Get current day & time */
$currentDay  = date("D");     // Mon Tue Wed...
$currentTime = date("H:i");   // 16:15

$dayMap = [
    "Mon" => "M",
    "Tue" => "T",
    "Wed" => "W",
    "Thu" => "Th",
    "Fri" => "F",
    "Sat" => "S",
    "Sun" => "Su"
];

$todayLetter = $dayMap[$currentDay];

/* SQL: match time within the SAME minute (handles seconds) */
$sql = "
SELECT *
FROM selectedtimes
WHERE specificday = '$todayLetter'
AND specifictime >= CONCAT('$currentTime', ':00')
AND specifictime <  ADDTIME(CONCAT('$currentTime', ':00'), '00:01:00')
";

$result = $conn->query($sql);

if (!$result || $result->num_rows == 0) {
    exit(); // nothing scheduled this minute
}

require("phpMQTT.php");

$server = "10.40.71.27"; // PC IP
$port   = 1883;

$mqtt = new Bluerhinos\phpMQTT($server, $port, "phpScheduler_" . uniqid());

if (!$mqtt->connect()) {
    exit();
}

while ($row = $result->fetch_assoc()) {

    $device  = $row['light'];    // led1
    $command = $row['command'];  // ON / OFF

    $topic = "home/IC-1/" . $device;

    $mqtt->publish($topic, $command, 0);
}

$mqtt->close();
$conn->close();
