<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add"])) {
    $c_date = $_POST["date"];
    $class_desc = $_POST['class_desc'];
    $tstart = $_POST['t_start'];
    $tend = $_POST['t_end'];

    $sql = "INSERT INTO classuspen (date, class_desc, t_start, t_end) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("ssss", $c_date, $class_desc, $tstart, $tend);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: class.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "<script>console.log('PHP: Form not submitted.');</script>";
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <title>Add Class Suspension</title>
    <style>
        table {
            border-collapse: collapse;
            width: 50%;
            margin: auto;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
    </style>
</head>

<body>
    <h2>Add Class Suspension</h2>
    <form action="classadd.php" method="post">
        <table class="table table-bordered mt-3">
           <!--  <tr>
                <td><label for="rm_id">Faculty id:</label></td>
                <td><input type="text" name="rm_id" id="rm_id" required></td>
            </tr> -->
            <tr>
                <td><label for="t_start">Time Started:</label></td>
                <td><input type="time" name="t_start" id="t_start" class="form-control" required></td>
            </tr>
            <tr>
                <td><label for="t_end">Time Ended:</label></td>
                <td><input type="time" name="t_end" id="t_end" class="form-control" required></td>
            </tr>
            <tr>
                <td><label for="date">Date:</label></td>
                <td><input type="date" name="date" id="date" class="form-control" required></td>
            </tr>
            
            <tr>
                <td><label for="class_desc">Description:</label></td>
                <td><input type="text" name="class_desc" id="class_desc" class="form-control" required></td>
            </tr>
        </table>
        <center><input type="submit" name="add" class="btn btn-primary" value="Add Class Suspension"></center>
    </form>
</body>

</html>
