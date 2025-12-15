 <?php
session_start();

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION["username"])) {
    header("Location: login1.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
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
            #addModal .modal-content {
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 40%;
            height: auto;
            }

            #editModal .modal-content {
                margin: 5% auto;
                padding: 20px;
                border: 1px solid #888;
                width: 40%;
                height: auto;
            }

            .close {
                color: #aaa;
                font-size: 28px;
                font-weight: bold;
                display: block;
                margin-left: auto;

            }

            .close:hover,
            .close:focus {
                color: black;
                text-decoration: none;
                cursor: pointer;
            }
            table{
                border-collapse: collapse;
                width: 100%;
                font-family: var(--bs-font-sans-serif);
                margin-bottom: 5%;
            }
            th{
                background-color: white;
                color: black;
                padding-top: 12px;
                padding-bottom: 12px;
                text-align: center;

            }
            tr td{
                padding: 6px;
                border: 1px solid #ddd;
            }
            tr{
                text-align: left;
                text-align: center;
            }
            tr:nth-child(even){
                background-color: #f2f2f2;
            }
            tr:hover{
                background-color: #ddd;
            }
            #addM:hover{
                background-color: #04AA6D;
                color: white;
            }
            .active-button {

            background-color: #04AA6D;
            color: white;
            border: none;
            padding: 5px 10px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            margin: 2px 1px;
            cursor: pointer;
            border-radius: 4px;
        }

        .inactive-button {
            background-color: #DC3545;
            color: white;
            border: none;
            padding: 5px 10px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            margin: 2px 1px;
            cursor: pointer;
            border-radius: 4px;
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
                    <h1 class="mt-4">USER MANAGEMENT</h1>
                <h1 class="mt-4"></h1>
               <!--  <table class="table table-default">
        <tr class>
            <td class="text-center">
                <button class="btn btn-danger" onclick="execute()" id="mqttButton">Light 1</button>
            </td>
            <td class="text-center">
                <button class="btn btn-danger" id="statusButton">Light 2</button>
                <script>
                    MQTTconnect();
                </script>
            </td>
        </tr>
        <tr>
            <td class="text-center">
                <button class="btn btn-danger">AC 1</button>
            </td>
            <td class="text-center">
                <button class="btn btn-danger">AC 2</button>
            </td>
        </tr>
    </table> -->
                <div class="card mb-4"> 
                    <div class="card-header">
                        <i class="fas fa-table me-1" style="padding: .5%"></i>
                        <button class="btn btn-success btn-sm" id= "addM" onclick="addModal()" style="position: absolute; right: 2%;">Add User</button>
                    </div>
                    <div class="card-body">
                        <?php
    include 'db.php'; 
    
    $sql = "SELECT * FROM user";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result === false) {
        die("Error executing query: " . $stmt->error);
    }
    
    // Output table data
    echo "<table id='datatablesSimple' class='table'>
            <thead>  
                <tr>
                    <th>User ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Username</th>
                    <th>Status</th>
                    <th>Operations</th>
                </tr>
            </thead>
            <tbody>";
    
    // Loop through the result set
    while ($row = $result->fetch_assoc()) {
        $buttonText = ($row["ustat"] == 1) ? 'Active' : 'Inactive';
        $buttonClass = ($row["ustat"] == 1) ? 'btn-success' : 'btn-danger';
        echo "<tr>
                <td>" . $row["user_id"] . "</td>
                <td>" . $row["fname"] . "</td>
                <td>" . $row["lname"] . "</td>
                <td>" . $row["username"] . "</td>
                <td><button id='statusBtn_" . $row["user_id"] . "' class='btn $buttonClass' data-status='" . $row["ustat"] . "'>$buttonText</button></td>
                <td><button class='btn btn-primary btn-sm' onclick=\"openModal({$row['user_id']})\">Update</button>
                <button class='btn btn-danger btn-sm' onclick=\"confirmDelete('{$row['user_id']}')\">Delete</button></td>
              </tr>";
    }
    
    echo "</tbody>
        </table>";
    
    $stmt->close();
    
    $conn->close();
?>

                    </div>
                    <div id="addModal" class="modal">
                            <div class="modal-content">
                                <span class="close" onclick="closeAddModal()">&times;</span>

                                <?php include 'useradd.php'; ?>
                            </div>
                        </div>
                    <div id="editModal" class="modal">
                            <div class="modal-content">
                                <span class="close" onclick="closeModal()">&times;</span>
                                <div id="userupdate"></div>
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
    $('#datatablesSimple').on('click', 'button[id^="statusBtn_"]', function() {
        // Extract idno and currentStatus from the button id
        var user_id = this.id.split('_')[1];
        var currentStatus = $(this).data('status');

        // Store the context in a variable for use in the AJAX callback
        var btnContext = this;

        // Make an AJAX request to update the status
        $.ajax({
            type: 'POST',
            url: 'userstatupdate.php',
            data: { user_id: user_id, currentStatus: currentStatus },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Toggle button
                    var buttonText = (response.newStatus == 1) ? 'Active' : 'Inactive';

                    // Update the button text
                    $(btnContext).text(buttonText);

                    // Update the data-status attribute
                    $(btnContext).data('status', response.newStatus);

                    // Remove existing Bootstrap classes
                    $(btnContext).removeClass('btn-success btn-danger');

                    // Add new Bootstrap class based on the updated status
                    if (response.newStatus == 1) {
                        $(btnContext).addClass('btn-success');
                    } else {
                        $(btnContext).addClass('btn-danger');
                    }
                } else {
                    // For error
                    if (currentStatus == 1) {
                        // Handle error for disabling
                        alert('Disabling. Please refresh page.');
                    } else {
                        // Handle error for enabling
                        alert('Enabling. Please refresh page.');
                    }
                }
            },
            error: function() {
                // Handle the AJAX error
                alert('Error communicating with the server. Please try again.');
            }
        });
    });
    function addModal() {
        document.getElementById("addModal").style.display = "block";
    }

    function closeAddModal() {
        document.getElementById("addModal").style.display = "none";
    }

    function confirmDelete(user_id) {
        var confirmDelete = confirm('Are you sure you want to delete?');
        if (confirmDelete) {
            window.location.href = 'user_remove.php?user_id=' + user_id;
        }
    }

    function openModal(user_id) {
        // Use AJAX to fetch edit content
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("userupdate").innerHTML = this.responseText;
                document.getElementById("editModal").style.display = "block";
            }
        };
        xhttp.open("GET", "userupdate.php?user_id=" + user_id, true);
        xhttp.send();
    }

    function closeModal() {
        document.getElementById("editModal").style.display = "none";
    }
        function updateStatus(user_id, currentStatus) {
    var newStatus = currentStatus == 1 ? 0 : 1;
    $.ajax({
        type: 'POST',
        url: 'userupdate.php',
        data: { user_id: user_id, ustat: newStatus },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Update button appearance
                var button = document.getElementById("status-btn-" + user_id);
                button.className = newStatus == 1 ? "active-button" : "inactive-button";
                button.innerHTML = newStatus == 1 ? "Active" : "Inactive";
                button.setAttribute("onclick", "updateStatus(" + user_id + ", " + newStatus + ")");
            } else {
                alert('Failed to update status. Please try again.');
            }
        },
        error: function() {
            alert('Error communicating with the server. Please try again.');
        }
    });
}
</script>

</body>
</html>