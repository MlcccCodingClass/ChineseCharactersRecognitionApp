<?php
require_once "_needSession.php";
require_once 'htmlpurifier-4.15.0-lite/library/HTMLPurifier.auto.php';
require_once '_incFunctions.php';
require "connect.php";

header('Content-Type: application/json');
$message = "";
$stmt = null;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try{
        $action = isset($_POST['action']) ? $_POST['action'] : '';
        
        // Handle batch delete records
        if ($action === 'batchDeleteRecords') {
            $months = isset($_POST['months']) ? intval($_POST['months']) : 0;
            
            if ($months <= 0 || $months > 6) {
                throw new Exception("Invalid number of months. Please select 1-6 months.");
            }
            
            // Calculate the date threshold (records older than this date will be deleted)
            $cutoffDate = date('Y-m-d H:i:s', strtotime("-$months months"));
            
            // Delete records where the associated activity's StartTime is older than the cutoff date
            $deleteSql = "DELETE r FROM `records` r 
                         INNER JOIN `activities` a ON r.ActivityID = a.ActivityID 
                         WHERE a.StartTime < ?";
            
            $stmt = $conn->prepare($deleteSql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("s", $cutoffDate);
            $stmt->execute();
            
            $deletedCount = $stmt->affected_rows;
            
            $response = array(
                'success' => true,
                'deletedCount' => $deletedCount,
                'message' => 'Successfully deleted ' . $deletedCount . ' record(s)'
            );
            echo json_encode($response);
            exit;
        }
        
        // Handle original event operations
        $id = isset($_POST['id']) ? $_POST['id'] : '';
        $eventName = isset($_POST['eventName']) ? $_POST['eventName'] : '';
        $accessKey = isset($_POST['accessKey']) ? $_POST['accessKey'] : '';
        $activeDateStr = isset($_POST['activeDate']) ? $_POST['activeDate'] : '';
        $expiredDateStr = isset($_POST['expiredDate']) ? $_POST['expiredDate'] : '';
        $isPrivate = isset($_POST['isPrivate']) ? intval($_POST['isPrivate']) : 0;
        $displayTimezoneStr = isset($_POST['displayTimezone']) ? $_POST['displayTimezone'] : 'UTC'; // Get display timezone

        // Validate and set timezones
        $allowedTimezones = ['UTC', 'America/New_York', 'America/Chicago', 'America/Los_Angeles'];
        if (!in_array($displayTimezoneStr, $allowedTimezones)) {
            $displayTimezoneStr = 'UTC'; // Default to UTC if invalid
        }
        $displayTimezone = new DateTimeZone($displayTimezoneStr);
        $utcTimezone = new DateTimeZone('UTC');

        // Convert dates from display timezone to UTC for storage
        // Parse the datetime string (Y-m-d H:i) from the display timezone
        $activeDateObj = DateTime::createFromFormat('Y-m-d H:i', $activeDateStr, $displayTimezone);
        if ($activeDateObj === false) {
            // Check if parsing failed due to missing seconds and retry
            $lastErrors = DateTime::getLastErrors();
            if ($lastErrors !== false && $lastErrors['warning_count'] > 0) {
                 $activeDateObj = DateTime::createFromFormat('Y-m-d H:i:s', $activeDateStr.':00', $displayTimezone);
            }
            // If still false, throw exception
            if ($activeDateObj === false) {
                throw new Exception("Invalid Active Date/Time format: " . $activeDateStr . ". Use YYYY-MM-DD HH:MM.");
            }
        }
        $activeDateObj->setTimezone($utcTimezone);
        $activeDateUTC = $activeDateObj->format('Y-m-d H:i:s'); // Format for DB

        $expiredDateObj = DateTime::createFromFormat('Y-m-d H:i', $expiredDateStr, $displayTimezone);
        if ($expiredDateObj === false) {
            // Check if parsing failed due to missing seconds and retry
            $lastErrors = DateTime::getLastErrors();
            if ($lastErrors !== false && $lastErrors['warning_count'] > 0) {
                 $expiredDateObj = DateTime::createFromFormat('Y-m-d H:i:s', $expiredDateStr.':00', $displayTimezone);
            }
            // If still false, throw exception
            if ($expiredDateObj === false) {
                throw new Exception("Invalid Expired Date/Time format: " . $expiredDateStr . ". Use YYYY-MM-DD HH:MM.");
            }
        }
        $expiredDateObj->setTimezone($utcTimezone);
        $expiredDateUTC = $expiredDateObj->format('Y-m-d H:i:s'); // Format for DB

        if(empty($id)){
            // Insert event
            $sql = "INSERT INTO `event` (EventName, AccessKey, ActiveDate, ExpiredDate, isPrivate) VALUES (?, ?, ?, ?, ?);";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $eventName, $accessKey, $activeDateUTC, $expiredDateUTC, $isPrivate);
        }else{
            // Update event
            $sql = "UPDATE `event` SET EventName = ?, AccessKey = ?, ActiveDate = ?, ExpiredDate = ?, isPrivate = ? WHERE ID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssii", $eventName, $accessKey, $activeDateUTC, $expiredDateUTC, $isPrivate, $id);
        }
        $stmt->execute();
        $message = 'The event is saved successfully!';
    }
    catch (Exception $e) {
        $message =  'There is an error occured while saving the event.'.$e->getMessage();
    }
    if ($stmt && is_object($stmt)) {
        $stmt->close();
    }
}
else
{
    $message =  'There is an error occured while saving the event.';
}
echo json_encode($message);

//echo $message;
?>