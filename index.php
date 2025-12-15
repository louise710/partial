<?php
session_start();
include_once "db.php"; 
$user = $_SESSION['username'];
// Check if user is logged in, if not redirect to login page
if (!isset($user)) {
    header("Location: login1.php");
    exit();
}
echo "<script>console.log('User logged in: " . htmlspecialchars($user) . "');</script>";


$log_query = "INSERT INTO devlog (dev, devfunc, action, oras, pitsa) VALUES (?, ?, CONCAT(?, ' - ', ?), CURTIME(), CURDATE())";

$log_stmt = $conn->prepare($log_query);
if ($log_stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}

// Use the username for devfunc
$log_stmt->bind_param("ssss", $dev, $user, $action, $user);

 

$log_stmt->close();
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <script src="mqttws31.js"></script>
    <title>Smart Classroom Management System</title>
    <style>
      .uniform-button {
            width: 90px; 
            height: 40px; 
            font-size: 16px; 
        }
        body{
background-image: url("assets/bg.png");
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
    <?php include 'header.php';?>
    <div id="layoutSidenav">
        <?php include 'sidenav.php';?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4">Dashboard</h1>
                    <table class="table table-default" style="background-color: #FFFFFF;">
                        <thead>
                            <tr style="background-color: whitesmoke;">
                                <th class="text-center">ROOMS</th>
                                <th class="text-center">AirCon1</th>
                                <th class="text-center">AirCon2</th>
                                <th class="text-center">Light1</th>
                                <th class="text-center">Light2</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include 'db.php';
                            $sql = "SELECT rm_no from room ORDER BY rm_no ASC";
                            $result = $conn->query($sql);
                            if ($result === false) {
                                die("ERROR executing query: " . $conn->error);
                            }
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $rm_no = htmlspecialchars($row['rm_no']);
                                    echo "<tr>";
                                    echo "<td class='text-center'>IC-{$rm_no}</td>";
                                    echo "<td class='text-center'><button class='btn btn-danger' style='pointer-events: none;' id='AC1'>OFF</button></td>";
                                    echo "<td class='text-center'><button class='btn btn-danger' style='pointer-events: none;' id='rm{$rm_no}AC2'>OFF</button></td>";
                                    echo "<td class='text-center'><button class='btn btn-danger' style='pointer-events: none;' id='rm{$rm_no}L1'>OFF</button></td>";
                                    echo "<td class='text-center'><button class='btn btn-danger' style='pointer-events: none;' id='rm{$rm_no}L2'>OFF</button></td>";
                                    echo "<td class='text-center'><button class='btn btn-danger uniform-button' style='pointer-events: none;' id='rm{$rm_no}DS'>Inactive</button></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center'>No rooms found.</td></tr>";
                            }
                            $result->free();
                            $conn->close();
                            ?>
                        </tbody>
                    </table>
                    <div id="addModal" class="modal">
                        <div class="modal-content">
                            <span class="close" onclick="closeAddModal()">&times;</span>
                            <?php include 'addclient1.php'; ?>
                        </div>
                    </div>
                    <div id="editModal" class="modal">
                        <div class="modal-content">
                            <span class="close" onclick="closeModal()">&times;</span>
                            <div id="update1"></div>
                        </div>
                    </div>
                </div>
            </main>
            <?php include 'footer.php'; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.4.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
<script src="js/scripts.js"></script>
<script src="js/datatables-simple-demo.js"></script>

<script>
    var mqtt;
var host = "10.40.71.27"; // MQTT broker IP (Arduino's IP)
var port = 1883;           // MQTT WebSocket port (make sure your broker supports WS on this port)

// Topics matching Arduino sketch
var mqttTopics = ["home/led1", "home/led2", "home/ac1", "home/ac2"];

function MQTTconnect() {
    console.log("Connecting to " + host + ":" + port);
    var clientId = "webClient-" + Math.floor(Math.random() * 10000);
    mqtt = new Paho.MQTT.Client(host, port, clientId);

    var options = {
        timeout: 3,
        onSuccess: onConnect,
        onFailure: function (e) {
            console.error("Failed to connect: ", e.errorMessage);
            setTimeout(MQTTconnect, 2000); // retry connection
        }
    };

    mqtt.onMessageArrived = onMessageArrived;
    mqtt.connect(options);
}

function onConnect() {
    console.log("Connected to MQTT broker.");
    // Subscribe to all device topics
    mqttTopics.forEach(topic => mqtt.subscribe(topic));
    restoreButtonStates();
}

// Handle incoming MQTT messages
function onMessageArrived(message) {
    console.log("Message Arrived: " + message.destinationName + " -> " + message.payloadString);

    var topic = message.destinationName;
    var payload = message.payloadString.trim().toUpperCase();

    var buttonId = "";
    switch(topic) {
        case "home/led1": buttonId = "rm1L1"; break;  // Replace '1' with your room number if dynamic
        case "home/led2": buttonId = "rm1L2"; break;
        case "home/ac1":  buttonId = "rm1AC1"; break;
        case "home/ac2":  buttonId = "rm1AC2"; break;
        default: return; // Ignore unknown topics
    }

    handleButtonStatus(buttonId, payload.toLowerCase());
}

// Publish MQTT message
function publish(topic, message) {
    if (mqtt && mqtt.isConnected()) {
        var mqttMessage = new Paho.MQTT.Message(message);
        mqttMessage.destinationName = topic;
        mqtt.send(mqttMessage);
        console.log("Published:", message, "to", topic);
    } else {
        console.error("MQTT client not connected.");
    }
}

// Toggle button state and publish MQTT command
function toggleDevice(device) {
    var button = document.getElementById(device);
    if (!button) {
        console.error("Button not found: " + device);
        return;
    }

    var currentStatus = button.innerText.toLowerCase();
    var newStatus = currentStatus === "on" ? "off" : "on";

    // Map button id to MQTT topic
    var topic = "";
    if (device.endsWith("L1")) topic = "home/led1";
    else if (device.endsWith("L2")) topic = "home/led2";
    else if (device.endsWith("AC1")) topic = "home/ac1";
    else if (device.endsWith("AC2")) topic = "home/ac2";
    else {
        console.error("Unknown device for topic mapping: " + device);
        return;
    }

    // Publish new status
    publish(topic, newStatus.toUpperCase());
    // Update UI immediately
    handleButtonStatus(device, newStatus);
}

// Update button display and store state locally
function handleButtonStatus(id, status) {
    var button = document.getElementById(id);
    if (!button) return;

    if (status === "on") {
        button.style.backgroundColor = "#04AA6D";
        button.innerText = "ON";
        button.classList.remove("btn-danger");
        button.classList.add("btn-success");
    } else if (status === "off") {
        button.style.backgroundColor = "#DC3545";
        button.innerText = "OFF";
        button.classList.remove("btn-success");
        button.classList.add("btn-danger");
    }
    localStorage.setItem(id, status);
}

// Restore saved states on page load
function restoreButtonStates() {
    var devices = ["rm1L1", "rm1L2", "rm1AC1", "rm1AC2"]; // Replace '1' with dynamic room if needed
    devices.forEach(id => {
        var status = localStorage.getItem(id);
        if (status) handleButtonStatus(id, status);
    });
}

// Initialize MQTT connection and restore states
window.onload = function() {
    MQTTconnect();
    // You can also attach click handlers to buttons here, e.g.:
    // document.getElementById('rm1L1').onclick = () => toggleDevice('rm1L1');
};

</script>
</body>
</html>
