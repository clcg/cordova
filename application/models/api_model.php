<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class API_model extends MY_Model {

  /**
   * Array of strings for views, filenames, etc.
   *
   * @var array
   */
  public $strings = array();

  /**
   * Array of tables used.
   *
   * @var array
   */
  public $tables = array();

  /**
   * Array of variables to be used with API queries.
   *
   * @var array
   */
  public $api_vars = array();

  public function __construct() {
    parent::__construct();
    $this->load->config('variation_database');

    // initialize common strings
    $this->strings = $this->config->item('strings');
    // initialize db tables data
    $this->tables = $this->config->item('tables');
    // initialize search validation values
    $this->api_vars = $this->config->item('api');
  }

  /**
   * Sanitize, validate inputs and perform search
   *
   * @author Nikhil Anand
   * @param string $get_variables 
   *    The $_GET array 
   * @return void
   */
  public function api_bootstrap($get_variables) {
    $validation_message = '';
  
    /* Sanitization */
  
    $format  = @trim(strip_tags($get_variables["format"]));
    $type    = @trim(strip_tags($get_variables["type"]));
    $method  = @trim(strip_tags($get_variables["method"]));
    $version = @trim(strip_tags($get_variables["version"]));
    // Make sure we trim every search term and that there are no null terms
    $terms   = @explode(",", trim(strip_tags($get_variables["terms"])));
    for ($i=0; $i < count($terms); $i++) {
      $terms[$i] = trim(strip_tags($terms[$i]));
      if ($terms[$i] == "") {
        unset($terms[$i]);
      }
    }
  
    /* Validation */
  
    // Format
    if ($format && !in_array($format, $this->api_vars['format'])) {
      $validation_message = "Invalid format specified";
    } 
    else if (!$format) {
      $format = $this->api_vars['format'][0]; // default
    }
    
    // Type of search
    if ($type && !in_array($type, $this->api_vars['type'])) {
      $validation_message = "Invalid search type specified";
    } 
    else if (!$type) {
      $type = $this->api_vars['type'][0]; // default
    }
    
    // Download method
    if ($method && !in_array($method, $this->api_vars['method'])) {
      $validation_message = "Invalid download method specified";
    } 
    else if (!$method) {
      $method = $this->api_vars['method'][0]; // default
    }
  
    // Database version
    if ($version) {
      $versions_results = $this->variations_model->get_all_db_version_info();
      $versions = array();
      foreach ($versions_results as $versions_result) {
        $versions[] = $versions_result->version; // get all version numbers
      }
      if ( ! in_array($version, $versions)) {
        $validation_message = "Invalid version number. Please see the API page for a list of versions.";
      }
    } 
    else if ( ! $version) {
      $version = $this->version; // default
    }
  
    // Search terms (validation method depends on type of search)
    if ( ! $this->_api_validate_terms($type, $terms)) {
      $validation_message = "One or more of your search terms is malformed for the type of search specified.";
    }
  
    if ($validation_message != '') {
      header("Content-type: text/plain");
      print $validation_message;
      exit;
    } 
    else {
      $search_results = $this->_api_search($type, $terms, $version);
      if(strcmp(get_class($search_results),'CI_DB_mysql_driver')==0){
      		$prog_load = TRUE;
      }
      return $this->_api_result($search_results, $format, $type, $terms, $method, $prog_load);
    }
  }
  
  /**
   * Validate search terms by invoking the appropriate method for each search type
   *
   * @author Nikhil Anand
   * @param string $type 
   *    Type of search (e.g. variant, PubMed ID, etc)
   * @param string $terms 
   *    An array of search terms for the type of search
   * @return void
   */
  public function _api_validate_terms($type, $terms) {
    
    // Check if we have any search terms
    if (!empty($terms)) {
      switch ($type) {
        case 'position':
          return $this->_api_validate_terms_variant($terms);
          break;
  
        case 'gene':
          return $this->_api_validate_terms_gene($terms);
          break;
  
        default:
          return $this->_api_validate_terms_variant($terms);
          break;
      }
    } 
    else if ($type == 'genelist') { // Special case for gene list
     return true;
    } 
    else if ($type == 'variantlist') { // Special case for variant list
     return true;
    }
    else {
      header("Content-type: text/plain");
      print "You need to specify some search terms.";
      exit;
    }
    
  }
  
  /**
   * Helper to search term validation method
   * Validate genes (e.g. gjb6, GJB6)
   *
   * @author Nikhil Anand
   * @param string $terms 
   *    An array of variants
   * @return void
   */
  public function _api_validate_terms_gene($terms) {
    $valid = TRUE;
  
    foreach ($terms as $term) {
      if (!preg_match('/[a-zA-Z]{2,}[0-9]{0,2}/', $term)) {
        print "\"$term\" is malformed for a gene search.\r\n";
        $valid = FALSE;
      }
    }
      
    return $valid;
  }
  
  /**
   * Helper to search term validation method
   * Validate variants (e.g. chr7:128917)
   *
   * @author Nikhil Anand
   * @param string $terms 
   *    An array of variants
   * @return void
   */
  public function _api_validate_terms_variant($terms) {
    $valid = TRUE;
    
    foreach ($terms as $term) {
      if (!preg_match('/chr\d{1,2}+:\d+/', $term)) {
        print "\"$term\" is malformed for a variant search.\r\n";
        $valid = FALSE;
      }
    }   
    
    return $valid;
  }
  
  /**
   * Perform the actual search
   *
   * @author Nikhil Anand
   * @param string $format 
   *    Output format (CSV, tab-delimited, JSON or XML)
   * @param string $type 
   *    Type of search
   * @param string $terms 
   *    An array of (validated) search terms
   * @param string $version 
   *    Version of the database to search
   * @return void
   */
  public function _api_search($type = 'position', $terms, $version) {
    $search_results = array();
    
    // Gene list
    if ($type == 'genelist') {
      $fields = implode(',', $this->api_vars['genelist']);
      $query = $this->db->select($fields)
                        ->get($this->tables['variant_count']);
      foreach ($query->result_array() as $row) {
        $search_results[] = $row;
      }
    }
    
    // Variant list
    else if ($type == 'variantlist') {
      $fields = implode(',', $this->api_vars['variantlist']);
      $query = $this->db->select($fields)
                        ->where('variation IS NOT NULL', NULL, FALSE)
                        ->order_by('variation', 'asc')
                        ->get($this->tables['vd_live']);
      foreach ($query->result_array() as $row) {
        $search_results[] = $row;
      }
    }

    // Variants of a gene
    else if ($type == 'gene') {
    	
    		//estimate memory consumption incase it is exhorbitant
    		$prog_load = FALSE;
    		$est_result_size = 0;
    		foreach ($terms as $term){
    			$est_result_size += $this->variations_model->count_variants_by_gene(strtoupper($term));
    		}
    		
    		if($est_result_size >= 1000){ //1Kb in bytes, TODO: for implementation, set this value in config
    			//progressive loading needed
    			$prog_load = TRUE;
    		}
    		
    		foreach ($terms as $term) {
    			$search_results = $this->variations_model->get_variants_by_gene(strtoupper($term),NULL,$prog_load);
    		}
    		
    }

    // Variant position or exact position
    else {
      if ($type == 'exactposition') {
        // 'exactposition' uses exact search
        $fuzzy_search = FALSE;
      }
      else {
        // 'position' uses fuzzy search
        $fuzzy_search = TRUE;
      }
      // For each search term, build a query statement and execute  
      foreach ($terms as $term) {
        $query_results = $this->variations_model->get_variants_by_position($term, $this->tables['vd_live'], $fuzzy_search);
        if (empty($query_results)) {
          // this term produced no results
          $search_results[$term] = NULL;
        } 
        else {
          // this term produced results, now gather all results
          foreach ($query_results as $query_result) {
            $search_results[$query_result->variation] = (array) $query_result;
          }
        }
      } // End foreach
    } // End else
  
    return $search_results;
  }
  
  /**
   * Pass search results to the appropriate helper public functions for display or download
   *
   * @author Nikhil Anand
   * @param string $search_results 
   *    The array of search results
   * @param string $format 
   *    Output format (CSV, tab-delimited, JSON, XML)
   * @param string $type 
   *    Type of search
   * @param string $terms 
   *    An array of (validated) search terms
   * @param string $method 
   *    Download/display method
   * @return void
   */
  public function _api_result($search_results, $format, $type, $terms, $method, $prog_load=FALSE) {
    switch ($format) {
      case 'tab':
//         return $this->_api_result_text($search_results, $method, $type, "\t");
			return $this->_api_result_text_dload($search_results, $terms, $method, $type, ".tsv", "\t", $prog_load);
        break;
  
      case 'csv':
//         return $this->_api_result_text($search_results, $method, $type, ",");
      	return $this->_api_result_text_dload($search_results, $terms, $method, $type, ".csv", ",", $prog_load);
        break;
        
      case 'json':
//         return $this->_api_result_json($search_results, $method);
      	return $this->_api_result_json_dload($search_results, $terms, $method, $type);
        break;
      
      case 'xml':
//         return $this->_api_result_xml($search_results, $method);
      	return $this->_api_result_xml_dload($search_results, $terms, $method, $type);
        break;
      
      case 'vcf':
        if ($type == 'variantlist') {
//           return $this->_api_result_vcf($search_results, $method);
          return $this->_api_result_vcf_dload($search_results, $terms, $method, $type);
          break;
        } else {
          header("Content-type: text/plain");
          print "VCF output is only valid for query of type 'variantlist'";
          exit;
        }
      
      default:
        return $this->_api_result_text_dload($search_results, $terms, $method, $type, 'tab', "\t", $prog_load);
        break;
    }
  }
  
  /**
   * _api_result_text_dload
   * forces a download of text data in a delimited way
   * 
   * @author Rob Marini
   * @param CI_DB_MYSQL Object $search_results
   * 	contains the query and results and connecting information
   * @param string $terms 
   *    An array of (validated) search terms (used as a WHERE clause of MYSQL statement)
   * @param string $method 
   *    Download/display method
   * @param string $type 
   *    Type of search
   * @param string $suffix
   * 	the file extension
   * @param string $delimiter
   * 	the delemitting character
   * @return void
   */
  public function _api_result_text_dload($search_results, $terms, $method, $type, $suffix, $delimiter, $prog_load=FALSE){
  	
  	if(strcmp(strtolower($method), 'plain') == 0){
  		
  		return $this->_api_result_text($search_results, $method, $type, $suffix, $delimiter);
  		
  	} else { //default is to download
  	
  		if($prog_load){
  			
  			$this->load->helper('download');
  			
  			$filename = "";
  			foreach($terms as $term){
  				$filename .= $term;
  			}
  			$filename .= "_variants_list" . $suffix;
  			$num_rows_per_chunk = ceil($this->config->item('chunk_load_limit') / $this->config->item('mem_per_row_ratio'));
			$upperbound = get_query_result_size(clone($search_results))[0];
  			$bounds = [1,$upperbound]; //1 for all items
  			$newline = "\r\n";
  			force_prog_dload($search_results, $num_rows_per_chunk, $bounds, $filename, $delimiter, $newline);			
  			
  		} else{ // get all results and hold in memory to then download
  			
  			$this->load->dbutil();
  			$this->load->helper('file');
  			$this->load->helper('download');
  			$newline = "\r\n";
  			$data = $this->dbutil->csv_from_result($search_results, $delimiter, $newline);
  			
  			$filename = "";
  			foreach($terms as $term){
  				$filename .= $term;
  			}
  			$filename .= "_variants_list" . $suffix;
  			
  			force_download($filename,$data); //automatically exits
  		}
  		
  	}
  	
  }
  
  /**
   * 
   * Helper function that provides plain text data for file streaming
   *
   * @author Nikhil Anand
   * @author Sean Ephraim
   * @author Rob Marini
   * @param string $search_results
   *    The array of search results
   * @param string 
   * @param string 
   * @param string $delimiter
   *    Any delimiter with which to separate results
   * @return string
   */
  public function _api_result_text_4stream($search_results, $delimiter = ",", $newline = "\r\n", $col_headers = TRUE) {
  	
  	if($col_headers){
  		$output = '#';
  		
	  	// Special case for genelist query
	  	if ($type == 'genelist') {
	  		$output .= implode($delimiter, $this->api_vars['genelist']) . $newline;
	  	}
	  	
	  	// Special case for variation list
	  	else if ($type == 'variantlist') {
	  		$output .= implode($delimiter, $this->api_vars['variantlist']) . $newline;
	  	}
	  	
	  	// Add column headers for all others
	  	else {
	  		$output .= implode($delimiter, $this->variations_model->get_variant_fields()) . $newline;
	  	}
  	} else {
  	
	  	// Using 'implode' would have been neater, but we need to specify NULL for missing values
	  	foreach ($search_results as $result => $columns) {
	  		if (is_array($columns)) {
	  			foreach ($columns as $column) {
	  				if (trim($column) == '') {
	  					$output .= "NULL";
	  				} else {
	  					$output .= $column;
	  				}
	  				$output .= $delimiter;
	  			}
	  		}
	  		
	  		// Remove the last delimiter and add a newline
	  		$output = substr($output, 0, -1);
	  		$output .= $newline;
	  	}
  	}
  	
  	return($output);
  }
  
  /**
   * _api_result_xml_dload
   * forces a download of text data in xml format
   *
   * @author Rob Marini
   * @param CI_DB_MYSQL Object $search_results
   * 	contains the query and results and connecting information
   * @param string $terms 
   *    An array of (validated) search terms (used as a WHERE clause of MYSQL statement)
   * @param string $method 
   *    Download/display method
   * @param string $type 
   *    Type of search
   * @return void
   */
  public function _api_result_xml_dload($search_results, $terms, $method, $type){
  	
  	if(strcmp(strtolower($method), 'plain') == 0){
  		
  		return $this->_api_result_xml($search_results, $method);
  		
  	} else { //default is to download
	  	$this->load->dbutil();
	  	$this->load->helper('file');
	  	$this->load->helper('download');
	  	
	  	$config = array(
	  			'root'		=>	'root',
	  			'element'	=>	'element',
	  			'newline'	=>	"\n",
	  			'tab'		=>	"\t"
	  			);
	  	
	  	$data = $this->dbutil->xml_from_result($search_results, $config);
	  	
	  	$filename = "";
	  	foreach($terms as $term){
	  		$filename .= $term;
	  	}
	  	$filename .= "_variants_list" . ".xml";
	  	
	  	force_download($filename,$data);
	  	
  	}
  	
  }
  
  /**
   * _api_result_json_dload
   * forces a download of text data in a json format
   *
   * @author Nikhil Anand
   * @author Sean Ephraim
   * @author Rob Marini
   * @param CI_DB_MYSQL Object $search_results
   * 	contains the query and results and connecting information
   * @param string $terms 
   *    An array of (validated) search terms (used as a WHERE clause of MYSQL statement)
   * @param string $method 
   *    Download/display method
   * @param string $type 
   *    Type of search
   * @return void
   */
  public function _api_result_json_dload($search_results, $terms, $method, $type){
  	
  	// this is needed in order to modify headers
  	ob_end_clean();
  	
  	//Send headers depending on download method
  	if ($method == "download") {
  		header('Content-type: application/json');
  		header("Content-Disposition: attachment; filename=" . $this->strings['site_short_name'] . "-data." . $this->_api_iso_date() . ".json");
  		header("Pragma: no-cache");
  		header("Expires: 0"); //DANGEROUS AND MUST BE CHANGED
  	} else {
  		header('Content-type: text/plain');
  	}
  	
  	if(strcmp(strtolower($method), 'plain') == 0){
  	
  		print json_encode($search_results);
  		
  	} else { //default is to download
  		
  		$this->load->helper('file');
  		$this->load->helper('download');
  		$data = json_encode($search_results->result());
  		
  		$filename = "";
  		foreach($terms as $term){
  			$filename .= $term;
  		}
  		$filename .= "_variants_list" . ".json";
  		
  		force_download($filename,$data);
  		
  	}
  	
  }
  
  /**
   * Return variant list in VCF format. Will be used in Galaxy
   *
   * NOTE: This is only valid for "type=variantlist".
   *
   * @author Nikhil Anand
   * @author Sean Ephraim
   * @author Rob Marini
   * @param CI_DB_MYSQL Object $search_results
   * 	contains the query and results and connecting information
   * @param string $terms 
   *    An array of (validated) search terms (used as a WHERE clause of MYSQL statement)
   * @param string $method 
   *    Download/display method
   * @param string $type 
   *    Type of search
   * @return void
   */
  public function _api_result_vcf_dload($search_results, $terms, $method, $type) {
  	// this is needed in order to modify headers
  	ob_end_clean();
  	
  	//Send headers depending on download method
  	if ($method == "download") {
  		$prefix = strtolower($this->strings['site_full_name']);
  		$prefix = str_replace(array(' ', '.'), '-', $prefix);
  		header('Content-type: application/vcf');
  		header("Pragma: no-cache");
  		header("Expires: 0"); //DANGEROUS AND MUST BE CHANGED
  		header('Content-Disposition: attachment; filename="' . $prefix . '-' . $this->_api_iso_date() . '.vcf"');
  	} else {
  		header('Content-type: text/plain');
  	}
  	
  	// Standard VCF headers
  	$fields = $this->api_vars['variantlist'];
  	$output = "##fileformat=VCFv4.1\r\n";
  	foreach ($fields as $field) {
  		if ($field === "disease") {
  			$output .= "##INFO=<ID=$field,Number=.,Type=String,Description=\"\">";
  		}
  		else if ($field === "id") {
  			$output .= "##INFO=<ID=$field,Number=1,Type=Flag,Description=\"Variant ID, internal use only\">";
  		}
  		else {
  			$output .= "##INFO=<ID=$field,Number=1,Type=Flag,Description=\"\">";
  		}
  		$output .= "\r\n";
  	}
  	$output .= "#CHROM\tPOS\tID\tREF\tALT\tQUAL\tFILTER\tINFO\r\n";
  	print $output;
  	
  	// Iteratively build array and write to output
  	foreach ($search_results as $result) {
  		
  		preg_match('/(chr.*):(\d+)\:(\D{1})>(\D{1})/', $result["variation"], $variant_info);
  		
  		$vcf_id = $result["dbsnp"];
  		if (trim($result["dbsnp"]) == '')
  		{
  			$vcf_id = ".";
  		}
  		
  		// Fill in '.' (VCF standard) if no values exist for field
  		foreach ($result as $key => $value) {
  			if (trim($value) == '') {
  				$result[$key] = '.';
  			}
  		}
  		$info_string = "";
  		foreach ($fields as $field) {
  			$info_string .= "$field=" . $result[$field];
  			if ($field !== end($fields)) {
  				$info_string .= ";";
  			}
  		}
  		
  		$write_array = array(
  				$variant_info[1],
  				$variant_info[2],
  				$vcf_id,
  				$variant_info[3],
  				$variant_info[4],
  				".", // QUAL
  				".", // FILTER
  				$info_string
  		);
  		
  		if(strcmp(strtolower($method), 'plain') == 0){
  			
  			print implode($write_array, "\t") . "\n";
  			
  		} else { //default is to download
  		
  			$data = implode($write_array, "\t") . "\n";
  			
	  		$filename = "";
	  		foreach($terms as $term){
	  			$filename .= $term;
	  		}
	  		$filename .= "_variants_list" . ".vcf";
	  		
	  		force_download($filename,$data);
  		}
  		
  	}
  }
  
  /**
   * ------ For Plain print to screen results ------ 3/29/2018
   * Helper public function to display search results
   * Shows CSV or tab-delimited results 
   *
   * @author Nikhil Anand
   * @author Sean Ephraim
   * @param string $search_results 
   *    The array of search results
   * @param string $method 
   *    Download/display method
   * @param string $type 
   *    Type of search
   * @param string $delimiter 
   *    Any delimiter with which to separate results
   * @return void
   */
  public function _api_result_text($search_results, $method, $type, $delimiter) {
    // this is needed in order to modify headers
    ob_end_clean();
  
    //Send download headers depending on download method
    if ($method == "download") {
      if ($delimiter == ',') {
        $extension = ".csv";
      }
      else {
        $extension = ".txt";
      }
      $filename = $this->strings['site_short_name'] . "-data." . $this->_api_iso_date() . $extension;
      header("Content-Disposition: attachment; filename=$filename");
      header("Pragma: no-cache");
      $epoc1 = new DateTime('1970-01-01 12:00:00',new DateTimezone(date_default_timezone_get()));
      $expire_str = $epoc1->format('D, d M Y H:i:s');
      header("Expires: " . $expire_str);
    } else {
      header("Content-type: text/plain");
    }
  
    $output = '#';
  
    // Special case for genelist query
    if ($type == 'genelist') {
      $output .= implode($delimiter, $this->api_vars['genelist'])."\r\n";
    } 

    // Special case for variation list
    else if ($type == 'variantlist') {
      $output .= implode($delimiter, $this->api_vars['variantlist'])."\r\n";
    }
    
    // Add column headers for all others
    else {
      $output .= implode($delimiter, $this->variations_model->get_variant_fields()) . "\r\n";
    }
    
    // Using 'implode' would have been neater, but we need to specify NULL for missing values
    foreach ($search_results as $result => $columns) {
      if (is_array($columns)) {
        foreach ($columns as $column) {
          if (trim($column) == '') {
            $output .= "NULL";
          } else {
            $output .= $column;
          }
          $output .= $delimiter;
        }
      }
  
      // Remove the last delimiter and add a newline
      $output = substr($output, 0, -1); 
      $output .= "\r\n";
    }

    print $output;
  }
  
  /**
   * ------ For Plain print to screen results ------ 3/29/2018
   * Helper public function to display search results
   * Shows XML-formatted results
   *
   * @author Sean Ephraim
   * @author Nikhil Anand
   * @param string $search_results 
   *    The array of search results
   * @param string $method 
   *    Download/display method
   * @return void
   */
  public function _api_result_xml($search_results, $method) {
    // this is needed in order to modify headers
    ob_end_clean();
  
    //Send headers depending on download method
    if ($method == "download") {
      header('Content-type: application/xml');
      header("Pragma: no-cache");
      header("Expires: 0");
    } else {
      header('Content-type: text/plain');
    }
  
    // Create a new DOM object
    $output = new DOMDocument();
    $output->formatOutput = true;
  
    // Create the root element
    $root = $output->createElement("results");
    $output->appendChild($root);
  
    // Iterate through results; create children
    // http://css.dzone.com/news/creating-xml-documents-php
    foreach ($search_results as $search_value => $columns) {
      
      $result = $root->appendChild($output->createElement("result"));
      $result->appendChild($output->createAttribute("value"))->appendChild($output->createTextNode($search_value));
  
      if (is_array($columns)) {
        foreach ($columns as $column => $value) {
          $result->appendChild($output->createElement($column, $value));
        }
      }
    }
  
    echo $output->saveXML();
  }
  
  /**
   * Get the ISO8601-formatted date
   *
   * @author Nikhil Anand
   * @param string $time 
   *    If specified, this timestamp is ISO8601-formatted. 
   *    If not, current time is returned
   * @return void
   */
  public function _api_iso_date($time=false) {
      if(!$time) $time=time();
      return date("Y-m-d", $time) . 'T' . date("H:i:s", $time);
  }
}

/* End of file api_model.php */
/* Location: ./application/models/api_model.php */
