<?php
echo $page_links;
$attributes = array('id' => 'form-release-changes', 
                    'class' => 'rounded',
                   );
echo form_open('variations/submit', $attributes);
?>
    <div class="row-fluid">
        <h3 id="unreleased-header" class="span10"><?php echo $header; ?></h3> 
        <!-- "Confirm" label (hide if no variant changes exist) -->
        <div id="notice-confirm-variant" class="span2 <?php echo hidden(empty($variants)); ?>"><small>Confirm</small></div>
    </div>
    <?php foreach ($variants as $variant): ?>
        <div class="row-fluid">
            <div class="accordion span11" id="accordion-variant-changes-<?php echo $variant['id']; ?>">
                  <div class="accordion-group">
                        <div class="accordion-heading">
                            <a class="accordion-toggle rowlink" data-toggle="collapse" data-parent="#accordion-variant-changes-<?php echo $variant['id']; ?>" href="#variant-changes-<?php echo $variant['id']; ?>">
                                <i class="icon-minus"></i> <b><?php echo $variant['name']; ?></b><?php echo new_variant_notice($variant['is_new']); ?>
                            </a>
                        </div>
                        <div id="variant-changes-<?php echo $variant['id']; ?>" class="accordion-body collapse in">
                            <div class="accordion-inner">
                                <?php echo deletion_notice($variant['id']); ?>
                                <!-- Table of changes (only display if changes exist) -->
                                <table class="variant-changes-table table table-striped table-bordered table-condensed <?php echo hidden(empty($variant['changes'])); ?>">
                                    <thead>
                                        <tr>
                                            <th>Field</th>
                                            <th>Current value</th>
                                            <th>Unreleased value</th>
                                        </tr>
                                    </thead>
                                    <tbody data-provides="rowlink">
                                        <?php foreach ($variant['changes'] as $field => $diffs): ?>
                                            <tr>
                                                <td><a href="<?php echo site_url('variations/edit/'.$variant['id'].'?expand=true'.'#'.$field); ?>"><?php echo $field; ?></a></td>
                                                <td><?php echo $diffs['live_value']; ?></td>
                                                <td><?php echo $diffs['queue_value']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <div>
                                    <a href="<?php echo site_url('variations/edit/'.$variant['id'].'?expand=true#informatics_comments'); ?>" class="rowlink">
                                        <?php echo informatics_team_comments($variant['id']); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                  </div>
            </div>
            <label class="variant-change-confirm-label hold-change span1">
                <i class="icon-off"></i>
                <input name="unconfirmed-variants[]" value="<?php echo $variant['id']; ?>" class="variant-change-confirm" type="checkbox" <?php echo variant_confirmation_status($variant['id']) ?>/>
            </label>
        </div>
    <?php endforeach; ?>
    <?php echo $page_links; ?>
    <!-- "Release changes" button (hide if no variant changes exist) -->
    <div id="release-changes-wrapper" class="rounded <?php echo hidden(empty($variants)); ?>">
        <h4>Ready to release these changes?</h4> 
        <button id="release-variant-changes" type="button" class="btn btn-primary btn-medium" data-toggle="modal" data-target="#modal-release-confirm">Release changes</button>
    </div>

    <!-- "Confirm/Unconfirm all" buttons (hide if no variant changes exist) -->
    <span id="confirm-toggles" class="<?php echo hidden(empty($variants)); ?>">
        <label id="unconfirm-all" class="hold-change" type="button">
            <i class="icon-off"></i>
        </label>
        <label id="confirm-all" class="release-change" type="button">
            <i class="icon-off"></i>
        </label>
        <span id="confirm-all-label">Confirm/unconfirm all</span>
    </span>

    <!-- "Initial release" button (display for Version 0 only) -->
    <div id="initial-release-wrapper" class="rounded <?php echo hidden(($this->version != 0)); ?>">
        <h4>Create an initial release?</h4> 
        <button name="release-changes" class="btn btn-medium btn-primary" type="submit" value="Release">Create</button>
    </div>

    <!-- "Special release options" tab (hide if no variant changes exist) -->
    <div class="accordion <?php echo hidden(empty($variants)); ?>" id="special-release-options">
        <div class="accordion-group">
            <div class="accordion-heading">
                <a class="accordion-toggle rowlink" data-toggle="collapse" data-parent="#special-release-options" href="#special-release-options-collapse">
                  <i class="icon-plus"></i> <b>Special release options</b>
                </a>
            </div>
            <div id="special-release-options-collapse" class="accordion-body collapse">
                <div class="accordion-inner">
                    <p class="label-radio-pair">
                        <input type="radio" id="special-release-none" name="special-release" value="none" checked>
                        <label for="special-release-none">
                            <b>None</b><br/>
                            <small>All changes must be confirmed prior to release.</small>
                        </label>
                    </p>
                    <p class="label-radio-pair">
                        <input type="radio" id="special-release-force-confirmed" name="special-release" value="force-confirmed">
                        <label for="special-release-force-confirmed">
                            <b>Force confirmed only</b><br/>
                            <small>Release only the changes that have been confirmed. All other changes will remain in the queue.</small>
                        </label>
                    </p>
                    <p class="label-radio-pair">
                        <input type="radio" id="special-release-force-all" name="special-release" value="force-all">
                        <label for="special-release-force-all">
                          <b>Force all</b><br/>
                          <small>Release all changes regardless of confirmation status.</small>
                        </label>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- "Release changes" confirmation modal -->
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
            <button name="release-changes" class="btn btn-medium btn-primary" type="submit" value="Release">OK</button>
        </div>
    </div>
    <!-- "Save changes" button -->
    <div id="affixed-save-wrapper" class="affix" data-offset-top="50">
        <button name="save-changes" class="btn btn-medium btn-primary" type="submit" value="Save">Save</button>
    </div>
</form>
