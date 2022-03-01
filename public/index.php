<?php
require(__DIR__.'/../bootstrap.php');

$log_start_dt = strtotime('today 00:00');

/* Add an entry */
if(isset($_POST['submitaddentry'])) {

    // TODO: sanitize form values.
    $tz = new DateTimeZone($_POST['tz']);
    $dt1 = NULL;
    $dt2 = NULL;

    if(!empty($_POST['date1'])) {
        $dt1 = DateTime::createFromFormat('H:i', $_POST['date1'], $tz);
    }
    else {
        $dt1 = new DateTime();
    }

    if(!empty($_POST['date2'])) {
        $dt2 = DateTime::createFromFormat('H:i', $_POST['date2'], $tz);
    }

    $entry = [
        'event' => $_POST['event'],
        'start_date' => $dt1 ? $dt1->getTimestamp() : NULL,
        'end_date' => $dt2 ? $dt2->getTimestamp() : NULL,
    ];

    if(addLogEntry($entry)) {
        redirect('/');
    }
}

/* Stop an entry */

if(isset($_POST['stop-entry'])) {
    $entry = getLogEntry($_POST['stop-entry']);
    if(!$entry) {
        throw new Exception('No entry for the specified ID');
    }
    updateLogEntry($entry['id'], ['enddt' => (new DateTime())->getTimestamp()]);
    redirect('/');
}


/* Delete an entry */

if(isset($_POST['delete-entry'])) {
    $entry = getLogEntry($_POST['delete-entry']);
    if(!$entry) {
        throw new Exception('No entry for the specified ID');
    }
    
    deleteLogEntry($entry['id']);
    redirect('/');
}

?>
<?php require(__DIR__.'/../template_header.php'); ?>
<div class="container">
    <div class="row">
        <div class="col-sm-12 col-lg-6 my-3">
            <div class="p-3 bg-body rounded shadow-sm">
                <form method="post" class="form-add-entry">
                    <h2 class="form-add-entry-heading">Add an entry to the log</h2>
                    <div class="form-floating mb-3">
                        <input type="text" name="event" id="inputevent" class="form-control" placeholder="Event Description..." required autofocus>
                        <label for="inputevent" class="sr-only">Type an event (use #tag for tags)</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="pit" value="1">
                        <label class="form-check-label" for="flexSwitchCheckDefault">Point in Time</label>
                    </div>
                    <div class="input-group mb-3">
                        <input type="time" name="date1" id="inputDate1" class="form-control"/>
                        <span class="input-group-text"> to </span>
                        <input type="time" name="date2" id="inputDate2" class="form-control"/>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" name="tz" id="inputtz" class="form-control"/>
                        <label for="inputtz" class="sr-only">Time Zone</label>
                    </div>
                    <button name="submitaddentry" class="btn btn-lg btn-primary btn-block" type="submit">Add Entry</button>
                </form>
            </div>
        </div>
        <div class="entries col-sm-12 col-lg-6 my-3">
            <div class="p-3 bg-body rounded shadow-sm">
                <h2 class="text-center">Entries for <span class="text-muted"><?php echo htmlspecialchars(date('l Y-m-d', $log_start_dt)); ?></span></h2>
                <form method="post" class="form-add-entry">
                    <table class="table table-sm table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <td>Event</td>
                                <td class="text-center">Start</td>
                                <td class="text-center">End</td>
                                <td class="text-center">Duration</td>
                                <td class="text-center">Actions</td>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $total_duration = 0;
                        foreach(getLogEntries(getCurrentUserId(), $log_start_dt) as $entry):
                            $total_duration += getEntryDuration($entry);
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($entry['event']) ?></td>
                                <td class="text-center"><?php if(!empty($entry['startdt'])): ?>
                                    <?php echo htmlspecialchars(displayDate($entry['startdt'], APP_TIMEZONE)) ?>
                                <?php endif; ?></td>

                                <td class="text-center"><?php if(empty($entry['enddt'])): ?>
                                    <button name="stop-entry" class="btn btn-danger btn-sm" type="submit" value="<?php echo htmlspecialchars($entry['id']); ?>"><i class="bi-x-circle"></i></button>
                                    <?php else: ?>
                                    <?php echo htmlspecialchars(displayDate($entry['enddt'], APP_TIMEZONE)) ?>
                                <?php endif; ?></td>

                                <td class="text-center"><?php if(!empty($entry['enddt']) && !empty($entry['startdt'])): ?>
                                    <?php echo htmlspecialchars(number_format(getEntryDuration($entry) / 3600, 2)); ?>
                                <?php endif; ?></td>

                                <td class="text-center">
                                    <button name="delete-entry" class="btn btn-danger btn-sm" type="submit" value="<?php echo htmlspecialchars($entry['id']); ?>"><i class="bi-trash"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; // End of looping through entries ?>
                        </tbody>
                        <tfoot>
                            <td colspan="4" class="text-end">Total Duration:</td>
                            <td class="text-center"><?php echo htmlspecialchars(number_format($total_duration/3600,2)); ?></td>
                        </tfoot>
                    </table>
                </form> <!-- /form-add-entry -->
            </div>
        </div> <!-- /entries -->
    </div> <!-- /row -->
</div> <!-- /container -->

<?php require(__DIR__.'/../template_footer.php'); ?>