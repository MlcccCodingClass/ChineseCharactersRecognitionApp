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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
// Check if form is submitted
    $isTestChecked = isset($_POST['testActivities']);
    $isPracticeChecked = isset($_POST['practiceActivities']);
    if (!$isTestChecked && !$isPracticeChecked) {
        echo "<script>alert('Please check at least one activity type.');</script>";
    } else {
        $activitySql = "SELECT StudentName, StudentID, ActivityID, g.GradeName, FinalScore, TimeSpent, isPractice FROM activities a join grade g on g.GradeId=a.level WHERE EventID = ?";

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
    $activitySql = "SELECT StudentName, StudentID, ActivityID, g.GradeName, FinalScore, TimeSpent, isPractice FROM activities a join grade g on g.GradeId=a.level WHERE EventID = ? AND isPractice = 0 ORDER BY ActivityID DESC";
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
                <input type="submit" value="Filter" style="display:none">
                </form>
                </div>
                <table>
                    <tr>
                        <th class="text-center p-1">Student Name #</th>
                        <th class="text-center p-1">Student ID</th>
                        <th class="text-center p-1">Activity #</th>
                        <th class="text-center p-1">Grade Level</th>
                        <th class="text-center p-1">Score</th>
                        <th class="text-center p-1">Time Spent</th>
                        <th class="text-center p-1">IsPractice</th>
                    </tr>
                    <?php
                    while ($row = $resultActivity->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . $row["StudentName"] . '</td>';
                        echo '<td>' . $row["StudentID"] . '</td>';
                        echo '<td>' . $row["ActivityID"] . '</td>';                     
                        echo '<td>' . $row["GradeName"] . '</td>';
                        echo '<td>' . $row["FinalScore"] . '</td>';
                        echo '<td>' . $row["TimeSpent"] . '</td>';
                        echo '<td>' . $row["isPractice"] . '</td>';
                        echo '</tr>';
                    }
                    ?>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require "_footer.php" ?>