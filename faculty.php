 <?php
session_start();

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
                    <h1 class="mt-4">FACULTY MANAGEMENT</h1>
                <h1 class="mt-4"></h1>
                <div class="card mb-4"> 
                    <div class="card-header">
                        <i class="fas fa-table me-1" style="padding: .5%"></i>
                        <button class="btn btn-success btn-sm" id= "addM" onclick="addModal()" style="position: absolute; right: 2%;">Add Faculty</button>
                    </div>
                    <div class="card-body">
                        <?php
    include 'db.php'; 
    
    $sql = "SELECT * FROM faculty";
    
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
                    <!--<th>Faculty ID</th>-->
                    <!--<th>rfid</th>-->
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Status</th>
                    <th>Operations</th>
                </tr>
            </thead>
            <tbody>";
    
    // Loop through the result set
    while ($row = $result->fetch_assoc()) {
        $buttonText = ($row["stat"] == 1) ? 'Active' : 'Inactive';
        $buttonClass = ($row["stat"] == 1) ? 'btn-success' : 'btn-danger';
        echo "<tr>
                <!--<td>" . $row["fidno"] . "</td>-->
                <!--<td>" . $row["rfid"] . "</td>-->
                <td>" . $row["fname"] . "</td>
                <td>" . $row["lname"] . "</td>
                <td><button id='statusBtn_" . $row["fid"] . "' class='btn $buttonClass' data-status='" . $row["stat"] . "'>$buttonText</button></td>
                <td><button class='btn btn-primary btn-sm' onclick=\"openModal({$row['fid']})\">Update</button>
                <button class='btn btn-danger btn-sm' onclick=\"confirmDelete('{$row['fid']}')\">Delete</button></td>
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

                                <?php include 'facadd.php'; ?>
                            </div>
                        </div>
                    <div id="editModal" class="modal">
                            <div class="modal-content">
                                <span class="close" onclick="closeModal()">&times;</span>
                                <div id="facupdate"></div>
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
        var fid = this.id.split('_')[1];
        var currentStatus = $(this).data('status');

        // Store the context in a variable for use in the AJAX callback
        var btnContext = this;

        // Make an AJAX request to update the status
        $.ajax({
            type: 'POST',
            url: 'statupdate.php',
            data: { fid: fid, currentStatus: currentStatus },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var buttonText = (response.newStatus == 1) ? 'Active' : 'Inactive';

                    $(btnContext).text(buttonText);
                    $(btnContext).data('status', response.newStatus);
                    $(btnContext).removeClass('btn-success btn-danger');

                    if (response.newStatus == 1) {
                        $(btnContext).addClass('btn-success');
                    } else {
                        $(btnContext).addClass('btn-danger');
                    }
                } else {
                    // For error
                    if (currentStatus == 1) {
                        alert('Disabling. Please refresh page.');
                    } else {
                        alert('Enabling. Please refresh page.');
                    }
                }
            },
            error: function() {
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

    function confirmDelete(fid) {
        var confirmDelete = confirm('Are you sure you want to delete?');
        if (confirmDelete) {
            window.location.href = 'facremove.php?fid=' + fid;
        }
    }

    function openModal(fid) {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("facupdate").innerHTML = this.responseText;
                document.getElementById("editModal").style.display = "block";
            }
        };
        xhttp.open("GET", "facupdate.php?fid=" + fid, true);
        xhttp.send();
    }

    function closeModal() {
        document.getElementById("editModal").style.display = "none";
    }
        function updateStatus(fid, currentStatus) {
    var newStatus = currentStatus == 1 ? 0 : 1;
    $.ajax({
        type: 'POST',
        url: 'facupdate.php',
        data: { fid: fid, stat: newStatus },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // for Updating button appearance
                var button = document.getElementById("status-btn-" + fid);
                button.className = newStatus == 1 ? "active-button" : "inactive-button";
                button.innerHTML = newStatus == 1 ? "Active" : "Inactive";
                button.setAttribute("onclick", "updateStatus(" + fid + ", " + newStatus + ")");
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