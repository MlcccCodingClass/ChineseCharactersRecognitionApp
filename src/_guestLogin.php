<?php
require_once '_incFunctions.php';
?>
<div class="form-title">Guest Judge Login</div>
<form action="guestLoginProcess.php" method="post">
    <div class="input-component">
        <div class="label-frame">
            <div class="label">GuestName</div>
        </div>
        <div>
            <input name="username" class="textbox-frame form-control" id="txtUserName" placeholder="">
        </div>
    </div>
    <div class="input-component">
        <div class="label-frame">
            <div class="label">AccessKey</div>
        </div>
        <div>
            <input type="password" name="accessKey" class="textbox-frame form-control" id="txtAccessKey"
                placeholder="access key">
        </div>
    </div>
    <?php
        if (!empty($_GET['guestError'])) {
            $error =sanitizeHTML($_GET['guestError']);
            echo "<p style='color: red;'>$error</p>";
        }
    ?>
    <div class="frame-button">
        <div class="frame-button2">
            <button class="button submit" type="submit">Log In</button>
        </div>
    </div>
</form>
