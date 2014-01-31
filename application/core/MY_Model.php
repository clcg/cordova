<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Model extends CI_Model {

  public $version;

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
    $this->load->config('variation_database');

    // Initialize database tables (if they don't exist)
    if ( ! $this->db->table_exists('versions')) {
      $this->load->library('migration');
      if ( ! $this->migration->latest())
      {
        show_error($this->migration->error_string());
      }
      $html = 'Database tables have been intialized.';
      $this->session->set_flashdata('success', $html);
//      redirect($this->uri->uri_string()); // reload the page
    }

		// Database version number
		$this->version = $this->get_db_version_num();

    // Set the appropriate production tables (latest version)
    $this->set_vd_live_table();
    $this->set_vd_queue_table();
    $this->set_reviews_table();
    $this->set_variant_count_table();
	}

  /**
   * Get DB Version Num
   *
   * Returns the version number for the latest database if
   * $config['vd_version'] is set to 0. Otherwise the specified
   * version will be used.
   *
   * @author Sean Ephraim
   * @access public
   * @return int      Correct version of the variation database
   */
  public function get_db_version_num() {
    $version = $this->config->item("vd_version");
    if ($version === 0) {
      // Automatically determine latest version
      $tables = $this->config->item("tables");
      $query = $this->db
                    ->select_max('version')
                    ->limit(1)
                    ->get($tables['versions']);
      return $query->row()->version;
    }
    else {
      $html = 'WARNING: your database has not been configured to automatically use the latest version. Until this is changed, you may be using an older version of the database.';
      $this->session->set_flashdata('warning', $html);
      return $version;
    }
  }

  /**
   * Get All DB Version Info
   *
   * Returns the versioning info for the variation database.
   *
   * @author Sean Ephraim
   * @access public
   * @return object  All DB version info
   */
  public function get_all_db_version_info() {
    $tables = $this->config->item("tables");
    $query = $this->db
                  ->get($tables['versions']);
    return $query->result();
  }

  /**
   * Set VD Live Table
   *
   * Sets the proper name of the variations table by determining the
   * latest version of the database.
   *
   * Read more about it in application/config/variation_database.php.
   *
   * @author Sean Ephraim
   * @access public
   */
  public function set_vd_live_table() {
    // Load table name from config file
    $tables = $this->config->item("tables");
    $vd_live = $tables['vd_live'];
    $vd_prefix = $this->config->item("vd_prefix");

    $default = 'variations'; // default table name

    if ($vd_live == $default || $vd_live == '') {
      // Update the table name
      $tables['vd_live'] = $default.'_'.$this->version;
      $this->config->set_item('tables', $tables);
    }
  }

  /**
   * Set VD Queue Table
   *
   * Sets the proper name of the variations queue table by determining the
   * latest version of the database.
   *
   * Read more about it in application/config/variation_database.php.
   *
   * @author Sean Ephraim
   * @access public
   */
  public function set_vd_queue_table() {
    // Load table name from config file
    $tables = $this->config->item("tables");
    $vd_queue = $tables['vd_queue'];
    $vd_prefix = $this->config->item("vd_prefix");

    $default = 'variations_queue'; // default table name

    if ($vd_queue == $default || $vd_queue == '') {
      // Update the table name
      $tables['vd_queue'] = $default.'_'.$this->version;
      $this->config->set_item('tables', $tables);
    }
  }

  /**
   * Set Reviews Table
   *
   * Sets the proper name for the variant reviews table by determining the
   * latest version of the database.
   *
   * Read more about it in application/config/variation_database.php.
   *
   * @author Sean Ephraim
   * @access public
   */
  public function set_reviews_table() {
    // Load table name from config file
    $tables = $this->config->item("tables");
    $table = $tables['reviews'];

    if ($table == 'reviews' || $table == '') {
      // Update the table name
      $tables['reviews'] = 'reviews_'.$this->version;
      $this->config->set_item('tables', $tables);
    }
  }

  /**
   * Set Variant Count Table
   *
   * Sets the proper name for the variant count table by determining the
   * latest version of the database.
   *
   * Read more about it in application/config/variation_database.php.
   *
   * @author Sean Ephraim
   * @access public
   */
  public function set_variant_count_table() {
    // Load table name from config file
    $tables = $this->config->item("tables");
    $table = $tables['variant_count'];

    if ($table == 'variant_count' || $table == '') {
      // Update the table name
      $tables['variant_count'] = 'variant_count_'.$this->version;
      $this->config->set_item('tables', $tables);
    }
  }

  /**
   * Get Last Update Date
   *
   * Returns the correct date for when the database was last updated.
   *
   * @author Sean Ephraim
   * @access public
   */
  public function get_last_update_date() {
    $tables = $this->config->item("tables");
    $query = $this->db
                  ->get_where($tables['versions'], array('version' => $this->version), 1);
    return $query->row()->updated;
  }

  /**
   * Get New Version Name
   *
   * Returns the name of the next version of a table based on
   * the current name.
   *
   * For example:
   *    if current name = 'dvd_1'
   *    then next name  = 'dvd_2'
   *
   * @author Sean Ephraim
   * @access public
   * @return string Next version of a table name
   */
  public function get_new_version_name($cur_name, $latest_version = NULL) {
    if ($latest_version === NULL) {
      // Get latest version
      $tables = $this->config->item("tables");
      $query = $this->db
                    ->select_max('version')
                    ->limit(1)
                    ->get($tables['versions']);
      $latest_version = $query->row()->version;
    }
    // take the name apart
    $parts = explode('_', $cur_name);
    // remove the last part (the number)
    array_pop($parts);
    // increment latest version num. and put it back on
    array_push($parts, $latest_version+1);
    // put the name back together
    $next_name = join('_', $parts);
    return $next_name;
  }

  /**
   * Copy Table
   *
   * Makes an exact copy of a table (structure and data).
   * To only copy the structure (and not the data), set the
   * 3rd parameter to FALSE.
   *
   * @author Sean Ephraim
   * @access public
   * @param  string   Name of the source table
   * @param  string   Name of the target (new) table
   * @param  boolean  (optional) Copy the data into the new table
   * @return boolean  TRUE on success, else FALSE
   */
  public function copy_table($source, $target, $include_data = TRUE) {
    if ($this->db->table_exists($target)) {
      return FALSE;     
    }

    // Sanitize input
    $source = mysql_real_escape_string(stripslashes($source));
    $target = mysql_real_escape_string(stripslashes($target));
    $this->db->query("CREATE TABLE $target LIKE $source");
    if ($include_data) {
      $this->db->query("INSERT INTO $target SELECT * FROM $source");
    }
    return TRUE;
  }

  /**
   * Empty Table
   *
   * Remove all data from a table, leaving structure intact.
   *
   * @author Sean Ephraim
   * @access public
   * @param string Name of the source table
   */
  public function empty_table($table) {
    $this->db->empty_table($table);
  }
}

/* End of file MY_Model.php */
/* Location: ./application/core/MY_Model.php */
