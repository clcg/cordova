<h3>Release changes</h3>
<?php
$attributes = array('id' => 'form-release-changes', 'class' => 'rounded');
echo form_open('variations/release', $attributes);
?>
    <p>You are about to release all changes in the queue. Please make sure the changes have been reviewed and are correct. A backup of the current database will be made before any changes occur.</p>
    <button id="release-variant-changes" type="button" class="btn btn-primary btn-medium" data-toggle="modal" data-target="#modal-release-confirm">Release changes</button>

    <!-- Save confirmation modal -->
    <div id="modal-release-confirm" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="modal-release-confirm-label" aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="modal-release-confirm-label">Confirm</h3>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to create a backup and release all changes now?</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-medium" data-dismiss="modal" aria-hidden="true">Cancel</button>
            <button name="submit" class="btn btn-medium btn-primary" type="submit" value="Submit">OK</button>
        </div>
    </div>
</form>
