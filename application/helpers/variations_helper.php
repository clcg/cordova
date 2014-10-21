 <?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('new_variant_notice'))
{
  /**
  * New Variant Notice
  *
  * This is for use on the "Unreleased changes" page.
  * If parameter is TRUE, the HTML for a 'new variant' notice is returned,
  * otherwise NULL is returned.
  *
  * @author   Sean Ephraim
  * @access   public
  * @param    boolean  $display
  *    Display new variant notice or not?
  * @return   string   HTML string
  */
  function new_variant_notice($display)
  {
    if ($display) {
      return '<small class="notice-new-variant"><i class="icon-info-sign"></i><i> new variant</i></small>';
    }
    return NULL;
  }
}

if ( ! function_exists('unreleased_changes_notice'))
{
  /**
  * Unreleased Changes Notice
  *
  * This is for use on the variant edit form.
  * If this variant is found in the queue, the HTML for an 'unreleased changes exist' notice 
  * is returned, otherwise NULL is returned.
  *
  * @author   Sean Ephraim
  * @access   public
  * @param    int   $id
  *    Variation unique ID 
  * @return   mixed
  */
  function unreleased_changes_notice($id)
  {
		// Initialize db tables data
		$tables = get_instance()->config->item('tables');

    $result = get_instance()->variations_model->get_unreleased_changes($id);

    // Last edit date
    $review = get_instance()->variations_model->get_variant_review_info($id);
    if ( ! empty($review)) {
      $last_edit = $review->updated;
    }

    if ( ! empty($result[$id]['changes'])) {
      $href = site_url("variations/unreleased/variation/$id"); 
      $html = '';
      $html .= '<p class="notice rounded">';
      $html .= '    <a class="close" data-dismiss="alert" href="#">&times;</a>';
      $html .= '    This variant contains unreleased changes. Changed fields are highlighted below, or <a href="'.$href.'">click here</a> to see list of changes.';

      if ($last_edit !== NULL) {
        // Add last update date if available
        $last_edit = date('j F o', strtotime($last_edit));
        $html .= '    <br/>';
        $html .= "    <small>Last edited on: $last_edit</small>";
      }
      $html .= '</p>';
      return $html;
    }
    return NULL;
  }
}

if ( ! function_exists('undesired_comments_notice'))
{
  /**
  * Undesired Comments Notice
  *
  * This is for use on the variant edit form. If the 'comments' field
  * contains the text "Manual curation in progress", then this notice
  * will urge the user to change the comments accordingly.
  * If parameter is TRUE, the HTML for a 'unreleased changes exist' notice 
  * is returned, otherwise NULL is returned.
  *
  * @author   Sean Ephraim
  * @access   public
  * @param    int  $id
  *    Variation unique ID 
  * @return   mixed
  */
  function undesired_comments_notice($id)
  {
    $undesired_text = "Manual curation in progress";
    $variant = get_instance()->variations_model->get_variant_by_id($id);
    if (stristr($variant->comments, $undesired_text) !== FALSE) {
      $html = ''
            . '<p class="warning rounded">'
            . '    <a class="close" data-dismiss="alert" href="#">&times;</a>'
            . '    If this variant has been manually curated, please edit the <a href="#comments">comments</a> to reflect this.'
            . '</p>';
      return $html;
    }
    return NULL;
  }
}

if ( ! function_exists('deletion_notice'))
{
  /**
  * Deletion Notice
  *
  * This is for use on the variant edit form and the unreleased changes form.
  * If this variant is scheduled for deletion, the HTML for a 'scheduled for 
  * deletion' notice is returned, otherwise NULL is returned.
  *
  * @author   Sean Ephraim
  * @access   public
  * @param    int   $variant_id
  *    Variation unique ID 
  * @return   mixed HTML string or NULL
  */
  function deletion_notice($variant_id)
  {
    $review = get_instance()->variations_model->get_variant_review_info($variant_id);
    if ($review) {
      $delete = $review->scheduled_for_deletion;
      if ($delete) {
        $html = ''
          . '<p class="deletion-notice rounded">'
          . '    <i class="icon-exclamation-sign"></i> This variant is scheduled for deletion upon the next release. To unschedule it, reset the changes <a href="'.site_url("variations/edit/$variant_id").'#reset-variant-changes">here</a>.'
          . '</p>';
        return $html;
      }
    }
    return NULL;
  }
}


