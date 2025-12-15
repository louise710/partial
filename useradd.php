<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add"])) {
    $username = $_POST["username"];
    $userpass = $_POST['userpass'];
    $fname = $_POST['fname'];
    $lname = $_POST["lname"];
    $acctype = isset($_POST["acctype"]) ? $_POST["acctype"] : null;
    $ustat = isset($_POST["ustat"]) ? $_POST["ustat"] : 0; // Default to inactive if not set

    $check_sql = "SELECT * FROM user WHERE username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo "<script>alert('Username already exists. Please choose a different username.'); window.location.href = 'user.php';</script>";
        $check_stmt->close();
        exit();
    }

    $hashed_password = password_hash($userpass, PASSWORD_BCRYPT);

    $sql = "INSERT INTO user (username, userpass, fname, lname, acctype, ustat) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param("sssiii", $username, $hashed_password, $fname, $lname, $acctype, $ustat);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: user.php");
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
    <title>Add User</title>
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
    <h2>Add User</h2>
    <form action="useradd.php" method="post">
        <table class="table table-bordered mt-3">
            <tr>
                <td><label for="fname">First Name:</label></td>
                <td><input type="text" name="fname" id="fname" class="form-control" required></td>
            </tr>
            <tr>
                <td><label for="lname">Last Name:</label></td>
                <td><input type="text" name="lname" id="lname" class="form-control" required></td>
            </tr>
            <tr>
                <td><label for="username">Username:</label></td>
                <td><input type="text" name="username" id="username" class="form-control" required></td>
            </tr>
            <tr>
                <td><label for="userpass">Password:</label></td>
                <td><input type="password" name="userpass" id="userpass" class="form-control" required></td>
            </tr>
            <!-- <tr>
                <td><label for="acctype">Access Type:</label></td> 
                <td><input type="number" name="acctype" id="acctype" class="form-control"></td> 
            </tr> -->
            <tr>
                <td>Status:</td>
                <td>
                    <label><input type="radio" name="ustat" value="1" checked> Active</label>
                    <label><input type="radio" name="ustat" value="0"> Inactive</label>
                </td>
            </tr>
        </table>
        <center><input type="submit" name="add" value="Add User" class="btn btn-primary"></center>
    </form>
</body>
</html>
