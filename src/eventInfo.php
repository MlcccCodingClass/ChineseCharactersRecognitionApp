<?php require "_adminSessionHeader.php" ?>
<?php require_once '_incFunctions.php';

//get event name
$eventID = $_GET['event'];
$eventSql = "SELECT EventName FROM event WHERE ID = ?";      
if($stmt = $conn->prepare($eventSql)){
    $stmt->bind_param("i", $eventID);
    $stmt->execute();
}else{
    die("Errormessage: ". $conn->error);
}
$row = $stmt->get_result()->fetch_assoc();
if ($row==null) {
    $tableTitle = "No event found";
}
if ($row != null && $row['EventName'] != null) {
    $tableTitle = $row['EventName']. " Activity List";
}
$errormsg="";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
// Check if form is submitted
    $isTestChecked = isset($_POST['testActivities']);
    $isPracticeChecked = isset($_POST['practiceActivities']);
    if (!$isTestChecked && !$isPracticeChecked) {
        $errormsg='Please check at least one activity type.';
    } else {
        $activitySql = "SELECT StudentName, StudentID, ActivityID, g.GradeName, FinalScore, TimeSpent, StartTime, isPractice FROM activities a join grade g on g.GradeId=a.level WHERE EventID = ?";

        if ($isTestChecked && !$isPracticeChecked) {
            $activitySql .= " AND isPractice = 0";
        } elseif (!$isTestChecked && $isPracticeChecked) {
            $activitySql .= " AND isPractice = 1";
        }

        $activitySql .= " ORDER BY ActivityID DESC";

        if ($stmtActivity = $conn->prepare($activitySql)) {
            $stmtActivity->bind_param("i", $eventID);
            $stmtActivity->execute();
        } else {
            die("Errormessage: " . $conn->error);
        }
        $resultActivity = $stmtActivity->get_result();
    }
} else {
    // Default query to show test activities
    $activitySql = "SELECT StudentName, StudentID, ActivityID, g.GradeName, FinalScore, TimeSpent,StartTime, isPractice FROM activities a join grade g on g.GradeId=a.level WHERE EventID = ? AND isPractice = 0 ORDER BY ActivityID DESC";
    if ($stmtActivity = $conn->prepare($activitySql)) {
        $stmtActivity->bind_param("i", $eventID);
        $stmtActivity->execute();
    } else {
        die("Errormessage: " . $conn->error);
    }
    $resultActivity = $stmtActivity->get_result();

    $isTestChecked = true;
    $isPracticeChecked = false;
}
?>

<div class="container">
    <div class="row">
        <div class="frame-main col-md-12 col-sm-12">

        <h2 class="border-bottom border-dark"><?php echo $tableTitle?></h2>

            <div class="col-md-12 col-sm-12">
                <div style="font-size: small">
                <form method="post" action="">
                <input type="checkbox" onchange="this.form.submit()" name="testActivities" id="testActivities" value="1" <?php echo $isTestChecked ? 'checked' : ''; ?>>   <label for="testActivities">Test activities</label>
                <input type="checkbox" onchange="this.form.submit()"  name="practiceActivities" id="practiceActivities" value="1" <?php echo $isPracticeChecked ? 'checked' : ''; ?>>  <label for="practiceActivities">Practice activities</label>
                <input type="submit" value="Filter" style="display:none">        <div id="errorMessage" class="alert alert-danger mt-2" style="display: none;"></div>
                </form>
                </div>
        
                <table>
                    <tr>
                        <th class="text-center p-1">Student Name #</th>
                        <th class="text-center p-1">Student ID</th>
                        <th class="text-center p-1">Activity #</th>
                        <th class="text-center p-1">Grade Level</th>

                        <th class="text-center p-1">Time Spent</th>
                        <th class="text-center p-1">Score</th>
                        <th class="text-center p-1">IsPractice</th>
                        <th class="text-center p-1">DateTime</th>
                        <th class="text-center p-1">View Details</th>
                    </tr>
            
                    <?php while (isset($resultActivity) && $row = $resultActivity->fetch_object()): ?>
                    <tr class="<?php echo $row->isPractice ? 'practice-row' : 'test-row'; ?></tr>">
                        <td><?php echo htmlspecialchars($row->StudentName); ?></td>
                        <td><?php echo htmlspecialchars($row->StudentID); ?></td>
                        <td><?php echo $row->ActivityID; ?></td>
                        <td><?php echo $row->GradeName; ?></td>
                        <td><?php echo $row->TimeSpent; ?></td>
                        <td><?php echo $row->FinalScore; ?></td>
                        <td><?php echo $row->isPractice ? 'Practice' : 'Test'; ?></td>
                        <td>
                        <?php
                            // Timezone logic: match _adminSessionHeader.php
                            $allowedTimezones = ['UTC', 'America/New_York', 'America/Chicago', 'America/Los_Angeles'];
                            $selectedTz = 'UTC';
                            if (isset($_GET['tz']) && in_array($_GET['tz'], $allowedTimezones)) {
                                $selectedTz = $_GET['tz'];
                            } elseif (isset($_COOKIE['admin_timezone']) && in_array($_COOKIE['admin_timezone'], $allowedTimezones)) {
                                $selectedTz = $_COOKIE['admin_timezone'];
                            }
                            $displayTz = new DateTimeZone($selectedTz);
                            $utcTz = new DateTimeZone('UTC');
                            $dt = new DateTime($row->StartTime, $utcTz);
                            $dt->setTimezone($displayTz);
                            echo $dt->format('Y-m-d H:i');
                        ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-info view-details" <?php echo $row->FinalScore>0 ? '' : 'disabled'; ?>
                                    data-activity-id="<?php echo $row->ActivityID; ?>">
                                View Details
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/_activityDetailsModal.php'; ?>
<script>
function showError(message) {
    if(!!message){
        $('#errorMessage')
            .text(message)
            .show()
            //.delay(3000)  // Show for 3 seconds
            //.fadeOut();
    }
}
showError('<?php echo $errormsg ?>')
// Replace the existing checkbox click handler
</script>

<style>
#errorMessage {
    max-width: 500px;
    margin: auto;
    text-align: center;
    border-radius: 4px;
    padding: 10px 15px;
    line-height: 1em;
    margin-top: -15px;
    margin-bottom: 30px;
}
</style>

<?php require "_footer.php" ?>