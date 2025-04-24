<?php
require_once "../_needSession.php";

require "../connect.php";

header('Content-Type: application/json');

$query = $conn->query("SELECT GradeID, GradeName FROM grade  order by gradeName ASC");
$grades = [];

while ($row = $query->fetch_assoc()) {
    $grades[] = $row;
}

echo json_encode($grades);
?> 