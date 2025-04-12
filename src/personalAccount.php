<?php require "_sessionHeader.php" ?>
<?php require_once '_incFunctions.php';
$myEmail = $_SESSION["loginUser"];
$userType = $_SESSION["userType"];

$sql = "SELECT user.ID, user.GradeID, grade.GradeName FROM user LEFT JOIN grade ON user.GradeID = grade.GradeID WHERE user.Email = '$myEmail'";
$result = $conn->query($sql);
$row = $result->fetch_object();
if ($row==null)
    {
        $error = "There are no users data for  [$myEmail]. ";
        header("Location: error.php?error=" . urlencode($error));
    }
$ID = $row->ID;
$grade = "0";
$gradeName  = "N/A";
if ($row != null && $row->GradeID != null) {
    $grade = $row->GradeID;
    if($userType == "student")
    {
        $gradeName=$row->GradeName;
        
    }
}
// $levelSql = "SELECT GradeName FROM grade WHERE GradeId = '$grade'";
// $result = $conn->query($levelSql);
// $levelRow = $result->fetch_object();
// $level = ($userType == "student") ? $levelRow->GradeName : "N/A";
// //get activity list by StudentID or JudgeName
if ($userType == "student")
{
    $activitySql = "SELECT ActivityId, EventName, Level, FinalScore, StartTime, TimeSpent,isPractice FROM activities INNER JOIN event on activities.EventID=event.ID WHERE StudentID = ? ORDER BY ActivityID DESC LIMIT 50";
    if($stmtActivity = $conn->prepare($activitySql)){
        $stmtActivity->bind_param("i", $ID);
        $stmtActivity->execute();
    }else{
        die("Errormessage: ". $conn->error);
    }
    $resultActivity = $stmtActivity->get_result();
}
else
{
    $activitySql = "SELECT ActivityId, EventName, Level, FinalScore,  StartTime, TimeSpent, StudentName ,isPractice FROM activities INNER JOIN event on activities.EventID=event.ID WHERE JudgeName = ? ORDER BY ActivityID DESC";
    if($stmtActivity = $conn->prepare($activitySql)){
        $stmtActivity->bind_param("s", $myEmail);
        $stmtActivity->execute();
    }else{
        die("Errormessage: ". $conn->error);
    }
    $resultActivity = $stmtActivity->get_result();
}
?>

<?php
// Timezone selection logic (shared with admin pages)
$allowedTimezones = ['UTC', 'America/New_York', 'America/Chicago', 'America/Los_Angeles'];
$selectedTz = 'UTC';
if (isset($_GET['tz']) && in_array($_GET['tz'], $allowedTimezones)) {
    $selectedTz = $_GET['tz'];
    setcookie('admin_timezone', $selectedTz, time() + (86400 * 365), "/");
} elseif (isset($_COOKIE['admin_timezone']) && in_array($_COOKIE['admin_timezone'], $allowedTimezones)) {
    $selectedTz = $_COOKIE['admin_timezone'];
}
?>
<div style="text-align:right; margin: 10px 20px 0 0;">
    <form id="tzForm" style="display:inline;">
        <label for="timezoneSelect" style="font-weight:normal;">Display Timezone:</label>
        <select id="timezoneSelect" name="tz" style="width:auto;display:inline;">
            <option value="UTC" <?php echo ($selectedTz === 'UTC') ? 'selected' : ''; ?>>UTC</option>
            <option value="America/New_York" <?php echo ($selectedTz === 'America/New_York') ? 'selected' : ''; ?>>US Eastern (EDT)</option>
            <option value="America/Chicago" <?php echo ($selectedTz === 'America/Chicago') ? 'selected' : ''; ?>>US Central (CDT)</option>
            <option value="America/Los_Angeles" <?php echo ($selectedTz === 'America/Los_Angeles') ? 'selected' : ''; ?>>US Pacific (PDT)</option>
        </select>
    </form>
</div>
<script>
document.getElementById('timezoneSelect').addEventListener('change', function() {
    var selectedTz = this.value;
    // Set cookie for 1 year
    document.cookie = "admin_timezone=" + encodeURIComponent(selectedTz) + ";path=/;max-age=" + (60*60*24*365);
    // Reload with tz param
    var currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('tz', selectedTz);
    window.location.assign(currentUrl.toString());
});
</script>

