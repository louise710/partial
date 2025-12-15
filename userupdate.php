<?php
include 'db.php';

// Fetch user details for editing
if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']); 
    $sql = "SELECT * FROM user WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}

// Update user details
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update"])) {
    $user_id = intval($_POST["user_id"]);
    $username = $_POST["username"];
    $userpass = $_POST["userpass"];
    $currentpass = $_POST["currentpass"];
    $fname = $_POST["fname"];
    $lname = $_POST["lname"];

    $sql = "SELECT userpass FROM user WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_user = $result->fetch_assoc();
    $stmt->close();

    if ($current_user) {
        $is_password_correct = password_verify($currentpass, $current_user['userpass']);

        if ($is_password_correct) {
            // Check for duplicate username
            $sql = "SELECT * FROM user WHERE username = ? AND user_id != ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $username, $user_id);
            $stmt->execute();
            $duplicate_result = $stmt->get_result();
            $stmt->close();

            if ($duplicate_result->num_rows > 0) {
                echo "<script>alert('Username already exists. Please choose another.'); window.location.href = 'user.php';</script>";
                exit();
            }

            // Check if new password is provided and hash it
            if (!empty($userpass)) {
                $hashed_password = password_hash($userpass, PASSWORD_BCRYPT);
                $sql = "UPDATE user SET username=?, userpass=?, fname=?, lname=? WHERE user_id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssi", $username, $hashed_password, $fname, $lname, $user_id);
            } else {
                // If no new password provided, omit password from update
                $sql = "UPDATE user SET username=?, fname=?, lname=? WHERE user_id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssi", $username, $fname, $lname, $user_id);
            }

            if ($stmt === false) {
                die("Error preparing statement: " . $conn->error);
            }

            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                header("Location: user.php");
                exit();
            } else {
                echo "<script>alert('No changes were made.'); window.location.href = 'user.php';</script>";
            }

            $stmt->close();
        } else {
            echo "<script>alert('Incorrect password.'); window.location.href = 'user.php';</script>";
            exit();
        }
    } else {
        echo "<script>alert('User not found.'); window.location.href = 'user.php';</script>";
        exit();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
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
    <h2>Update User</h2>
    <form method="post" action="userupdate.php">
        <input type="hidden" name="user_id" value="<?php echo isset($user['user_id']) ? htmlspecialchars($user['user_id']) : ''; ?>">
        <center>
            <table class="table table-bordered mt-3">
                <tr>
                    <td>First Name:</td>
                    <td><input type="text" name="fname" autocomplete="off" class="form-control" value="<?php echo isset($user['fname']) ? htmlspecialchars($user['fname']) : ''; ?>" required></td>
                </tr>
                <tr>
                    <td>Last Name:</td>
                    <td><input type="text" name="lname" autocomplete="off" class="form-control" value="<?php echo isset($user['lname']) ? htmlspecialchars($user['lname']) : ''; ?>" required></td>
                </tr>
                <tr>
                    <td>Username:</td>
                    <td><input type="text" name="username" autocomplete="off" class="form-control" value="<?php echo isset($user['username']) ? htmlspecialchars($user['username']) : ''; ?>" required></td>
                </tr>
                <tr>
                    <td>Current Password:</td>
                    <td><input type="password" name="currentpass" autocomplete="off" class="form-control" required></td>
                </tr>
                <tr>
                    <td>New Password:</td>
                    <td><input type="password" name="userpass" autocomplete="off" class="form-control" placeholder="Leave blank to keep current password"></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input type="submit" name="update" class="btn btn-primary" value="Update"></td>
                </tr>
            </table>
        </center>
    </form>
</body>
</html>
