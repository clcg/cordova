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
   * searchPosLetter
   *
   * Display all genes start with a certain letter based on the variants passed to the function
   * 	This allows for multiple genes' results to be displayed implicitly though it was not designed with that in mind.
   * 	Being that this is true, the functionality is there but it may need some tweaking for purposeful use of that functionality
   *
   * @author Robert Marini
   * @access public
   * @param  array $variants, specifically, $variants is an array of stdClass Objects
   *    The variants returned from position search
   * @return void
   */
  public function searchPosLetter($variants) {	
  	
  	$data['title'] = $variants[0]->gene;
  	$letter = $variants[0]->gene[0];
  	
  	$this->load->model('genes_model');
  	$this->load->helper('genes');
  	
  	$data['genes'] = $this->genes_model->get_genes_and_aliases($letter, FALSE);
  		
  	//narrowing results to just the $variants related genes
  	$tempGenes = Array();
  	$genesKeys = array_keys($data['genes']);
  	foreach ($genesKeys as $key) {
  		foreach ($variants as $variant) {
  			if(strcmp($variant->gene, $key) == 0){
  				$tempGenes[$key] = $data['genes'][$key];
  			}
  		}
  	}
  	ksort($tempGenes);
  	$data['genes'] = $tempGenes;
  	
  	if(count($data['genes']) > 1){
  		$data['content'] = 'variations/letter';
	  	
	  	// Format genes names to display as "GENE (ALIAS)", or just "GENE" if no alias
	  	$data['display_names'] = Array();
	  	foreach ($data['genes'] as $gene => $alias) {
	  		if ($alias !== NULL) {
	  			$data['display_names'][$gene] = "$gene ($alias)";
	  		}
	  		else {
	  			$data['display_names'][$gene] = $gene;
	  		}
	  	}
  	} else { //all variants found in a single gene
  		
  		$gene = $variants[0]->gene;
  		$data['gene'] = $gene;
  		$data['content'] = 'genes/gene_page';
  		$alias = $this->genes_model->get_gene_alias($gene);
  		
  		# Format genes names to display as "GENE (ALIAS)", or just "GENE" if no alias
  		if($alias !== NULL){
  			$data['display_name'] = "$gene ($alias)";
  		} else {
  			$data['display_name'] = $gene;
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
    $data['variations'] = $this->variations_model->get_variants_by_gene($gene, $columns)->result();
    $this->load->view('variations/gene', $data);
  }
  
  /**
   * variations_table_variant_pos_search
   *
   * Load all variations for a specific position based search string
   *
   * @author Robert Marini
   * @access public
   * @param  string $searchStr, a string of the chr:pos
   * @return void
   */
  public function variations_table_variant_pos_search($searchStr) {
  	
  	
	$search_array = array();
	$search_array[] = $searchStr;
	$positionAndAllele = $this->format_position_from_url_safe($searchStr);
  	$variants = $this->variations_model->get_variants_by_position_array($positionAndAllele); //hard code test case: 'chr10:89623197'
  	
  	$data['title'] = $positionAndAllele['pos'];
  	$data['content'] = 'variations/gene';
  
  	$data['gene'] = $variants[0]->gene;
  	
  	// Columns to select for this page....HERE WE CAN ADJUST THE TABLE FOR DISPLAY
  	$columns = 'id,hgvs_protein_change,hgvs_nucleotide_change,variantlocale,variation,pathogenicity,disease';
  	$columnArray = explode(',',$columns);
  	
  	//slim $variations into array of only the columns
  	$rows = array(); //empty array
  	foreach ($variants as $variation) {
  		$tempRow = new stdClass;
  		foreach ($columnArray as $column) {
  			$tempRow->$column = $variation->$column;
  		}
  		
  		$rows[] = $tempRow;
  	}
  	
  	$data['rows'] = $rows;
  	$data['columns'] = $columns;
  	
  	$data['variations'] = $variants; //$variationsColumns;
  
  	$this->load->view('variations/gene', $data);
  }

  /**
   * viewer for pv
   *
   * Load a PV viewer for each PDB file for the specified gene and redirect
   * to the viewer page.
   *
   * @author Matt Andress
   * @access public
   * @param  string $gene Gene name
   * @retrun void
   */
  public function viewer($gene) {
  	$data['title'] = "$gene Protein Structure(s)";
  	$data['content'] = 'variations/viewer';
  	$data['gene'] = $gene;
  	
  	
  	$genePath = "assets/public/pdb/dvd-structures/$gene/";
  	if (is_dir($genePath)) {
  		$structures = array();
  		
  		$structureDirs = glob("$genePath*", GLOB_ONLYDIR);
  		foreach ($structureDirs as $dir) {
  			$pathParts = explode("/", $dir);
  			$structureRange = $pathParts[count($pathParts)-1];
  			
  			$prevRes = "";
  			$structureRes = array();
  			$structFile = fopen("$dir/".$gene."_".$structureRange."_FFX.pdb", "r");
  			while($line = fgets($structFile)) {
  				$pregLine = preg_replace("/[\s]+/", " ", $line);
  				$lineArr = explode(" ", $pregLine);
  				if(($lineArr[0] == "ATOM") && ($lineArr[5] != $prevRes)) {
  					$structureRes[] = array("start_index" => intval($lineArr[1]), "name" => $lineArr[3], "residue_index" => intval($lineArr[5]));
  					$prevRes = $lineArr[5];
  				}
  			}
  			fclose($structFile);
  			$structures[] = array("name" => $structureRange, "residues" => $structureRes);
  		}
  		$data['structures'] = $structures;
  		$data['suffix'] = '_FFX.pdb';
  		$data['path'] = $genePath;
  	}
  	else {
  		$data['error'] = "Unable to find structures for $gene";
  	}
  	
  	
  	$this->load->view($this->public_layout, $data);
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
   * show_variant_with_position
   *
   * Display the variant data page by providing the target gene and position of the target variant.
   * pChart is required to load the frequencies.
   * For more info, refer to the frequency() function.
   *
   * @author Robert Marini
   * @access public
   * @param  string $position
   * 	position as a string, chr14_12345
   * 	can be fuzzy searched, see variations_model.php function 
   * 	'get_variants_with_position'
   * @return void
   * 
   * @Note: also provides legacy support for variant by id lookup
   * 
   * dev notes:
   * 	iterate through query->result
   * 	do a print statement thorugh all of records in result
   * 		create a basic output html to write dev output to
   * 
   */
  public function show_variant_with_position($positionUrlSafe) {
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
  
  	$positionAndAllele = $this->format_position_from_url_safe($positionUrlSafe);
  	$variants = $this->variations_model->get_variants_by_position_array($positionAndAllele);
  	
  	if(count($variants) === 1){
  		//a single variant found
  		
  		$variant = json_decode(json_encode($variants[0]),true);
  		
  		$data = $this->variations_model->get_variant_display_variables($variant['id'], $this->tables['vd_live']); //$variant changed to $aVariant
  		
  		$data['title'] = $data['variation'];
  		$content = 'variations/variant/index';
  		
  		// Set display style for frequency data
  		$freqs = $this->config->item('frequencies');
  		
  		$this->load->view($content, $data);
  		
  	} elseif (count($variants) < 1){
  			//display that no variant or gene was found
  			$data['title'] = "Variant Search By Position: $positionUrlSafe ";
  			$data['variantSearchTerm'] = $positionUrlSafe;
  			$data['content'] = 'variations/variant-search-404';
  			$data['error'] = "Unable to find variant based on the following input: $positionUrlSafe";
  			$this->load->view($this->public_layout, $data);
  			
  	} else {
  			//multiple variants found from search result
  			
  			$this->searchPosVariants = $variants;
  			$letter = $variants[0]->gene[0];
  			$this->searchPosLetter($variants); //////////////////////
  			
  	}
  	
  }
  
  /**
   * format_position
   * 
   * formats argument string into one that would be searchable i mysql tables
   * 
   * @author Robert Marini
   * @access public
   * @param string $position
   * 	position as a url compliant string using underscores to separate pieces
   * 	chr10:89623197:T>G would be chr10_89623197_T%3EG
   * @return string $formattedPosition
   */
  public function format_position_from_url_safe($positionUrlSafe) {
  	
  	$positionOrig = str_replace('%3A', ':',$positionUrlSafe);
  	$positionOrig = str_replace('%3E', '>',$positionOrig);
  	$positionOrig = str_replace('%7F', '',$positionOrig);
  	$explodedPosition = explode(':',$positionOrig);
  	
  	$searchSplitOut = array(
  			"chr" => "NA",
  			"pos" => "NA",
  			"ref" => "NA",
  			"alt" => "NA",
  			"format_error" => "NA",
  	);
  	
  	//run through explodedPosition looking for 'chr' substring, and '>', a 2nd element (pos). IN THAT ORDER
  	// save chr as chr
  	// split '>' into ref and alt
  	// save 2nd element as pos
  	if(count($explodedPosition) > 3){
  		//error, incorrect format of search string....too many fields
  		$searchSplitOut['format_error'] = "Incorrect format of search string: Too Many Fields. Correct format: chromosome:position:reference>alternate";
  	} else {
  		
  		$searchSplitOut['chr'] = str_replace('chr','',$explodedPosition[0]);
  		
  		if(count($explodedPosition) > 2){
  			
  			if(strpos($explodedPosition[2],'>') !== false){
  				$refAlt = explode('>',$explodedPosition[2]);
  				$searchSplitOut['ref'] = $refAlt[0];
  				$searchSplitOut['alt'] = $refAlt[1];
  			} else {
  				$searchSplitOut['ref'] = $explodedPosition[2];
  			}
  		
  		}
  		
  		$searchSplitOut['pos'] = $explodedPosition[1];
  		

  	}
  	
  	return $searchSplitOut;
  	
  }
  
  /**
   * position by search bar
   *
   * formats argument string into one that would be searchable i mysql tables
   *
   * @author Robert Marini
   * @access public
   * @param string $searchStrPos
   * 	position as a url compliant string:
   * 		chr14%3A23440404%3AG>A would be chr14:23440404:G>A
   * @return [void]
   */
  public function search_bar_pos () {
  	
  	// Prevent XSS
  	$_GET   = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
  	
  	$this->show_variant_with_position($_GET["searchStr"]); //searchPosition
  		
  }
  
  
  /**
   * pos_search_variations_table
   *
   * Load all variations for a specific position search.
   *
   * @author Robert Marini
   * @access public
   * @description
   * 	parallels show_variants($gene) function by Sean but works in arguments of already collected 
   * 		information of variations.
   * @param  stdobj $variations variation previously loaded
   * @return void
   */
    public function pos_search_variations_table($variations) {
  
    	$data['title'] = 'Variations - ' . strtoupper($variations[0]->gene);
    	$letter = $variations[0]->gene[0];
    	$data['content'] = 'variations/letter';
    	
    	//from letter function
    	$this->load->model('genes_model'); //from letter
    	$this->load->helper('genes'); //from letter
    	$data['genes'] = $this->genes_model->get_genes_and_aliases($letter, FALSE);
    	# Format genes names to display as "GENE (ALIAS)", or just "GENE" if no alias
    	$data['display_names'] = Array();
    	foreach ($data['genes'] as $gene => $alias) {
    		if (strcmp($gene, $variations[0]->gene) == 0) {
	    		if ($alias !== NULL) {
	    			$data['display_names'][$gene] = "$gene ($alias)";
	    		}
	    		else {
	    			$data['display_names'][$gene] = $gene;
	    		}
    		} else {
    			unset($data['genes'][$gene]);
    		}
    	}
    	//end from letter function
    	
    	$data['gene'] = $variations[0]->gene;
    	// Columns to select for this page....HERE WE CAN ADJUST THE TABLE FOR DISPLAY
    	$columns = 'id,hgvs_protein_change,hgvs_nucleotide_change,variantlocale,variation,pathogenicity,disease';
    	$columnArray = explode(',',$columns);
    	
    	//slim $variations into array of only the columns
    	$rows = array(); //empty array 
    	foreach ($variations as $variation) { 
    		$tempRow = new stdClass;
    		foreach ($columnArray as $column) {
    			$tempRow->$column = $variation->$column;
    		}

    		$rows[] = $tempRow;
    	}
    	
//     	$data['genes'] = $rows; //testing this
    	
    	$data['rows'] = $rows;
    	$data['columns'] = $columns;
    	
  		$data['variations'] = $variations; //$variationsColumns;
  		
  		$data['geneTable'] = 'variations/gene';

  		$data['content'] = 'variations/gene';
  		
		$this->load->view($this->public_layout, $data);
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
