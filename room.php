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
            width: 37%;
            height: auto;
            }

            #editModal .modal-content {
                margin: 5% auto;
                padding: 20px;
                border: 1px solid #888;
                width: 45%;
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
            button:hover{
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
                    <h1 class="mt-4">ROOM MANAGEMENT</h1>
                <h1 class="mt-4"></h1>
                <div class="card mb-4"> 
                    <div class="card-header">
                        <i class="fas fa-door-open" style="padding: .5%"></i>
                        <button class="btn btn-success btn-sm" onclick="addModal()" style="position: absolute; right: 2%;">Add Room</button>
                    </div>
                    <div class="card-body">
                        <?php
                        include 'db.php'; 
                        
                        $sql = "SELECT * FROM room ORDER BY rm_no ASC";
                        
                        $stmt = $conn->prepare($sql);
                        
                        if ($stmt === false) {
                            die("Error preparing statement: " . $conn->error);
                        }
                        
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result === false) {
                            die("Error executing query: " . $stmt->error);
                        }
                        
                        echo "<table id='datatablesSimple' class='table'>
                                <thead>  
                                    <tr>
                                        
                                        <th>Room Number</th>
                                        <th>Room Description</th>
                                        <th>Lights</th>
                                        <th>AC</th>
                                        <th>Operations</th>
                                    </tr>
                                </thead>
                                <tbody>";
                        
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    
                                    <td>" . $row["rm_no"] . "</td>
                                    <td>" . $row["rm_desc"] . "</td>
                                    <td>" . $row["lights"] . "</td>
                                    <td>" . $row["ac"] . "</td>
                                    <td>
                                        <button class='btn btn-primary btn-sm' onclick=\"openModal(" . htmlspecialchars($row['rm_id']) . ")\">Update</button>
                                        <button class='btn btn-danger btn-sm' onclick=\"confirmDelete('" . htmlspecialchars($row['rm_id']) . "')\">Delete</button>
                                    </td>
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

                                <?php include 'roomadd.php'; ?>
                            </div>
                        </div>
                    <div id="editModal" class="modal">
                            <div class="modal-content">
                                <span class="close" onclick="closeModal()">&times;</span>
                                <div id="roomupdate"></div>
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
        var idno = this.id.split('_')[1];
        var currentStatus = $(this).data('status');

        var btnContext = this;

        $.ajax({
            type: 'POST',
            url: 'statupdate.php',
            data: { idno: idno, currentStatus: currentStatus },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Toggle button
                    var buttonText = (response.newStatus == 1) ? 'Enabled' : 'Disabled';

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

    function confirmDelete(rm_id) {
        var confirmDelete = confirm('Are you sure you want to delete?');
        if (confirmDelete) {
            window.location.href = 'roomremove.php?rm_id=' + rm_id;
        }
    }

    function openModal(rm_id) {
        // Use AJAX to fetch edit content
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("roomupdate").innerHTML = this.responseText;
                document.getElementById("editModal").style.display = "block";
            }
        };
        xhttp.open("GET", "roomupdate.php?rm_id=" + rm_id, true);
        xhttp.send();
    }

    function closeModal() {
        document.getElementById("editModal").style.display = "none";
    }
</script>

</body>
</html>