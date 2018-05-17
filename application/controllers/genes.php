<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Genes extends MY_Controller {

  public function __construct() {
    parent::__construct();
		$this->load->model('genes_model');
  }

  /**
   * Index
   */
  public function index() {
    redirect('/');
  }

  /**
   * Letters
   *
   * Show all first letters of genes in the database.
   */
  public function letters() {
    redirect_all_nonmembers();

    $data['title'] = 'Letters';
    $data['content'] = 'genes/letters';

    $data['letters'] = range('A', 'Z');

    $this->load->view($this->editor_layout, $data);
  }

  /**
   * Show Genes
   *
   * Display all genes in the database, or only genes beginning
   * with a certain letter.
   *
   * @param char First letter of desired gene
   */
  public function show_genes($f_letter = NULL) {
    redirect_all_nonmembers();

    $f_letter = ucfirst($f_letter);
    $data['title'] = ($f_letter) ? 'Genes - ' . $f_letter : 'Genes';
    $data['content'] = 'genes/genes';

    $data['genes'] = $this->genes_model->get_genes($f_letter);
    $data['letters'] = range('A', 'Z');

    $this->load->view($this->editor_layout, $data);
  }
  
  /**
   * Gene Page
   * 
   * Display a gene of interest and its information including its 
   * 	variant list.
   * 
   * @author Rob Marini
   * @access public
   * @param string $gene
   * 	The gene
   * @return void
   * 
   */
	public function gene_page($gene) {
		$data['title'] = $gene;
		$data['content'] = 'genes/gene_page';
		
		$data['gene'] = $gene;
		$this->load->model('genes_model');
		$this->load->helper('genes');
		$alias = $this->genes_model->get_gene_alias($gene);
		
		# Format genes names to display as "GENE (ALIAS)", or just "GENE" if no alias
		if($alias !== NULL){
			$data['display_name'] = "$gene ($alias)";
		} else {
			$data['display_name'] = $gene;
		}
		
		$this->load->view($this->public_layout,$data);
	}
  
}




