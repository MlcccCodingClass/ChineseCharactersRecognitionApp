<?php require "_adminSessionHeader.php" ?> 
<?php require_once '_incFunctions.php' // htmlpurifier-4.15.0-lite/library/HTMLPurifier.auto.php is included ?>
<?php
   if (isset($_GET['includeNonActive']) && $_GET['includeNonActive']==1) {
        $includeNonActive = true;                  
    } else {     
        $includeNonActive = false;               
   }

   // Determine selected timezone
   // Determine selected timezone: priority is query param > cookie > UTC
   $allowedTimezones = ['UTC', 'America/New_York', 'America/Chicago', 'America/Los_Angeles'];
   $selectedTz = 'UTC';
   if (isset($_GET['tz']) && in_array($_GET['tz'], $allowedTimezones)) {
       $selectedTz = $_GET['tz'];
       setcookie('admin_timezone', $selectedTz, time() + (86400 * 365), "/"); // 1 year
   } elseif (isset($_COOKIE['admin_timezone']) && in_array($_COOKIE['admin_timezone'], $allowedTimezones)) {
       $selectedTz = $_COOKIE['admin_timezone'];
   }
   $displayTimezone = new DateTimeZone($selectedTz);
   $utcTimezone = new DateTimeZone('UTC');

?>
<script>
function limitTextLength(event, maxLength) {
    if (event.target.textContent.length >= maxLength && !isSpecialKey(event)) {
        event.preventDefault(); // Prevent further input
    }
}

function isSpecialKey(event) {
    return event.ctrlKey && (event.key === 'a' || event.key === 'x') ||isNavigationKey(event.key);
}

function isNavigationKey(key) {
    return key.includes('Arrow') || key === 'Backspace' || key === 'Delete' || key === 'Home' || key === 'End';
}

function showMessage(message, isSuccess = true) {
    $('#modalMessage').text(message);
    $('#messageModal').modal('show');
    
    if (isSuccess) {
        $('#messageModal').on('hidden.bs.modal', function () {
            location.reload();
        });
    }
}

$(function() {
        // Create New Row
        $('#add_event').click(function() {
            if ($('tr[data-id=""]').length > 0) {
                $('tr[data-id=""]').find('[name="eventName"]').focus()
                return false;
            }
            var tr = $('<tr>')
            $('input[name="id"]').val('')
            tr.addClass('py-1 px-2');
            tr.attr('data-id', '');
            tr.append('<td contenteditable name="eventName" onkeydown="limitTextLength(event, 100)"></td>')
            tr.append('<td contenteditable name="accessKey" onkeydown="limitTextLength(event, 100)"></td>')
            tr.append('<td contenteditable name="activeDate"></td>')
            tr.append('<td contenteditable name="expiredDate"></td>')
            tr.append('<td name="isPrivate" class="text-center"><input type="checkbox" class="isPrivate-checkbox"></td>')
            tr.append('<td class="text-center"><button class="btn btn-sm btn-primary btn-flat rounded-0 px-2 py-0">Save</button><button class="btn btn-sm btn-dark btn-flat rounded-0 px-2 py-0" onclick="cancel_button($(this))" type="button">Cancel</button></td>')
            $('#form-tbl').append(tr)
            tr.find('[name="eventName"]').focus()
        })

        // Edit Row
        $('.edit_data').click(function() {
            var id = $(this).closest('tr').attr('data-id')
            $('input[name="id"]').val(id)
            var count_column = $(this).closest('tr').find('td').length
            $(this).closest('tr').find('td').each(function() {
                if ($(this).index() != (count_column - 1))
                    $(this).attr('contenteditable', true)
            })
            $(this).closest('tr').find('.isPrivate-checkbox').prop('disabled', false)
            $(this).closest('tr').find('[name="eventName"]').focus()
            $(this).closest('tr').find('.editable').show('fast')
            $(this).closest('tr').find('.noneditable').hide('fast')
        })


        $('#form-data').submit(function(e) {
            e.preventDefault();
            var id = $('input[name="id"]').val();
           
            var data = {};
            // check fields promise
            var check_fields = new Promise(function(resolve, reject) {
                    data['id'] = id;
                    var tr = $('tr[data-id="' + (id || '') + '"]');
                    var isValid = true;
                    tr.find('td[contenteditable]').each(function() {
                        var fieldName = $(this).attr('name');
                        var fieldValue = $(this).text().trim();
                        data[fieldName] = fieldValue;

                        if (fieldValue === '') {
                            showMessage("All fields are required.", false);
                            isValid = false;
                            resolve(false);
                            return false; // Exit .each loop
                        }

                        // Basic date format validation
                        if (fieldName === 'activeDate' || fieldName === 'expiredDate') {
                            // Basic datetime format validation (YYYY-MM-DD HH:MM)
                            if (!/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/.test(fieldValue)) {
                                showMessage("Invalid date/time format for " + fieldName + ". Please use YYYY-MM-DD HH:MM (24-hour format).", false);
                                isValid = false;
                                resolve(false);
                                return false; // Exit .each loop
                            }
                        }
                    });

                    if (!isValid) return; // Stop if validation failed in the loop

                    // Add isPrivate value
                    data['isPrivate'] = tr.find('.isPrivate-checkbox').is(':checked') ? 1 : 0;

                    // Add the display timezone used for editing
                    data['displayTimezone'] = $('#timezoneSelect').val();

                    resolve(true);
                })
            check_fields.then(function(resp) {
                if (!resp)
                    return false;

                $.ajax({
                    url: "adminAPI.php",
                    method: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function(res) {
                        showMessage(res, res.includes('successfully'));
                    },
                    error: function (err) {
                        showMessage('An error occurred while saving the data!', false);
                        console.log(err);
                    }
                })
            })
        })
    })


