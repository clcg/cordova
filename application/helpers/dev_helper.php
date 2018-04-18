<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('dev_print'))
{

	/**
	 * dev_print_stop
	 * 	provides information as a basic html page and stops current operations
	 * 
	 * @author Rob Marini
	 * @access public
	 * @return (None)
	 * 
	*/
	function dev_print_stop($somethingToSee, $note = " "){
		print "<pre>";
		print_r($note);
		print("</br>");
		print("**************************************");
		print("</br>");
		print_r($somethingToSee);
		print "</pre>";
		die();
	}
	
}
/* End of file dev_helper.php */
/* Location: ./application/helpers/dev_helper.php */