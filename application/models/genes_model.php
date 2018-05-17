<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Genes_model extends MY_Model {
	/**
	 * Holds an array of tables used.
	 *
	 * @var array
	 */
  public $tables = array();

	public function __construct() {
		parent::__construct();
    $this->load->config('variation_database');

		//initialize db tables data
		$this->tables = $this->config->item('tables');
	}

  /**
   * Get Genes
   *
   * Get a list of all genes in the variation database.
   * A first letter may be provided to only get the
   * genes that start with that letter.
   *
   * @author Sean Ephraim
   * @access public
   * @param string $f_letter
   *    First letter of the gene
   * @param boolean $include_queue_genes
   *    Include/exclude the genes that are only in the queue
   * @param string $table
   *    Table from which to retrieve genes (default: 'variant_count')
   * @return array Gene names
   */
  public function get_genes($f_letter = NULL, $include_queue_genes = TRUE, $table = NULL) {
    // Only get genes of a certain letter
    if ($f_letter) {
      $this->db->like('gene', $f_letter, 'after');
    }

    if ($table === NULL) {
      $table = $this->tables['variant_count'];
    }

    $query = $this->db->distinct()
                      ->select('gene')
                      ->get($table);

    // Build array of gene names from result
    $genes = array();
    foreach ($query->result() as $row) {
      if ( ! empty($row->gene)) {
        $genes[] = $row->gene;
      }
    }

    if ($include_queue_genes) {
      // Include genes in the queue as well
      if ($f_letter) {
        $this->db->like('gene', $f_letter, 'after');
      }
      $query = $this->db->distinct()
                        ->select('gene')
                        ->get($this->tables['vd_queue']);
  
      foreach ($query->result() as $row) {
        if ( ! empty($row->gene)) {
          $genes[] = $row->gene;
        }
      }
    }

    $genes = array_unique($genes);
    sort($genes);
    return $genes;
  }

  /**
   * Get Genes And Aliases
   *
   * Get a list of all genes and their aliases in the variation
   * database. Aliases for genes that don't have an alias
   * will be NULL. A first letter may be provided to only
   * get the genes that start with that letter.
   *
   * Example return:
   *   $genes = Array(
   *     'DCDC2'  = NULL,
   *     'DFNA5'  = NULL,
   *     'DFNB31' = 'WHRN',
   *     'DFNB59' = 'PJBK',
   *     'DIABLO' = NULL,
   *   );
   *
   * @author Sean Ephraim
   * @access public
   * @param string $f_letter
   *    First letter of the gene
   * @param boolean $include_queue_genes
   *    Include/exclude the genes that are only in the queue
   * @param string $table
   *    Table from which to retrieve genes (default: 'variant_count')
   * @return array Gene names (i.e. keys) and their aliases (i.e. values)
   */
  public function get_genes_and_aliases($f_letter = NULL, $include_queue_genes = TRUE, $table = NULL) {
    // Only get genes of a certain letter
    if ($f_letter) {
      $this->db->like('gene', $f_letter, 'after');
    }

    if ($table === NULL) {
      $table = $this->tables['variant_count'];
    }

    $query = $this->db->distinct()
                      ->select('gene, gene_alias')
                      ->get($table);

    // Build array of gene names from result
    $genes = array();
    foreach ($query->result() as $row) {
      if ( ! empty($row->gene)) {
        $genes[$row->gene] = $row->gene_alias;
      }
    }

    if ($include_queue_genes) {
      // Include genes in the queue as well
      if ($f_letter) {
        $this->db->like('gene', $f_letter, 'after');
      }
      $query = $this->db->distinct()
                        ->select('gene, gene_alias')
                        ->get($this->tables['vd_queue']);
  
      foreach ($query->result() as $row) {
        if ( ! empty($row->gene)) {
          $genes[$row->gene] = $row->gene_alias;
        }
      }
    }

    
    ksort($genes);
    return $genes;
  }
  
  /**
   * Get Gene's Alias
   *
   * Get a gene's alias. If a gene has no alias, returns a NULL.
   *
   * Example return: '' or 'NULL'
   *
   * @author Rob Marini
   * @access public
   * @param string $gene
   *    a gene name
   * @param boolean $include_queue_genes
   *    Include/exclude the genes that are only in the queue
   * @param string $table
   *    Table from which to retrieve genes (default: 'variant_count')
   * @return string alias
   */
  public function get_gene_alias($gene, $table = NULL) {
  	// Only get genes of a certain letter
  	
  	if ($table === NULL) {
  		$table = $this->tables['variant_count'];
  	}
  	
  	$query = $this->db->distinct()
  	->select('gene_alias')
  	->get_where($table,array('gene'=>$gene));
  	
  	$alias = $query->result()[0]->gene_alias;
  	
  	return $alias;
  }
  
}

/* End of file genes_model.php */
/* Location: ./application/models/genes_model.php */
