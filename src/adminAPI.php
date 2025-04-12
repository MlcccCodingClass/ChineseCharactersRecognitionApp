<?php
require_once "_needSession.php";
require_once 'htmlpurifier-4.15.0-lite/library/HTMLPurifier.auto.php';
require_once '_incFunctions.php';
require "connect.php";

$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try{
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
        $message =  'There is an error occured while saving the event.'.$e;
    }
    $stmt->close();
}
else
{
    $message =  'There is an error occured while saving the event.';
}
echo json_encode($message);

//echo $message;
?>