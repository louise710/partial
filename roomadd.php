<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add"])) {
    $rm_no = $_POST["rm_no"];
    $rm_desc = $_POST['rm_desc'];
    $lights = $_POST["lights"];
    $ac = $_POST["ac"];

    $sql = "INSERT INTO room (rm_no, rm_desc, lights, ac) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("ssii", $rm_no, $rm_desc, $lights, $ac);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: room.php");
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
    <title>Add Room</title>
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
    <div class="container">
        <h2>Add New Room</h2>
        <form action="roomadd.php" method="post">
            <table class="table table-bordered mt-3">
                <tr>
                    <td><label for="rm_no">Room Number:</label></td>
                    <td><input type="text" name="rm_no" id="rm_no" class="form-control" required></td>
                </tr>
                <tr>
                    <td><label for="rm_desc">Room Description:</label></td>
                    <td><input type="text" name="rm_desc" id="rm_desc" class="form-control" required></td>
                </tr>
                <tr>
                    <td><label for="lights">Lights:</label></td>
                    <td><input type="number" name="lights" id="lights" class="form-control" required></td>
                </tr>
                <tr>
                    <td><label for="ac">AC:</label></td>
                    <td><input type="number" name="ac" id="ac" class="form-control"></td>
                </tr>
            </table>
            <div class="text-center mt-3">
                <input type="submit" name="add" value="Add Room" class="btn btn-primary">
            </div>
        </form>
    </div>
</body>

</html>
