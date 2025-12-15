<?php
session_start();
include_once "db.php"; 

$user = $_SESSION['username'] ?? null;
$data = json_decode(file_get_contents("php://input"), true);

// Debugging output for error
// echo '<pre>';
// print_r($data);
// echo '</pre>';

// ini_set('display_errors', 1);
// error_reporting(E_ALL);

// header('Content-Type: application/json');

// // Example response data
// $response = array("status" => "success", "message" => "Command executed successfully");

// // Send the JSON response
// echo json_encode($response);
// exit;  // Ensure no further output is sent after the JSON response


if (!$user) {
    header("Location: login1.php");
    exit();
}

// Debugging
echo "<script>console.log('User logged in: " . htmlspecialchars($user) . "');</script>";
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
        html, body {
            height: 100%; 
            margin: 0; 
        }

        body {
            background-image: url("assets/bg.png");
            background-repeat: no-repeat;
            background-size: cover; 
            background-position: center;
            background-attachment: fixed; 
        }
        #addModal .modal-content,
        #editModal .modal-content {
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
        }

        #addModal .modal-content {
            width: 45%;
            height: auto;
        }

        #editModal .modal-content {
            width: 45%;
            height: auto;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            display: block;
            text-align: right;

        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            font-family: var(--bs-font-sans-serif);
            margin-bottom: 5%;
        }

        th {
            background-color: white;
            color: black;
            padding: 12px;
            text-align: left;
        }

        td {
            padding: 6px;
            border: 1px solid #ddd;
            text-align: left;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #ddd;
        }
        tr.dev_header{
            background-color: #103D20;
            color: #FEC039;
        }
        tr.dev_header th.text-center {
            color: #fff;  
        }
        button:hover {
            background-color: #04AA6D;
            color: white;
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
                    <h1 class="mt-4">IC-<?php echo $selectedrm_no; ?></h1>
                    <table class="table table-default" style="background-color: #fff;">
                        <input type="hidden" id="roomId" value="<?php echo $selectedrm_no; ?>">
                        <tr class = "dev_header">
                            <th class="text-center">Devices</th>
                            <th class="text-center">Power Status</th>
                            <th class="text-center">Device Status</th>
                        </tr>
                        <tr>
                            <td class="text-center">Light 1</td>
                            <td class="text-center"><button class="btn btn-danger" onclick="execute('led1')" id="rm<?php echo $selectedrm_no; ?>led1">OFF</button></td>
                            <td class="text-center"><button class="btn btn-danger" id="statled1">OFF</button></td>
                        </tr>
                        <tr>
                            <td class="text-center">Light 2</td>
                            <td class="text-center"><button class="btn btn-danger" onclick="execute('led2')" id="rm<?php echo $selectedrm_no; ?>led2">OFF</button></td>
                            <td class="text-center"><button class="btn btn-danger" id="statled2">OFF</button></td>
                        </tr>
                        <tr>
                            <td class="text-center">AirCon 1</td>
                            <td class="text-center"><button class="btn btn-danger" onclick="execute('AC1')" id="rm<?php echo $selectedrm_no; ?>AC1">OFF</button></td>
                            <td class="text-center"><button class="btn btn-danger" id="statac1">OFF</button></td>
                        </tr>
                        <tr>
                            <td class="text-center">AirCon 2</td>
                            <td class="text-center"><button class="btn btn-danger" onclick="execute('AC2')" id="rm<?php echo $selectedrm_no; ?>AC2">OFF</button></td>
                            <td class="text-center"><button class="btn btn-danger" id="statAC2">OFF</button></td>
                        </tr>
                    </table>
                    <h3 style="margin-top: 3%;">SCHEDULE</h3>
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-clock" style="padding: .5%"></i>
                            <button class="btn btn-success btn-sm" onclick="addModal()" style="position: absolute; right: 2%;">Add Schedule</button>
                        </div>
                        <div class="card-body">
                            <?php
                            include 'db.php';

                            $rm_id = isset($_GET['id']) ? intval($_GET['id']) : null;

                            if ($rm_id === null) {
                                die("Invalid or missing 'id' parameter.");
                            }

                            echo "<script>console.log('Initial ROOM ID from GET: " . $rm_id . "');</script>";

                            $sql = "SELECT sched.*, faculty.fname, faculty.lname, sub.code
                            FROM sched 
                            JOIN faculty ON sched.fid = faculty.fid 
                            JOIN sub ON sched.sub_id = sub.sid 
                            WHERE sched.rm_id = ? 
                            ORDER BY sched.day, sched.time_in ASC";
                            
                            $stmt = $conn->prepare($sql);
                              
                            if ($stmt === false) {
                                die("Error preparing statement: " . $conn->error);
                            }

                            $stmt->bind_param('i', $rm_id);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if ($result === false) {
                                die("Error executing query: " . $stmt->error);
                            }

                            // Check if there are results and output them
                            if ($result->num_rows > 0) {
                                echo "<table id='datatablesSimple' class='table'>
                                    <thead>
                                        <tr>
                                            <th>Time In</th>
                                            <th>Time Out</th>
                                            <th>Day</th>
                                            <th>Faculty Name</th>
                                            <th>Subject</th>
                                            <th>Operation</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>";

                                while ($row = $result->fetch_assoc()) {
                                    $initial = substr($row["fname"], 0, 1);
                                    // echo "<pre>";
                                    //     var_dump($row);
                                    // echo "</pre>";
                                    $buttonText = ($row["stat"] == 1) ? 'Active' : 'Inactive';
                                    $buttonClass = ($row["stat"] == 1) ? 'btn-success' : 'btn-danger';
                                    echo "<tr>
                                        <td>" . htmlspecialchars($row["time_in"]) . "</td>
                                        <td>" . htmlspecialchars($row["time_out"]) . "</td>
                                        <td>" . htmlspecialchars($row["day"]) . "</td>
                                        <td>" . htmlspecialchars($initial . ". " . $row["lname"]) . "</td>
                                        <td>" . htmlspecialchars($row["code"]) . "</td> 
                                        <td>
                                          <button class='btn btn-primary btn-sm' onclick='openModal(" . $row['sched_id'] . ", " . $row['rm_id'] . ")'>Update</button>
                                            <button class='btn btn-danger btn-sm' onclick=\"confirmDelete('{$row['sched_id']}')\">Delete</button>
                                        </td>
                                        <td><button id='statusBtn_" . $row["sched_id"] . "' class='btn $buttonClass' data-status='" . $row["stat"] . "'>$buttonText</button></td>
                                    </tr>";
                                }

                                echo "</tbody></table>";
                            } else {
                                echo "<p>No schedules found for this room.</p>";
                            }

                            $stmt->close();
                            $conn->close();
                            ?>
                        </div>
                        <div id="addModal" class="modal">
                            <div class="modal-content">
                                <span class="close" onclick="closeAddModal()">&times;</span>
                                <?php include 'schedadd.php'; ?>
                            </div>
                        </div>
                        <div id="editModal" class="modal">
                            <div class="modal-content">
                                <span class="close" onclick="closeModal()">&times;</span>
                                <div id="schedupdate"></div>
                            </div>
                        </div>
                    </div>
            </main>
            <?php include 'footer.php'; ?>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script>
var mqtt;
var host = "10.40.71.27"; // ESP8266 MQTT broker
var port = 1883;           // Standard MQTT port
var roomId = "<?php echo $selectedrm_no; ?>"; // Room ID from PHP


// LED topics mapping
var ledTopics = {
    "led1": "home/IC-1/led1",
    "led2": "home/IC-1/led2",
    "ac1": "home/IC-1/ac1",
    "ac2": "home/IC-1/ac2"
};


// Connect to MQTT broker
function MQTTconnect() {
    console.log("Connecting to MQTT broker " + host + ":" + port);
    var clientId = "webClient-" + Math.floor(Math.random() * 1000);
    mqtt = new Paho.MQTT.Client(host, port, clientId);

    var options = {
        timeout: 3,
        onSuccess: onConnect,
        onFailure: function (message) {
            console.error("MQTT Connection failed: " + message.errorMessage);
            setTimeout(MQTTconnect, 2000); // Retry after 2s
        }
    };

    mqtt.onMessageArrived = onMessageArrived;
    mqtt.connect(options);
}

// Subscribe to LED topics after successful connection
function onConnect() {
    console.log("Connected to MQTT broker");
    for (var key in ledTopics) {
        mqtt.subscribe(ledTopics[key]);
    }
    restoreButtonStates(); // Restore buttons from localStorage
}

// Handle incoming MQTT messages
function onMessageArrived(message) {
    console.log("Message received:", message.destinationName, message.payloadString);
    var topic = message.destinationName;
    var payload = message.payloadString.trim().toLowerCase(); // "on" or "off"

    // Find the button that corresponds to this topic
    for (var key in ledTopics) {
        if (ledTopics[key] === topic) {
            var button = document.getElementById(`rm${roomId}${key.toUpperCase()}`);
            if (button) updateButton(button, payload);
            break;
        }
    }
}

// Update button appearance and save state
function updateButton(button, status) {
    if (!button) return;
    if (status === "on") {
        button.style.backgroundColor = "#04AA6D";
        button.innerText = "ON";
        button.classList.remove("btn-danger");
        button.classList.add("btn-success");
    } else {
        button.style.backgroundColor = "#DC3545";
        button.innerText = "OFF";
        button.classList.remove("btn-success");
        button.classList.add("btn-danger");
    }
    localStorage.setItem(button.id, status);
}

// Handle button clicks
function execute(device) {
    var button = document.getElementById(`rm${roomId}${device}`);
    if (!button) return;

    var currentStatus = button.innerText.toLowerCase();
    var newStatus = (currentStatus === "on") ? "off" : "on";

    var topic = ledTopics[device.toLowerCase()];
    if (!topic) return;

    var message = new Paho.MQTT.Message(newStatus.toUpperCase());
    message.destinationName = topic;
    mqtt.send(message);
    console.log(`Published "${newStatus}" to ${topic}`);

    updateButton(button, newStatus); // Update immediately
}

// Restore previous button states from localStorage
function restoreButtonStates() {
    var buttons = document.querySelectorAll("button[id^='rm']");
    buttons.forEach(button => {
        var stored = localStorage.getItem(button.id);
        if (stored) updateButton(button, stored);
    });
}

// Initialize MQTT and restore buttons on page load
window.onload = function() {
    MQTTconnect();
    restoreButtonStates();
};
function addModal() {
    document.getElementById("addModal").style.display = "block";
}
function closeAddModal() {
    document.getElementById("addModal").style.display = "none";
}

function confirmDelete(sched_id) {
    var confirmDelete = confirm('Are you sure you want to delete?');
    if (confirmDelete) {
        window.location.href = 'schedremove.php?sched_id=' + sched_id;
    }
}

function openModal(sched_id, rm_id) {
    var xhttp = new XMLHttpRequest();
    let rm_id1 = rm_id;
  
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("schedupdate").innerHTML = this.responseText;
            document.getElementById("editModal").style.display = "block";
        }
    };
  
    xhttp.open("GET", "schedupdate.php?sched_id=" + sched_id + "&rm_id=" + rm_id1, true);

    xhttp.send();
}

function closeModal() {
    document.getElementById("editModal").style.display = "none";
}

</script>



</body>

</html>