if ( ! function_exists('informatics_team_comments'))
{
  /**
  * Informatics Team Comments
  *
  * Returns the HTML for displaying the informatics team comments for
  * a specified variant. Returns NULL if variant has no comments.
  *
  * @author   Sean Ephraim
  * @access   public
  * @param    int   $variant_id
  *    Variation unique ID 
  * @param    boolean   $comments_only
  *    If TRUE, do not wrap in HTML
  * @return   mixed
  */
  function informatics_team_comments($variant_id, $comments_only = FALSE)
  {
    $review = get_instance()->variations_model->get_variant_review_info($variant_id);
    if ($review) {
      $comments = $review->informatics_comments;
      if ($comments) {
        if ($comments_only) {
          return $comments;
        }
        $html = ''
              . '<p class="informatics-comments-notice rounded">'
              .     '<b><i class="icon-info-sign"></i> Comments for the informatics team:</b><br />'
              .     $comments
              . '</p>';
        return $html;
      }
    }
    return NULL;
  }
}

if ( ! function_exists('variant_confirmation_status'))
{
  /**
  * Variant Confirmation Status
  *
  * Checks whether or not a variant is confirmed for release.
  * This is specifically used for the confirmation checkboxes
  * on the 'unreleased changes' page in order to decide whether
  * or not a checkbox should be checked. Checked boxes indicate
  * that the variant is unconfirmed for release.
  *
  * @author   Sean Ephraim
  * @access   public
  * @param    int   $variant_id
  *    Variation unique ID 
  * @return   mixed
  */
  function variant_confirmation_status($variant_id)
  {
    $review = get_instance()->variations_model->get_variant_review_info($variant_id);
    if ($review) {
      $confirmed_for_release = $review->confirmed_for_release;
      if ($confirmed_for_release) {
        return NULL;
      }
    }
    return "checked";
  }
}

if ( ! function_exists('highlight_if_changed'))
{
  /**
  * Highlight If Changed
  *
  * This is for use on the variant edit form.
  * Checks if a field for a specified variant has been edited. If it has,
  * the string 'highlight' will be returned, which will serve as the class
  * for the corresponding HTML element. If there are no changes, an empty
  * string will be returned.
  *
  * @author   Sean Ephraim
  * @access   public
  * @param    int  $variant_id
  *    Variant unique ID
  * @param    string   $field
  *    The name of the field to check for changes
  * @return   string
  */
  function highlight_if_changed($variant_id, $field)
  {
    $result = get_instance()->variations_model->get_unreleased_changes($variant_id);

    if ( ! empty($result[$variant_id]['changes'])) {
      $changes = $result[$variant_id]['changes'];
      if (array_key_exists($field, $changes)) {
        return 'highlight';
      }
    }
    return '';
  }
}

if ( ! function_exists('select_option_if_matching'))
{
  /**
  * Select Option If Matching
  *
  * This is for use on the variant edit form.
  * This function is used on HTML select elements in order to automatically
  * select the value that is currently stored in the database.
  * For example, if a variant has an associated pathogenicity (out of a
  * handful of possible pathogenicities), then the current pathogenicity
  * must be automatically selected every time the edit form is visited.
  *
  * @author   Sean Ephraim
  * @access   public
  * @param    string   $value1
  *    Value 1 to compare for equality
  * @param    string   $value2
  *    Value 2 to compare for equality
  * @return   string   Proper CSS
  */
  function select_option_if_matching($value1, $value2)
  {
    if ($value1 === $value2) {
      return 'selected="selected"';
    }
    else if ((string) $value1 === (string) $value2) {
      // Special case for 0's
      // |---> for some reason (0 !== 0) -- Why, PHP? Why?! :/
      return 'selected="selected"';
    }
    return '';
  }
}

