<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Variations extends MY_Controller {
  
  /**
   * Array of strings for views, filenames, etc.
   *
   * @var array
   */
  public $strings = array();

	/**
	 * Holds an array of tables used.
	 *
	 * @var array
	 */
  public $tables = array();

  /**
   * Constructor
   */
  public function __construct() {
    parent::__construct();

    // initialize common strings
    $this->strings = $this->config->item('strings');
		// Initialize db tables data
		$this->tables = $this->config->item('tables');
  }

  /**
   * Index
   */
  public function index() {
    redirect('/');
  }

  /**
   * Show Variants
   *
   * Displays all variations in the database associated with a
   * certain gene.
   *
   * @author Sean Ephraim
   * @access public
   * @param  string $gene
   *    Name of gene
   * @return void
   */
  public function show_variants($gene) {
    redirect_all_nonmembers();

    $data['title'] = 'Variations - ' . strtoupper($gene);
    $data['content'] = 'variations/index';

    $data['gene'] = $gene;
    // Columns to select for this page
    $columns = 'id,hgvs_protein_change,hgvs_nucleotide_change,variantlocale,variation,pathogenicity,disease';
    $data['rows'] = $this->variations_model->get_variants_by_gene($gene, $columns);

    $this->load->view($this->editor_layout, $data);
  }

  /**
   * Edit
   *
   * Display/submit a form of editable variation data.
   *
   * @author Sean Ephraim
   * @access public
   * @param int $id ID number of variant
   * @return void
   */
  public function edit($id) {
    redirect_all_nonmembers();

    $variation = $this->variations_model->get_variant_by_id($id);
    $review = $this->variations_model->get_variant_review_info($id);

    if ($variation === NULL) {
      die("Variant ID does not exist.");
    }

    $data['title'] = 'Edit - ' . $variation->hgvs_protein_change;
    $data['content'] = 'variations/edit';

    $data['variation'] = $variation;
    $data['id'] = $id;
    $data['pathogenicity_options'] = $this->config->item('pathogenicities');

    // Variable for unlocking all fields for editing
    if (isset($_GET['unlock']) && $_GET['unlock'] === 'true') {
      $data['unlock'] = TRUE;
    }
    else {
      $data['unlock'] = FALSE;
    }

    $this->load->library('form_validation');
    $this->form_validation->set_rules('summary_insilico', 'Summary Insilico', 'trim|is_natural|less_than[7]');
    $this->form_validation->set_rules('summary_frequency', 'Summary Frequency', 'trim|is_natural|less_than[7]');
    $this->form_validation->set_rules('summary_published', 'Summary Published', 'trim|is_natural|less_than[7]');
    $this->form_validation->set_rules('pubmed_id', 'PubMed ID', 'trim');
    $this->form_validation->set_rules('informatics_comments', 'Informatics Team Comments', 'trim');

    if (isset($_POST['save-changes'])) {
      if ($this->form_validation->run() !== FALSE) {
        // Passed!
        // get $_POST data
        $post = $this->input->post();
        
        $this->variations_model->update_variant_in_queue($id, $post);
        $this->variations_model->update_variant_review_info($id, $post);

        // Remove variant from queue if no changes exist
        $this->variations_model->remove_from_queue_if_unchanged($id);
    
        $this->session->set_flashdata('success', 'Changes saved.');
        redirect($this->uri->uri_string()); // reload the page
      }
    }
    else if (isset($_POST['reset-changes'])) {
      $variation = $this->variations_model->get_variant_by_id($id);

      // Remove all unreleased changes for this variant
      $this->variations_model->remove_all_changes($id);

      $this->session->set_flashdata('success', "Removed all changes for $variation->variation.");

      $variation = $this->variations_model->get_variant_by_id($id);
      if (empty($variation)) {
        // Variant no longer exists in the database, redirect elsewhere
        redirect("variations/unreleased");
      }
      else {
        // Variant still exists in the database, reload its edit page
        redirect($this->uri->uri_string()); // reload the page
      }
    }
    else if (isset($_POST['delete-variant'])) {
      // Update 'reviews' table
      $data = array('scheduled_for_deletion' => 1);
      $this->variations_model->update_variant_review_info($id, $data);

      redirect($this->uri->uri_string()); // reload the page
    }

    $this->load->view($this->editor_layout, $data);
  }

  /**
   * Add
   *
   * Create a new variant in the queue. It will go live after the
   * next batch release.
   *
   * @author Sean Ephraim
   * @access public
   * @return void
   */
  public function add() {
    redirect_all_nonmembers();

    $data['title'] = 'Add new variant';
    $data['content'] = 'variations/add';
    $data['default_value'] = $this->session->flashdata('default_value');

    // Allow the option to force add variant?
    $hide = $this->session->flashdata('hide_force_option');
    if ($hide === FALSE) {
      // default is hidden
      $data['hide_force_option'] = 'hidden';
    }
    else {
      $data['hide_force_option'] = '';
    }

    $this->load->library('form_validation');
    $this->form_validation->set_rules('variation', 'Genomic Position (Hg19)', 'trim|required');

    if ($this->form_validation->run() !== FALSE) {
      $variation = $this->input->post('variation');

      if (isset($_POST['force-add-variant'])) {
        // Add variant without annotation data
        $id = $this->variations_model->create_new_variant($variation, TRUE);
      }
      else {
        // Add variant with annotation data
        $id = $this->variations_model->create_new_variant($variation);
      }

      // ERROR handling
      if ($id == -409) {
        // ERROR: Variant already exists in the database
        $error = "$variation already exists in the database.";
      }
      else if ($id == -404) {
        // ERROR: No data for this variant was returned
        $error = "No data found for $variation.";
      }
      else if ($id == -400) {
        // ERROR: Unsupported mutation type
        $error = "$variation is not a supported mutation type. Please check that the formatting is correct.";
      }
      else if ($id == -501) {
        // ERROR: No matching RefSeq (ASAP returned nothing)
        $error = "No matching RefSeq for $variation.";
      }
      else if ($id == -503) {
        // ERROR: Annotation tool (e.g. kafeen) not configured
        $error = "Automatic annotation/validation has not been properly configured.";
      }
      else if ($id < 0) {
        // ERROR: Some other error occurred
        $error = "An error occurred. Please try again or contact the administrator.";
      }
      else {
        // Success!
        $url = site_url("variations/edit/$id"); // URL for editing the new variant
        $this->session->set_flashdata('success', "$variation added successfully. Edit it now <a href='$url'>here</a>.");
      }

      if ($id < 0) {
        $error .= '<br/><br/>To add this variant anyway, select the checkbox below and resubmit.';
        $this->session->set_flashdata('error', $error);
        // Put the variation back into the input field if an error occured
        $this->session->set_flashdata('default_value', $variation);
        // Unhide the force add option
        $this->session->set_flashdata('hide_force_option', '');
      }

      redirect($this->uri->uri_string()); // reload the page
    }

    $this->load->view($this->editor_layout, $data);
  }

  /**
   * Uplaod Genes
   *
   * First interface of variant-CADI. Allows the user to upload
   * genes in file format as specified in the interface or to 
   * enter genes in a text box.
   *
   * @author arhallier@gmail.com
   * @access public
   * @param none
   */

  public function upload_genes() {
    redirect_all_nonmembers();
    $data['title'] = "Uplaod Genes";
    $data['content'] = 'variations/upload_genes';
    $this->load->helper('file');
    $annotation_path = $this->config->item('annotation_path');
    $time_stamp = date("YmdHis");
    $data['time_stamp'] = $time_stamp;
    $file_path = "$annotation_path/mygenes$time_stamp.txt";
    $this->session->set_flashdata('file_path', $file_path);
    //if input is from the text box
    if($this->input->post('text-submit'))
    {
      $genesOrFile = $this->input->post('text');
      $this->session->set_flashdata('genes', $genesOrFile);
      if($fh = fopen("$file_path", 'w+')){ 
        fwrite($fh, $genesOrFile);
        fclose($fh);
        redirect('variations/query_public_database/'.$time_stamp);
      }
      else{
        die("Could not open file.");
      }#need to add an else here 
    }
    //if the input is from the file submit
    if($this->input->post('file-submit'))
    {
      $this->load->library('upload');
      $this->upload->set_allowed_types('*');
      $genesOrFile = $_FILES["file"]["name"];
      $this->session->set_flashdata('genes', $genesOrFile);
      move_uploaded_file($_FILES["file"]["tmp_name"], "$file_path");
      redirect('variations/query_public_database/'.$time_stamp);
      #need to add some try catches here
    }
    $this->load->view($this->editor_layout, $data);
  }

  /**
   * Query Public Database
   *
   * Annotation pipeline for submitted genes is run when
   * the user selects submit.
   *
   * @author arhallier@gmail.com
   * @access public
   * @param none
   */
  public function query_public_database($time_stamp) {
    redirect_all_nonmembers();
    $data['title'] = "Query Public Databases";
    $data['content'] = 'variations/query_public_database';
    $annotation_path = $this->config->item('annotation_path');
    $genes = $this->session->flashdata('genes');
    $data['genes'] = $genes;
    $genesFile = "$annotation_path/mygenes$time_stamp.txt";
    $data['time_stamp'] = $time_stamp;
    $this->load->library('email');
    $this->email->clear();
    $config['mailtype'] = 'text';
    $config['wordwrap'] = TRUE;
    $config['newline'] = "\r\n";
    $config['crlf'] = "\r\n";
    $this->email->initialize($config);
    $this->email->from($this->config->item('contact_email'));
    $this->email->to($this->config->item('contact_email')); 
    $this->email->subject('Cordova variant-CADI Variant Collection Update');
    $this->email->message("The variant collection has completed, please follow this link to continue initializing your database. ".$this->config->base_url()."variations/norm_nomenclature/".$time_stamp);  

    if($this->input->post('submit'))
    {
      $success = $this->variations_model->run_annotation_pipeline($time_stamp, $genesFile);     
      $this->email->attach(BASEPATH."tmp/myvariants$time_stamp.log");
      $this->email->send();
    }
   $this->load->view($this->editor_layout, $data);
  }

  /**
   * Show unreleased
   *
   * Show all of the unreleased changes made to the variation data.
   *
   * @author Sean Ephraim
   * @access public
   * @param string $mode Display mode: 'page' (for multiple variations) or 'variation' (for single)
   * @param int $id Either the page number or the variation's unique ID (depending on the mode)
   * @return void
   */
  public function show_unreleased($mode = 'page', $id = 1) {
    redirect_all_nonmembers();

    $data['title'] = 'Unreleased changes';
    $data['content'] = 'variations/unreleased';

    if ($mode === 'variation') {
      // Get changes for single variant
      $data['page_links'] = ''; // Hide page numbers for this
      $data['variants'] = $this->variations_model->get_unreleased_changes($id);
    }
    else {
      // Setup pagination for multiple variants
      $page_num = $id;
      $this->load->library('pagination');
      $config['base_url'] = site_url('variations/unreleased/page');
      $config['total_rows'] = $this->variations_model->num_unreleased();
      $config['per_page'] = 100; 
      $this->pagination->initialize($config); 
      $data['page_links'] = $this->pagination->create_links();
      // Get variant IDs within specified range
      $start_pos = ($page_num - 1)*$config['per_page'];
      $reviews = $this->variations_model->get_ids_within_range($this->tables['reviews'], $start_pos, $config['per_page'], TRUE);
      // Query variant changes individually, then push them onto array
      $data['variants'] = array();
      foreach ($reviews as $review) {
        $changes = $this->variations_model->get_unreleased_changes($review->variant_id);
        array_push($data['variants'], array_shift($changes)); // Take first (and only) result, push onto array
      }
    }

    // Create proper header (depending on whether or not changes occurred)
    if (empty($data['variants'])) {
      $data['variants'] = array();
      $data['header'] = 'No new changes have been made';
    }
    else {
      if ($mode === 'variation') {
        // Header for single variation
        $data['header'] = "Unreleased changes for this variation";
      }
      else {
        // Header for multiple variations
        $range_limit_1 = $start_pos + 1;
        $range_limit_2 = $start_pos + count($reviews);
        $num_unreleased = $this->variations_model->num_unreleased();
        $data['header'] = "$num_unreleased Unreleased changes | Showing $range_limit_1 - $range_limit_2";
      }
    }

    // Store this URL in order to refer back to it after saving
    $this->session->set_flashdata('refer_url', current_url());

    $this->load->view($this->editor_layout, $data);
  }

  /**
   * Submit changes
   * 
   * This has 2 different functions:
   *   1.) Save the variant confirmation selection, OR
   *   2.) Release all variant changes
   *
   * 1.) Saves the confirmation selection for variant change
   *     quality control.
   *
   * 2.) Releases all changes currently within the queue.
   * A backup is first created, then the changes are made, and finally
   * the changes are emptied from the queue.
   *
   * @author Sean Ephraim
   * @access public
   * @return void
   */
  public function submit_changes() {
    redirect_all_nonmembers();

    // Refuse access to wanderers
    if ( ! isset($_POST['save-changes']) && ! isset($_POST['release-changes'])) {
      die("Hmmm... you must have wandered here by mistake.");
    }

    // Update confirmation status of all variants on this page
    $post = $this->input->post();
    $variants = (isset($post['variants-on-this-page'])) ? $post['variants-on-this-page'] : NULL;
    if (is_array($variants)) {
      foreach ($variants as $variant_id) {
        $old_review = $this->variations_model->get_variant_review_info($variant_id);
        $data['confirmed_for_release'] = TRUE;
        if (isset($post['unconfirmed-variants']) && array_search($variant_id, $post['unconfirmed-variants']) !== FALSE) {
          // variant was found in list of unconfirmed variants
          $data['confirmed_for_release'] = FALSE;
        }
        $this->variations_model->update_variant_review_info($variant_id, $data);

        // Log the activity if the review changed
        if ( ! empty($old_review) && $data['confirmed_for_release'] != (bool) $old_review->confirmed_for_release) {
          $username = $this->ion_auth->user()->row()->username;
          $variation = $this->db->get_where($this->tables['vd_queue'], array('id' => $variant_id))->row_array();
          $gene = empty($variation['gene']) ? 'MISSING_GENE' : $variation['gene'];
          $protein = empty($variation['hgvs_protein_change']) ? 'MISSING_PROTEIN_CHANGE' : $variation['hgvs_protein_change'];
          $variation = empty($variation['variation']) ? 'MISSING_VARIATION' : $variation['variation'];
          if ($data['confirmed_for_release']) {
            activity_log("User '$username' confirmed changes for variant $gene|$protein|$variation", 'confirm');
          }
          else {
            activity_log("User '$username' unconfirmed changes for variant $gene|$protein|$variation", 'unconfirm');
          }
        }
      }
    }

    if (isset($_POST['save-changes'])) {
      // Confirmation changes saved
      $html = 'Changes saved.';
      $this->session->set_flashdata('success', $html);
    }
    else if (isset($_POST['release-changes'])) {
      // Attempt to RELEASE all changes

      /* NOTE: A release can only be successful if all variant changes have been
       *       confirmed for release. This means that 'unconfirmed-variants' must be
       *       empty. If any checkboxes in the name of 'unconfirmed-variants' are checked,
       *       then this element will not be empty, and the attempt to release will fail.
       */
      if ($_POST['special-release'] === 'none') {
        $found_unconfirmed = FALSE;
        if (isset($_POST['unconfirmed-variants']) && count($_POST['unconfirmed-variants']) > 0) {
          // ERROR: found unconfirmed variants on this page
          $found_unconfirmed = TRUE;
        }
        // Check that all variants in queue have been confirmed for release
        $all_queue_variants = $this->variations_model->get_unreleased_changes();
        foreach ($all_queue_variants as $variant_id => $values) {
          $variant_review = $this->variations_model->get_variant_review_info($variant_id);
          if ($variant_review->confirmed_for_release == 0) {
            // ERROR: found unconfirmed variants in the queue (not necessarily on this page)
            $found_unconfirmed = TRUE;
          }
        }
        if ($found_unconfirmed) {
          // Release failed! Not all variants have been confirmed for release
          $html = 'All changes must be confirmed prior to release. Check the boxes on the right side to confirm each change, or see the bottom of this page for special release options.';
          $this->session->set_flashdata('error', $html);
          redirect('variations/unreleased');
        }
      }

      if ($_POST['special-release'] === 'force-all' || $this->version == 0) {
        // Release all variants regardless of confirmation status
        $success = $this->variations_model->push_data_live(FALSE);
      }
      else {
        // Only release the confirmed variants
        $success = $this->variations_model->push_data_live();
      }

      if ($success === TRUE) {
        // Successful release
        $confirmed = '';
        if ($_POST['special-release'] === 'force-confirmed') {
          $confirmed = 'confirmed ';
        }
        $html = '<p>'
              . '    <p><i class="icon-ok"></i>&nbsp;&nbsp;&nbsp;Backup created</p>'
              . '    <p><i class="icon-ok"></i>&nbsp;&nbsp;&nbsp;All '.$confirmed.'changes released</p>'
              . '</p>';
        $this->session->set_flashdata('success', $html);
      }
      else {
        // ERROR: Problem with releasing changes
        $html = '<p>There was an error releasing changes. Please make sure that any changes you would like to release have been confirmed and/or any special release options have been selected.</p>';
        $this->session->set_flashdata('error', $html);
      }
    }

    // Redirect to proper page
    $refer_url = $this->session->flashdata('refer_url');
    if (isset($_POST['release-changes']) || empty($refer_url)) {
      // Return to default URL if there's no reference URL or after releasing changes
      redirect('variations/unreleased');
    }
    else {
      // Return to reference URL
      redirect($refer_url);
    }
  }

  /** 
   * Letter
   *
   * Display all genes start with a certain letter
   *
   * @author Sean Ephraim
   * @access public
   * @param  string $letter
   *    The gene's starting letter
   * @return void
   */
  public function letter($letter) {
    $data['title'] = $letter;
    $data['content'] = 'variations/letter';

    $this->load->model('genes_model');
    $this->load->helper('genes');
    $data['genes'] = $this->genes_model->get_genes_and_aliases($letter, FALSE);

    # Format genes names to display as "GENE (ALIAS)", or just "GENE" if no alias
    $data['display_names'] = Array();
    foreach ($data['genes'] as $gene => $alias) {
      if ($alias !== NULL) {
        $data['display_names'][$gene] = "$gene ($alias)";
      }
      else {
        $data['display_names'][$gene] = $gene;
      }
    }

    $this->load->view($this->public_layout, $data);
  }

  /** 
   * Variations_table
   *
   * Load all variations for a specific gene.
   *
   * @author Sean Ephraim
   * @access public
   * @param  string $gene Gene name
   * @return void
   */
  public function variations_table($gene) {
    $data['title'] = $gene;
    $data['content'] = 'variations/gene';

    $data['gene'] = $gene;
    // Columns to select for this page
    $columns = 'id,hgvs_protein_change,hgvs_nucleotide_change,variantlocale,variation,pathogenicity,disease';
    $data['variations'] = $this->variations_model->get_variants_by_gene($gene, $columns);

    $this->load->view('variations/gene', $data);
  }

  /** 
   * Show Variant
   *
   * Display the variant data page.
   * pChart is required to load the frequencies.
   * For more info, refer to the frequency() function.
   *
   * @author Sean Ephraim
   * @access public
   * @param  int $id
   *    Variant's unique ID
   * @return void
   */
  public function show_variant($id) {
    // Install pChart (if it's missing)
    if (!file_exists(APPPATH.'third_party/pChart')) {
      $dir = APPPATH."third_party/";
      // Download pChart
      file_put_contents($dir."pChart.tar.gz", file_get_contents("http://www.pchart.net/release/pChart2.1.4.tar.gz"));
      // Decompress from gz
      $p = new PharData($dir.'pChart.tar.gz');
      $p->decompress(); // creates pChart.tar
      // Unarchive from the tar
      $p = new PharData($dir.'pChart.tar');
      $p->extractTo($dir.'pChart_temp');
      rename($dir.'pChart_temp/pChart2.1.4', $dir.'pChart');
      // Remove unwanted files/directories
      unlink($dir.'pChart.tar.gz');
      unlink($dir.'pChart.tar');
      rmdir($dir.'pChart_temp');
    }

    $data = $this->variations_model->get_variant_display_variables($id, $this->tables['vd_live']);
    $data['title'] = $data['variation'];
    $content = 'variations/variant/index';

    // Set display style for frequency data
    $freqs = $this->config->item('frequencies');

    $this->load->view($content, $data);
  }

  /** 
   * Download Variant PDF
   *
   * Download the variant data page in PDF format using the
   * dompdf library (found in application/third_party/dompdf/)
   *
   * More info on dompdf at https://github.com/dompdf/dompdf
   *
   * @author Sean Ephraim, Nikhil Anand
   * @access public
   * @param  int $id Variant's unique ID
   * @return void
   */
  public function download_variant_pdf($id) {
    // Install DomPDF (if it's missing)
    if (!file_exists(APPPATH.'third_party/dompdf')) {
      $dir = APPPATH."third_party/";
      // Download DomPDF
      file_put_contents($dir."dompdf.tar.gz", file_get_contents("https://dompdf.googlecode.com/files/dompdf_0-6-0_beta3.tar.gz"));
      // Decompress from gz
      $p = new PharData($dir.'dompdf.tar.gz');
      $p->decompress(); // creates dompdf.tar
      // Unarchive from the tar
      $p = new PharData($dir.'dompdf.tar');
      $p->extractTo($dir.'dompdf_temp');
      rename($dir.'dompdf_temp/dompdf', $dir.'dompdf');
      // Remove unwanted files/directories
      unlink($dir.'dompdf.tar.gz');
      unlink($dir.'dompdf.tar');
      rmdir($dir.'dompdf_temp');
    }

    // 'full' and 'print' must be set as parameters for proper PDF display
    if ( ! isset($_GET['print']) || ! isset($_GET['full'])) {
      // ... set these parameters if they aren't already
      redirect("pdf/$id?full&print");
    }

    define("DOMPDF_ENABLE_REMOTE", true); // Override default config
    require_once(APPPATH."third_party/dompdf/dompdf_config.inc.php");

    $data = $this->variations_model->get_variant_display_variables($id);
    $data['title'] = $data['variation'];
    $content = 'variations/variant/index';

    // Get HTML
    $target_html = $this->load->view($content, $data, true);
    
    // Make the PDF using DOMPDF and offer it for download
    $pdf_object = new DOMPDF();
    $pdf_object->load_html($target_html);
    $pdf_object->render();
    $pdf_object->stream($this->strings['site_short_name'] . '-pdf.' . $id . '.pdf');
  }

  /**
  * Frequency
  *
  * This uses the pChart library to graph the frequency.
  *
  * Upon browser request, this script will run with the specificed
  * percentage ($value) as a parameter. The pChart script will generate
  * a graph image on-the-fly which will then be displayed on the
  * variant page. As stated, they are on-the-fly images that are returned
  * at runtime and are not actually saved anywhere.
  *
  * The pChart library that can be found in application/third_party/pChart/
  * More info on pChart at http://www.pchart.net/
  *
  * @author Nikhil Anand
  * @author Sean Ephraim
  * @access public
  * @return void
  */
  public function frequency() {
    // this is needed to allow stroke() to modify headers
    ob_end_clean();
    
    // pChart Classes              
    require_once(APPPATH."third_party/pChart/class/pDraw.class.php"); 
    require_once(APPPATH."third_party/pChart/class/pImage.class.php");
    
    // Validate precent value
    if (isset($_GET["value"]) && is_numeric($_GET["value"])) {
      $percent = trim($_GET["value"]);
    } else {
      print "Invalid value.\n";
      exit;
    }
    
    // Set default size
    $size = 200;
    if (isset($_GET["small"])) {
      $size = 60;
    }
    
    // Small bug with 0%
    if ($percent == 0) {
      $percent = 0.001;
    }
    
    /* Initialize object */
    $myPicture = new pImage($size,12); 
    
    $myPicture->setFontProperties(array(
      "FontName" => APPPATH."third_party/pChart/fonts/GeosansLight.ttf",
      "FontSize" => 15)
    );
    
    /* Set options */
    $progressOptions = array(
        "Width"=>($size - 1),
        "Height"=>11,
        "R"=>8, 
        "G"=>160, 
        "B"=>43, 
        "Surrounding"=>0, 
        "BoxBorderR"=>204, 
        "BoxBorderG"=>204, 
        "BoxBorderB"=>204, 
        "BoxBackR"=>255, 
        "BoxBackG"=>255, 
        "BoxBackB"=>255, 
        "RFade"=>206, 
        "GFade"=>133, 
        "BFade"=>30, 
        "ShowLabel"=>FALSE,
    ); 
    
    /* Draw a progress bar */
    $myPicture->drawProgress(0,0,$percent,$progressOptions); 
    $myPicture->stroke();
  }
}