<script>
$(document).ready(function () {
    $('th').each(function (col) {
        $(this).hover(
                function () {
                    $(this).addClass('focus');
                },
                function () {
                    $(this).removeClass('focus');
                }
        );
        $(this).click(function () {
            if ($(this).is('.asc')) {
                $(this).removeClass('asc');
                $(this).addClass('desc selected');
                sortOrder = -1;
            } else {
                $(this).addClass('asc selected');
                $(this).removeClass('desc');
                sortOrder = 1;
            }
            $(this).siblings().removeClass('asc selected');
            $(this).siblings().removeClass('desc selected');
            var arrData = $('table').find('tbody >tr:has(td)').get();
            arrData.sort(function (a, b) {
                var val1 = $(a).children('td').eq(col).text().toUpperCase();
                var val2 = $(b).children('td').eq(col).text().toUpperCase();
                if ($.isNumeric(val1) && $.isNumeric(val2))
                    return sortOrder == 1 ? val1 - val2 : val2 - val1;
                else
                    return (val1 < val2) ? -sortOrder : (val1 > val2) ? sortOrder : 0;
            });
            $.each(arrData, function (index, row) {
                $('tbody').append(row);
            });
        });
    });

    // Add click handler for details links
    $('.details-link').click(function(e) {
        e.preventDefault();
        const activityId = $(this).data('activity-id');
        loadWordDetails(activityId);
    });

    // Add change handler for checkbox
    $('#showFailedOnly').change(function() {
        const showFailedOnly = $(this).is(':checked');
        $('.word-block').each(function() {
            const passed = $(this).data('passed') === 1;
            $(this).toggle(!showFailedOnly || !passed);
        });
    });

    function loadWordDetails(activityId) {
        $.ajax({
            url: 'api/getWordDetails.php',
            method: 'GET',
            data: { activityId: activityId },
            success: function(response) {
                const words = JSON.parse(response);
                displayWords(words);
                $('#wordDetailsModal').modal('show');
            },
            error: function(xhr, status, error) {
                alert('Error loading word details: ' + error);
            }
        });
    }

    function displayWords(words) {
        const wordList = $('#wordList');
        wordList.empty();
        
        words.forEach(word => {
            const wordBlock = $(`
                <div class="col-md-4 col-sm-6 mb-3 word-block" data-passed="${word.Passed}">
                    <div class="card ${word.Passed == 1 ? 'border-success' : 'border-danger'}">
                        <div class="card-body">
                            <h4 class="card-title">${word.Words}</h4>
                            <p class="card-text">
                                Time: ${word.TimeElapsed} seconds<br>
                            
                            </p>
                        </div>
                    </div>
                </div>
            `);
            wordList.append(wordBlock);
        });
    }
});

</script>

<style>

</style>

<div class="container">
    <div class="row">
        <div class="frame-main col-md-12 col-sm-12">
            <div class="form-title">Account Info</div>
            <div class="row">
                <div class="col-md-3 col-sm-3">
                    <div class="label">UserName:</div>
                </div>
                <div class="col-md-9 col-sm-9">
                    <div class="label">
                        <?php echo $_SESSION["loginUser"] ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 col-sm-3">
                    <div class="label">Grade:</div>
                </div>
                <div class="col-md-9 col-sm-9">
                    <div class="label">
                        <?php echo $gradeName?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="label">
                        <a href="editAccount.php">Edit Account</a>
                    </div>
                </div>
            </div>

            <br />

            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="label">Previous Tests:</div>
                    <table>
                        <tr>
                            <th class="text-center p-1 sortableHeader">Event Name</th>
                            <?php
                            if ($userType == "parent")
                            {
                                echo '<th class="text-center p-1 sortableHeader">Student Name</th>';
                            }
                            ?>
                            <th class="text-center p-1 sortableHeader">Grade Level</th>
                            <th class="text-center p-1 sortableHeader">Score</th>
                            <th class="text-center p-1 sortableHeader">Start Time</th>
                            <th class="text-center p-1 sortableHeader">Time Spent</th>
                            <th class="text-center p-1 sortableHeader">details</th>
                        </tr>
                        <?php
                        while ($row = $resultActivity->fetch_object()) {
                            echo '<tr>';
                            echo '<td>' . $row->EventName .($row->isPractice==0?'':' (practice)' ). '</td>';
                            if ($userType == "parent")
                            {
                                echo '<td>' . $row->StudentName . '</td>';
                            }
                            $level = $row->Level;
                            $gradeSql = "SELECT GradeName FROM grade WHERE GradeID = $level";
                            $gradeResult = $conn->query($gradeSql);
                            $gradeRow = $gradeResult->fetch_assoc();
                            $gradeName = $gradeRow["GradeName"];
                            echo '<td>' . $gradeName . '</td>';
                            echo '<td>' . $row->FinalScore . '</td>';
                            // Timezone display for StartTime
                            $displayTz = new DateTimeZone($selectedTz);
                            $utcTz = new DateTimeZone('UTC');
                            $dt = new DateTime($row->StartTime, $utcTz);
                            $dt->setTimezone($displayTz);
                            echo '<td>' . $dt->format('Y-m-d H:i') . '</td>';
                            echo '<td>' . $row->TimeSpent . '</td>';
                            echo '<td><a href="#" class="details-link" data-activity-id="' . $row->ActivityId . '">View Details</a></td>';
                            echo '</tr>';
                        }
                        ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add modal popup for word details -->
<div class="modal fade" id="wordDetailsModal" tabindex="-1" role="dialog" aria-labelledby="wordDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h3 class="modal-title" id="wordDetailsModalLabel">Word Details</h3>
            </div>
            <div class="modal-body">
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="showFailedOnly">
                    <label class="form-check-label" for="showFailedOnly">Show incorrect words only</label>
                </div>
                <div id="wordList" class="row">
                    <!-- Word blocks will be inserted here -->
                </div>
            </div>
        </div>
    </div>
</div>

<?php require "_footer.php" ?>