if ( ! function_exists('print_letter_table'))
{
  /**
  * Print Letter Table
  *
  * @author   Sean Ephraim
  * @access   public
  * @return   string
  */
  function print_letter_table()
  {
    $selected_letter = get_instance()->uri->segment(2);
    if ($selected_letter !== FALSE) {
      // Sanitization handled by method
      return get_instance()->variations_model->get_letter_table($selected_letter);
    } 
    else {
      return get_instance()->variations_model->get_letter_table();
    }
  }
}

if ( ! function_exists('display_variant_frequencies'))
{
  /**
  * Display Variant Frequencies
  *
  * Displays the proper HTML to show variant allele frequencies 
  * depending on the URI.
  *
  * If 'full' is a URI parameter (i.e. /variant/1769?full), then
  * the full frequency display is shown. If 'full' is not a parameter
  * then the small frequency display is shown.
  *
  * @author   Sean Ephraim
  * @access   public
  * @return   void
  */
  function display_variant_frequencies()
  {
    if (isset($_GET['full'])) {
      // Load full view
      get_instance()->load->view('/variations/variant/frequencies/freq_full.php');
    } 
    else {
      // Load small view
      get_instance()->load->view('/variations/variant/frequencies/freq_small.php');
    }
  }
}

if ( ! function_exists('display_variant_header'))
{
  /**
  * Display Variant Header
  *
  * Decides whether or not to display the header on the variant page.
  * If the user is viewing the variant in 'full' page mode then the
  * header will display, otherwise it won't.
  *
  * @author   Sean Ephraim
  * @access   public
  * @return   void
  */
  function display_variant_header()
  {
    if (isset($_GET['full'])) {
      get_instance()->load->view('/variations/variant/header.php');
    } 
  }
}

if ( ! function_exists('display_variant_footer'))
{
  /**
  * Display Variant Footer
  *
  * Decides whether or not to display the footer on the variant page.
  * If the user is viewing the variant in 'full' page mode then the
  * footer will display, otherwise it won't.
  *
  * @author   Sean Ephraim
  * @access   public
  * @return   void
  */
  function display_variant_footer()
  {
    if (isset($_GET['full'])) {
      get_instance()->load->view('/variations/variant/footer.php');
    } 
  }
}

if ( ! function_exists('include_variant_js'))
{
  /**
  * Include Variant JS
  *
  * Decides whether or not to include JavaScript on the variant page.
  * If the user is viewing the variant in 'small' page mode then the
  * JavaScript will load, otherwise it won't.
  *
  * @author   Sean Ephraim
  * @access   public
  * @return   void
  */
  function include_variant_js()
  {
    if (isset($_GET['full'])) {
      return '<script type="text/javascript" src="'.site_url('assets/public/js/script.js').'"></script>';
    } 
  }
}

if ( ! function_exists('include_proper_variant_css'))
{
  /**
  * Include Proper Variant CSS
  *
  * Includes the proper links to CSS files depending on the URI.
  *
  * @author   Sean Ephraim
  * @access   public
  * @return   string Proper CSS links
  */
  function include_proper_variant_css()
  {
    if (isset($_GET['print'])) {
      // Load print version...
      $html = '<link rel="stylesheet" href="'.site_url("assets/public/css/variant.print.css").'" type="text/css" media="screen" title="Print Styles" charset="utf-8" />';
    }
    else {
      // Otherwise load screen version...
      $html = '<link rel="stylesheet" href="'.site_url("assets/public/css/variant.css").'" type="text/css" media="screen" title="Screen Styles" charset="utf-8" />';
    }
    return $html;
  }
}

