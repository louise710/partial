
<?php
include 'db.php';

$sql = "SELECT devstat FROM systatus LIMIT 1";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->execute();

$result = $stmt->get_result();

if ($result === false) {
    die("Error executing query: " . $stmt->error);
}

$devstat = 0;

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $devstat = $row['devstat'];
}
$roomsql = "SELECT * FROM room ORDER BY rm_no ASC";
$result = $conn->query($roomsql);

$roomLinks = "";
if ($result->num_rows > 0) {
    // Loop through the results and build the links
    while($row = $result->fetch_assoc()) {
        $rm_id = $row["rm_id"];
        $rm_no = $row["rm_no"];
        $roomLinks .= "<a class='nav-link' href='rms.php?id=$rm_id'>$rm_no</a>";
    }
} else {
    $roomLinks = "<p>No ROOM found.</p>";
}
$selectedrm_no = "";
if (isset($_GET['id'])) {
    $rm_id = $_GET['id'];
    $roomsql = "SELECT rm_no FROM room WHERE rm_id = ? ORDER BY rm_no DESC";
    $stmt = $conn->prepare($roomsql);
    $stmt->bind_param("i", $rm_id);
    $stmt->execute();
    $stmt->bind_result($rm_no);
    if ($stmt->fetch()) {
        $selectedrm_no = $rm_no;
    }
}


$stmt->close();
$conn->close();

$buttonColor = ($devstat == 1) ? "btn-success" : "btn-danger";
$buttonText = ($devstat == 1) ? "Enabled" : "Disabled";


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidenav</title>
    <!-- <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.0/css/bootstrap.min.css" rel="stylesheet"> -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
</head>
<body>
<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <div class="sb-sidenav-menu-heading">menu</div>
                <a class="nav-link" href="index.php">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-landmark"></i></div>
                    DASHBOARD
                </a>
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                    <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                    ROOMS
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseLayouts" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <?php echo $roomLinks; ?>
                        <!-- <a class="nav-link" href="rm1.php">rm209</a>
                        <a class="nav-link" href="rm2.php">rm208</a> -->
                    </nav>
                </div>
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapsePages" aria-expanded="false" aria-controls="collapsePages">
                    <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                    MANAGEMENTS
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapsePages" aria-labelledby="headingTwo" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="room.php">Room</a>
                        <a class="nav-link" href="faculty.php">Faculty</a>
                        <a class="nav-link" href="sub.php">Subject</a>
                        <a class="nav-link" href="holiday.php">Holiday</a>
                        <a class="nav-link" href="class.php">Class Suspension</a>
                    </nav>
                </div>
            </div>
        </div>
        <div class="sb-sidenav-footer">
            <form method="post" action="toggle_button.php">
                <button type="submit" id ="side" class="btn <?php echo $buttonColor; ?> d-flex align-items-center">
                    <i class="fas fa-table me-2"></i>
                    <?php echo $buttonText; ?>
                </button>
            </form>
            <div class="small">Logged in as:</div>
            <?php echo $_SESSION["username"]; ?>
        </div>
    </nav>
</div>
</body>
</html>
