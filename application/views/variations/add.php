<?php
$attributes = array('id'    => 'form-add-variant',
                    'class' => 'rounded',
                   );
echo form_open('variations/add', $attributes);
?>
    <p>To add a variant, enter its <b>genomic position (Hg19)</b></p>
    <div class="input-append">
        <input id="variation" name="variation" type="text" class="input-xlarge" value="<?php echo set_value('variation', $default_value); ?>" placeholder="i.e. chr3:191075848:G>A">
        <button id="add-variant-submit" class="btn btn-primary" type="submit" title="Add variant">Add</button>
    </div>
    <p id="force-add-variant-wrapper" class="label-checkbox-pair <?php echo $hide_force_option; ?>">
        <input type="checkbox" id="force-add-variant" name="force-add-variant" value="force">
        <label for="force-add-variant">
            <small>Add  without any autofilled data</small>
        </label>
    </p>
</form>
<div id="add-variant-progress">
    <div>
        <p>Retreiving data. Please wait...</p>
    </div>
    <div class="progress progress-striped active">
        <div class="bar"></div>
    </div>
</div>
