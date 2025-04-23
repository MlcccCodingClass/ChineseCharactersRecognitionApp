<?php
// Capture errors instead of displaying them
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

//require_once '_incFunctions.php';
require_once '../connect.php';
require "_mail_sender.php";



// Ensure no output before setting headers
header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['email'])) {
        throw new Exception('Invalid request');
    }

    $email = $conn->real_escape_string(trim($_POST['email']));

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please enter a valid email address.');
    }

    // Check if email exists in database
    $sql = "SELECT Email, Password FROM user WHERE Email = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        $dbError = $conn->error;
        error_log("Database prepare error: " . $dbError);
        throw new Exception('Database error occurred');
    }

    $stmt->bind_param("s", $email);
    
    // Capture any execution errors
    if (!$stmt->execute()) {
        $execError = $stmt->error;
        error_log("Database execution error: " . $execError);
        throw new Exception('Database error occurred');
    }
    
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        error_log("No user found for email: " . $email);
        throw new Exception('This email address was not found in our system. Please contact MLCCC admin for assistance.');
    }

    $user = $result->fetch_assoc();

    SendRecoverPwd2($email,$user['Password']);

    error_log("Password recovery email sent successfully to: " . $email);
        
    $response = [
        'success' => true,
        'message' => "Your password has been sent to your email address ($email). Please check your inbox. If not found, please also check your spam emails."
    ];

} catch (Exception $e) {
    // Capture any PHP errors/warnings that occurred
    $errorOutput = ob_get_clean();
    error_log("Password recovery error for {$email}: " . $e->getMessage() . "\nDetails: " . $errorOutput);
    
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];

    // Include error details in development environment
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        $response['errorDetails'] = [
            'phpErrors' => $errorOutput,
            'exceptionTrace' => $e->getTraceAsString()
        ];
    }
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}

// Clear any buffered output
ob_end_clean();

// Send JSON response
echo json_encode($response); 