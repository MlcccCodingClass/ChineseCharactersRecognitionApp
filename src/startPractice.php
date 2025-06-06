<?php

require_once '_incFunctions.php';
require "connect.php";
$error = '';
$studentName = '';
$grade = '';
$numberOfWords = 0;
$timeLimit = 0;
$wordlist='';

    try {
        if (isset($_GET['studentName'])) {
            $studentName = $conn->real_escape_string(sanitizeHTML($_GET['studentName']));
        };
        if (isset($_GET['grade'])) {
            $grade = $conn->real_escape_string(sanitizeHTML($_GET['grade']));

            $sql = sprintf(
                "SELECT GradeName, NumberOfWords, TimeLimit FROM grade WHERE GradeId = %s",
                $grade);

            $result = $conn->query($sql);
            $row = $result->fetch_object();

            if ($row != null) {
                $numberOfWords = $row->NumberOfWords;
                $timeLimit = $row->TimeLimit;

                //continue to get word list from database based on grade and NumberOfWords
                $sql_words = sprintf(
                    "SELECT ID, Words FROM wordslibrary WHERE Level = %s  ORDER BY RAND() limit %s",
                    $grade,
                    $conn->real_escape_string($numberOfWords));

                $result = $conn->query($sql_words);       
                $wordlist = $result->fetch_all(MYSQLI_ASSOC);
               
                //window.sessionStorage.setItem('wordlist', serialize($rows));
                //setcookie('wordlist', serialize($rows), time()+3600);
                setcookie('timeLimit', $timeLimit, time()+3600);


            } else {
                $error = 'Invalid grade.';
            }
            $conn->close();
        };
    }
    catch (Exception $e) {
        $error = "Invalid grade";
    }
?>


<?php require "_sessionHeader.php" ?>

<script>

var testList = [];
var wordItem;
    <?php
    foreach ($wordlist as $item) : 
    ?>
    wordItem = {
            id: <?php echo $item['ID']?>,
            word: "<?php echo $item['Words']?>",
            passed:null,
            timeElapsed:null
            };

        testList.push(wordItem);
    <?php endforeach; ?>
sessionStorage.setItem("wordlist", JSON.stringify(testList));
</script>
<div class="container">
            <div class="row">
         
            <div class="frame-main col-md-12 col-sm-12">
               
                
                <div class="label" style="margin-bottom:10px;">Welcome to Level&nbsp <b><?php echo "$row->GradeName" ?> Practice Test</b>&nbsp! 
                </div>
                <div class="label"  style="margin-top:10px;margin-bottom:10px;">In this contest, students will identify&nbsp <b><?php echo "$numberOfWords" ?></b>&nbspChinese words.
                </div>
                <div class="label"  style="margin-top:10px;margin-bottom:10px;">
                    If the student identifies the word correctly, they will receive one point. There are no half points. The student <br>either gets it correct or incorrect. 
                </div>
                <div class="label"  style="margin-top:10px;;margin-bottom:10px;">
                    Each word will be displayed for&nbsp<b> <?php echo "$timeLimit" ?> </b>&nbspseconds. 
                </div>
                <div class="label"  style="margin-top:10px;;margin-bottom:10px;">
                    If the time runs out, it will be considered incorrect.
                </div>

                <div class="label"  style="margin-top:10px;;margin-bottom:10px;">
                    In the Audio Practice, students will read aloud each word. The audio button is available in the top right for self-grading purposes.
                </div>

                <div class="label"  style="margin-top:10px;;margin-bottom:10px;">
                    In the Graded Practice, students will read aloud each word and the system will grade the recorded response based on correct pinyin and tone pronounciation.
                    
                    To ensure that the system is able to recognize the words, please say at least 2 words for each response. For example, if the word is "我", please say "我的" or "我是".
                </div>

                <div class="frame-botton">
                    <div class="frame-botton2 col-sm-4 col-xs-12">
                        <div class="button button-tall" onclick="(()=>{window.location.assign('studentPractice.php')})()">
                            <div class="submit">Audio Practice</div>
                        </div>
                    </div>
                     <div class="frame-botton2 col-sm-4 col-xs-12">
                        <div class="button button-tall" onclick="(()=>{window.location.assign('studentgradedPractice.php')})()">
                            <div class="submit">Graded Practice</div>
                        </div>
                    </div>
                </div>

            
      
            </div>
           
            </div>
        </div>
   
<?php require "_footer.php" ?>