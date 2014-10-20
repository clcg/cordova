<?php echo deletion_notice($variation->id); ?>
<?php echo undesired_comments_notice($variation->id); ?>
<?php echo unreleased_changes_notice($variation->id); ?>
<div id="edit-header-wrapper">
  <div id="alter-fields">
      <a id="expand-all" class="rounded rowlink" href="#"><i class="icon-plus icon-white"></i> Expand all tabs</a>
      <?php echo unlock_all_fields_button(); ?>
  </div>
  <div id="edit-variant-header">
      <h2><?php echo $variation->hgvs_protein_change; ?></h2>
      <h4><?php echo $variation->gene; ?></h4>
      <h4><?php echo $variation->hgvs_nucleotide_change; ?></h4>
  </div>
</div>
<?php
$attributes = array('class' => 'form-edit-variation rounded',
                    'onkeypress' => 'return event.keyCode != 13');
echo form_open('variations/edit/'.$id, $attributes);
?>
    <div class="row-fluid rounded">
        <div class="accordion span6" id="accordion-variant-id-info">
            <div class="accordion-group">
                  <div class="accordion-heading">
                      <a class="accordion-toggle rowlink" data-toggle="collapse" data-parent="#accordion-variant-id-info" href="#edit-variant-id-info">
                          <i class="icon-minus"></i> ID Information
                      </a>
                  </div>
                  <div id="edit-variant-id-info" class="accordion-body collapse in">
                      <div class="accordion-inner">
                          <?php echo variant_form_input('gene', 'Gene', $variation->gene, $unlock) ?>
                          <?php echo variant_form_input('hgvs_protein_change', 'HGVS Protein Change', $variation->hgvs_protein_change, $unlock) ?>
                          <?php echo variant_form_input('hgvs_nucleotide_change', 'HGVS Nucleotide Change', $variation->hgvs_nucleotide_change, $unlock) ?>
                      </div>
                  </div>
            </div>
        </div>
        <div class="accordion span6" id="accordion-variant-information">
              <div class="accordion-group">
                    <div class="accordion-heading">
                        <a class="accordion-toggle rowlink" data-toggle="collapse" data-parent="#accordion-variant-information" href="#edit-variant-information">
                            <i class="icon-minus"></i> Information
                        </a>
                    </div>
                    <div id="edit-variant-information" class="accordion-body collapse in">
                        <div class="accordion-inner">
                            <?php echo variant_form_input('variantlocale', 'Variant Locale', $variation->variantlocale, $unlock) ?>
                            <?php echo variant_form_input('pubmed_id', 'PubMed ID', $variation->pubmed_id) ?>
                            <?php echo variant_form_input('dbsnp', 'dbSNP ID', $variation->dbsnp, $unlock) ?>
                        </div>
                    </div>
              </div>
        </div>
    </div>
    <div class="row-fluid rounded">
        <div class="accordion span6" id="accordion-variant-call">
              <div class="accordion-group">
                    <div class="accordion-heading">
                      <a class="accordion-toggle rowlink" data-toggle="collapse" data-parent="#accordion-variant-call" href="#edit-variant-call">
                        <i class="icon-minus"></i> Call
                      </a>
                    </div>
                    <div id="edit-variant-call" class="accordion-body collapse in">
                        <div class="accordion-inner">
                            <?php echo variant_form_input('variation', 'Genomic Position (Hg19)', $variation->variation, $unlock) ?>
                            <?php echo variant_form_dropdown('pathogenicity', 'Pathogenicity', $pathogenicity_options, $variation->pathogenicity) ?>
                            <?php echo variant_form_input('disease', 'Phenotype', $variation->disease) ?>
                        </div>
                    </div>
              </div>
        </div>
    </div>
    <div class="accordion" id="accordion-prediction-scores">
          <div class="accordion-group">
                <div class="accordion-heading">
                    <a class="accordion-toggle rowlink" data-toggle="collapse" data-parent="#accordion-prediction-scores" href="#edit-prediction-scores">
                        <i class="icon-plus"></i> Prediction Scores
                    </a>
                </div>
                <div id="edit-prediction-scores" class="accordion-body collapse">
                    <div class="accordion-inner">
                        <?php echo variant_form_input('lrt_omega', 'LRT Omega', $variation->lrt_omega, $unlock) ?>
                        <?php echo variant_form_input('phylop_score', 'PhyloP Score', $variation->phylop_score, $unlock) ?>
                        <?php echo variant_form_input('phylop_pred', 'PhyloP Prediction', $variation->phylop_pred, $unlock) ?>
                        <?php echo variant_form_input('sift_score', 'SIFT Score', $variation->sift_score, $unlock) ?>
                        <?php echo variant_form_input('sift_pred', 'SIFT Prediction', $variation->sift_pred, $unlock) ?>
                        <?php echo variant_form_input('polyphen2_score', 'Polyphen-2 Score', $variation->polyphen2_score, $unlock) ?>
                        <?php echo variant_form_input('polyphen2_pred', 'Polyphen-2 Prediction', $variation->polyphen2_pred, $unlock) ?>
                        <?php echo variant_form_input('lrt_score', 'LRT Score', $variation->lrt_score, $unlock) ?>
                        <?php echo variant_form_input('lrt_pred', 'LRT Prediction', $variation->lrt_pred, $unlock) ?>
                        <?php echo variant_form_input('mutationtaster_score', 'MutationTaster Score', $variation->mutationtaster_score, $unlock) ?>
                        <?php echo variant_form_input('mutationtaster_pred', 'MutationTaster Prediction', $variation->mutationtaster_pred, $unlock) ?>
                        <?php echo variant_form_input('gerp_nr', 'GERP++ Neutral Rate', $variation->gerp_nr, $unlock) ?>
                        <?php echo variant_form_input('gerp_rs', 'GERP++ Rejected Substitutions (RS) Score', $variation->gerp_rs, $unlock) ?>
                        <?php echo variant_form_input('gerp_pred', 'GERP++ Prediction', $variation->gerp_pred, $unlock) ?>
                    </div>
                </div>
          </div>
    </div>
    <?php echo edit_allele_frequencies($variation, $unlock); ?>
    <label for="comments">
        Comments <span class="edit-icon-wrapper"><i class="icon-pencil"></i></span>
    </label>
    <textarea id="comments" name="comments" class="input-block-level <?php echo highlight_if_changed($variation->id, 'comments'); ?>" rows="3"><?php echo set_value('comments', $variation->comments); ?></textarea>
    <div>
        <div class="accordion" id="accordion-informatics-team">
            <div class="accordion-group">
                <div class="accordion-heading">
                    <a class="accordion-toggle rowlink" data-toggle="collapse" data-parent="#accordion-informatics-team" href="#informatics-team">
                      <i class="icon-plus"></i> For The Informatics Team
                    </a>
                </div>
                <div id="informatics-team" class="accordion-body collapse">
                    <div class="accordion-inner">
                        <label for="informatics_comments">
                            Comments for the informatics team <span class="edit-icon-wrapper"><i class="icon-pencil"></i></span>
                        </label>
                        <textarea id="informatics_comments" name="informatics_comments" class="input-block-level" rows="3"><?php echo set_value('informatics_comments', informatics_team_comments($variation->id, TRUE)); ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        <!-- Buttons to trigger modals -->
        <button id="save-variant-changes" type="button" class="btn btn-primary btn-medium" data-toggle="modal" data-target="#modal-save-confirm">Save</button>
        <button id="cancel-variant-changes" type="button" class="btn btn-medium" data-toggle="modal" data-target="#modal-cancel-confirm">Cancel</button>
        <button id="reset-variant-changes" type="button" class="btn btn-danger btn-mini" data-toggle="modal" data-target="#modal-reset-confirm">Reset</button>
        <button id="delete-variant" type="button" class="btn btn-danger btn-mini" data-toggle="modal" data-target="#modal-delete-confirm">Delete</button>
    </div>

    <!-- Unlock all fields (confirmation modal) -->
    <div id="modal-unlock-confirm" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="modal-unlock-confirm-label" aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="modal-unlock-confirm-label">Confirm</h3>
        </div>
        <div class="modal-body">
            <p><b style="color:red">WARNING:</b> Any unsaved changes will be lost. Please save all changes first.</p>
            <p>Please note that locked fields contain data collected from genomic studies and algorithms. Editing locked fields can result in conflicting data.</p>
            <p>Unlock all fields?</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-medium" data-dismiss="modal" aria-hidden="true">Cancel</button>
            <a href="<?php echo current_url().'?unlock=true'?>" class="btn btn-medium btn-primary">Unlock all fields</a>
        </div>
    </div>

    <!-- Lock autofill fields (confirmation modal) -->
    <div id="modal-lock-confirm" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="modal-lock-confirm-label" aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="modal-lock-confirm-label">Confirm</h3>
        </div>
        <div class="modal-body">
            <p><b style="color:red">WARNING:</b> Any unsaved changes will be lost. Please save all changes first.</p>
            <p>Lock autofill fields?</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-medium" data-dismiss="modal" aria-hidden="true">Cancel</button>
            <a href="<?php echo current_url(); ?>" class="btn btn-medium btn-primary">Lock</a>
        </div>
    </div>

    <!-- Save (confirmation modal) -->
    <div id="modal-save-confirm" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="modal-save-confirm-label" aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="modal-save-confirm-label">Confirm</h3>
        </div>
        <div class="modal-body">
            <p>Save changes?</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-medium" data-dismiss="modal" aria-hidden="true">Cancel</button>
            <button class="btn btn-medium btn-primary" type="submit" name="save-changes" value="Save">OK</button>
        </div>
    </div>

    <!-- Cancel (confirmation modal) -->
    <div id="modal-cancel-confirm" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="modal-cancel-confirm-label" aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="modal-cancel-confirm-label">Cancel changes?</h3>
        </div>
        <div class="modal-body">
            <p>All unsaved changes will be lost. Are you sure you want to clear this form?</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-medium" data-dismiss="modal" aria-hidden="true">Cancel</button>
            <a href="<?php echo current_url(); ?>" class="btn btn-medium btn-primary">Clear</a>
        </div>
    </div>

    <!-- Reset (confirmation modal) -->
    <div id="modal-reset-confirm" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="modal-reset-confirm-label" aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="modal-reset-confirm-label">Reset all changes?</h3>
        </div>
        <div class="modal-body">
            <p>
                All saved and unsaved changes will be lost, and this cannot be undone.
                Are you sure you want to reset all changes that have been made for this variant?
            </p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-medium" data-dismiss="modal" aria-hidden="true">Cancel</button>
            <button class="btn btn-medium btn-danger" type="submit" name="reset-changes" value="Reset">Reset</button>
        </div>
    </div>

    <!-- Delete (confirmation modal) -->
    <div id="modal-delete-confirm" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="modal-delete-confirm-label" aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="modal-delete-confirm-label">Schedule for deletion?</h3>
        </div>
        <div class="modal-body">
            <p>
                You are about to schedule this variant for deletion. 
                If you change your mind before the next database release, you may unschedule
                it for deletion by hitting the 'Reset' button on this form.
            </p>
            <p>Are you sure you want to delete this variant from future releases?</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-medium" data-dismiss="modal" aria-hidden="true">Cancel</button>
            <button class="btn btn-medium btn-danger" type="submit" name="delete-variant" value="Delete">Delete</button>
        </div>
    </div>
</form>
