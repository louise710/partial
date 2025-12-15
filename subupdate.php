<?php
include 'db.php';

if (isset($_GET['sid'])) {
    $sid = intval($_GET['sid']);  
    $sql = "SELECT * FROM sub WHERE sid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $sid);
    $stmt->execute();
    $result = $stmt->get_result();
    $sub = $result->fetch_assoc();
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update"])) {
    $sid = intval($_POST["sid"]); 
    $code = trim($_POST["code"]);
    $s_desc = trim($_POST["s_desc"]);

    $check_sql = "SELECT * FROM sub WHERE code = ? AND sid != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $code, $sid);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo "<script>
                alert('Subject code already exists.');
                window.location.href = 'sub.php'; // Redirect to the subject page or wherever you need
              </script>";
        exit();
    }

    $sql = "UPDATE sub SET code=?, s_desc=? WHERE sid=?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("ssi", $code, $s_desc, $sid);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: sub.php");
        exit();
    } else {
        // Check for errors
        if ($stmt->error) {
            echo "Update failed: " . $stmt->error;
        } else {
            echo "<script>
                alert('No changes made.');
                window.location.href = 'sub.php'; // Redirect to the subject page or wherever you need
              </script>";
        }
    }

    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Subject</title>
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
    <h2>Update Subject</h2>
    <form method="post" action="subupdate.php">
        <input type="hidden" name="sid" value="<?php echo htmlspecialchars($sub['sid'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <center>
            <table class="table table-bordered mt-3">
                <tr>
                    <td>Subject:</td>
                    <td><input type="text" name="code" autocomplete="off" value="<?php echo htmlspecialchars($sub['code'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required></td>
                </tr>
                <tr>
                    <td>Description:</td>
                    <td><input type="text" name="s_desc" autocomplete="off" value="<?php echo htmlspecialchars($sub['s_desc'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required></td>
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
