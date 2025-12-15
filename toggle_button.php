<?php
include 'db.php';

$sql = "SELECT devstat FROM systatus LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $currentDevstat = $row['devstat'];

    $newDevstat = ($currentDevstat == 1) ? 0 : 1;

    $sqlUpdate = "UPDATE systatus SET devstat = ? LIMIT 1";
    $stmt = $conn->prepare($sqlUpdate);
    $stmt->bind_param("i", $newDevstat);
    $stmt->execute();
    $stmt->close();
}

$conn->close();

header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
?>
