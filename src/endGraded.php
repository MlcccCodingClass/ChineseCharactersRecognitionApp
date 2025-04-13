<?php
    require_once "_needSession.php";
    require_once '_incFunctions.php';
    require "connect.php";

    $activityidFromSession = $_SESSION["activityid"];
    $activitiesSql = "SELECT StudentName, Level, FinalScore, TimeSpent FROM activities WHERE ActivityID = '$activityidFromSession'";
    $result = $conn->query($activitiesSql);
    if (!$result) {
        die("Error executing query: " . $conn->error);
    }
    $row = $result->fetch_object();
    
    $studentName = $row->StudentName;
    $level = $row->Level;
    $finalScore = $row->FinalScore;
    $timeSpent = $row->TimeSpent;

    $sqlGrade = "SELECT GradeId, GradeName FROM grade WHERE GradeId='$level'";
    $resultGrade = $conn->query($sqlGrade);
    if (!$resultGrade) {
        die("Error executing query: " . $conn->error);
    }
    $rowGrade = $resultGrade->fetch_object();
    $gradeLevel = $rowGrade->GradeName;
?>

<?php require "_sessionHeader.php" ?>

<div class="container">
    <div class="row">
        <div class="frame-main col-md-12 col-sm-6">
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    Result:
                    <div class="label wrap">Student: <?php echo $studentName ?></div>
                    <div class="label wrap">Level: <?php echo $gradeLevel ?></div>
                    <div class="label wrap">Score: <?php echo $finalScore ?>%</div>
                    <div class="label wrap">Time used: <?php echo $timeSpent ?> seconds</div>
                </div>
                <div class="col-md-6 col-sm-12">
                    Wrong Words:
                    <div class="label wrap" id="div-wrong-words"></div>
                </div>
            </div>  
            <div class="frame-button">
                <div class="frame-button2">
                    <div class="button button-tall" onclick="(()=>{window.location.assign('studentInfo.php')})()">
                        <div class="submit">Next Practice</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require "_footer.php" ?>

<script>
    var testResultString = sessionStorage.getItem("testResult");
    //to get the object we have to parse it.
    var testResult = JSON.parse(testResultString);
    console.log(testResult);
    var wrong_list = "";
    testResult.forEach(item => {
        if (!item.passed) {
            if (wrong_list != "") {
                wrong_list += ", ";
            }
            let answer = item.answer !== undefined ? item.answer : "-";
            let result = item.result !== undefined ? item.result : "-";
            wrong_list += item.word + " (" + answer + "," + result + ")";
        }
    });
    document.getElementById('div-wrong-words').innerText = wrong_list;
</script>