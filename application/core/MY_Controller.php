<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

  public $public_layout;
  public $editor_layout;
  public $version;
  public $user;

	public function __construct()
  {
    parent::__construct();
    $strings = $this->config->item('strings');

    // Variables for ALL controllers
    $this->public_layout = "layouts/public/master";
    $this->editor_layout = "layouts/editor/master";
		$this->version = $this->variations_model->get_db_version_num();

    // Variables for ALL views
    $data = new stdClass();
    $data->version = $this->variations_model->get_db_version_num(TRUE);
		$data->update_date = date("j M Y", strtotime($this->variations_model->get_last_update_date()));
    $data->site_full_name = $strings['site_full_name'];
    $data->site_short_name = $strings['site_short_name'];
    $data->footer_info = $strings['footer_info'];

    if($this->ion_auth->logged_in()) {
      // Make $this->user available to all controllers
      $this->user = $this->ion_auth->user()->row();

      // Make $user available to all views
      $data->user = $this->user;
    }
    $this->load->vars($data); // Load variables for all views
  }
}

/* End of file MY_Controller.php */
/* Location: ./application/core/MY_Controller.php */