// removing table row when cancel button triggered clicked
window.cancel_button = function(_this) {
    var tr = _this.closest('tr');
    if (tr.attr('data-id') == '') {
        tr.remove()
    } else {
        $('input[name="id"]').val('')
        tr.find('td').each(function() {
            $(this).removeAttr('contenteditable')
        })
        tr.find('.isPrivate-checkbox').prop('disabled', true);
        tr.find('.editable').hide('fast')
        tr.find('.noneditable').show('fast')
    }
}

function switchEventList(includeNonActive)
{
    if (includeNonActive == 1)
    {
        window.location.assign('admin.php?includeNonActive=1');
    }
    else
    {
        window.location.assign('admin.php');
    }
}

function chkIncludeNonActive_Click(checkbox) {
    if(checkbox.checked){
        window.location.assign('admin.php?includeNonActive=1');
    }
    else{
        window.location.assign('admin.php');
    }
}

function changeTimezone(selectElement) {
    var selectedTz = selectElement.value;
    // Set cookie for 1 year
    document.cookie = "admin_timezone=" + encodeURIComponent(selectedTz) + ";path=/;max-age=" + (60*60*24*365);
    var currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('tz', selectedTz);
    window.location.assign(currentUrl.toString());
}

</script>

<style>
/* Add these styles for the modal */
#messageModal .modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

#messageModal .modal-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
}

#messageModal .modal-body {
    padding: 20px;
}

#modalMessage {
    margin: 0;
    font-size: 16px;
}
</style>

