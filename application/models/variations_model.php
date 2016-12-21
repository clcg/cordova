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
   * @param  string $prefix Filename prefix for all files to be removed
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
   * @param   string  $variation Genomic position (Hg19) (unformatted)
   * @param   boolean $for_dbnsfp Format as input into dbNSFP search program
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
   * @param   string  $variation Genomic Position (Hg19) (machine name: variation)
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
   * @param  string $variation Genomic Position (Hg19) (machine name: variation)
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
    if (empty($annot_path) || ! file_exists($run_script)) {
      // ERROR: annotation tool has not been properly configured
      return -503;
    }

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
        'evs_ea_af'              => $annot_result['evs_ea_af'],
        'evs_aa_ac'              => $annot_result['evs_aa_ac'],
        'evs_aa_af'              => $annot_result['evs_aa_af'],
        'otoscope_aj_ac'         => $annot_result['otoscope_aj_ac'],
        'otoscope_aj_af'         => $annot_result['otoscope_aj_af'],
        'otoscope_co_ac'         => $annot_result['otoscope_co_ac'],
        'otoscope_co_af'         => $annot_result['otoscope_co_af'],
        'otoscope_us_ac'         => $annot_result['otoscope_us_ac'],
        'otoscope_us_af'         => $annot_result['otoscope_us_af'],
        'otoscope_jp_ac'         => $annot_result['otoscope_jp_ac'],
        'otoscope_jp_af'         => $annot_result['otoscope_jp_af'],
        'otoscope_es_ac'         => $annot_result['otoscope_es_ac'],
        'otoscope_es_af'         => $annot_result['otoscope_es_af'],
        'otoscope_tr_ac'         => $annot_result['otoscope_tr_ac'],
        'otoscope_tr_af'         => $annot_result['otoscope_tr_af'],
        'otoscope_all_ac'        => $annot_result['otoscope_all_ac'],
        'otoscope_all_af'        => $annot_result['otoscope_all_af'],
        'tg_afr_ac'              => $annot_result['tg_afr_ac'],
        'tg_afr_af'              => $annot_result['tg_afr_af'],
        'tg_eur_ac'              => $annot_result['tg_eur_ac'],
        'tg_eur_af'              => $annot_result['tg_eur_af'],
        'tg_amr_ac'              => $annot_result['tg_amr_ac'],
        'tg_amr_af'              => $annot_result['tg_amr_af'],
        'tg_sas_ac'              => $annot_result['tg_sas_ac'],
        'tg_sas_af'              => $annot_result['tg_sas_af'],
        'tg_eas_ac'              => $annot_result['tg_eas_ac'],
        'tg_eas_af'              => $annot_result['tg_eas_af'],
        'tg_asn_ac'              => $annot_result['tg_asn_ac'],
        'tg_asn_af'              => $annot_result['tg_asn_af'],
        'tg_all_ac'              => $annot_result['tg_all_ac'],
        'tg_all_af'              => $annot_result['tg_all_af'],
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
   * @param   string  $prefix  Prefix to check for
   * @param   array   $data  Associate array of variant data
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
   * @param   string   $variation Genomic Position (Hg19) (machine name: variation)
   * @param   boolean  $manual_mode (optional) Bypass annotation to manually insert variant
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

    // SUCCESS: Variant does NOT already exist in the database
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
   * @param  int $id Variant unique ID
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
   * @param  int $id Variant unique ID
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
   * @param string $gene
   *    Gene name
   * @param string $columns
   *    (optional) Columns to select from the database; defaults to all
   * @return object Gene variations
   */
  public function get_variants_by_gene($gene, $columns=NULL)
  {
    // Optionally select specific columns (otherwise select *)
    if ($columns !== NULL && $columns !== '') {
      $this->db->select($columns);
    }

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
   * @param  char    $letter Gene name's first letter
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
   * @param  int      $id Variant unique ID
   * @param  string   $table DB table to query
   * @return mixed    Variant data object or NULL
   */
  public function get_variant_by_id($id, $table = NULL)
  {
    // Default table is the queue
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
   * @param   string   $posisiton Genomic position w/o nucleotide change (i.e. chr13:20796839)
   * @param   string   $table DB table to query
   * @param   boolean  $fuzzy_search Use fuzzy search
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
   * @param  int    $variant_id Variant unique ID
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
   * @param  int $id Variant unique ID
   * @param  string $table Table name
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
   * @param string $table Table name
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
   * Copy Variant Into Queue
   *
   * Copies a variant from the live site into the queue.
   *
   * @author Sean Ephraim
   * @access public
   * @param array $id Variant ID number
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
   * @param int $id Variant ID number
   * @param array $data Assoc. array of variant fields
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
   *   - Copy the current production data (e.g. 'dvd_3') to a new table (e.g.
   *     'dvd_4'), then update the new table (e.g. 'dvd_4') to reflect the
   *     new changes
   *   - Update the 'versions' table
   *   - Create a new 'variant_count_' table
   *   - Backup the '_queue' table and 'reviews' table
   *   - Clear the '_queue' table and 'reviews' table of variants that were
   *     just released
   *
   * By default, only changes that have been confirmed for release are acutally
   * released. As an optional first parameter, you can turn this setting off
   * and release all changes regardless of confirmation status. To do this,
   * pass in FALSE for the first parameter.
   *
   * @author  Sean Ephraim
   * @access  public
   * @param   boolean   $confirmed_only
   *    (optional) Only release confirmed variants?
   * @return  boolean   TRUE on success, else FALSE
   */
  public function push_data_live($confirmed_only = TRUE)
  {
    // Set unlimited memory/time when retrieving all variants in the queue (queue could be quite large)
    ini_set('memory_limit', '-1');
    set_time_limit(0);

    // Get all variants to update
    $new_records = $this->variations_model->get_all_variants($this->tables['vd_queue']);

    if ($confirmed_only === TRUE) {
      // Get only variants confirmed for deletion
      $delete_records = $this->db->get_where($this->tables['reviews'],
                                             array(
                                               'scheduled_for_deletion' => 1,
                                               'confirmed_for_release' => 1,
                                             ))->result();
      // Remove unconfirmed variants from update list
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
    else {
      // Get all variants scheduled for deletion (confirmed or not)
      $delete_records = $this->db->get_where($this->tables['reviews'],
                                             array(
                                               'scheduled_for_deletion' => 1,
                                             ))->result();
    }

    if (empty($new_records) && empty($delete_records) && $this->version != 0) {
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
    foreach ($delete_records as $delete_record) {
      $this->db->delete($new_live_table, array('id' => $delete_record->variant_id));
      $this->db->delete($new_queue_table, array('id' => $delete_record->variant_id));
      $this->db->delete($new_reviews_table, array('variant_id' => $delete_record->variant_id));
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
    $this->db->delete($this->tables['vd_live'], array('variation' => NULL, 'hgvs_nucleotide_change' => NULL));
    $this->db->delete($new_live_table, array('variation' => NULL, 'hgvs_nucleotide_change' => NULL));

    // Delete all review information and queue data for ONLY the records
    // that were released
    $delete_records = $new_records;
    foreach ($delete_records as $delete_record) {
      $this->db->delete($new_queue_table, array('id' => $delete_record->id));
      $this->db->delete($new_reviews_table, array('variant_id' => $delete_record->id));
    }

    // Get new version number
    $new_version = ((int) $this->version) + 1;

    // Update versions table
    $datetime = date('Y-m-d H:i:s');
    $data = array(
      'id'       => NULL,
      'version'  => $new_version,
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
    activity_log("User '$username' released a new version of the database -- Version $new_version", 'release');
    
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
   * @param   int    $variant_id Variant ID number
   * @param   array  $data Assoc. array of variant fields/values
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
   * @param  int $variant_id Variant unique ID
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
          $query_live = $this->db->get_where($this->tables['vd_live'], array('id' => $queue_variant['id'], 'variation' => NULL), 1);
          if ($query_live->num_rows() > 0) {
            $variants[$id]['is_new'] = TRUE;
          }
          else {
            $variants[$id]['is_new'] = FALSE;
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
   * @param string $id Variant unique ID
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
   * @param  int $id  Variant unique ID
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
   * @author   Nikhil Anand
   * @author   Sean Ephraim
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
<<<<<<< HEAD
   * Create a formatted table of variants for all genes starting with a given letter.
   *
   * @author Nikhil Anand
   * @author Zachary Ladlie
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
   * By default, all variants are displayed. If the second parameter ($show_unknown) is
   * set to FALSE, then the variants labeled with "Unknown significance" will not be shown.
   *
   * @author Nikhil Anand
   * @author Sean Ephraim
   * @author Zachary Ladlie
   * @param  string $letter First letter of gene
   * @param  boolean $show_unknown Show/hide unknown variants
   * @return array  Variation data to be displayed genes page
   */
  public function load_gene($letter, $show_unknown = TRUE) {
      
        $counter = 0;
        $result = '';
      
        // Sanitize in case the invoker doesn't
        $letter = $this->validate_gene_letter($letter);

        // Construct and run query
        if ($letter == '') {
            $query = "SELECT * FROM `" . $this->tables['vd_live'] . "` ORDER BY gene ASC;";
        }
        elseif ($show_unknown) {
            $query = sprintf('SELECT * FROM `%s` WHERE gene LIKE \'%s%%\' ORDER BY gene ASC', $this->tables['vd_live'], $letter);
        }
        else {
            $query = sprintf('SELECT * FROM `%s` WHERE gene LIKE \'%s%%\' AND pathogenicity != "Unknown significance" ORDER BY gene ASC', $this->tables['vd_live'], $letter);
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
   * Validate Gene Name
   *
   * @author Sean Ephraim
   * @param  string $name Name of gene
   * @return string Name of gene (sanitized)
   */
  public function validate_gene_name($name) {
    if (!(preg_match('/[A-Z]{1}/', $name))) {
      print "Invalid request for name ".$name;
      exit(8);
    }
    return $name;
  }

  /**
   * Validate Gene Letter
   *
   * @author Nikhil Anand (modified by Zachary Ladlie)
   * @param  string $letter Letter of gene
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
   * Get the variables for the public view of a variant.
   *
   * @author Nikhil Anand
   * @author Sean Ephraim
   * @param  int $id Variant unique ID
   * @param  string $table Table to query from
   * @return array  Data variables to load into the view
   */
  public function get_variant_display_variables($id, $table = NULL) {
    // Default table is the queue
    if ($table === NULL) {
      $table = $this->tables['vd_live'];
    }

    // Load variant data
    $id = trim($id);
    $variant = $this->get_variant_by_id($id, $table);
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
    
    // phyloP
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
    if (stristr($data['sift_pred'], "D") !== FALSE) {
      $data['class_sift'] = "red";
      $data['desc_sift'] = "Damaging";
    } elseif (stristr($data['sift_pred'], "T") !== FALSE) {
      $data['class_sift'] = "green";
      $data['desc_sift'] = "Tolerated";    
    } else {
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

    // Which frequency data to show, if any?
    $data['disp_freqs'] = (count($freqs) > 0) ? 'block' : 'none';
    $data['disp_evs'] = in_array('evs', $freqs) ? 'block' : 'none';
    $data['disp_1000g'] = in_array('1000genomes', $freqs) ? 'block' : 'none';
    $data['disp_exac'] = in_array('exac', $freqs) ? 'block' : 'none';
    $data['disp_otoscope'] = in_array('otoscope', $freqs) ? 'block' : 'none';
    
    // Frequency computations
    $zero_label = 'Unseen (0.000)'; // What to display when 0 alleles are seen
    if (in_array('otoscope', $freqs)) {
      // Display OtoSCOPE
      ($data['otoscope_aj_af'] == '')  ? $data['otoscope_aj_label']  = '(No data)' : ($data['otoscope_aj_af']  == 0) ? $data['otoscope_aj_label']  = $zero_label : $data['otoscope_aj_label']  = $data['otoscope_aj_ac']  . "/" . 400  . " (" . number_format((float) $data['otoscope_aj_af'],  3, '.', '') . ")";
      ($data['otoscope_co_af'] == '')  ? $data['otoscope_co_label']  = '(No data)' : ($data['otoscope_co_af']  == 0) ? $data['otoscope_co_label']  = $zero_label : $data['otoscope_co_label']  = $data['otoscope_co_ac']  . "/" . 320  . " (" . number_format((float) $data['otoscope_co_af'],  3, '.', '') . ")";
      ($data['otoscope_us_af'] == '')  ? $data['otoscope_us_label']  = '(No data)' : ($data['otoscope_us_af']  == 0) ? $data['otoscope_us_label']  = $zero_label : $data['otoscope_us_label']  = $data['otoscope_us_ac']  . "/" . 320  . " (" . number_format((float) $data['otoscope_us_af'],  3, '.', '') . ")";
      ($data['otoscope_jp_af'] == '')  ? $data['otoscope_jp_label']  = '(No data)' : ($data['otoscope_jp_af']  == 0) ? $data['otoscope_jp_label']  = $zero_label : $data['otoscope_jp_label']  = $data['otoscope_jp_ac']  . "/" . 400  . " (" . number_format((float) $data['otoscope_jp_af'],  3, '.', '') . ")";
      ($data['otoscope_es_af'] == '')  ? $data['otoscope_es_label']  = '(No data)' : ($data['otoscope_es_af']  == 0) ? $data['otoscope_es_label']  = $zero_label : $data['otoscope_es_label']  = $data['otoscope_es_ac']  . "/" . 360  . " (" . number_format((float) $data['otoscope_es_af'],  3, '.', '') . ")";
      ($data['otoscope_tr_af'] == '')  ? $data['otoscope_tr_label']  = '(No data)' : ($data['otoscope_tr_af']  == 0) ? $data['otoscope_tr_label']  = $zero_label : $data['otoscope_tr_label']  = $data['otoscope_tr_ac']  . "/" . 200  . " (" . number_format((float) $data['otoscope_tr_af'],  3, '.', '') . ")";
      ($data['otoscope_all_af'] == '') ? $data['otoscope_all_label'] = '(No data)' : ($data['otoscope_all_af'] == 0) ? $data['otoscope_all_label'] = $zero_label : $data['otoscope_all_label'] = $data['otoscope_all_ac'] . "/" . 2000 . " (" . number_format((float) $data['otoscope_all_af'], 3, '.', '') . ")";
    }
    else {
      // Don't display OtoSCOPE
      $data['otoscope_aj_af']  = 0;
      $data['otoscope_co_af']  = 0;
      $data['otoscope_us_af']  = 0;
      $data['otoscope_jp_af']  = 0;
      $data['otoscope_es_af']  = 0;
      $data['otoscope_tr_af']  = 0;
      $data['otoscope_all_af'] = 0;
      $data['otoscope_aj_label']  = '(No data)';
      $data['otoscope_co_label']  = '(No data)';
      $data['otoscope_us_label']  = '(No data)'; 
      $data['otoscope_jp_label']  = '(No data)'; 
      $data['otoscope_es_label']  = '(No data)'; 
      $data['otoscope_tr_label']  = '(No data)'; 
      $data['otoscope_all_label'] = '(No data)';
    }
    if (in_array('evs', $freqs)) {
      // Display EVS
      ($data['evs_ea_af'] == '')  ? $data['evs_ea_label']  = '(No data)' : ($data['evs_ea_af']  == 0) ? $data['evs_ea_label']  = $zero_label : $data['evs_ea_label']  = $data['evs_ea_ac']  . "/" . intval($data['evs_ea_ac']/$data['evs_ea_af'])   . " (" . number_format((float) $data['evs_ea_af'],  3, '.', '') . ")";
      ($data['evs_aa_af'] == '')  ? $data['evs_aa_label']  = '(No data)' : ($data['evs_aa_af']  == 0) ? $data['evs_aa_label']  = $zero_label : $data['evs_aa_label']  = $data['evs_aa_ac']  . "/" . intval($data['evs_aa_ac']/$data['evs_aa_af'])   . " (" . number_format((float) $data['evs_aa_af'],  3, '.', '') . ")";
      ($data['evs_all_af'] == '') ? $data['evs_all_label'] = '(No data)' : ($data['evs_all_af'] == 0) ? $data['evs_all_label'] = $zero_label : $data['evs_all_label'] = $data['evs_all_ac'] . "/" . intval($data['evs_all_ac']/$data['evs_all_af']) . " (" . number_format((float) $data['evs_all_af'], 3, '.', '') . ")";
    }
    else {
      // Don't display EVS
      $data['evs_ea_af']  = 0;
      $data['evs_aa_af']  = 0;
      $data['evs_all_af'] = 0;
      $data['evs_ea_label']  = '(No data)';
      $data['evs_aa_label']  = '(No data)';
      $data['evs_all_label'] = '(No data)';

    }
    if (in_array('1000genomes', $freqs)) {
      // Display 1000 Genomes
      ($data['tg_afr_af'] == '') ? $data['tg_afr_label'] = '(No data)' : ($data['tg_afr_af'] == 0) ? $data['tg_afr_label'] = $zero_label : $data['tg_afr_label'] = $data['tg_afr_ac'] . "/" . intval($data['tg_afr_ac']/$data['tg_afr_af']) . " (" . number_format((float) $data['tg_afr_af'], 3, '.', '') . ")";
      ($data['tg_eur_af'] == '') ? $data['tg_eur_label'] = '(No data)' : ($data['tg_eur_af'] == 0) ? $data['tg_eur_label'] = $zero_label : $data['tg_eur_label'] = $data['tg_eur_ac'] . "/" . intval($data['tg_eur_ac']/$data['tg_eur_af']) . " (" . number_format((float) $data['tg_eur_af'], 3, '.', '') . ")";
      ($data['tg_amr_af'] == '') ? $data['tg_amr_label'] = '(No data)' : ($data['tg_amr_af'] == 0) ? $data['tg_amr_label'] = $zero_label : $data['tg_amr_label'] = $data['tg_amr_ac'] . "/" . intval($data['tg_amr_ac']/$data['tg_amr_af']) . " (" . number_format((float) $data['tg_amr_af'], 3, '.', '') . ")";
      ($data['tg_sas_af'] == '') ? $data['tg_sas_label'] = '(No data)' : ($data['tg_sas_af'] == 0) ? $data['tg_sas_label'] = $zero_label : $data['tg_sas_label'] = $data['tg_sas_ac'] . "/" . intval($data['tg_sas_ac']/$data['tg_sas_af']) . " (" . number_format((float) $data['tg_sas_af'], 3, '.', '') . ")";
      ($data['tg_eas_af'] == '') ? $data['tg_eas_label'] = '(No data)' : ($data['tg_eas_af'] == 0) ? $data['tg_eas_label'] = $zero_label : $data['tg_eas_label'] = $data['tg_eas_ac'] . "/" . intval($data['tg_eas_ac']/$data['tg_eas_af']) . " (" . number_format((float) $data['tg_eas_af'], 3, '.', '') . ")";
      ($data['tg_all_af'] == '') ? $data['tg_all_label'] = '(No data)' : ($data['tg_all_af'] == 0) ? $data['tg_all_label'] = $zero_label : $data['tg_all_label'] = $data['tg_all_ac'] . "/" . intval($data['tg_all_ac']/$data['tg_all_af']) . " (" . number_format((float) $data['tg_all_af'], 3, '.', '') . ")";
    }
    else {
      // Don't display 1000 Genomes
      $data['tg_afr_af'] = 0;
      $data['tg_eur_af'] = 0;
      $data['tg_amr_af'] = 0;
      $data['tg_asn_af'] = 0;
      $data['tg_all_af'] = 0;
      $data['tg_afr_label'] = '(No data)';
      $data['tg_eur_label'] = '(No data)';
      $data['tg_amr_label'] = '(No data)';
      $data['tg_asn_label'] = '(No data)';
      $data['tg_all_label'] = '(No data)';
    }
    if (in_array('exac', $freqs)) {
      // Display ExAC
      ($data['exac_afr_af'] == '') ? $data['exac_afr_label'] = '(No data)' : ($data['exac_afr_af'] == 0) ? $data['exac_afr_label'] = $zero_label : $data['exac_afr_label'] = $data['exac_afr_ac'] . "/" . intval($data['exac_afr_ac']/$data['exac_afr_af']) . " (" . number_format((float) $data['exac_afr_af'], 3, '.', '') . ")";
      ($data['exac_amr_af'] == '') ? $data['exac_amr_label'] = '(No data)' : ($data['exac_amr_af'] == 0) ? $data['exac_amr_label'] = $zero_label : $data['exac_amr_label'] = $data['exac_amr_ac'] . "/" . intval($data['exac_amr_ac']/$data['exac_amr_af']) . " (" . number_format((float) $data['exac_amr_af'], 3, '.', '') . ")";
      ($data['exac_fin_af'] == '') ? $data['exac_fin_label'] = '(No data)' : ($data['exac_fin_af'] == 0) ? $data['exac_fin_label'] = $zero_label : $data['exac_fin_label'] = $data['exac_fin_ac'] . "/" . intval($data['exac_fin_ac']/$data['exac_fin_af']) . " (" . number_format((float) $data['exac_fin_af'], 3, '.', '') . ")";
      ($data['exac_nfe_af'] == '') ? $data['exac_nfe_label'] = '(No data)' : ($data['exac_nfe_af'] == 0) ? $data['exac_nfe_label'] = $zero_label : $data['exac_nfe_label'] = $data['exac_nfe_ac'] . "/" . intval($data['exac_nfe_ac']/$data['exac_nfe_af']) . " (" . number_format((float) $data['exac_nfe_af'], 3, '.', '') . ")";
      ($data['exac_sas_af'] == '') ? $data['exac_sas_label'] = '(No data)' : ($data['exac_sas_af'] == 0) ? $data['exac_sas_label'] = $zero_label : $data['exac_sas_label'] = $data['exac_sas_ac'] . "/" . intval($data['exac_sas_ac']/$data['exac_sas_af']) . " (" . number_format((float) $data['exac_sas_af'], 3, '.', '') . ")";
      ($data['exac_eas_af'] == '') ? $data['exac_eas_label'] = '(No data)' : ($data['exac_eas_af'] == 0) ? $data['exac_eas_label'] = $zero_label : $data['exac_eas_label'] = $data['exac_eas_ac'] . "/" . intval($data['exac_eas_ac']/$data['exac_eas_af']) . " (" . number_format((float) $data['exac_eas_af'], 3, '.', '') . ")";
      ($data['exac_oth_af'] == '') ? $data['exac_oth_label'] = '(No data)' : ($data['exac_oth_af'] == 0) ? $data['exac_oth_label'] = $zero_label : $data['exac_oth_label'] = $data['exac_oth_ac'] . "/" . intval($data['exac_oth_ac']/$data['exac_oth_af']) . " (" . number_format((float) $data['exac_oth_af'], 3, '.', '') . ")";
      ($data['exac_all_af'] == '') ? $data['exac_all_label'] = '(No data)' : ($data['exac_all_af'] == 0) ? $data['exac_all_label'] = $zero_label : $data['exac_all_label'] = $data['exac_all_ac'] . "/" . intval($data['exac_all_ac']/$data['exac_all_af']) . " (" . number_format((float) $data['exac_all_af'], 3, '.', '') . ")";
    }
    else {
      // Don't display ExAC
      $data['tg_afr_af'] = 0;
      $data['tg_amr_af'] = 0;
      $data['tg_fin_af'] = 0;
      $data['tg_nfe_af'] = 0;
      $data['tg_eas_af'] = 0;
      $data['tg_sas_af'] = 0;
      $data['tg_oth_af'] = 0;
      $data['tg_all_af'] = 0;
      $data['tg_afr_label'] = '(No data)';
      $data['tg_fin_label'] = '(No data)';
      $data['tg_nfe_label'] = '(No data)';
      $data['tg_amr_label'] = '(No data)';
      $data['tg_eas_label'] = '(No data)';
      $data['tg_sas_label'] = '(No data)';
      $data['tg_oth_label'] = '(No data)';
      $data['tg_all_label'] = '(No data)';
    }
    
    return $data;
  }

  /**
   * Num Unreleased
   *
   * Returns total number of variants with unreleased changes.
   *
   * @author Sean Ephraim
   * @return int 
   *    Number of unreleased changes
   */
  public function num_unreleased() {
    return $this->db->count_all($this->tables['reviews']);
  }

  /**
  * Run Annotation Pipeline
  *
  * Takes the timestamp associated with this submit and the list
  * of genes submitted and runs a series of scripts to collect
  * and annotate variants associated with the genes submitted.
  *
  * @author Andrea Hallier
  * @input timeStamp, genesFile
  */
  public function run_annotation_pipeline($timeStamp, $genesFile){
    $this->load->database();
    $RUBY = $this->config->item('ruby_path');
    $annotation_path = $this->config->item('annotation_path');
    $PATH = getenv('PATH');
    $vd_queue = $this->tables['vd_queue'];
    $BASEPATH = BASEPATH;
    
    ini_set('memory_limit', '-1');
    set_time_limit(0);
    //exec("nohup sh -c '$RUBY /asap/cordova_pipeline/genes2regions.rb $genesFile &> $regionsFile && $RUBY /asap/cordova_pipeline/regions2variants.rb $regionsFile &> $variantsFile && $RUBY /asap/cordova_pipeline/map.rb $variantsFile &> $mapFile ; cut -f1 $mapFile>$listFile && $RUBY /asap/kafeen/kafeen.rb --progress -i $listFile -o $kafeenFile && $RUBY /asap/cordova_pipeline/annotate_with_hgmd_clinvar.rb $kafeenFile $mapFile &> $hgmd_clinvarFile && cut -f-6  $kafeenFile > $f1File && cut -f2-4 $hgmd_clinvarFile > $f2File && cut -f10- $kafeenFile > $f3File && paste $f1File $f2File > $f4File && paste $f4File $f3File > $finalFile' &");
    //exec("nohup sh -c '$RUBY /Shared/utilities/cordova_pipeline_v2/pipeline.rb $genesFile' &");
    //$op = system("$RUBY /Shared/utilities/cordova_pipeline_v2/pipeline.rb $genesFile &> outPutLog.txt",$returns);
    //exec("export PATH=$PATH:/Shared/utilities/vcftools_0.1.13/bin/");
    
    //exec("cd $annotation_path && export PATH=$PATH:/Shared/utilities/vcftools_0.1.13/bin/:/Shared/utilities/bin/ && $RUBY pipeline.rb $genesFile &> outPutLog$timeStamp.txt && gunzip /Shared/utilities/cordova_pipeline_v2/mygenes$timeStamp.vcf.gz && vcf-to-tab < /Shared/utilities/cordova_pipeline_v2/mygenes$timeStamp.vcf &> /Shared/utilities/cordova_pipeline_v2/mygenes$timeStamp.tab");

    //exec("cd $annotation_path && export PATH=$PATH:/Shared/utilities/vcftools_0.1.13/bin/:/Shared/utilities/bin/ && $RUBY pipeline.rb $genesFile &> outPutLog$timeStamp.txt && gunzip /Shared/utilities/cordova_pipeline_v2/mygenes$timeStamp.vcf.gz && vcftools --vcf mygenes$timeStamp.vcf --get-INFO ASAP_VARIANT --get-INFO GENE --get-INFO ASAP_HGVS_C --get-INFO ASAP_HGVS_P --get-INFO ASAP_LOCALE --get-INFO FINAL_PATHOGENICITY --get-INFO FINAL_DISEASE --out mygenes$timeStamp.tab &> vcftoolOUTPUT.txt && cut -f1,5- mygenes$timeStamp.tab.INFO -d'	' &> mygenes$timeStamp.final");
    
    $queueTable = $this->tables['vd_queue'];
    $liveTable = $this->tables['vd_live'];
    $resultsTable = $this->tables['reviews'];
    $database = $this->db->database;
    $date = date("Y-m-d H:i:s"); 
   
    //GOING TO NEED TO MOVE THIS!!! NOT SAFE HERE!! MIGHT BREAK SOMETHING ELSE, IE IF NOT UNIUQE BEFORE NOW...
    $this->db->query("ALTER TABLE $queueTable ADD UNIQUE INDEX (`variation`)");
    
    //$COLUMNS=("dbsnp","evs_all_ac","evs_all_an","evs_all_af","evs_ea_ac","evs_ea_an","evs_ea_af","evs_aa_ac","evs_aa_an","evs_aa_af","tg_all_ac","tg_all_an","tg_all_af","tg_afr_ac","tg_afr_an","tg_afr_af","tg_amr_ac","tg_amr_an","tg_amr_af","tg_eas_ac","tg_eas_an","tg_eas_af","tg_eur_ac","tg_eur_an","tg_eur_af","tg_sas_ac","tg_sas_an","tg_sas_af","otoscope_all_ac","otoscope_all_an","otoscope_all_af","otoscope_aj_ac","otoscope_aj_an","otoscope_aj_af","otoscope_co_ac","otoscope_co_an","otoscope_co_af","otoscope_us_ac","otoscope_us_an","otoscope_us_af","otoscope_jp_ac","otoscope_jp_an","otoscope_jp_af","otoscope_es_ac","otoscope_es_an","otoscope_es_af","otoscope_tr_ac","otoscope_tr_an","otoscope_tr_af","gene","sift_score","sift_pred","polyphen2_score","polyphen2_pred","lrt_score","lrt_pred,mutationtaster_score,mutationtaster_pred,gerp_rs,phylop_score,gerp_pred,phylop_pred,variation,hgvs_nucleotide_change,hgvs_protein_change,variantlocale,pathogenicity,disease,pubmed_id,comments,exac_afr_ac,exac_afr_an,exac_afr_af,exac_amr_ac,exac_amr_an,exac_amr_af,exac_eas_ac,exac_eas_an,exac_eas_af,exac_fin_ac,exac_fin_an,exac_fin_af,exac_nfe_ac,exac_nfe_an,exac_nfe_af,exac_oth_ac,exac_oth_an,exac_oth_af,exac_sas_ac,exac_sas_an,exac_sas_af,exac_all_ac,exac_all_an,exac_all_af");
    $COLUMNS = "(id,chr,pos,ref,alt,gene,sift_score,sift_pred,polyphen2_score,polyphen2_pred,lrt_score,lrt_pred,mutationtaster_score,mutationtaster_pred,gerp_rs,phylop_score,gerp_pred,phylop_pred,variation,hgvs_nucleotide_change,hgvs_protein_change,variantlocale,pathogenicity,disease,pubmed_id,comments,dbsnp,evs_all_af,evs_ea_ac,evs_ea_af,evs_aa_ac,evs_aa_an,evs_aa_af,tg_all_af,tg_afr_af,tg_amr_af,tg_eur_af)";
    exec("cd $annotation_path && export PATH=$PATH:/Shared/utilities/vcftools_0.1.13/bin/:/Shared/utilities/bin/ && $RUBY pipeline.rb $genesFile &> outPutLog$timeStamp.txt && gunzip mygenes$timeStamp.vcf.gz && bash convert_Cordova_VCF_to_mysqlimport_TSV.sh mygenes$timeStamp.vcf &> mygenes$timeStamp.final && cp mygenes$timeStamp.final $queueTable.tsv && cut -f 0-32 $queueTable.tsv > $queueTable.tsvcleaned");
    exec("cp $annotation_path/outPutLog$timeStamp.txt $BASEPATH/tmp/myvariants$timeStamp.log");
    
    $file = fopen("$annotation_path/$queueTable.tsvcleaned", "r");
    //$numLines = count(file("$annotation_path/$queueTable.tsvcleaned"));
    $finalTsvPath = "$annotation_path/final$queueTable.tsv";
    exec("cp $finalTsvPath /var/www/html/cordova_sites_ah/vvd/tmp/");
    $finalTsv = fopen($finalTsvPath, 'w'); 
    //get max id from queuei
    $maxid = 0;
    $row = $this->db->query("SELECT MAX(id) AS `maxid` FROM $liveTable")->row();
    if ($row) {
      #get max id from current queueTable
      $maxid = $row->maxid; 
      #Increment the id
      $maxid = $maxid+1;
    }
    #else the max id = 0
    $i = $maxid;
    //for each entry in tsv, add temp id
    while($line = fgets($file)){
      $data = explode("\t", $line);
      #encode the disease name, prone to incompatable characters
      if(isset($data[18])){
        $data[18] = urlencode($data[18]);
      }
      $dataString = implode("\t",$data);
      $newline = "$i"."\t"."$dataString";
      fwrite($finalTsv,$newline);
      $i=$i+1;
    }
    #chmod($finalTsvPath,0777);
    //mysql import tsv with replace on id and variation
    //$this->db->query("DELETE * FROM $queueTable");
    //$this->db->query("DELETE * FROM $liveTable");
    //$this->db->query("DELETE * FROM $resultsTable");
    $this->db->query("LOAD DATA LOCAL INFILE '".$finalTsvPath."' 
        REPLACE INTO TABLE $queueTable 
        FIELDS TERMINATED BY '\t'
        LINES TERMINATED BY '\\n' 
        IGNORE 1 LINES
        $COLUMNS");
    //does not allow duplicate entries, deletes old reccord and inserts new one
    //join with live table on variation and update ids in queue
    //could speed this up by only querrying the new variants in queue, ie id>maxid or id=null
    //maybe create a view?? to hold the newest variants in queue
    $query1 = $this->db->query("update $queueTable u
            inner join $liveTable s on
            u.variation = s.variation
            set u.id = s.id");

    //insert ids(autogenerated) and variant into live table where queue id is greater than maxid
    //this should be fine,, because there is no gene name associated with the variant in the live table
    $query2 = $this->db->query("INSERT INTO $liveTable (variation) SELECT variation FROM $queueTable WHERE id>=$maxid");

    //Reindex id's in queue
    $query3 = $this->db->query("update $queueTable u
              inner join $liveTable s on
              u.variation = s.variation
              set u.id = s.id");

    //insert ids and dates into reviews where queue id is greater than maxid
    //There is a problem here, this only inserts restuls when id>max id, needs to be when case is in queue and not in results...
    //This works when not re-entering exsisting genes.
    $query4 = $this->db->query("INSERT INTO $resultsTable (variant_id,created) SELECT id,'$date' FROM $queueTable WHERE id NOT IN(SELECT variant_id FROM $resultsTable)");
    
    //exec("touch /var/www/html/cordova_sites_ah/rdvd/tmp/queue.csv && chmod 777 /var/www/html/cordova_sites_ah/rdvd/tmp/queue.csv");
    $query5 = $this->db->query("SELECT * from $queueTable INTO OUTFILE '/tmp/queue$timeStamp.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\n';");
    exec("cp /tmp/queue$timeStamp.csv /var/www/html/cordova_sites_ah/vvd/tmp/queue$timeStamp.csv");



    //Get id's from reviews where variation matches, ie it has been replaced and exsists in review and is null in live
    
    return $timeStamp;
  }

}
/* End of file variations_model.php */
/* Location: ./application/models/variations_model.php */

