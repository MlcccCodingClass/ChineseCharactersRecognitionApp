<?php
if (!isset($userID)) {
    die("User ID not set");
}

// Get user's activities
$activitySql = "SELECT a.*, e.EventName, g.GradeName 
                FROM activities a 
                INNER JOIN event e ON a.EventID = e.ID 
                INNER JOIN grade g ON a.Level = g.GradeID 
                WHERE a.StudentID = ? 
                ORDER BY a.ActivityID DESC 
                LIMIT 50";

if($stmtActivity = $conn->prepare($activitySql)){
    $stmtActivity->bind_param("i", $userID);
    $stmtActivity->execute();
    $activities = $stmtActivity->get_result();
} else {
    die("Error preparing statement: " . $conn->error);
}

// Get activity statistics
$statsSql = "SELECT 
    COUNT(*) as total_activities,
    SUM(CASE WHEN isPractice = 0 THEN 1 ELSE 0 END) as test_count,
    SUM(CASE WHEN isPractice = 1 THEN 1 ELSE 0 END) as practice_count,
    AVG(CASE WHEN isPractice = 0 THEN FinalScore ELSE NULL END) as avg_test_score,
    AVG(CASE WHEN isPractice = 1 THEN FinalScore ELSE NULL END) as avg_practice_score
    FROM activities WHERE StudentID = ?";

$statsStmt = $conn->prepare($statsSql);
$statsStmt->bind_param("i", $userID);
$statsStmt->execute();
$stats = $statsStmt->get_result()->fetch_object();
?>

<div class="card mb-4">
    <div class="card-header">
        <h4>Activity Statistics</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <p><strong>Total Activities:</strong> <?php echo $stats->total_activities; ?></p>
                <p><strong>Test Activities:</strong> <?php echo $stats->test_count; ?></p>
                <p><strong>Practice Activities:</strong> <?php echo $stats->practice_count; ?></p>
            </div>
            <div class="col-md-4">
                <p><strong>Average Test Score:</strong> <?php echo number_format($stats->avg_test_score, 1); ?></p>
                <p><strong>Average Practice Score:</strong> <?php echo number_format($stats->avg_practice_score, 1); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Activity History</h4>
        <div class="form-check form-check-inline">
            <input type="checkbox" class="form-check-input" id="showTestOnly">
            <label class="form-check-label" for="showTestOnly">Show Test Activities Only</label>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped" id="activitiesTable">
                <thead>
                    <tr>
                        <th>Event Name</th>
                        <th>Grade Level</th>
                        <th>Score</th>
                        <th>Time Spent</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($activity = $activities->fetch_object()): ?>
                    <tr class="<?php echo $activity->isPractice ? 'practice-row' : 'test-row'; ?>">
                        <td><?php echo htmlspecialchars($activity->EventName); ?></td>
                        <td><?php echo htmlspecialchars($activity->GradeName); ?></td>
                        <td><?php echo $activity->FinalScore; ?></td>
                        <td><?php echo $activity->TimeSpent; ?></td>
                        <td><?php echo $activity->isPractice ? 'Practice' : 'Test'; ?></td>
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
                            $dt = new DateTime($activity->StartTime, $utcTz);
                            $dt->setTimezone($displayTz);
                            echo $dt->format('Y-m-d H:i');
                        ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-info view-details" <?php echo $activity->FinalScore>0 ? '' : 'disabled'; ?>
                                    data-activity-id="<?php echo $activity->ActivityID; ?>">
                                View Details
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '_activityDetailsModal.php'; ?>


<script>
$(document).ready(function() {
    // Handle test/practice filter
    $('#showTestOnly').change(function() {
        if ($(this).is(':checked')) {
            $('.practice-row').hide();
            $('.test-row').show();
        } else {
            $('.practice-row, .test-row').show();
        }
    });


});


</script>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    margin-bottom: 1rem;
}

.card-header {
    background-color: #f8f9fa;
    padding: 1rem;
}

.table th {
    background-color: #f8f9fa;
}

.view-details {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style> 