<div class="container">
    <h2>Event List</h2>
    <p class="alert alert-danger"><b>Note to admin</b>:<br> Guest with EventKey can access active private event, you should only make one private event active for MLLCCC competition.
    and you should keep one event pubic for students to practice. <br>
    
    <div class="row">

        <div class="col-12">
            <!-- Table Form start -->
            <form action="" id="form-data">
                <?php 
                    if ($includeNonActive) {
                        echo '<input type="checkbox" id="chkIncludeNonActive" name="chkIncludeNonActive" checked onclick="chkIncludeNonActive_Click(this)">';                 
                    } else {                
                        echo '<input type="checkbox" id="chkIncludeNonActive" name="chkIncludeNonActive" onclick="chkIncludeNonActive_Click(this)">';  
                    }
                ?>
                <label for="chkIncludeNonActiveEvent">Include non-active events</label>
                <div style="float: right;">
                    <label for="timezoneSelect">Display Timezone:</label>
                    <select id="timezoneSelect" name="tz" onchange="changeTimezone(this)">
                        <option value="UTC" <?php echo ($selectedTz === 'UTC') ? 'selected' : ''; ?>>UTC</option>
                        <option value="America/New_York" <?php echo ($selectedTz === 'America/New_York') ? 'selected' : ''; ?>>US Eastern (EDT)</option>
                        <option value="America/Chicago" <?php echo ($selectedTz === 'America/Chicago') ? 'selected' : ''; ?>>US Central (CDT)</option>
                        <option value="America/Los_Angeles" <?php echo ($selectedTz === 'America/Los_Angeles') ? 'selected' : ''; ?>>US Pacific (PDT)</option>
                    </select>
                </div>
                <br>
                <input type="hidden" name="id" value="">
                <table id="form-tbl">
                    <colgroup>
                        <col width="20%">
                        <col width="15%">
                        <col width="15%">
                        <col width="15%">
                        <col width="15%">
                        <col width="20%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="text-center p-1">Event Name</th>
                            <th class="text-center p-1">Access Key</th>
                            <th class="text-center p-1">Active Date</th>
                            <th class="text-center p-1">Expire Date</th>
                            <th class="text-center p-1">Private</th>
                            <th class="text-center p-1">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    if ($includeNonActive) {
                        $query = $conn->query("SELECT * FROM `event` ORDER by ExpiredDate DESC limit 50");                      
                    } else {                
                        $query = $conn->query("SELECT * FROM `event` WHERE ExpiredDate> CURRENT_DATE() and ActiveDate<=CURRENT_DATE() ORDER by ExpiredDate DESC");                               
                    }
                  
                    while($row = $query->fetch_assoc()):
                    ?>
                    <tr data-id='<?php echo $row['ID'] ?>'>
                        <?php
                            $eventName = $row['EventName']; 
                            $eventID = $row['ID'];
                        ?>
                        <td name="eventName" onkeydown="limitTextLength(event, 100)"><?php echo "<a href=eventInfo.php?event=".$eventID.">".$eventName."</a>" ?></td>
                        <td name="accessKey" onkeydown="limitTextLength(event, 100)"><?php echo $row['AccessKey'] ?></td>
                        <td name="activeDate"><?php
                            $activeDate = new DateTime($row['ActiveDate'], $utcTimezone);
                            $activeDate->setTimezone($displayTimezone);
                            echo $activeDate->format('Y-m-d H:i');
                        ?></td>
                        <td name="expiredDate"><?php
                            $expiredDate = new DateTime($row['ExpiredDate'], $utcTimezone);
                            $expiredDate->setTimezone($displayTimezone);
                            echo $expiredDate->format('Y-m-d H:i');
                        ?></td>
                        <td name="isPrivate" class="text-center">
                            <input type="checkbox" class="isPrivate-checkbox" <?php echo $row['isprivate'] ? 'checked' : ''; ?> 
                                  disabled=true>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-primary btn-sm rounded-0 py-0 edit_data noneditable" type="button">Edit</button>
                            <button class="btn btn-sm btn-primary btn-flat rounded-0 px-2 py-0 editable">Save</button>
                            <button class="btn btn-sm btn-dark btn-flat rounded-0 px-2 py-0 editable" onclick="cancel_button($(this))" type="button">Cancel</button></td>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </form>
            <!-- Table Form end -->
        </div>
        <div class="w-100 d-flex pposition-relative justify-content-center">
            <button class="btn btn-flat btn-primary" id="add_event" type="button">Add New Event</button>
        </div>
    </div>
</div>

<!-- Add this modal HTML before the closing </div> of the container -->
<div class="modal fade" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageModalLabel">Message</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="modalMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<?php require "_footer.php" ?>