<?php
include 'db.php';

$rm_id = isset($_GET['id']) ? intval($_GET['id']) : null;

// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch dropdown data
$sql_select_faculty = "SELECT fid, fname, lname FROM faculty";
$result_faculty = $conn->query($sql_select_faculty);

$sql_select_sub = "SELECT sid, code FROM sub";
$result_sub = $conn->query($sql_select_sub);

$sql_select_rooms = "SELECT rm_id, rm_no, rm_desc FROM room";
$result_rooms = $conn->query($sql_select_rooms);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add"])) {
    $sub_id = $_POST['sub_id'];
    $time_in = $_POST["time_in"];
    $time_out = $_POST["time_out"];
    $days = isset($_POST['day']) ? $_POST['day'] : []; // Array of selected days
    $fid = $_POST['fid'];
    $rm_id = $_POST['rm_id'];
    
    $days_string = implode("", $days);

    // 1. Check for exact duplicate schedule
    $sql_check_duplicate = "
        SELECT * FROM sched 
        WHERE rm_id = ? 
        AND sub_id = ?
        AND fid = ?
        AND day = ?
        AND time_in = ?
        AND time_out = ?
    ";
    
    $stmt_duplicate = $conn->prepare($sql_check_duplicate);
    $stmt_duplicate->bind_param("iissss", $rm_id, $sub_id, $fid, $days_string, $time_in, $time_out);
    $stmt_duplicate->execute();
    
    if ($stmt_duplicate->get_result()->num_rows > 0) {
        echo "<script>
            alert('This exact schedule already exists in the system.');
            window.location.href = 'rms.php?id=" . urlencode($rm_id) . "';
        </script>";
        $stmt_duplicate->close();
        exit();
    }
    $stmt_duplicate->close();

    // 2. Check for time conflicts on each selected day
    foreach ($days as $day) {
        $sql_check_conflict = "
            SELECT * FROM sched
            WHERE rm_id = ? 
            AND (day LIKE ? OR day LIKE ? OR day LIKE ?)
            AND (
                (time_in < ? AND time_out > ?) OR  -- New schedule is entirely within existing
                (time_in < ? AND time_out > ?) OR  -- New schedule overlaps start of existing
                (time_in < ? AND time_out > ?) OR  -- New schedule overlaps end of existing
                (time_in >= ? AND time_out <= ?)   -- New schedule entirely contains existing
            )
        ";
        
        // Prepare patterns to match the day in various formats
        $dayPattern1 = "%" . $day . "%";       // Day as part of comma-separated string
        $dayPattern2 = $day . ",%";            // Day at start of string
        $dayPattern3 = "%," . $day;            // Day at end of string
        
        $stmt_check = $conn->prepare($sql_check_conflict);
        $stmt_check->bind_param(
            "isssssssssss", 
            $rm_id, 
            $dayPattern1, $dayPattern2, $dayPattern3,
            $time_out, $time_in,    // For first condition
            $time_in, $time_out,    // For second condition
            $time_in, $time_out,    // For third condition
            $time_in, $time_out     // For fourth condition
        );
        
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $conflict_info = $result_check->fetch_assoc();
            $conflict_days = $conflict_info['day'];
            $conflict_time_in = $conflict_info['time_in'];
            $conflict_time_out = $conflict_info['time_out'];
            
            echo "<script>
                alert('Schedule conflict detected on day(s) $day:\\n' +
                      'Existing schedule: $conflict_time_in - $conflict_time_out\\n' +
                      'Please choose a different time or day.');
                window.location.href = 'rms.php?id=" . urlencode($rm_id) . "';
            </script>";
            $stmt_check->close();
            exit();
        }
        $stmt_check->close();
    }

    // 3. No conflicts found - proceed with insertion
    $sql_insert = "INSERT INTO sched (time_in, time_out, day, fid, rm_id, sub_id, stat) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    
    if ($stmt_insert === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stat = 1; // Active status
    $stmt_insert->bind_param("sssissi", $time_in, $time_out, $days_string, $fid, $rm_id, $sub_id, $stat);
    
    if ($stmt_insert->execute()) {
        header("Location: rms.php?id=" . urlencode($rm_id));
        exit();
    } else {
        echo "Error inserting data: " . $conn->error;
    }
    $stmt_insert->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Schedule</title>
    <style>
        table {
            border-collapse: collapse;
            width: 60%;
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
    <form action="schedadd.php?id=<?php echo htmlspecialchars($rm_id); ?>" method="post" id="scheduleForm">
        <h2>Add Schedule</h2>
        <table class="table table-bordered mt-3">
            <tr>
                <td><label for="time_in">Time In:</label></td>
                <td><input type="time" name="time_in" id="time_in" required></td>
            </tr>
            <tr>
                <td><label for="time_out">Time Out:</label></td>
                <td><input type="time" name="time_out" id="time_out" required></td>
            </tr>
            <tr>
                <td><label for="day">Days:</label></td>
                <td>
                    <div class="checkbox-group">
                        <label for="mon"><input type="checkbox" id="mon" name="day[]" value="M"> Mon</label>
                        <label for="tue"><input type="checkbox" id="tue" name="day[]" value="T"> Tue</label>
                        <label for="wed"><input type="checkbox" id="wed" name="day[]" value="W"> Wed</label>
                        <label for="thu"><input type="checkbox" id="thu" name="day[]" value="Th"> Thu</label>
                        <label for="fri"><input type="checkbox" id="fri" name="day[]" value="F"> Fri</label>
                        <label for="sat"><input type="checkbox" id="sat" name="day[]" value="Sa"> Sat</label>
                        <label for="sun"><input type="checkbox" id="sun" name="day[]" value="Su"> Sun</label>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="fid">Faculty ID:</label></td>
                <td>
                    <select name="fid" id="fid" required>
                        <option value="" disabled selected>Select Faculty ID</option>
                        <?php
                        while ($row = $result_faculty->fetch_assoc()) {
                            echo "<option value='" . $row['fid'] . "'>" . htmlspecialchars($row['fname'] . " " . $row['lname']) . "</option>";
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label for="sub_id">Subject:</label></td>
                <td>
                    <select name="sub_id" id="sub_id" required>
                        <option value="" disabled selected>Select Subject</option>
                        <?php
                        while ($row = $result_sub->fetch_assoc()) {
                            echo "<option value='" . $row['sid'] . "'>" . htmlspecialchars($row['code']) . "</option>"; 
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <input type="hidden" name="rm_id" id="rm_id" value="<?php echo htmlspecialchars($rm_id); ?>" required>
        </table>
        <br>
        <center><input type="submit" name="add" class="btn btn-primary" value="Add Schedule"></center>
    </form>
    <script>
    document.getElementById('scheduleForm').addEventListener('submit', function(event) {
        var checkboxes = document.querySelectorAll('input[name="day[]"]:checked');
        if (checkboxes.length === 0) {
            alert("Please select at least one day.");
            event.preventDefault();
        }
    });
    </script>
</body>
</html>