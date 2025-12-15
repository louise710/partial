<?php
require("phpMQTT.php");

$server = "10.40.71.27";
$port   = 1883;

$mqtt = new Bluerhinos\phpMQTT(
    $server,
    $port,
    "phpTest_" . uniqid()
);

if ($mqtt->connect()) {

    echo "Connected to MQTT broker<br>";

    $topic   = "home/IC-1/led1";
    $message = "OFF";

    $mqtt->publish($topic, $message, 0);

    echo "Published: $topic → $message<br>";

    $mqtt->close();

} else {
    echo "MQTT connection FAILED";
}
