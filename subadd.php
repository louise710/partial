<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add"])) {
    $code = trim($_POST["code"]);
    $s_desc = trim($_POST['s_desc']);

    // Check if the subject code already exists
    $check_sql = "SELECT * FROM sub WHERE code = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $code);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo "<script>
                alert('Subject code already exists.');
                window.location.href = 'sub.php';
              </script>";
        exit();
    }

    $sql = "INSERT INTO sub (code, s_desc) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("ss", $code, $s_desc);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: sub.php");
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
    <title>Add Subject</title>
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
    <h2>Add Subject</h2>
    <form action="subadd.php" method="post">
        <table class="table table-bordered mt-3">
            <tr>
                <td><label for="code">Subject:</label></td>
                <td><input type="text" name="code" id="code" class="form-control" required></td>
            </tr>
            <tr>
                <td><label for="s_desc">Description:</label></td>
                <td><input type="text" name="s_desc" id="s_desc" class="form-control" required></td>
            </tr>
        </table>
        <br>
        <center><input type="submit" name="add" class="btn btn-primary" value="Add Subject"></center>
    </form>
</body>
</html>