if ( ! function_exists('display_proper_logo'))
{
  /**
  * Display Proper Logo
  *
  * Displays special logo text for the /doc and /help pages.
  * By default, all other pages will display the name of site.
  *
  * @author   Sean Ephraim
  * @access   public
  * @return   string Proper CSS links
  */
  function display_proper_logo()
  {
    if (uri_string() === 'help') {
      $html = '<h1 id="help-logo"><span>How to use this site</span></h1>';
    }
    else if (uri_string() === 'doc') {
      $html = '<h1 id="api-logo"><span>API Documentation</span></h1>';
    }
    else {
      $strings = get_instance()->config->item('strings');
      $html = '<h1 id="main-logo"><span>'.strtoupper($strings['site_full_name']).'</span></h1>';
    }
    return $html;
  }
}

if ( ! function_exists('variant_form_input'))
{
  /**
  * Variant Form Input
  *
  * Returns a HTML text input element with a proper id, name, label
  * and default value. Also locks/unlocks the field for editing and
  * highlights the field if it has been changed in any way. By
  * default, the field is unlocked for editing.
  *
  * @author   Sean Ephraim
  * @access   public
  * @param    string   $field
  *    Field name (database name)
  * @param    string   $label
  *    Field label (human-readable name)
  * @param    mixed    $default_value
  *    Default value
  * @param    boolean  $editable
  *    (optional) Editable field or not (default = TRUE)
  * @return   string   HTML text input element
  */
  function variant_form_input($field, $label, $default_value, $editable = TRUE)
  {
    // Get the variation's unique ID from the URI
    $variation_id = get_instance()->uri->segment(3);

    $html  = '';
    if ($editable === TRUE) {
      $highlight = highlight_if_changed($variation_id, $field);
      // Editable input
      $html .= '<label for="'.$field.'">';
      $html .=     $label.' <span class="edit-icon-wrapper"><i class="icon-pencil"></i></span>';
      $html .= '</label>';
      $html .= '<input id="'.$field.'" name="'.$field.'" type="text" class="input-xlarge '.$highlight.'" value="'.set_value($field, $default_value).'">';
    }
    else {
      $highlight = ''; // Don't highlight uneditable input
      // Uneditable input -- not actually input at all :0
      $html .= "<div>$label</div>";
      $html .= '<span id="'.$field.'" class="input-xlarge uneditable-input '.$highlight.'">'.$default_value.'</span>';
    }
    return $html;
  }
}

if ( ! function_exists('variant_form_dropdown'))
{
  /**
  * Variant Form Dropdown
  *
  * Returns a HTML dropdown element with a proper id, name, label,
  * options, and default selection. Also locks/unlocks the field for editing
  * and highlights the field if it has been changed in any way. By
  * default, the field is unlocked for editing.
  *
  * @author   Sean Ephraim
  * @access   public
  * @param    string   $field
  *    Field name (database name)
  * @param    string   $label
  *    Field label (human-readable name)
  * @param    array    $options
  *    Selectable options
  * @param    mixed    $default_selection
  *    Default selection
  * @param    boolean  $editable
  *    (optional) Editable field or not (default = TRUE)
  * @return   string   HTML dropdown element
  */
  function variant_form_dropdown($field, $label, $options, $default_selection, $editable = TRUE)
  {
    // Get the variation's unique ID from the URI
    $variation_id = get_instance()->uri->segment(3);

    // Did the user already try to submit this field (and an error was thrown)?
    // ... if so, get that value back from POST
    if (isset($_POST[$field])) {
      $default_selection = $_POST[$field];
    }

    $html  = '';
    if ($editable === TRUE) {
      $highlight = highlight_if_changed($variation_id, $field);
      // Editable input
      $html .= '<label for="'.$field.'">';
      $html .=     $label.' <span class="edit-icon-wrapper"><i class="icon-pencil"></i></span>';
      $html .= '</label>';
      $html .= '<select id="'.$field.'" name="'.$field.'" class="'.$highlight.'">';
      foreach($options as $option) {
        $html .= '  <option value="'.$option.'" '.select_option_if_matching($option, $default_selection).'>'.$option.'</option>';
      }
      $html .= '</select>';
    }
    else {
      $highlight = ''; // Don't highlight uneditable input
      // Uneditable input -- not actually input at all :0
      $html .= "<div>$label</div>";
      $html .= '<span id="'.$field.'" class="input-xlarge uneditable-input '.$highlight.'">'.$default_selection.'</span>';
    }
    return $html;
  }
}

