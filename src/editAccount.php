<?php require "_sessionHeader.php" ?>
<?php require_once '_incFunctions.php';
if (!$_SESSION["IsAdmin"]) {
    $options = "<option  value=''>Select Grade</option>";
    $sql = "SELECT Grade FROM grade";
    $result = $conn->query($sql);
    $grade=isset($_SESSION["grade"]) ? sanitizeHTML($_SESSION["grade"]) : "";
    while ($row = $result->fetch_assoc()) {
        $optionValue = $row["Grade"];
        $Selected="";
        if($optionValue==$grade)
            $Selected="selected";
        $options .= "<option value=\"$optionValue\" ".$Selected.">Grade $optionValue</option>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {  
    // username and password sent from form 
    if (isset($_POST["oldPassword"])) {
        $grade = $conn->real_escape_string(trim(sanitizeHTML($_POST["grade"])));
        $myOldPassword = $conn->real_escape_string(trim(sanitizeHTML($_POST["oldPassword"]))); 
        $myNewPassword = $conn->real_escape_string(trim(sanitizeHTML($_POST["newPassword"]))); 
        $myNewPasswordRetyped = $conn->real_escape_string(trim(sanitizeHTML($_POST["newPasswordRetyped"])));
        $myemail = $_SESSION["loginUser"];
        if (!$_SESSION["IsAdmin"]) {
            $sql = "SELECT StudentId FROM student WHERE Email = '$myemail' and Password = '$myOldPassword'";
            $result = $conn->query($sql);
            $row = $result->fetch_object();
        } else {
            $sql = "SELECT ID FROM user WHERE Email = '$myemail' and Password = '$myOldPassword'";
            $result = $conn->query($sql);
            $row = $result->fetch_object();
        }
        
        if ($row != null) {
            if ($myNewPassword == $myNewPasswordRetyped) {
                if (!$_SESSION["IsAdmin"]) {
                    if ($myNewPassword != null) {
                        $sql = "UPDATE student SET Password = '$myNewPassword' WHERE Email = '$myemail'";
                        $conn->query($sql);
                    }
                    if ($grade != null) {
                        $sql = "UPDATE student SET GradeID = '$grade' WHERE Email = '$myemail'";
                        $conn->query($sql);
                    }
                } else {
                    if ($myNewPassword != null) {
                        $sql = "UPDATE user SET Password = '$myNewPassword' WHERE Email = '$myemail'";
                        $conn->query($sql);
                    }
                }
                $error = "Account successfully updated";
            } else if ($myNewPassword != $myNewPasswordRetyped) {
                $error = "New password does not match retyped password";
            }
        } else {
            $error = "Incorrect password";
        }
        header("Location: editAccount.php?error=" . urlencode($error));
        exit();
    }
}
?>

<div class="two-column-frame container">
    <div class="frame col-md-5 col-sm-8">
        <form action="" method="post">
            <div class="form-title">Edit Account Details</div>

            <div class="input-component">
                <div class="label-frame">
                    <div class="label">Password</div>
                </div>
                <div >
                    <input type="password" name="oldPassword" class="textbox-frame form-control" id="txtPassword" placeholder="Password" />
                </div>
            </div>

            <div class="input-component">
                <div class="label-frame">
                    <div class="label">New Password </div>
                </div>
                <div >
                    <input type="password" name="newPassword" class="textbox-frame form-control" id="txtPassword" placeholder="(Leave blank if you don't want to change)" />
                </div>
            </div>

            <div class="input-component">
                <div class="label-frame">
                    <div class="label">Retype New <br> Password</div>
                </div>
                <div >
                    <input type="password" name="newPasswordRetyped" class="textbox-frame form-control" id="txtPassword" placeholder="(Leave blank if you don't want to change)" />
                </div>
            </div>

            <?php
                if (!$_SESSION["IsAdmin"]):
            ?>
            <div class="input-component">
                <div class="label-frame">
                    <div class="label">Grade</div>
                </div>
                <div>
                    <?php
                    echo '<select name="grade" class="form-select form-select-lg mb-3 textbox-frame form-control" aria-label=".form-select-lg example">';
                    echo $options;
                    echo '</select>';
                    ?>
                </div>
            </div>
            <?php
                endif
            ?>

            <?php
                if (isset($_GET['error'])) {
                    $error = sanitizeHTML($_GET['error']);
                    echo "<p style='color: red;'>$error</p>";
                }
            ?>

            <div class="frame-botton">
                <div class="frame-botton2">
                    <button class="button submit" type="submit">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require "_footer.php" ?>