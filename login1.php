<?php
session_start();
// ini_set('session.gc_maxlifetime',7200);
if (isset($_SESSION["username"])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include_once "db.php";
    
    $username = $_POST["username"];
    $password = $_POST["password"];
    
    $query = "SELECT userpass FROM user WHERE username = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $hashed_password = $user['userpass'];
        
        if (password_verify($password, $hashed_password)) {
            $_SESSION["username"] = $username;
            header("Location: index.php");
            exit();
        } else {
            header("Location: login1.php?error=1");
            exit();
        }
    } else {
        header("Location: login1.php?error=1");
        exit();
    }
    
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f0f0f0;
            background-image: url("assets/img.png"); 
            background-repeat: no-repeat;
            background-size: cover; 
            background-position: center;
        }
        .login-container {
            width: 250px; 
            padding: 50px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .form-group {
            margin-bottom: 20px;
            width: 100%;
            display: grid;
                justify-items: center;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 2px;
        }

        button[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #00ab60;
            color: #fff;
            border: none;
            border-radius: 100px;
            cursor: pointer;
            margin-left: auto;
                justify-items: center;
        }

        button[type="submit"]:hover {
            background-color: #009b56;
                justify-content: center;
        }
    </style>
    
</head>
<!-- <//?php
    if(!session_unset()){
        header("Location:index.php");
    }esle
?> -->
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (isset($_GET['error']) && $_GET['error'] == '1'): ?>
            <p style="color: red;">Invalid username or password.</p>
        <?php endif; ?>
        <form action="login1.php" method="POST">
            <div class="form-group">
                <input type="text" id="username" name="username" placeholder="Username" required>
            </div>
            <div class="form-group">
                <input type="password" id="password" name="password" placeholder="Password" required autocomplete="off">
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>