if ( ! function_exists('unlock_all_fields_button'))
{
  /**
  * Variant Form Dropdown
  *
  * Returns a HTML dropdown element with a proper id, name, label,
  * options, and default selection. Also locks/unlocks the field for editing
  * and highlights the field if it has been changed in any way. By
  * default, the field is unlocked for editing.
  *
  * @author   Sean Ephraim
  * @access   public
  * @return   string   HTML dropdown element
  */
  function unlock_all_fields_button() {
    if (isset($_GET['unlock']) && $_GET['unlock'] === 'true') {
      $html = '<button id="unlock-all" class="rounded" type="button" data-toggle="modal" data-target="#modal-lock-confirm"><i class="icon-lock icon-white"></i> Lock autofill fields</button>';
    }
    else {
      $html = '<button id="unlock-all" class="rounded" type="button" data-toggle="modal" data-target="#modal-unlock-confirm"><i class="icon-chevron-up icon-white"></i> Unlock all fields</button>';
    }

    return $html;
  }
}

if ( ! function_exists('edit_allele_frequencies'))
{
  /**
  * Edit Allele Frequencies
  *
  * Display the minor allele frequency edit fields. This function will
  * dynamically generate HTML based on the configuration preferences for
  * minor allele frequencies. It will only display frequencies that the
  * user has configured to be displayed.
  *
  * @author   Sean Ephraim
  * @access   public
  * @param    object   $variation
  *    Database record for the variation
  * @param    boolean  $unlock
  *    Editable fields or not
  * @return   string   HTML of input fields
  */
  function edit_allele_frequencies($variation, $unlock) {
    // Which frequencies should be shown?
    $freqs = get_instance()->config->item('frequencies');

    // Build a HTML string of allele frequency fields
    $html = '';
    if (count($freqs) > 0) {
      // Begin collapsable accordion
      $html .= '<div class="accordion" id="accordion-variant-freqs">';
      $html .= '      <div class="accordion-group">';
      $html .= '            <div class="accordion-heading">';
      $html .= '                <a class="accordion-toggle rowlink" data-toggle="collapse" data-parent="#accordion-variant-freqs" href="#edit-variant-freqs">';
      $html .= '                    <i class="icon-plus"></i> Variant Frequencies';
      $html .= '                </a>';
      $html .= '            </div>';
      $html .= '            <div id="edit-variant-freqs" class="accordion-body collapse">';
      $html .= '                <div class="accordion-inner">';
      // EVS
      if (in_array('evs', $freqs)) {
        $html .= variant_form_input('evs_ea_ac', 'EVS European-American Alternate Allele Count', $variation->evs_ea_ac, $unlock);
        $html .= variant_form_input('evs_ea_an', 'EVS European-American Total Allele Count', $variation->evs_ea_an, $unlock);
        $html .= variant_form_input('evs_ea_af', 'EVS European-American Frequency', $variation->evs_ea_af, $unlock);
        $html .= variant_form_input('evs_aa_ac', 'EVS African-American Alternate Allele Count', $variation->evs_aa_ac, $unlock);
        $html .= variant_form_input('evs_aa_an', 'EVS African-American Total Allele Count', $variation->evs_aa_an, $unlock);
        $html .= variant_form_input('evs_aa_af', 'EVS African-American Frequency', $variation->evs_aa_af, $unlock);
        $html .= variant_form_input('evs_all_ac', 'EVS All Populations Alternate Allele Count', $variation->evs_all_ac, $unlock);
        $html .= variant_form_input('evs_all_an', 'EVS All Populations Total Allele Count', $variation->evs_all_an, $unlock);
        $html .= variant_form_input('evs_all_af', 'EVS All Populations Frequency', $variation->evs_all_af, $unlock);
      }
      // OtoSCOPE
      if (in_array('otoscope', $freqs)) {
        $html .= variant_form_input('otoscope_aj_ac', 'OtoSCOPE Ashkenazi Jewish Alternate Allele Count', $variation->otoscope_aj_ac, $unlock);
        $html .= variant_form_input('otoscope_aj_an', 'OtoSCOPE Ashkenazi Jewish Total Allele Count', $variation->otoscope_aj_an, $unlock);
        $html .= variant_form_input('otoscope_aj_af', 'OtoSCOPE Ashkenazi Jewish Allele Frequency', $variation->otoscope_aj_af, $unlock);
        $html .= variant_form_input('otoscope_co_ac', 'OtoSCOPE Colombian Allele Count', $variation->otoscope_co_ac, $unlock);
        $html .= variant_form_input('otoscope_co_an', 'OtoSCOPE Colombian Total Allele Count', $variation->otoscope_co_an, $unlock);
        $html .= variant_form_input('otoscope_co_af', 'OtoSCOPE Colombian Allele Frequency', $variation->otoscope_co_af, $unlock);
        $html .= variant_form_input('otoscope_us_ac', 'OtoSCOPE European-Americans Alternate Allele Count', $variation->otoscope_us_ac, $unlock);
        $html .= variant_form_input('otoscope_us_an', 'OtoSCOPE European-Americans Total Allele Count', $variation->otoscope_us_an, $unlock);
        $html .= variant_form_input('otoscope_us_af', 'OtoSCOPE European-Americans Allele Frequency', $variation->otoscope_us_af, $unlock);
        $html .= variant_form_input('otoscope_jp_ac', 'OtoSCOPE Japanese Alternate Allele Count', $variation->otoscope_jp_ac, $unlock);
        $html .= variant_form_input('otoscope_jp_an', 'OtoSCOPE Japanese Total Allele Count', $variation->otoscope_jp_an, $unlock);
        $html .= variant_form_input('otoscope_jp_af', 'OtoSCOPE Japanese Allele Frequency', $variation->otoscope_jp_af, $unlock);
        $html .= variant_form_input('otoscope_es_ac', 'OtoSCOPE Spanish Alternate Allele Count', $variation->otoscope_es_ac, $unlock);
        $html .= variant_form_input('otoscope_es_an', 'OtoSCOPE Spanish Total Allele Count', $variation->otoscope_es_an, $unlock);
        $html .= variant_form_input('otoscope_es_af', 'OtoSCOPE Spanish Allele Frequency', $variation->otoscope_es_af, $unlock);
        $html .= variant_form_input('otoscope_tr_ac', 'OtoSCOPE Turkish Alternate Allele Count', $variation->otoscope_tr_ac, $unlock);
        $html .= variant_form_input('otoscope_tr_an', 'OtoSCOPE Turkish Total Allele Count', $variation->otoscope_tr_an, $unlock);
        $html .= variant_form_input('otoscope_tr_af', 'OtoSCOPE Turkish Allele Frequency', $variation->otoscope_tr_af, $unlock);
        $html .= variant_form_input('otoscope_all_ac', 'OtoSCOPE All Populations Alternate Allele Count', $variation->otoscope_all_ac, $unlock);
        $html .= variant_form_input('otoscope_all_an', 'OtoSCOPE All Populations Total Allele Count', $variation->otoscope_all_an, $unlock);
        $html .= variant_form_input('otoscope_all_af', 'OtoSCOPE All Populations Allele Frequency', $variation->otoscope_all_af, $unlock);
      }
      // 1000 Genomes
      if (in_array('1000genomes', $freqs)) {
        $html .= variant_form_input('tg_afr_ac', '1000 Genomes African Alternate Allele Count', $variation->tg_afr_ac, $unlock);
        $html .= variant_form_input('tg_afr_an', '1000 Genomes African Total Allele Count', $variation->tg_afr_an, $unlock);
        $html .= variant_form_input('tg_afr_af', '1000 Genomes African Allele Frequency', $variation->tg_afr_af, $unlock);
        $html .= variant_form_input('tg_eur_ac', '1000 Genomes European Alternate Allele Count', $variation->tg_eur_ac, $unlock);
        $html .= variant_form_input('tg_eur_an', '1000 Genomes European Total Allele Count', $variation->tg_eur_an, $unlock);
        $html .= variant_form_input('tg_eur_af', '1000 Genomes European Allele Frequency', $variation->tg_eur_af, $unlock);
        $html .= variant_form_input('tg_amr_ac', '1000 Genomes American Alternate Allele Count', $variation->tg_amr_ac, $unlock);
        $html .= variant_form_input('tg_amr_an', '1000 Genomes American Total Allele Count', $variation->tg_amr_an, $unlock);
        $html .= variant_form_input('tg_amr_af', '1000 Genomes American Allele Frequency', $variation->tg_amr_af, $unlock);
        $html .= variant_form_input('tg_asn_ac', '1000 Genomes Asian Alternate Allele Count', $variation->tg_asn_ac, $unlock);
        $html .= variant_form_input('tg_asn_an', '1000 Genomes Asian Total Allele Count', $variation->tg_asn_an, $unlock);
        $html .= variant_form_input('tg_asn_af', '1000 Genomes Asian Allele Frequency', $variation->tg_asn_af, $unlock);
        $html .= variant_form_input('tg_all_ac', '1000 Genomes All Populations Alternate Allele Count', $variation->tg_all_ac, $unlock);
        $html .= variant_form_input('tg_all_an', '1000 Genomes All Populations Total Allele Count', $variation->tg_all_an, $unlock);
        $html .= variant_form_input('tg_all_af', '1000 Genomes All Populations Allele Frequency', $variation->tg_all_af, $unlock);
      }
      // End collapsable accordion
      $html .= '                </div>';
      $html .= '            </div>';
      $html .= '      </div>';
      $html .= '</div>';
    } // End if

    return $html;
  }
}

if ( ! function_exists('format_table_cell'))
{
  /**
  * Format Table Cell
  *
  * Formats strings to be stored into an HTML table cell.
  * This function will perform word-wrapping for certain
  * columns and will insert a &nbsp into empty cells to
  * avoid an HTML error.
  *
  * @author   Sean Ephraim
  * @access   public
  * @param    string  $column_name
  *   Name of the column in the table
  * @param    string  $cell_data
  *   Data to be stored in the table cell
  * @return   string  HTML string
  */
  function format_table_cell($column_name, $cell_data)
  {
    if (empty($cell_data)) {
      // Avoid HTML errors for empty data
      $html = "&nbsp;";
    }
    elseif ($column_name == 'hgvs_protein_change') {
      // HGVS protein change
      $html = wordwrap($cell_data, 30, '<br />', 1);
    }
    elseif ($column_name == 'hgvs_nucleotide_change' || $column_name == 'variation') {
      // HGVS nucleotide change, variation
      $html = wordwrap($cell_data, 25, '<br />', 1);
    }
    else {
      $html = $cell_data;
    }
    return $html;
  }
}

/* End of file variations_helper.php */
/* Location: ./application/helpers/variations_helper.php */  
