<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add"])) {
    $h_date = $_POST["date"];
    $holi_desc = $_POST['descript'];

    $sql = "INSERT INTO holiday (date, descript) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("ss", $h_date, $holi_desc);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: holiday.php");
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
    <title>Add Holiday</title>
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
    <h2>Add Holiday</h2>
    <form action="holidayadd.php" method="post">
        <table class="table table-bordered mt-3">
           <!--  <tr>
                <td><label for="rm_id">Faculty id:</label></td>
                <td><input type="text" name="rm_id" id="rm_id" required></td>
            </tr> -->
            <tr>
                <td><label for="date">Date:</label></td>
                <td><input type="date" name="date" id="date" class="form-control" required></td>
            </tr>
            
            <tr>
                <td><label for="descript">Description:</label></td>
                <td><input type="text" name="descript" id="descript" class="form-control" required></td>
            </tr>
        </table>
        <br>
        <center><input type="submit" name="add" class="btn btn-primary" value="Add Holiday"></center>
    </form>
</body>

</html>
