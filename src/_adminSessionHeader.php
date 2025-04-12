<?php require "_needSession.php";?>


<html>
<head>
    <title> MLCCC 识字比赛
    </title>
 
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
   <link rel="stylesheet" type="text/css" href="css\styles.css" />
</head>

<body>
    <div class="responsive">
        <div class="header">
            <div class="header2">
                <div class="logo" style="cursor:pointer" onclick="(()=>{window.location.assign('studentInfo.php')})()">
                    <img class="logo2" src="images/logo.png" />
                </div>
                <div class="mlccc-words-test" style="cursor:pointer" onclick="(()=>{window.location.assign('studentInfo.php')})()">识字比赛<br>Character Recognition Contest</div>

                <ul class="nav navbar-nav navbar-right">
                <?php
                    if (isset($_SESSION["IsAdmin"])){
                        echo "<li> <a href='admin.php'>Admin</a></li>";
                    }                    
                    ?>  
                    <?php
                        if (isset($_SESSION["userType"]) && $_SESSION["userType"]!= "guest"){
                            echo "<li> <a href='personalAccount.php'> Account</a></li>";
                        }
                    ?>   
                    <li> <a href="logout.php"> Logout</a></li>
                </ul>
        </div>
        </div>
        <div>
            <ul class="adminMenu">
                <li><a href="admin.php">Events</a></li>
                <li><a href="useradmin.php">Users</a></li>
                <li><a href="adminWords.php">Words</a></li>
            </ul>
        </div>
        <div style="text-align:right; margin: 10px 20px 0 0;">
            <?php
                // Timezone selection logic (shared with admin.php)
                $allowedTimezones = ['UTC', 'America/New_York', 'America/Chicago', 'America/Los_Angeles'];
                $selectedTz = 'UTC';
                if (isset($_GET['tz']) && in_array($_GET['tz'], $allowedTimezones)) {
                    $selectedTz = $_GET['tz'];
                    setcookie('admin_timezone', $selectedTz, time() + (86400 * 365), "/");
                } elseif (isset($_COOKIE['admin_timezone']) && in_array($_COOKIE['admin_timezone'], $allowedTimezones)) {
                    $selectedTz = $_COOKIE['admin_timezone'];
                }
            ?>
            <form id="tzForm" style="display:inline;">
                <label for="timezoneSelect" style="font-weight:normal;">Display Timezone:</label>
                <select id="timezoneSelect" name="tz" style="width:auto;display:inline;">
                    <option value="UTC" <?php echo ($selectedTz === 'UTC') ? 'selected' : ''; ?>>UTC</option>
                    <option value="America/New_York" <?php echo ($selectedTz === 'America/New_York') ? 'selected' : ''; ?>>US Eastern (EDT)</option>
                    <option value="America/Chicago" <?php echo ($selectedTz === 'America/Chicago') ? 'selected' : ''; ?>>US Central (CDT)</option>
                    <option value="America/Los_Angeles" <?php echo ($selectedTz === 'America/Los_Angeles') ? 'selected' : ''; ?>>US Pacific (PDT)</option>
                </select>
            </form>
        </div>
        <script>
        // Move this script to header or keep here for timezone select
        document.getElementById('timezoneSelect').addEventListener('change', function() {
            var selectedTz = this.value;
            // Set cookie for 1 year
            document.cookie = "admin_timezone=" + encodeURIComponent(selectedTz) + ";path=/;max-age=" + (60*60*24*365);
            // Reload with tz param
            var currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('tz', selectedTz);
            window.location.assign(currentUrl.toString());
        });
        </script>
        <div>
            <P>
                <?php
                if (!$_SESSION["IsAdmin"]=='1') {
                    $error = "Only admins can access this page.";
                    header("Location: error.php?error=" . urlencode($error));
                } else {
                    echo "Welcome, admin.";
                }
                ?>
            </P>
        </div>
    