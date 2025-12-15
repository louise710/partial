<?php
$broker = "172.20.10.2";  // Your Mosquitto broker IP

// Define LED topics
$ledTopics = ["home/led1", "home/led2", "home/led3", "home/led4"];

// Arrays to store outputs and return codes
$output = [];
$returnCodes = [];

foreach ($ledTopics as $index => $topic) {
    // Example: subscribe once to check status (optional)
    $cmdSub = "mosquitto_sub -h $broker -t $topic -C 1";
    exec($cmdSub, $output[$index], $returnCodes[$index]);

    // Example: publish a test message (here OFF)
    $cmdPub = "mosquitto_pub -h $broker -t $topic -m 'OFF'";
    exec($cmdPub, $output[$index], $returnCodes[$index]);
}

// Check if all commands succeeded
$allSuccess = true;
foreach ($returnCodes as $code) {
    if ($code !== 0) {
        $allSuccess = false;
        break;
    }
}

if ($allSuccess) {
    echo "All MQTT commands executed successfully for 4 LEDs.";
} else {
    echo "Error executing one or more MQTT commands for LEDs.";
}
?>
