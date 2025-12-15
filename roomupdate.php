<?php
include 'db.php';

if (isset($_GET['rm_id'])) {
    $rm_id = $_GET['rm_id'];
    $sql = "SELECT * FROM room WHERE rm_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $rm_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $room = $result->fetch_assoc();
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update"])) {
    $rm_id = $_POST["rm_id"];
    $rm_no = $_POST["rm_no"];
    $rm_desc = $_POST["rm_desc"];
    $lights = $_POST["lights"];
    $ac = $_POST["ac"];
    
    

    $sql = "UPDATE room SET rm_no=?, rm_desc=?, lights=?, ac=? WHERE rm_id=?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("isiii", $rm_no, $rm_desc, $lights, $ac, $rm_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: room.php");
        exit();
    } else {
        header("Location: room.php");
        exit();
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update room</title>
    <style>
        table {
            border-collapse: collapse;
            width: 40%;
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
    <h2>Update Room</h2>
    <form method="post" action="roomupdate.php">
        <input type="hidden" name="rm_id" value="<?php echo isset($room['rm_id']) ? $room['rm_id'] : ''; ?>">
        <table class="table table-bordered mt-3">
            <tr>
                <td>Room ID Number:</td>
                <td><input type="number" name="rm_no" autocomplete="off" class="form-control" value="<?php echo isset($room['rm_no']) ? $room['rm_no'] : ''; ?>"></td>
            </tr>
            <tr>
                <td>Room Description:</td>
                <td><input type="text" name="rm_desc" autocomplete="off" class="form-control" value="<?php echo isset($room['rm_desc']) ? $room['rm_desc'] : ''; ?>"></td>
            </tr>
            <tr>
                <td>Lights:</td>
                <td><input type="text" name="lights" autocomplete="off" class="form-control" value="<?php echo isset($room['lights']) ? $room['lights'] : ''; ?>"></td>
            </tr>
            <tr>
                <td>AC:</td>
                <td><input type="text" name="ac" autocomplete="off" class="form-control" value="<?php echo isset($room['ac']) ? $room['ac'] : ''; ?>"></td>
            </tr>
        </table>
            <div class="text-center mt-3">
                <input type="submit" name="update" class="btn btn-primary" value="Update">
            </div>
    </form>
</body>
</html>
