<?php require "_adminSessionHeader.php" ?>
<?php
require_once 'htmlpurifier-4.15.0-lite/library/HTMLPurifier.auto.php';
require_once '_incFunctions.php';
require "connect.php";



if ($_SERVER["REQUEST_METHOD"] == "POST") {  
    if (isset($_POST["grade"])) {
        $grade = $conn->real_escape_string(trim(sanitizeHTML($_POST["grade"])));
    }
    else
    {
        echo "Grade is required.";
        exit;
    }


    if ($_FILES['wordListFile']['error'] == UPLOAD_ERR_OK         //checks for errors
      && is_uploaded_file($_FILES['wordListFile']['tmp_name'])) { //checks that file is uploaded
        $wordlist =  file_get_contents($_FILES['wordListFile']['tmp_name']); 

        $wordlist = str_replace("，", ",", $wordlist); //replace Chinese comma with English comma
        $words = preg_split("/[',']+/", $wordlist); 
        
        $msg = "";

        foreach($words as $word){
            
            $word = trim($word);
          
            $msg = $msg. $word ."|";
            if (empty($word))
            {
                continue;
            }
            $sql = "INSERT INTO `wordslibrary` (`Words`, `Level`) SELECT * FROM  (SELECT ? as Words, ? as Level) AS tmp ";
            $sql = $sql." WHERE NOT EXISTS (Select `Words` From `wordslibrary` WHERE `Level` = ? and `Words` = ?) LIMIT 1;";

            if($stmt = $conn->prepare($sql)){
                $stmt->bind_param("siis", $word, $grade, $grade, $word);
                $stmt->execute();   
                $msg =$msg."+";  
            }
        }
        echo ($msg. " Words have been imported successfully.");
    }
    else
    {
        echo "File upload failed.";
    }
}
?>
<script>

</script>


<div class="container py-3">
    <h2 class="border-bottom border-dark">Import Words</h2>
 
    <div class="row">
       <form action="adminWords.php" method="post" enctype="multipart/form-data">

            <div class="input-component">
                <div class="label-frame">
                    <div class="label">Grade:</div>
                </div>
                <div>
                            <?php
                            $options = "<option  value=''>Select Grade</option>";
                            $sql = "SELECT GradeId, GradeName FROM grade order by gradeName ASC";
                            $result = $conn->query($sql);
                            while ($row = $result->fetch_assoc()) {
                                $optionValue = $row["GradeId"];
                                $optionName = $row["GradeName"];
                                $Selected="";

                                $options .= "<option value=\"$optionValue\">Grade $optionName</option>";
                            }
                            echo '<select name="grade" required class="form-select form-select-lg mb-3 textbox-frame form-control" aria-label=".form-select-lg example">';
                            echo $options;
                            echo '</select>';
                            ?>
                </div>
            </div>
            <div class="input-component">
                <div class="label-frame">
                    <div class="label">Word list file:</div>
                </div>
                <div>
                <input type="hidden" name="MAX_FILE_SIZE" value="30000" />
                <input type="file" id="wordListFile" name="wordListFile" required accept=".txt,.csv" />
                </div>
            </div>
            <div class="frame-button">
                <div class="frame-button2">
                    <button class="button submit" type="submit">Upload File</button>
                </div>
            </div>
        </form>
    </div>

</div>
<?php require "_footer.php" ?>