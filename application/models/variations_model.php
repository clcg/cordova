<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Variations_model extends MY_Model {
	/**
	 * Array of database tables used.
	 *
	 * @var array
	 */
  public $tables = array();

	public function __construct() {
		parent::__construct();

		// Initialize db tables data
		$this->tables = $this->config->item('tables');
	}

  /**
   * Remove Temp Files
   *
   * Removes all temporary files that get created during the annotation
   * process. Any files that BEGIN with the provided string will be
   * removed. For example if 'foo' is provided, then the bash command
   * 'rm foo*' will be performed.
   *
   * @author Sean Ephraim
   * @access public
   * @param  string Filename prefix for all files to be removed
   */
   public function remove_temp_files($prefix) {
     exec("rm $prefix*");
   }

  /**
   * Format Hg19 Position
   *
   * This function will return the correct format for the
   * Hg19 genomic position. This means:
   *   - The user does not have to worry about case-sensitivity
   *     when entering a variation
   *       - i.e. "CHR1:41283868:g>a" vs. "chr1:41283868:G>A"
   *   - The user does not have to include "chr" in the name
   *     of the variation
   *       - i.e. "1:41283868:G>A" vs. "chr1:41283868:G>A"
   *
   * If the second parameter ($for_dbnsfp) is TRUE, then the input
   * will be formatted for use with dbNSFP. This means:
   *   - chr1:41283868:G>A becomes 1 41283868 G A
   *
   * @author  Sean Ephraim
   * @access  public
   * @param   string  Genomic position (Hg19) (unformatted)
   * @return  string  Genomic position (Hg19) (formatted)
   */
   public function format_hg19_position($variation, $for_dbnsfp = FALSE) {
    /*
     Get the parts of the variation
     For a variant such as chr1:41283868:G>A, the parts would be
       [0] => chr1
       [1] => 41283868
       [2] => G>A
    */
    $parts = explode(':', $variation);

    // Format part 0 of the variation...
    // But first, does the part contain "chr" in it?
    if (stristr($parts[0], 'chr') !== FALSE) {
      // Yes, the part contains "chr", now make it all lowercase
      $parts[0] = strtolower($parts[0]);
    }
    else {
      // No, the part doesn't contain "chr", so add it
      $parts[0] = 'chr' . $parts[0];
    }

    // Make sure X and Y are uppercase (if applicable)
    $parts[0] = str_replace("x", "X", $parts[0]);
    $parts[0] = str_replace("y", "Y", $parts[0]);

    // Make part 2 (the alleles) uppercase
    $parts[2] = strtoupper($parts[2]);

    // Put all 3 parts back together and return it
    $variation = implode(':', $parts);

    if ($for_dbnsfp) {
      // Format the variation for dbNSFP
      // |--> chr1:41283868:G>A becomes 1 41283868 G A
      $variation = str_replace('chr', '', $variation);
      $variation = str_replace(array(':', '>'), "\t", strtoupper($variation));
    }

    return $variation;
   }

  /**
   * Get dbSNP ID
   *
   * Queries dbSNP in order to find a variant's dbSNP ID. This function will
   * retrieve the HTML for a dbSNP result based on a variant's chromosome number/letter
   * and chromosomal position.
   *
   * *KNOWN ISSUE*: This function will only return a SNP ID if 1 (and only 1) SNP ID is
   *                associated with that position. Otherwise it will return NULL, even
   *                if 2 or more SNP IDs exist.
   *
   * @author  Sean Ephraim
   * @access  public
   * @param   string  Genomic Position (Hg19) (machine name: variation)
   * @return  mixed   dbSNP ID if only 1 is found; else NULL if more than 1 found
   */
  public function get_dbsnp_id($variation) {
    /*
     Get the parts of the variation
     For a variant such as chr1:41283868:G>A, the parts would be
       [0] => chr1
       [1] => 41283868
       [2] => G>A
    */
    $parts = explode(':', $variation);

    // Get the chromosome number/letter
    $chr = substr($parts[0], strpos($parts[0], 'chr') + 3); 
    // Get the chromosomal position
    $pos = $parts[1];
    // Query dbSNP
    $url = "http://www.ncbi.nlm.nih.gov/snp/?term=((".$chr."[Chromosome])+AND+".$pos."[Base+Position])&amp;report=DocSet";
    $html = shell_exec("curl -g --silent --max-time 5 --location \"$url\"");
    $lines = preg_split('/\s+/', $html);
    $dbsnp = NULL;
    $dbsnps = array();
    // Fetch the SNP ID from the HTML
    foreach ($lines as $line) {
      if (strstr($line, "SNP_ID=")) {
        // Strip away everything except the SNP ID number
        $dbsnp = 'rs'.substr($line, strpos($line, 'SNP_ID=') + 7); 
        if ( ! in_array($dbsnp, $dbsnps)) {
          $dbsnps[] = $dbsnp;
        }   
      }   
    }   
  
    // return dbSNP ID (if only 1 was found)
    if (count($dbsnps) == 1) {
      return trim($dbsnps[0]);
    }   
    else {
      return NULL;
    } 
  }

  /**
   * Get Annotation Data
   *
   * NOTE: This function should ONLY be used to *add* new data to the variation database.
   *       Never use this function for variants that already exist in the database
   *       because it will take way too long (it pulls from several large databases).
   *       For loading variant data that's already in the variation database, use
   *       get_variant_by_id() or get_variants_by_position() instead (they pull from
   *       the variation database, which is MUCH faster) as they are much more practical
   *       to use with things like the API and views.
   *
   * Uses the annotation tool to retrieve variant data based on the Genomic Position (Hg19).
   * This function will run variant annotation, parse the variant data, and return the fields
   * that are relevant to the database.
   *
   * Be sure to specify the path to the annotation tool in the 
   * application/config/variation_database.config file.
   *
   * Sample structure of the output array:
   * $data['variation']              
   * $data['gene']                   
   * $data['hgvs_nucleotide_change'] 
   * $data['hgvs_protein_change']    
   * $data['variantlocale']          
   *
   * @author Sean Ephraim
   * @access public
   * @param  string Genomic Position (Hg19) (machine name: variation)
   * @return mixed  Array of fields to be autofilled; negative number on error
   */
  public function get_annotation_data($variation) {

    // Path to annotation tool and associated files
    $annot_path = $this->config->item('annotation_path');
    $ruby_path = $this->config->item('ruby_path');
    $run_script = $annot_path.'kafeen.rb';
    $id = random_string('unique'); // unique ID (needed to avoid file collisions)
    $f_in = $annot_path."tmp/$id.in"; // annotation input file
    $f_out = $annot_path."tmp/$id.out"; // annotation output file
    $f_errors = $annot_path."tmp/$id.error_log"; // annotation errors file

    /* Is the annotation tool installed and properly referenced? */
    if (empty($annot_path)) die("The path to the annotation tool has not been configured. Please contact the administrator.\n");
    if ( ! file_exists($annot_path.'bin/ASAP-dedup_test-1.18.dev.jar')) die("The annotation tool cannot be found. Please contact the administrator.\n");
    if ( ! file_exists($run_script)) die("The script to run annotation cannot be found. Please contact the administrator.\n");

    /* BEGIN RUNNING ANNOTATION */

    // Delete old input/output files if they exist (just to be safe)
    $this->remove_temp_files($f_in);

    $variation = $this->format_hg19_position($variation);

    // Create annotation input file
    $success = file_put_contents($f_in, $variation);
    if ($success === FALSE) die("The input file for annotation could not be created. Please contact the administrator.\n");
    if ( ! chmod($f_in, 0777)) die("Annotation input file must have correct permissions. Please contact the administrator.");

    // Run annotation (logs are written to $annot_path/tmp/log)
    if ($ruby_path == '') {
      $ruby_path = 'ruby'; // use default location if blank
    }
    exec("$ruby_path $run_script --progress --in $f_in --out $f_out > ".$annot_path."tmp/log 2>&1");
    
    // Check if annotation returned an error
    if (file_exists($f_errors)) {
      $contents = file_get_contents($f_errors);
      if (strpos($contents, 'ERROR_NOT_SUPPORTED_MUTATION_TYPE')) {
        // ERROR: unsupported mutation type
        $this->remove_temp_files($f_in);
        return -400;
      }

      if (strpos($contents, 'ERROR_NO_MATCHING_REFSEQ')) {
        // ERROR: no matching refseq (annotation returned nothing)
        $this->remove_temp_files($f_in);
        return -501;
      }
    }
    
    // Get annotation data and cleanup
    $contents = file_get_contents($f_out);
    $this->remove_temp_files($f_in);

    /* END RUNNING ANNOTATION */

    if (empty($contents)) {
      // ERROR: No data found for this variant
      return -404;
    }

    // Turn column data into associative array
    $lines = explode("\n", $contents);
    $keys = explode("\t", $lines[0]);
    $values = explode("\t", $lines[1]);
    $annot_result = array_combine($keys, $values);
    
    // Convert all '.' or '' values to NULL
    foreach ($annot_result as $key => $value) {
      if ($value === '.' || $value === '') {
        $annot_result[$key] = NULL;
      }
    }

    /**
     * NOTE: Each key is the exact same name of a column in the database.
     */
    $data = array(
        'variation'              => $annot_result['variation'],
        'gene'                   => $annot_result['gene'],
        'hgvs_nucleotide_change' => $annot_result['hgvs_nucleotide_change'],
        'hgvs_protein_change'    => $annot_result['hgvs_protein_change'],
        'variantlocale'          => $annot_result['variantlocale'],
        'pathogenicity'          => $annot_result['pathogenicity'],
        'dbsnp'                  => $annot_result['dbsnp'],
        'phylop_score'           => $annot_result['phylop_score'],
        'phylop_pred'            => $annot_result['phylop_pred'],
        'sift_score'             => $annot_result['sift_score'],
        'sift_pred'              => $annot_result['sift_pred'],
        'polyphen2_score'        => $annot_result['polyphen2_score'],
        'polyphen2_pred'         => $annot_result['polyphen2_pred'],
        'lrt_score'              => $annot_result['lrt_score'],
        'lrt_pred'               => $annot_result['lrt_pred'],
        'mutationtaster_score'   => $annot_result['mutationtaster_score'],
        'mutationtaster_pred'    => $annot_result['mutationtaster_pred'],
        'gerp_nr'                => $annot_result['gerp_nr'],
        'gerp_rs'                => $annot_result['gerp_rs'],
        'gerp_pred'              => $annot_result['gerp_pred'],
        'lrt_omega'              => $annot_result['lrt_omega'],
        'evs_ea_ac'              => $annot_result['evs_ea_ac'],
        'evs_ea_an'              => $annot_result['evs_ea_an'],
        'evs_aa_ac'              => $annot_result['evs_aa_ac'],
        'evs_aa_an'              => $annot_result['evs_aa_an'],
        'otoscope_ac'            => $annot_result['otoscope_ac'],
        'otoscope_an'            => $annot_result['otoscope_an'],
        'tg_acb_ac'              => $annot_result['tg_acb_ac'],
        'tg_acb_an'              => $annot_result['tg_acb_an'],
        'tg_asw_ac'              => $annot_result['tg_asw_ac'],
        'tg_asw_an'              => $annot_result['tg_asw_an'],
        'tg_cdx_ac'              => $annot_result['tg_cdx_ac'],
        'tg_cdx_an'              => $annot_result['tg_cdx_an'],
        'tg_ceu_ac'              => $annot_result['tg_ceu_ac'],
        'tg_ceu_an'              => $annot_result['tg_ceu_an'],
        'tg_chb_ac'              => $annot_result['tg_chb_ac'],
        'tg_chb_an'              => $annot_result['tg_chb_an'],
        'tg_chs_ac'              => $annot_result['tg_chs_ac'],
        'tg_chs_an'              => $annot_result['tg_chs_an'],
        'tg_clm_ac'              => $annot_result['tg_clm_ac'],
        'tg_clm_an'              => $annot_result['tg_clm_an'],
        'tg_fin_ac'              => $annot_result['tg_fin_ac'],
        'tg_fin_an'              => $annot_result['tg_fin_an'],
        'tg_gbr_ac'              => $annot_result['tg_gbr_ac'],
        'tg_gbr_an'              => $annot_result['tg_gbr_an'],
        'tg_gih_ac'              => $annot_result['tg_gih_ac'],
        'tg_gih_an'              => $annot_result['tg_gih_an'],
        'tg_ibs_ac'              => $annot_result['tg_ibs_ac'],
        'tg_ibs_an'              => $annot_result['tg_ibs_an'],
        'tg_jpt_ac'              => $annot_result['tg_jpt_ac'],
        'tg_jpt_an'              => $annot_result['tg_jpt_an'],
        'tg_khv_ac'              => $annot_result['tg_khv_ac'],
        'tg_khv_an'              => $annot_result['tg_khv_an'],
        'tg_lwk_ac'              => $annot_result['tg_lwk_ac'],
        'tg_lwk_an'              => $annot_result['tg_lwk_an'],
        'tg_mxl_ac'              => $annot_result['tg_mxl_ac'],
        'tg_mxl_an'              => $annot_result['tg_mxl_an'],
        'tg_pel_ac'              => $annot_result['tg_pel_ac'],
        'tg_pel_an'              => $annot_result['tg_pel_an'],
        'tg_pur_ac'              => $annot_result['tg_pur_ac'],
        'tg_pur_an'              => $annot_result['tg_pur_an'],
        'tg_tsi_ac'              => $annot_result['tg_tsi_ac'],
        'tg_tsi_an'              => $annot_result['tg_tsi_an'],
        'tg_yri_ac'              => $annot_result['tg_yri_ac'],
        'tg_yri_an'              => $annot_result['tg_yri_an'],
    );
    
    // Credits for the comments
    $credits = array();
    $freqs = $this->config->item('frequencies'); // frequencies to display
    $keys = array_keys($data);
    // ESP6500 credit
    if (in_array('evs', $freqs)) {
      if ($this->give_credit_to('evs', $data)) {
        $credits[] = 'ESP6500';
      }
    }
    // 1000 Genomes credit
    if (in_array('1000genomes', $freqs)) {
      if ($this->give_credit_to('tg', $data)) {
        $credits[] = '1000 Genomes';
      }
    }
    // OtoSCOPE credit
    if (in_array('otoscope', $freqs)) {
      if ($this->give_credit_to('otoscope', $data)) {
        $credits[] = 'OtoSCOPE';
      }
    }
    // Always give credit to dbNSFP 2
    $credits[] = 'dbNSFP 2';
    $credits = array_filter($credits);

    // Put together the comments
    $comments = array('Manual curation in progress.');
    if ( ! empty($credits)) {
      $comments[] = 'Record generated from: ' . implode(', ', $credits) . '.';
    }
    $data['comments'] = implode(' ', $comments);

    return $data;
  }

  /**
   * Give Credit To
   *
   * Decides whether or not credit should be given to certain data
   * sources such as EVS, 1000 Genomes, etc. For example, if 
   * $data['evs_ea_an'] contains data, then credit should be given to
   * EVS in the comments section. To test for this, an example call
   * would be
   *   give_credit_to('evs', $data)
   * and if any array element in $data such that $data['evs_*'] is 
   * non-empty, then TRUE will be returned.
   *
   * @author  Sean Ephraim
   * @access  public
   * @param   string   Prefix to check for
   * @param   array    Associate array of variant data
   * @return  boolean  TRUE if credit should be given, else FALSE
   */
  public function give_credit_to($prefix, $data)
  {
    $prefix = $prefix . '_';
    foreach ($data as $key => $value) {
      if (strstr($key, $prefix) !== FALSE) {
        if ($data[$key] !== NULL && $data[$key] !== '') {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * Create New Variant
   *
   * Adds a variant to the database. This will first create an empty row
   * in the live table in order to create a unique ID for the variant.
   * Subsequently, the new record will get copied to the queue (in order
   * to maintain the unique ID). Any real data will get added to this
   * record in the queue and will not be seen on the live site until a
   * batch release is performed.
   *
   * Setting the second parameter to TRUE allows you to turn on manual mode
   * in order to insert a variant without any autofill data from annotation.
   * Use this with care, as it also bypasses any checks for duplication
   * or improper formatting.
   *
   * @author  Sean Ephraim
   * @access  public
   * @param   string   Genomic Position (Hg19) (machine name: variation)
   * @param   boolean  (optional) Bypass annotation to manually insert variant
   * @return  int      Variant ID (positive integer) on success; negative integer on error
   */
  public function create_new_variant($variation, $manual_mode = FALSE)
  {
    // Variation database tables
    $vd_live  = $this->tables['vd_live'];
    $vd_queue = $this->tables['vd_queue'];

    // Check if variation is already in the live and/or queue database
    $query_live  = $this->db->get_where($vd_live, array('variation' => $variation), 1);
    $query_queue = $this->db->get_where($vd_queue, array('variation' => $variation), 1);

    if ($manual_mode !== TRUE) {
      if ($query_live->num_rows() > 0 || $query_queue->num_rows() > 0) {
        // ERROR: Variant exists in the database
        return -409;
      }
    }

    // SUCCESS: Variant does NOT exist in the database
    if ($manual_mode === TRUE) {
      // Manually set the variation
      $annot_data = array('variation' => $variation,
                          'pathogenicity' => 'Unknown significance',
                          'comments' => 'Manual curation in progress.',
                         );
    }
    else {
      // Try running annotation query script...
      $annot_data = $this->get_annotation_data($variation);

      if (is_numeric($annot_data) && $annot_data < 0) {
        // ERROR: annotation returned an error (aka a negative integer)
        return $annot_data;
      }
    }

    // Create empty row in live table and get its unique ID
    $keys = $this->get_variant_fields($vd_live);
    $null_data = array_fill_keys($keys, NULL); // set all values to NULL
    $this->db->insert($vd_live, $null_data);
    $id = $this->db->insert_id();

    // Log it!
    $username = $this->ion_auth->user()->row()->username;
    $gene = empty($queue_data['gene']) ? 'MISSING_GENE' : $queue_data['gene'];
    $protein = empty($queue_data['hgvs_protein_change']) ? 'MISSING_PROTEIN_CHANGE' : $queue_data['hgvs_protein_change'];
    activity_log("User '$username' added new variant $gene|$protein|$variation", 'add');
   
    // Then create a row for this variant in the queue with matching unique ID
    $queue_data = array_merge($null_data, $annot_data); // overwrite non-NULL values
    $queue_data['id'] = $id;
    $this->variations_model->update_variant_in_queue($id, $queue_data);

    // Lastly, create a review record for this variant
    $this->variations_model->update_variant_review_info($id);

    return $id;
  }

  /**
   * Remove Variant From Queue
   *
   * This removes all variant changes from the queue. In addition
   * it removes the empty row that was created for it in the live
   * table as well as any review information for it.
   *
   * @author Sean Ephraim
   * @access public
   * @param  int     Variant unique ID
   */
  public function remove_all_changes($id)
  {
    // Variation database tables
    $vd_live  = $this->tables['vd_live'];
    $vd_queue = $this->tables['vd_queue'];
    $reviews = $this->tables['reviews'];

    $variation = $this->db->get_where($vd_queue, array('id' => $id))->row_array();

    $this->db->delete($vd_queue, array('id' => $id)); 
    $this->db->delete($reviews, array('variant_id' => $id)); 

    // If variant is new, delete its empty record from the live data
    // (it's considered empty if 'variation' and 'hgvs_nucleotide_change' are NULL)
    $this->db->delete($vd_live, array('id' => $id, 'variation' => NULL, 'hgvs_nucleotide_change' => NULL));

    // Log it!
    $username = $this->ion_auth->user()->row()->username;
    $gene = empty($variation['gene']) ? 'MISSING_GENE' : $variation['gene'];
    $protein = empty($variation['hgvs_protein_change']) ? 'MISSING_PROTEIN_CHANGE' : $variation['hgvs_protein_change'];
    $variation = empty($variation['variation']) ? 'MISSING_VARIATION' : $variation['variation'];
    activity_log("User '$username' removed all changes for variant $gene|$protein|$variation", 'delete');
  }

  /**
   * Remove From Queue If Unchanged
   *
   * If no changes for this variant exist, then it will
   * be removed from the queue.
   *
   * @author Sean Ephraim
   * @access public
   * @param  int     Variant unique ID
   */
  public function remove_from_queue_if_unchanged($id)
  {
    // Variation database tables
    $vd_live  = $this->tables['vd_live'];
    $vd_queue = $this->tables['vd_queue'];
    $reviews = $this->tables['reviews'];

    $result = $this->get_unreleased_changes($id);

    if (empty($result[$id]['changes'])) {
      $this->db->delete($vd_queue, array('id' => $id)); 
      // If variant is new, delete its empty record from the live data
      // (it's considered empty if 'variation' and 'hgvs_nucleotide_change' are NULL)
      $this->db->delete($vd_live, array('id' => $id, 'variation' => NULL, 'hgvs_nucleotide_change' => NULL));
    }

  }

  /**
   * Get Variants By Gene
   *
   * Get all variants for a gene.
   *
   * @author Sean Ephraim
   * @access public
   * @param string Gene name
   * @return object Gene variations
   */
  public function get_variants_by_gene($gene)
  {
    $query = $this->db
                  ->where('gene', $gene)
                  ->order_by('variation', 'asc')
                  ->get($this->tables['vd_live']);
    return $query->result();
  }

  /**
   * Get Variants By Gene Letter
   *
   * Get all variants within a gene of the specified letter.
   *
   * @author Sean Ephraim
   * @access public
   * @param  char    Gene name's first letter
   * @return object  Gene variations
   */
  public function get_variants_by_gene_letter($letter)
  {
    $query = $this->db
                  ->where('gene', $gene)
                  ->order_by('variation', 'asc')
                  ->get($this->tables['vd_live']);
    return $query->result();
  }

  /**
   * Get Variant By ID
   *
   * Get all data for a single variant. The data in the queue
   * takes precedence over the current data, therefore
   * if the variant exists in the queue, it will be returned.
   * If you don't want to query the queue table, then specify
   * the name of the table you want to query as the second
   * parameter.
   *
   * @author Sean Ephraim
   * @access public
   * @param  int      Variant unique ID
   * @param  string   DB table to query
   * @return mixed    Variant data object or NULL
   */
  public function get_variant_by_id($id, $table = NULL)
  {
    if ($table === NULL) {
      $table = $this->tables['vd_queue'];
    }

    $query = $this->db
                  ->where('id', $id)
                  ->limit(1)
                  ->get($table);

    // This variant is not in the queue
    if ($query->num_rows() === 0 && $table !== $this->tables['vd_live']) {
      // Query the live DB instead
      return $this->get_variant_by_id($id, $this->tables['vd_live']);
    }

    // Still no result? This ID doesn't exist!
    if ($query->num_rows() === 0) {
      return NULL;
    }

    return $query->row();
  }

  /**
   * Get Variants By Position
   *
   * Get all data for a variants at a position. The data in the queue
   * takes precedence over the current data, therefore
   * if the variant exists in the queue, it will be returned.
   * If you don't want to query the queue table, then specify
   * the name of the table you want to query as the second
   * parameter. If the third parameter is TRUE, then fuzzy search
   * will be used, meaning that "chr13:20" will actually search
   * for "chr13:20*" and a variant such as "chr13:20796839" will be
   * included in the return results.
   *
   * @author  Sean Ephraim
   * @access  public
   * @param   string   Genomic position w/o nucleotide change (i.e. chr13:20796839)
   * @param   string   DB table to query
   * @param   boolean  Use fuzzy search
   * @return  mixed    Variant data array or NULL
   */
  public function get_variants_by_position($position, $table = NULL, $fuzzy_search = FALSE)
  {
    if ($table === NULL) {
      $table = $this->tables['vd_queue'];
    }

    $this->db->like('variation', $position.":", 'after'); // for exact search
    if ($fuzzy_search) {
      $this->db->or_like('variation', $position, 'after'); // for fuzzy search
    }
    $query = $this->db->get($table);

    // This variant is not in the queue
    if ($query->num_rows() === 0 && $table !== $this->tables['vd_live']) {
      // Query the live DB instead
      return $this->get_variants_by_position($position, $this->tables['vd_live']);
    }

    // Still no result? This ID doesn't exist!
    if ($query->num_rows() === 0) {
      return NULL;
    }

    return $query->result();
  }

  /**
   * Get Variant Review
   *
   * Get all of the review information for a variant.
   *
   * @author Sean Ephraim
   * @access public
   * @param  int    Variant unique ID
   * @return object Variant data object or NULL
   */
  public function get_variant_review_info($variant_id)
  {
    $table = $this->tables['reviews'];
    $query = $this->db
                  ->where('variant_id', $variant_id)
                  ->limit(1)
                  ->get($table);

    return $query->row();
    // This variant is not in the queue
    if ($query->num_rows() > 0) {
      return $query->row();
    }
    else {
      return NULL;
    }

  }

  /**
   * Get Variant Reviews.
   *
   * Get the review information for all variants.
   *
   * @author Sean Ephraim
   * @access public
   * @return object  Variant data object or NULL
   */
  public function get_variant_reviews()
  {
    $table = $this->tables['reviews'];
    $query = $this->db
                  ->get($table);
    return $query->result();
  }

  /**
   * Variant Exists In Table
   * 
   * Check if a variant exists within a certain table.
   *
   * @author Sean Ephraim
   * @access public
   * @param  int Variant unique ID
   * @param  string Table name
   * @return boolean
   */
  public function variant_exists_in_table($id, $table)
  {
    $query = $this->db
                  ->where('id', $id)
                  ->limit(1)
                  ->get($table);
    if ($query->num_rows() > 0) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Get Variant Fields
   *
   * Get all variant fields specified in the database.
   *
   * @author Sean Ephraim
   * @access public
   * @param string Table name
   * @return array Fieldnames
   */
  public function get_variant_fields($table = NULL)
  {
    if ($table === NULL) {
      $table = $this->tables['vd_live'];
    }
    return $this->db->list_fields($table);
  }

  /**
   * Get All Current Values
   *
   * Get a list of all values currently stored in the
   * database for a specific field.
   *
   * @author Sean Ephraim
   * @access public
   * @param  string Field name
   * @param  string (optional) Table name
   * @return array Current values in the DB
   */
  public function get_all_current_values($field, $table = NULL) {
    if ($table === NULL) {
      $table = $this->tables['vd_live'];
    }
    $query = $this->db->distinct()
                      ->select($field)
                      ->get($table);
    $values = array();
    // Extract just the field values from the query
    foreach ($query->result_array() as $key => $value) {
      $values[] = $value[$field];
    }
    return $values;
  }

  /**
   * Copy Variant Into Queue
   *
   * Copies a variant from the live site into the queue.
   *
   * @author Sean Ephraim
   * @access public
   * @param array Variant ID number
   */
  public function copy_variant_into_queue($id)
  {
    $variant = $this->get_variant_by_id($id); 
    $this->db->insert($this->tables['vd_queue'], $variant);
  }

  /**
   * Update Variant In Queue
   *
   * For each $data key, check if it exists as a field in the
   * queue. If it exists, update the value for the
   * given ID. Otherwise, create that variant in the queue.
   *
   * @author Sean Ephraim
   * @access public
   * @param int Variant ID number
   * @param array Assoc. array of variant fields
   * @return boolean
   */
  public function update_variant_in_queue($id, $data)
  {
    // Sanitize the data to be inserted
    // Remove fields that are not in this table
    $table_fields = $this->db->list_fields($this->tables['vd_queue']);
    foreach ($data as $key => $value) {
      if (in_array($key, $table_fields)) {
        $clean_data[$key] = trim($value);

        // Map all empty strings to NULL (or data may not save correctly in the database)
        if ($clean_data[$key] == '') {
          $clean_data[$key] = NULL;
        }
      }
    }

    $live_variant = (array) $this->variations_model->get_variant_by_id($id, $this->tables['vd_live']); 

    $query = $this->db->get_where($this->tables['vd_queue'], array('id' => $id), 1);
    if ($query->num_rows() > 0) {
      // Variant exists in queue, update it!
      $this->db
           ->where('id', $id)
           ->update($this->tables['vd_queue'], $clean_data);
    }
    else {
      // Variant does NOT exist in queue, copy it from the live table
      // ... But only if edits have actually been made
      $changes = array_diff_assoc($clean_data, $live_variant);
      if (empty($changes)) {
        return FALSE;
      }
      $this->copy_variant_into_queue($id);
      $this->update_variant_in_queue($id, $data);
    }

    // Log it!
    $username = $this->ion_auth->user()->row()->username;
    $variation = $this->db->get_where($this->tables['vd_queue'], array('id' => $id))->row_array();
    $gene = empty($variation['gene']) ? 'MISSING_GENE' : $variation['gene'];
    $protein = empty($variation['hgvs_protein_change']) ? 'MISSING_PROTEIN_CHANGE' : $variation['hgvs_protein_change'];
    $variation = empty($variation['variation']) ? 'MISSING_VARIATION' : $variation['variation'];
    activity_log("User '$username' edited variant $gene|$protein|$variation", 'edit');

    return TRUE;
  }

  /**
   * Push Data Live
   *
   * Pushes data to live production. 
   *
   * Note that by default, the table with the highest version number
   * will automatically be the production data. Therefore, for example,
   * if you have variation data stored in tables 'dvd_1', 'dvd_2', and
   * 'dvd_3', then the 'dvd_3' data will be displayed on the public site.
   * This function will:
   *   - Copy the current production data (i.e. 'dvd_3') to a new table (i.e.
   *     'dvd_4'), then update the new table (i.e. 'dvd_4') to reflect the
   *     new changes.
   *   - Update the 'versions' table
   *   - Create a new 'variant_count_' table
   *   - Backup the '_queue' table and 'reviews' table
   *   - Clear the '_queue' table and 'reviews' table
   *
   * By default, only changes that have been confirmed for release are acutally
   * released. As an optional first parameter, you can turn this setting off
   * and release all changes regardless of confirmation status. To do this,
   * pass in FALSE for the first parameter.
   *
   * @author  Sean Ephraim
   * @access  public
   * @param   boolean   (optional) Only release confirmed variants?
   * @return  boolean   TRUE on success, else FALSE
   */
  public function push_data_live($confirmed_only = TRUE)
  {
    $new_records = $this->variations_model->get_all_variants($this->tables['vd_queue']);

    // Remove unconfirmed variants
    if ($confirmed_only === TRUE) {
      foreach ($new_records as $key => $new_record) {
        $query = $this->db->get_where($this->tables['reviews'], array(
                                                                  'variant_id' => $new_record->id,
                                                                  'confirmed_for_release' => 0,
                                                                ));
        if ($query->num_rows > 0) {
          unset($new_records[$key]);
        }
      }
    }

    if (empty($new_records) && $this->version != 0) {
      // ERROR: no new records to update
      // NOTE: an empty update is only allowed for Version 0
      return FALSE;
    }

    // Create new variation table
    $new_live_table = $this->variations_model->get_new_version_name($this->tables['vd_live']);
    $copy_success = $this->variations_model->copy_table($this->tables['vd_live'], $new_live_table);
    if ( ! $copy_success) {
      // ERROR: problem copying live table
      return FALSE;
    }

    // Create new queue table
    $new_queue_table = $this->variations_model->get_new_version_name($this->tables['vd_queue']);
    $copy_success = $this->variations_model->copy_table($this->tables['vd_queue'], $new_queue_table);
    if ( ! $copy_success) {
      // ERROR: problem copying queue table
      return FALSE;
    }

    // Create new reviews table
    $new_reviews_table = $this->variations_model->get_new_version_name($this->tables['reviews']);
    $copy_success = $this->variations_model->copy_table($this->tables['reviews'], $new_reviews_table);
    if ( ! $copy_success) {
      // ERROR: problem copying reviews table
      return FALSE;
    }

    // Create new variant count table
    $new_count_table = $this->variations_model->get_new_version_name($this->tables['variant_count']);
    $copy_success = $this->variations_model->copy_table($this->tables['variant_count'], $new_count_table, FALSE);

    if ( ! $copy_success) {
      // ERROR: problem copying table
      return FALSE;
    }

    // Update the *new* live table with the new changes
    foreach ($new_records as $record) {
      $this->db->update($new_live_table, $record, 'id = ' . $record->id);
    }

    // Remove variants from the *new* live table that were scheduled for deletion
    $delete_records = $this->db->get_where($this->tables['reviews'], array('scheduled_for_deletion' => 1))->result();
    foreach ($delete_records as $delete_record) {
      $this->db->delete($new_live_table, array('id' => $delete_record->variant_id));
    }

    // Get genes and associated variant counts, insert into new variant count table
    $this->load->model('genes_model');
    $genes = $this->genes_model->get_genes();
    foreach ($genes as $gene) {
      $variant_count = $this->db
                            ->get_where($new_live_table, array('gene' => $gene))
                            ->num_rows();
      $data = array(
        'id'    => NULL,
        'gene'  => $gene,
        'count' => $variant_count,
      );
      $this->db->insert($new_count_table, $data);
    }

    // Delete empty records from the new and previous live tables
    // --> if a record doesn't have a 'variation' or a 'hgvs_nucleotide_change' then it shouldn't be here
    // Remove variants from the *new* live table that were scheduled for deletion
    $this->db->delete($this->tables['vd_live'], array('variation' => NULL, 'hgvs_nucleotide_change' => NULL));
    $this->db->delete($new_live_table, array('variation' => NULL, 'hgvs_nucleotide_change' => NULL));

    // Delete all review information and queue data for ONLY the records
    // that were released
    $delete_records = $new_records;
    foreach ($delete_records as $delete_record) {
      $this->db->delete($new_queue_table, array('id' => $delete_record->id));
      $this->db->delete($new_reviews_table, array('variant_id' => $delete_record->id));
    }

    // Get latest version number
    $query = $this->db
                  ->select_max('version')
                  ->limit(1)
                  ->get($this->tables['versions']);
    $latest_version = $query->row()->version;

    // Update versions table
    $datetime = date('Y-m-d H:i:s');
    $data = array(
      'id'       => NULL,
      'version'  => $latest_version + 1,
      'created'  => $datetime,
      'updated'  => $datetime,
      'variants' => $this->db->count_all($new_live_table),
      'genes'    => count($genes),
    );
    $this->db->insert($this->tables['versions'], $data);

    // Delete any intial import data/tables (they aren't needed anymore)
    // NOTE: initial import data is equal to Version 0
    $initial_live = $this->variations_model->get_new_version_name($this->tables['vd_live'], -1); // i.e. "variations_0"
    if ($this->db->table_exists($initial_live)) {
      $this->load->dbforge();
      // Drop initial live table
      $this->dbforge->drop_table($initial_live);
      // Drop initial queue table
      $initial_queue = $this->variations_model->get_new_version_name($this->tables['vd_queue'], -1); // i.e. "variations_queue_0"
      $this->dbforge->drop_table($initial_queue);
      // Drop variant count table
      $initial_count = $this->variations_model->get_new_version_name($this->tables['variant_count'], -1); // i.e. "variant_count_0"
      $this->dbforge->drop_table($initial_count);
      // Drop reviews table
      $initial_reviews = $this->variations_model->get_new_version_name($this->tables['reviews'], -1); // i.e. "reviews_0"
      $this->dbforge->drop_table($initial_reviews);
      // Delete version 0 from the versions table
      $this->db->delete($this->tables['versions'], array('version' => 0)); 
    }

    // Log it!
    $username = $this->ion_auth->user()->row()->username;
    $version = $latest_version + 1;
    activity_log("User '$username' released a new version of the database -- Version $version", 'release');
    
    return TRUE;
  }

  /**
   * Update Variant Review Info
   *
   * Updates all of the review information for the variant.
   * Review info is for staff use only and is never displayed
   * to the public.
   *
   * @author  Sean Ephraim
   * @access  public
   * @param   int    Variant ID number
   * @param   array  Assoc. array of variant fields/values
   * @return  void
   */
  public function update_variant_review_info($variant_id, $data = array())
  {
    // Sanitize the data to be inserted
    // Remove fields that are not in this table (or are auto-incremeted)
    $table_fields = $this->db->list_fields($this->tables['reviews']);
    foreach ($data as $key => $value) {
      if (in_array($key, $table_fields) && $key !== 'id') {
        $clean_data[$key] = $value;
      }
    }
    // 'variant_id' must be specially mapped
    $clean_data['variant_id'] = $variant_id;

    // Set update time
    $datetime = date('Y-m-d H:i:s');
    $clean_data['updated'] = $datetime;

    $query = $this->db->get_where($this->tables['reviews'], array('variant_id' => $variant_id), 1);

    if ($query->num_rows() > 0) {
      // Variant already has a review, update it!
      $this->db
           ->where('variant_id', $variant_id)
           ->update($this->tables['reviews'], $clean_data);
    }
    else {
      // Variant does NOT already have a review, create one!
      // Update versions table
      $clean_data['created'] = $datetime;
      $this->db
           ->insert($this->tables['reviews'], $clean_data);
    }
  }

  /**
   * Get All Variants
   *
   * Gets all the variant records in a table.
   *
   * @author Sean Ephraim
   * @access public
   * @return object  All variant data in a specific table
   */
  public function get_all_variants($table = NULL)
  {
    if ($table === NULL) {
      $table = $this->tables['vd_live'];
    }

    return $this->db->get($table)->result();
  }

  /**
   * Get Unreleased Changes
   *
   * For each variant in the queue, get all differences between the
   * unreleased queued data and the live data. Example output:
   *
   *     $variants[19]['id'] = 19
   *                  ['name'] = 'NM_012130:c.687G>A'
   *                  ['changes']['pubmed_id']['live_value']  = 123
   *                                          ['queue_value'] = 12345
   *                             ['lrt_score']['live_value']  = 0.992
   *                                          ['queue_value'] = 0.993
   *                  ['is_new'] = FALSE
   *              [46]['id']     = 46
   *                  ['name']   = 'NM_012130:c.690C>T'
   *                  ['changes']['pubmed_id']['live_value']  = NULL
   *                                          ['queue_value'] = 9876
   *                  ['is_new'] = FALSE
   *
   * Specify a variant ID in order to only get changes for that variant.
   * If no ID is specified, then unreleased changes for all variants are returned.
   *
   * @author Sean Ephraim
   * @access public
   * @param  int Variant unique ID
   * @return mixed Array of unreleased changes; NULL if no changes exist
   */
  public function get_unreleased_changes($variant_id = NULL)
  {
    if ($variant_id !== NULL) {
      // Get single variant from queue
      $query = $this->variations_model->get_variant_by_id($variant_id);
      $queue_variants = array(); // This is necessary in order for the foreach loop to work
      if ($query) {
        $queue_variants[] = $query; 
      }
    }
    else {
      // Get all variants from queue
      $queue_variants = $this->variations_model->get_all_variants($this->tables['vd_queue']);
    }

    // Compare queue values to live values
    $variants = array();
    if (is_array($queue_variants)) {
      foreach ($queue_variants as $queue_variant) {
        $queue_variant = (array) $queue_variant;
        $id = $queue_variant['id'];
        $live_variant = (array) $this->variations_model->get_variant_by_id($id, $this->tables['vd_live']); 
  
        $review = $this->variations_model->get_variant_review_info($id);
  
        // Create an array of variant changes
        if ($queue_variant !== $live_variant || !empty($review)) {
          $variants[$id]['id'] = $id;
          $variants[$id]['changes'] = array();
          $variants[$id]['name'] = $queue_variant['gene'] . ' <i class="icon-stop"></i> ' . $queue_variant['hgvs_protein_change'] . ' <i class="icon-stop"></i> ' . $queue_variant['variation'];
  
          // Check if variation is already in the live and/or queue database 
          // --> assign 'new variant' label accordingly
          $query_live = $this->db->get_where($this->tables['vd_live'], array('variation' => $queue_variant['variation'], 'hgvs_protein_change' => $queue_variant['hgvs_protein_change']), 1);
          if ($query_live->num_rows() > 0) {
            $variants[$id]['is_new'] = FALSE;
          }
          else {
            $variants[$id]['is_new'] = TRUE;
          }
  
          foreach ($queue_variant as $field => $value) {
            // Identify changed fields
            if ( ! array_key_exists($field, $live_variant) || $queue_variant[$field] !== $live_variant[$field]) {
              $variants[$id]['changes'][$field]['queue_value'] = $queue_variant[$field];
    
              if ($variants[$id]['is_new'] || ! array_key_exists($field, $live_variant)) {
                // If this is a new variant, then the 'live_value' should be NA
                $variants[$id]['changes'][$field]['live_value'] = '<i>None</i>';
  
                // If the queue value and live value are empty, then disregard this field altogether
                if ($variants[$id]['changes'][$field]['queue_value'] === NULL || $variants[$id]['changes'][$field]['queue_value'] === '') {
                  unset($variants[$id]['changes'][$field]);
                }
              }
              else {
                // If this variant already exists in the DB, then use its current 'live_value'
                $variants[$id]['changes'][$field]['live_value'] = $live_variant[$field];
  
                if ($variants[$id]['changes'][$field]['queue_value'] === NULL || $variants[$id]['changes'][$field]['queue_value'] === '') {
                  // If the queue value and live value are empty, then disregard this field altogether
                  if ($variants[$id]['changes'][$field]['live_value'] === NULL || $variants[$id]['changes'][$field]['live_value'] === '') {
                    unset($variants[$id]['changes'][$field]);
                  }
                  else {
                    // If queue value is empty, then display 'None'
                    $variants[$id]['changes'][$field]['queue_value'] = '<i>None</i>';
                  }
                }
                // If live value is empty, then display 'None'
                if (isset($variants[$id]['changes'][$field])) {
                  if ($variants[$id]['changes'][$field]['live_value'] === NULL || $variants[$id]['changes'][$field]['live_value'] === '') {
                    $variants[$id]['changes'][$field]['live_value'] = '<i>None</i>';
                  }
                }
              }
            }
          }
        }
      } // end foreach
    } // end if

    // Add variants that don't have any changes but (a.) are scheduled for deletion,
    // or (b.) have comments for the informatics team
    if ($variant_id !== NULL) {
      $review = $this->variations_model->get_variant_review_info($variant_id);
      if (empty($review)) {
        $reviews = array();
      }
      else {
        $reviews = array($review);
      }
    }
    else {
      $reviews = $this->variations_model->get_variant_reviews();
    }
    if (is_array($reviews)) {
      foreach ($reviews as $review) {
        $id = $review->variant_id;
        $comments = $review->informatics_comments;
        $delete = $review->scheduled_for_deletion;
        if ( ! array_key_exists($id, $variants)) {
          if (!empty($comments) || $delete == 1) {
            $live_variant = (array) $this->variations_model->get_variant_by_id($id, $this->tables['vd_live']); 
            if ( ! empty($live_variant)) {
              $variants[$id]['id'] = $id;
              $variants[$id]['changes'] = array();
              $variants[$id]['name'] = $live_variant['gene'] . ' <i class="icon-stop"></i> ' . $live_variant['hgvs_protein_change'] . ' <i class="icon-stop"></i> ' . $live_variant['variation'];
              $variants[$id]['is_new'] = FALSE;
            }
          }
        }
      }
    }

    if (count($variants) == 0) {
      return NULL;
    }

    return $variants;
  }

  /**
   * Validate Variant ID
   *
   * @author Nikhil Anand
   * @param string $id 
   * @return void
   */
  public function _validate_variant_id($id) {
    if (!(preg_match('/[0-9]+/', $id))) {
      print "Invalid request";
      exit(9);
    } 
  }
  
  /**
   * Load Variant
   *
   * @author Nikhil Anand
   * @param  int   Variant unique ID
   * @return void
   */
  public function load_variant($id) {
      _validate_variant_id($id);
      return _api_search("id", array($id), $this->version);
  }
  
  /**
   * Get Letter Table
   *
   * Generate the table of gene letters users can click on. 
   * Any letter that doesn't have any genes associated (yet) will not have a hyperlink.
   * The links themselves are handled on the frontend (via AJAX).
   *
   * @author   Nikhil Anand, Sean Ephraim
   * @return   string   HTML for gene letter table
   */
  public function get_letter_table($selected_letter = NULL) {
  
  	// Validate input param
  	if ($selected_letter != NULL) {
  		$selected_letter = $this->validate_gene_letter($selected_letter);
  	}
  
    // Ascertain which letters of the alphabet have genes associated with them
    $this->db->select('DISTINCT(LOWER(LEFT(gene,1))) AS val 
              FROM `' . $this->tables['vd_live'] . 
              '` ORDER BY gene', FALSE);
    $query = $this->db->get();
    $results = $query->result();

    foreach ($query->result() as $row) {
      $gene_letter = $row->val;
    
    	// Start building link HTML
    	$letter_uri = '<a ';
    
    	// Determine if a given letter is to be highlighted
    	if (strtoupper($gene_letter) == $selected_letter) {
    		$letter_uri .= ' class="active-letter" ';
    	}
    	
    	// Finish rest of link
    	$letter_uri .= ' href="'.base_url().'letter/'.$gene_letter.'">'.$gene_letter.'</a>';
    	
    	// Assign link to letter
    	$alphabet[$gene_letter] = $letter_uri;
  	
    } /* End while */
    
    // Make sure we show the 'inactive' letters as well (logic by Kyle Taylor!)
    for($i = 0; $i <= 26; $i++) {
        if (!isset($alphabet[chr($i+96)])) {
            $alphabet[chr($i+96)] = chr($i+96);
        }
    }
      
      // Draw the table
  	$output =<<<EOF
  	<table border="0" cellspacing="0" cellpadding="0">
  	  <tr>
  	    <td>{$alphabet["a"]}</td>
  	    <td>{$alphabet["b"]}</td>
  	    <td>{$alphabet["c"]}</td>
  	    <td>{$alphabet["d"]}</td>
  	    <td>{$alphabet["e"]}</td>
  	    <td>{$alphabet["f"]}</td>
  	    <td class="side-right">{$alphabet["g"]}</td>
  	  </tr>
  	  <tr>
  	    <td>{$alphabet["h"]}</td>
  	    <td>{$alphabet["i"]}</td>
  	    <td>{$alphabet["j"]}</td>
  	    <td>{$alphabet["k"]}</td>
  	    <td>{$alphabet["l"]}</td>
  	    <td>{$alphabet["m"]}</td>
  	    <td class="side-right">{$alphabet["n"]}</td>
  	  </tr>
  	  <tr>
  	    <td>{$alphabet["o"]}</td>
  	    <td>{$alphabet["p"]}</td>
  	    <td>{$alphabet["q"]}</td>
  	    <td>{$alphabet["r"]}</td>
  	    <td>{$alphabet["s"]}</td>
  	    <td>{$alphabet["t"]}</td>
  	    <td class="side-right">{$alphabet["u"]}</td>
  	  </tr>
  	  <tr>
  	    <td class="side-bottom">{$alphabet["v"]}</td>
  	    <td class="side-bottom">{$alphabet["w"]}</td>
  	    <td class="side-bottom">{$alphabet["x"]}</td>
  	    <td class="side-bottom">{$alphabet["y"]}</td>
  	    <td class="side-bottom">{$alphabet["z"]}</td>
  	    <td class="side-bottom"></td>
  	    <td class="side-right side-bottom"></td>
  	  </tr>
  	</table>
EOF;
  	return $output;
  }

  /**
   * Create a formatted table of variants for all genes starting with a given letter.
   *
   * @author Nikhil Anand (modified by Zachary Ladlie)
   * @access public
   * @param string $result 
   * 			An array of database results for a gene letter
   * @return void
   */
  public function format_variants_table(&$variant_info) {
    // Show the table opened if we have only one result
    $display   = "display:none;";
    $collapsed = "";
    if (sizeof($variant_info) == 1) {
      $display = "";
      $collapsed = "collapsed";
    }
    
    $table = '';

    foreach ($variant_info as $gene => $mutations) {

      // Build CSV, Tab-delimited, JSON and XML links
      $uri_str = site_url("api?type=gene&amp;terms=$gene&amp;format=");
      $uri_csv = $uri_str  . 'csv';
      $uri_tab = $uri_str  . 'tab';
      $uri_jsn = $uri_str  . 'json';
      $uri_xml = $uri_str  . 'xml';
        
      // Fieldset containing gene name and table header
      $table .=<<<EOF
      \n
      <fieldset>
          <legend class="genename $collapsed" id="$gene"><strong>$gene</strong> <span><a href="$uri_csv">CSV</a> <a href="$uri_tab">Tab</a> <a href="$uri_jsn">JSON</a> <a href="$uri_xml">XML</a></span></legend>
          <div id="table-$gene" style="$display">
              <table class="gene-table">
              <thead>
                  <tr>
                      <th class="header-link">&nbsp;</th>
                      <th class="top-border header-protein">HGVS protein change</th>
                      <th class="top-border header-nucleotide">HGVS nucleotide change</th>
                      <th class="top-border header-locale">Variant Locale</th>
                      <th class="top-border header-position">Genomic position (Hg19)</th>
                      <th class="top-border header-variant">Variant Type</th>
                      <th class="top-border header-disease">Phenotype</th>
                  </tr>
              </thead>
              <tbody>\n
EOF;

        // Rows of each table
        $zebra = '';
        foreach ($mutations as $mutation) {
            
            // To zebra stripe rows
            $zebra = ( 'odd' != $zebra ) ? 'odd' : 'even';

            $id                      = $mutation["id"];
            if (empty($mutation["hgvs_protein_change"])) {
              $hgvs_protein_change   = "&nbsp;"; // Avoid HTML errors
            }
            else {
              $hgvs_protein_change   = wordwrap($mutation["hgvs_protein_change"], 30, '<br />', 1);
            }
            $hgvs_nucleotide_change  = wordwrap($mutation["hgvs_nucleotide_change"], 25, '<br />', 1);
            $variantlocale           = $mutation["variantlocale"];
            $variation               = wordwrap($mutation["variation"], 25, '<br />', 1);
            $pathogenicity           = $mutation["pathogenicity"];
            $disease                 = $mutation["disease"];
            $variant_link            = site_url('variant/' . $id . '?full');    

            // Change the text of the variant type
            if(strcmp($pathogenicity, "vus") == 0) {
              $pathogenicity = '<span class="unknown_disease">Unknown significance</span>';
            } else if(strcmp($pathogenicity, "probable-pathogenic") == 0) {
              $pathogenicity = '<span class="probably_pathogenic">Probably Pathogenic</span>';
            } else if(strcmp($pathogenicity, "Pathogenic") == 0) {
              $pathogenicity = '<span class="pathogenic">Pathogenic</span>';
            }

            // Start drawing rows
            $table .=<<<EOF
                <tr class="$zebra showinfo" id="mutation-$id">
                    <td class="external-link"><a href="$variant_link"><span>More Information &raquo;</span></a></td>
                    <td class="showinfo-popup"><a><code>$hgvs_protein_change</code></a></td>
                    <td class="showinfo-popup"><code>$hgvs_nucleotide_change</code></td>
                    <td class="showinfo-popup">$variantlocale</td>
                    <td class="showinfo-popup"><code>$variation</code></td>
                    <td class="showinfo-popup">$pathogenicity</td>
                    <td class="showinfo-popup">$disease</td>
                </tr>
EOF;
        }

        // Finish table
        $table .= <<<EOF
                </tbody>
                </table>
            </div>
        </fieldset>
EOF;
    }
    
    return $table;
  }

  /**
   * Load all information in the variants table for all genes starting with a given letter.
   *
   * @param  string $letter 
   * @return array  Variation data to be displayed genes page
   * @author Nikhil Anand (modified by Zachary Ladlie)
   */
  public function load_gene($letter) {
      
        $counter = 0;
        $result = '';
      
        // Sanitize in case the invoker doesn't
        $letter = $this->validate_gene_letter($letter);
        $tables = $this->config->item('tables');
  
        // Construct and run query
        if ($letter == '') {
            $query = "SELECT * FROM `" . $this->tables['vd_live'] . "` ORDER BY gene ASC;";
        } else {
            $query = sprintf('SELECT * FROM `%s` WHERE gene LIKE \'%s%%\' ORDER BY gene ASC', $this->tables['vd_live'], $letter);
        }
        $query_result = mysql_query($query);
      
        // Build array of results. Group all by gene. 
        $current_gene = '';
        while ($mutation = mysql_fetch_assoc($query_result)) {
            
            // Make sure the multi array is indexed 0, 1, 2 etc for EACH gene
            if ($current_gene != $mutation["gene"]) {
                $counter = 0;
            }
  
            $result[$mutation["gene"]][$counter]["id"] = $mutation["id"];
            $result[$mutation["gene"]][$counter]["hgvs_protein_change"] = $mutation["hgvs_protein_change"];
            $result[$mutation["gene"]][$counter]["hgvs_nucleotide_change"] = $mutation["hgvs_nucleotide_change"];
            $result[$mutation["gene"]][$counter]["variantlocale"] = $mutation["variantlocale"];
            $result[$mutation["gene"]][$counter]["variation"] = $mutation["variation"];
            $result[$mutation["gene"]][$counter]["pathogenicity"] = $mutation["pathogenicity"];
            $result[$mutation["gene"]][$counter]["disease"] = $mutation["disease"];
            
            $current_gene = $mutation["gene"];
            $counter++;
        }
        return $result;
  }

  /**
   * Validate Gene Letter
   *
   * @author Nikhil Anand (modified by Zachary Ladlie)
   * @param  string Letter of gene
   * @return string Letter of gene (sanitized)
   */
  public function validate_gene_letter($letter) {
    
    $letter = strtoupper(substr(trim($letter),0,1));
    
    if (!(preg_match('/[A-Z]{1}/', $letter))) {
      print "Invalid request for letter ".$letter;
      exit(8);
    }
    return $letter;
  }

  /**
   * Get Variant Display Variables
   *
   * @author Nikhil Anand, Sean Ephraim
   * @param  string Letter of gene
   * @return array  Data variables to load into the view
   */
  public function get_variant_display_variables($id) {
    // Load variant data
    $id = trim($id);
    $variant = $this->get_variant_by_id($id);
    $freqs = $this->config->item('frequencies'); // frequencies to display

    // Make variables out of array keys. Variable variables are AWESOME!
    foreach ($variant as $key => $value) {
      $data[$key] = $value;
    }

    // These can get long
    $data['hgvs_protein_change']    = wordwrap($data['hgvs_protein_change'], 30, '<br />', 1);
    $data['hgvs_nucleotide_change'] = wordwrap($data['hgvs_nucleotide_change'], 25, '<br />', 1);
    $data['variation']              = wordwrap($data['variation'], 25, '<br />', 1);
    
    // Pubmed, dbSNP IDs, comments
    if (trim($data['pubmed_id']) == NULL) {
      $data['link_pubmed'] = "<span>(no data)</span>";
    } 
    else {
      $pubmed_url = "http://www.ncbi.nlm.nih.gov/pubmed/";
      $data['link_pubmed'] = '<a href="'.$pubmed_url.$data['pubmed_id'].'" title="Link to Pubmed">'.$data['pubmed_id'].'</a>';
    }
    if (trim($data['dbsnp']) == NULL) {
      $data['link_dbsnp'] = "<span>(no data)</span>";
    } 
    else {
      $amp = '&amp;'; // must use this in hrefs instead of '&' to avoid warnings
      $dbsnp_url = "http://www.ncbi.nlm.nih.gov/projects/SNP/snp_ref.cgi?searchType=adhoc_search&amp;type=rs&amp;rs=";
      $data['link_dbsnp'] = '<a href="'.$dbsnp_url.$data['dbsnp'].'" title="Link to dbSNP page">'.$data['dbsnp'].'</a>';
    }
    if (trim($data['comments']) == NULL) {
      $data['comments'] = "<span>(no data)</span>";
    } 
    
    // PhyloP
    if (is_numeric($data['phylop_score'])) {
      // Conservation threshold
      if ($data['phylop_score'] > 1) {
        $data['class_phylop'] = "red";
        $data['desc_phylop'] = "Conserved";
      } 
      else {
        $data['class_phylop'] = "green";
        $data['desc_phylop'] = "Non-conserved";    
      }
    }
    else {
      $data['class_phylop'] = "gray";
      $data['desc_phylop'] = "Unknown";
    }

    // GERP++
    if (is_numeric($data['gerp_rs'])) {
      // Conservation threshold
      if ($data['gerp_rs'] > 0) {
        $data['class_gerp'] = "red";
        $data['desc_gerp'] = "Conserved";
      } 
      else {
        $data['class_gerp'] = "green";
        $data['desc_gerp'] = "Non-conserved";    
      }
    }
    else {
      $data['class_gerp'] = "gray";
      $data['desc_gerp'] = "Unknown";
    }

    // SIFT
    if (is_numeric($data['sift_score'])) {
      // Damage threshold
      if ($data['sift_score'] < 0.05) {
        $data['class_sift'] = "red";
        $data['desc_sift'] = "Damaging";
      } 
      else {
        $data['class_sift'] = "green";
        $data['desc_sift'] = "Tolerated";    
      }
    }
    else {
      $data['class_sift'] = "gray";
      $data['desc_sift'] = "Unknown";
    }
    
    // PolyPhen2
    if (stristr($data['polyphen2_pred'], "D") !== FALSE) {
      $data['class_polyphen'] = "red";
      $data['desc_polyphen'] = "Probably Damaging";
    } elseif (stristr($data['polyphen2_pred'], "P") !== FALSE) {
      $data['class_polyphen'] = "orange";
      $data['desc_polyphen'] = "Possibly Damaging";
    } elseif (stristr($data['polyphen2_pred'], "B") !== FALSE) {
      $data['class_polyphen'] = "green";
      $data['desc_polyphen'] = "Benign";    
    } else {
      $data['class_polyphen'] = "gray";
      $data['desc_polyphen'] = "Unknown";
    }
    
    // LRT
    if (stristr($data['lrt_pred'], "D") !== FALSE) {
      $data['class_lrt'] = "red";
      $data['desc_lrt'] = "Deleterious";
    } elseif (stristr($data['lrt_pred'], "N") !== FALSE) {
      $data['class_lrt'] = "green";
      $data['desc_lrt'] = "Neutral";
    } else {
      $data['class_lrt'] = "gray";
      $data['desc_lrt'] = "Unknown";    
    }
    
    // MutationTaster
    if (stristr($data['mutationtaster_pred'], "D") !== FALSE) {
      $data['class_mutationtaster'] = "red";
      $data['desc_mutationtaster'] = "Disease Causing";
    } elseif (stristr($data['mutationtaster_pred'], "A") !== FALSE) {
      $data['class_mutationtaster'] = "red";
      $data['desc_mutationtaster'] = "Disease Causing (Automatic)";
    } elseif (stristr($data['mutationtaster_pred'], "N") !== FALSE) {
      $data['class_mutationtaster'] = "green";
      $data['desc_mutationtaster'] = "Polymorphism";
    } elseif (stristr($data['mutationtaster_pred'], "P") !== FALSE) {
      $data['class_mutationtaster'] = "green";
      $data['desc_mutationtaster'] = "Polymorphism (Automatic)";    
    } else {
      $data['class_mutationtaster'] = "gray";
      $data['desc_mutationtaster'] = "Unknown";    
    }

    // Variant Evidence Summary
    if ($this->config->item('variant_evidence_summary') === TRUE) {
      $data['disp_summary'] = 'block';
      $data['summary_insilico']  = (int) $data['summary_insilico'];
      $data['summary_frequency'] = (int) $data['summary_frequency'];
      $data['summary_published'] = (int) $data['summary_published'];
    }
    else {
      $data['disp_summary'] = 'none';
      $data['summary_insilico'] = 0;
      $data['summary_frequency'] = 0;
      $data['summary_published'] = 0;
    }

    // Which frequency data to show, if any?
    $data['disp_freqs'] = (count($freqs) > 0) ? 'block' : 'none';
    $data['disp_evs'] = in_array('evs', $freqs) ? 'block' : 'none';
    $data['disp_1000g'] = in_array('1000genomes', $freqs) ? 'block' : 'none';
    $data['disp_otoscope'] = in_array('otoscope', $freqs) ? 'block' : 'none';
    
    // Frequency computations
    if (in_array('otoscope', $freqs)) {
      // Display OtoSCOPE
      ($data['otoscope_an'] != 0) ? $data['freq_otoscope'] = number_format(($data['otoscope_ac']/$data['otoscope_an'])*100, 2) : $data['freq_otoscope'] = 0.00;
      ($data['otoscope_an'] == 0) ? $data['label_otoscope'] = '(No data)'  :  $data['label_otoscope'] = $data['otoscope_ac'] . "/" . $data['otoscope_an'];
    }
    else {
      // Don't display OtoSCOPE
      $data['freq_otoscope']  = 0;
      $data['label_otoscope'] = '(No data)';
    }
    if (in_array('evs', $freqs)) {
      // Display EVS
      ($data['evs_ea_an'] != 0)   ? $data['freq_evs_ea']   = number_format(($data['evs_ea_ac']/$data['evs_ea_an'])*100, 2)     : $data['freq_evs_ea'] = 0;
      ($data['evs_aa_an'] != 0)   ? $data['freq_evs_aa']   = number_format(($data['evs_aa_ac']/$data['evs_aa_an'])*100, 2)     : $data['freq_evs_aa'] = 0;
      ($data['evs_ea_an']   == 0) ? $data['label_evs_ea']   = '(No data)'  :  $data['label_evs_ea']   = $data['evs_ea_ac']   . "/" . $data['evs_ea_an'];
      ($data['evs_aa_an']   == 0) ? $data['label_evs_aa']   = '(No data)'  :  $data['label_evs_aa']   = $data['evs_aa_ac']   . "/" . $data['evs_aa_an'];
    }
    else {
      // Don't display EVS
      $data['freq_evs_ea']  = 0;
      $data['freq_evs_aa']  = 0;
      $data['label_evs_ea'] = '(No data)';
      $data['label_evs_aa'] = '(No data)';
    }
    if (in_array('1000genomes', $freqs)) {
      // Display 1000 Genomes
      ($data['tg_acb_an'] != 0)   ? $data['freq_tg_acb']   = number_format(($data['tg_acb_ac']/$data['tg_acb_an'])*100, 2)     : $data['freq_tg_acb'] = 0;
      ($data['tg_asw_an'] != 0)   ? $data['freq_tg_asw']   = number_format(($data['tg_asw_ac']/$data['tg_asw_an'])*100, 2)     : $data['freq_tg_asw'] = 0;
      ($data['tg_cdx_an'] != 0)   ? $data['freq_tg_cdx']   = number_format(($data['tg_cdx_ac']/$data['tg_cdx_an'])*100, 2)     : $data['freq_tg_cdx'] = 0;
      ($data['tg_ceu_an'] != 0)   ? $data['freq_tg_ceu']   = number_format(($data['tg_ceu_ac']/$data['tg_ceu_an'])*100, 2)     : $data['freq_tg_ceu'] = 0;
      ($data['tg_chb_an'] != 0)   ? $data['freq_tg_chb']   = number_format(($data['tg_chb_ac']/$data['tg_chb_an'])*100, 2)     : $data['freq_tg_chb'] = 0;
      ($data['tg_chs_an'] != 0)   ? $data['freq_tg_chs']   = number_format(($data['tg_chs_ac']/$data['tg_chs_an'])*100, 2)     : $data['freq_tg_chs'] = 0;
      ($data['tg_clm_an'] != 0)   ? $data['freq_tg_clm']   = number_format(($data['tg_clm_ac']/$data['tg_clm_an'])*100, 2)     : $data['freq_tg_clm'] = 0;
      ($data['tg_fin_an'] != 0)   ? $data['freq_tg_fin']   = number_format(($data['tg_fin_ac']/$data['tg_fin_an'])*100, 2)     : $data['freq_tg_fin'] = 0;
      ($data['tg_gbr_an'] != 0)   ? $data['freq_tg_gbr']   = number_format(($data['tg_gbr_ac']/$data['tg_gbr_an'])*100, 2)     : $data['freq_tg_gbr'] = 0;
      ($data['tg_gih_an'] != 0)   ? $data['freq_tg_gih']   = number_format(($data['tg_gih_ac']/$data['tg_gih_an'])*100, 2)     : $data['freq_tg_gih'] = 0;
      ($data['tg_ibs_an'] != 0)   ? $data['freq_tg_ibs']   = number_format(($data['tg_ibs_ac']/$data['tg_ibs_an'])*100, 2)     : $data['freq_tg_ibs'] = 0;
      ($data['tg_jpt_an'] != 0)   ? $data['freq_tg_jpt']   = number_format(($data['tg_jpt_ac']/$data['tg_jpt_an'])*100, 2)     : $data['freq_tg_jpt'] = 0;
      ($data['tg_khv_an'] != 0)   ? $data['freq_tg_khv']   = number_format(($data['tg_khv_ac']/$data['tg_khv_an'])*100, 2)     : $data['freq_tg_khv'] = 0;
      ($data['tg_lwk_an'] != 0)   ? $data['freq_tg_lwk']   = number_format(($data['tg_lwk_ac']/$data['tg_lwk_an'])*100, 2)     : $data['freq_tg_lwk'] = 0;
      ($data['tg_mxl_an'] != 0)   ? $data['freq_tg_mxl']   = number_format(($data['tg_mxl_ac']/$data['tg_mxl_an'])*100, 2)     : $data['freq_tg_mxl'] = 0;
      ($data['tg_pel_an'] != 0)   ? $data['freq_tg_pel']   = number_format(($data['tg_pel_ac']/$data['tg_pel_an'])*100, 2)     : $data['freq_tg_pel'] = 0;
      ($data['tg_pur_an'] != 0)   ? $data['freq_tg_pur']   = number_format(($data['tg_pur_ac']/$data['tg_pur_an'])*100, 2)     : $data['freq_tg_pur'] = 0;
      ($data['tg_tsi_an'] != 0)   ? $data['freq_tg_tsi']   = number_format(($data['tg_tsi_ac']/$data['tg_tsi_an'])*100, 2)     : $data['freq_tg_tsi'] = 0;
      ($data['tg_yri_an'] != 0)   ? $data['freq_tg_yri']   = number_format(($data['tg_yri_ac']/$data['tg_yri_an'])*100, 2)     : $data['freq_tg_yri'] = 0;
      ($data['tg_acb_an']   == 0) ? $data['label_tg_acb']   = '(No data)'  :  $data['label_tg_acb']   = $data['tg_acb_ac']   . "/" . $data['tg_acb_an'];
      ($data['tg_asw_an']   == 0) ? $data['label_tg_asw']   = '(No data)'  :  $data['label_tg_asw']   = $data['tg_asw_ac']   . "/" . $data['tg_asw_an'];
      ($data['tg_cdx_an']   == 0) ? $data['label_tg_cdx']   = '(No data)'  :  $data['label_tg_cdx']   = $data['tg_cdx_ac']   . "/" . $data['tg_cdx_an'];
      ($data['tg_ceu_an']   == 0) ? $data['label_tg_ceu']   = '(No data)'  :  $data['label_tg_ceu']   = $data['tg_ceu_ac']   . "/" . $data['tg_ceu_an'];
      ($data['tg_chb_an']   == 0) ? $data['label_tg_chb']   = '(No data)'  :  $data['label_tg_chb']   = $data['tg_chb_ac']   . "/" . $data['tg_chb_an'];
      ($data['tg_chs_an']   == 0) ? $data['label_tg_chs']   = '(No data)'  :  $data['label_tg_chs']   = $data['tg_chs_ac']   . "/" . $data['tg_chs_an'];
      ($data['tg_clm_an']   == 0) ? $data['label_tg_clm']   = '(No data)'  :  $data['label_tg_clm']   = $data['tg_clm_ac']   . "/" . $data['tg_clm_an'];
      ($data['tg_fin_an']   == 0) ? $data['label_tg_fin']   = '(No data)'  :  $data['label_tg_fin']   = $data['tg_fin_ac']   . "/" . $data['tg_fin_an'];
      ($data['tg_gbr_an']   == 0) ? $data['label_tg_gbr']   = '(No data)'  :  $data['label_tg_gbr']   = $data['tg_gbr_ac']   . "/" . $data['tg_gbr_an'];
      ($data['tg_gih_an']   == 0) ? $data['label_tg_gih']   = '(No data)'  :  $data['label_tg_gih']   = $data['tg_gih_ac']   . "/" . $data['tg_gih_an'];
      ($data['tg_ibs_an']   == 0) ? $data['label_tg_ibs']   = '(No data)'  :  $data['label_tg_ibs']   = $data['tg_ibs_ac']   . "/" . $data['tg_ibs_an'];
      ($data['tg_jpt_an']   == 0) ? $data['label_tg_jpt']   = '(No data)'  :  $data['label_tg_jpt']   = $data['tg_jpt_ac']   . "/" . $data['tg_jpt_an'];
      ($data['tg_khv_an']   == 0) ? $data['label_tg_khv']   = '(No data)'  :  $data['label_tg_khv']   = $data['tg_khv_ac']   . "/" . $data['tg_khv_an'];
      ($data['tg_lwk_an']   == 0) ? $data['label_tg_lwk']   = '(No data)'  :  $data['label_tg_lwk']   = $data['tg_lwk_ac']   . "/" . $data['tg_lwk_an'];
      ($data['tg_mxl_an']   == 0) ? $data['label_tg_mxl']   = '(No data)'  :  $data['label_tg_mxl']   = $data['tg_mxl_ac']   . "/" . $data['tg_mxl_an'];
      ($data['tg_pel_an']   == 0) ? $data['label_tg_pel']   = '(No data)'  :  $data['label_tg_pel']   = $data['tg_pel_ac']   . "/" . $data['tg_pel_an'];
      ($data['tg_pur_an']   == 0) ? $data['label_tg_pur']   = '(No data)'  :  $data['label_tg_pur']   = $data['tg_pur_ac']   . "/" . $data['tg_pur_an'];
      ($data['tg_tsi_an']   == 0) ? $data['label_tg_tsi']   = '(No data)'  :  $data['label_tg_tsi']   = $data['tg_tsi_ac']   . "/" . $data['tg_tsi_an'];
      ($data['tg_yri_an']   == 0) ? $data['label_tg_yri']   = '(No data)'  :  $data['label_tg_yri']   = $data['tg_yri_ac']   . "/" . $data['tg_yri_an'];
    }
    else {
      // Don't display 1000 Genomes
      $data['freq_tg_acb']   = 0;
      $data['freq_tg_asw']   = 0;
      $data['freq_tg_cdx']   = 0;
      $data['freq_tg_ceu']   = 0;
      $data['freq_tg_chb']   = 0;
      $data['freq_tg_chs']   = 0;
      $data['freq_tg_clm']   = 0;
      $data['freq_tg_fin']   = 0;
      $data['freq_tg_gbr']   = 0;
      $data['freq_tg_gih']   = 0;
      $data['freq_tg_ibs']   = 0;
      $data['freq_tg_jpt']   = 0;
      $data['freq_tg_khv']   = 0;
      $data['freq_tg_lwk']   = 0;
      $data['freq_tg_mxl']   = 0;
      $data['freq_tg_pel']   = 0;
      $data['freq_tg_pur']   = 0;
      $data['freq_tg_tsi']   = 0;
      $data['freq_tg_yri']   = 0;
      $data['label_tg_acb']  = '(No data)';
      $data['label_tg_asw']  = '(No data)';
      $data['label_tg_cdx']  = '(No data)';
      $data['label_tg_ceu']  = '(No data)';
      $data['label_tg_chb']  = '(No data)';
      $data['label_tg_chs']  = '(No data)';
      $data['label_tg_clm']  = '(No data)';
      $data['label_tg_fin']  = '(No data)';
      $data['label_tg_gbr']  = '(No data)';
      $data['label_tg_gih']  = '(No data)';
      $data['label_tg_ibs']  = '(No data)';
      $data['label_tg_jpt']  = '(No data)';
      $data['label_tg_khv']  = '(No data)';
      $data['label_tg_lwk']  = '(No data)';
      $data['label_tg_mxl']  = '(No data)';
      $data['label_tg_pel']  = '(No data)';
      $data['label_tg_pur']  = '(No data)';
      $data['label_tg_tsi']  = '(No data)';
      $data['label_tg_yri']  = '(No data)';
    }
    
    return $data;
  }
}

/* End of file variations_model.php */
/* Location: ./application/models/variations_model.php */

