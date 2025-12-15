<?php
include 'db.php';

$sched = [];
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch the schedule details for editing
if (isset($_GET['sched_id'])) {
    $sched_id = intval($_GET['sched_id']);
    $sql = "SELECT * FROM sched WHERE sched_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $sched_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $sched = $result->fetch_assoc();
    $stmt->close();
}

// Fetch faculty and subjects for dropdowns
$sql_select_faculty = "SELECT fid, fname, lname FROM faculty";
$result_faculty = $conn->query($sql_select_faculty);

$sql_select_sub = "SELECT sid, code FROM sub"; 
$result_sub = $conn->query($sql_select_sub);

// Handle the form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update"])) {
    // Get the POST data
    $sub_id = $_POST['sub_id'];
    $time_in = $_POST["time_in"];
    $time_out = $_POST["time_out"];
    $days = isset($_POST['day']) ? implode("", $_POST['day']) : ''; // Comma separated days
    $fid = $_POST['fid'];
    $rm_id = $_POST['rm_id'];
    $sched_id = $_POST['sched_id'];

    // Debugging: Check the variables received
    echo "Sched ID: $sched_id, Room ID: $rm_id, Faculty ID: $fid, Subject ID: $sub_id, Time In: $time_in, Time Out: $time_out, Days: $days";

    // Check for existing schedule conflicts
    $sql_check_conflict = "
        SELECT * FROM sched
        WHERE rm_id = ? 
        AND FIND_IN_SET(day, ?) > 0
        AND (
            (time_in < ? AND time_out > ?) OR 
            (time_in < ? AND time_out > ?) OR 
            (time_in < ? AND time_out > ?)
        )
    ";

    $stmt_check = $conn->prepare($sql_check_conflict);
    $stmt_check->bind_param("ssssssss", $rm_id, $days, $time_out, $time_in, $time_in, $time_out, $time_in, $time_out);

    $stmt_check->execute();
    $result_check = $stmt_check->get_result();



    if ($result_check->num_rows == 2) {
        // Conflict found
        $conflict_info = $result_check->fetch_assoc();
        $conflict_time_in = $conflict_info['time_in'];
        $conflict_time_out = $conflict_info['time_out'];
        $conflict_room_id = $conflict_info['rm_id'];




        echo "<script>
            alert('The schedule you are trying to create is already occupied. Conflict details:\\n' + 
                  'Room ID: $conflict_room_id\\n' + 
                  'Time: $conflict_time_in - $conflict_time_out.\\n' + 
                  'Please choose a different time or room.');
            window.location.href = 'rms.php?id=" . urlencode($rm_id) . "';
        </script>";
    } else {
        


        // Check if there are any changes to the schedule before updating
        if ($sched['sub_id'] === $sub_id && $sched['time_in'] === $time_in && $sched['time_out'] === $time_out && $sched['day'] === $days && $sched['fid'] === $fid) {
            // No changes, so just redirect
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        } else {
            // No conflict, proceed with the update
            $sql = "UPDATE sched SET time_in = ?, time_out = ?, day = ?, sub_id = ?, fid = ? WHERE sched_id = ?";
            $stmt = $conn->prepare($sql);
            
            if ($stmt === false) {
                die("Error preparing statement: " . $conn->error);
            }

            // Bind parameters: time_in, time_out, day, sub_id, fid, sched_id
            $stmt->bind_param("sssssi", $time_in, $time_out, $days, $sub_id, $fid, $sched_id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                // Redirect back after updating
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit();
            } else {
                echo "Error updating schedule: " . $conn->error;
            }

            $stmt->close();
        }
    }

    $stmt_check->close();
}

$conn->close();
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Schedule</title>
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
    <h2>Update Schedule</h2>
   <form method="post" action="schedupdate.php" id="scheduleForm">
    <input type="hidden" name="rm_id" value="<?php echo isset($sched['rm_id']) ? htmlspecialchars($sched['rm_id']) : ''; ?>">
    <input type="hidden" name="sched_id" value="<?php echo isset($sched['sched_id']) ? htmlspecialchars($sched['sched_id']) : ''; ?>">
    <center>
        <table class="table table-bordered mt-3">
            <tr>
                <td>Time In</td>
                <td><input type="time" name="time_in" class="form-control" value="<?php echo isset($sched['time_in']) ? htmlspecialchars($sched['time_in']) : ''; ?>" required></td>
            </tr>
            <tr>
                <td>Time Out</td>
                <td><input type="time" name="time_out" class="form-control" value="<?php echo isset($sched['time_out']) ? htmlspecialchars($sched['time_out']) : ''; ?>" required></td>
            </tr>
            <tr>
                <td><label for="day">Days:</label></td>
                <td>
                    <div class="checkbox-group">
                        <label for="mon"><input type="checkbox" id="mon" name="day[]" value="M" <?php echo (strpos($sched['day'], 'M') !== false) ? 'checked' : ''; ?>> Mon</label>
                        <label for="tue"><input type="checkbox" id="tue" name="day[]" value="T" <?php echo (strpos($sched['day'], 'T') !== false) ? 'checked' : ''; ?>> Tue</label>
                        <label for="wed"><input type="checkbox" id="wed" name="day[]" value="W" <?php echo (strpos($sched['day'], 'W') !== false) ? 'checked' : ''; ?>> Wed</label>
                        <label for="thu"><input type="checkbox" id="thu" name="day[]" value="Th" <?php echo (strpos($sched['day'], 'Th') !== false) ? 'checked' : ''; ?>> Thu</label>
                        <label for="fri"><input type="checkbox" id="fri" name="day[]" value="F" <?php echo (strpos($sched['day'], 'F') !== false) ? 'checked' : ''; ?>> Fri</label>
                        <label for="sat"><input type="checkbox" id="sat" name="day[]" value="Sa" <?php echo (strpos($sched['day'], 'Sa') !== false) ? 'checked' : ''; ?>> Sat</label>
                        <label for="sun"><input type="checkbox" id="sun" name="day[]" value="Su" <?php echo (strpos($sched['day'], 'Su') !== false) ? 'checked' : ''; ?>> Sun</label>
                    </div>
                </td>
            </tr>
            <tr>
                <td>Faculty</td>
                <td>
                    <select name="fid" required>
                        <option value="" disabled>Select Faculty</option>
                        <?php while ($row = $result_faculty->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($row['fid']); ?>" <?php echo (isset($sched['fid']) && $sched['fid'] == $row['fid']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['fname'] . " " . $row['lname']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Subject</td>
                <td>
                    <select name="sub_id" required>
                        <option value="" disabled>Select Subject</option>
                        <?php while ($row = $result_sub->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($row['sid']); ?>" <?php echo (isset($sched['sub_id']) && $sched['sub_id'] == $row['sid']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['code']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input type="submit" name="update" class="btn btn-primary" value="Update" id="updateBtn">
                    <a href="rms.php?id=<?php echo urlencode($sched['rm_id']); ?>" class="btn btn-secondary">Cancel</a>

                </td>
            </tr>
        </table>
    </center>
</form>

<script>
    const scheduleForm = document.getElementById('scheduleForm');
    const updateBtn = document.getElementById('updateBtn');
    
    // Function to enable/disable the Update button based on changes
    scheduleForm.addEventListener('input', function () {
        const isChanged = Array.from(scheduleForm.elements).some(element => {
            return element.value !== element.defaultValue || (element.type === 'checkbox' && element.checked !== element.defaultChecked);
        });
        updateBtn.disabled = !isChanged;
    });
</script>

</body>
</html>
