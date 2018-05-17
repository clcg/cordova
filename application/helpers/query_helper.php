<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * get_query_result_size
 * 
 * tests a query to see how many rows will be returned if the query was
 * made in full.
 * 
 * @author	Rob Marini
 * @access	public
 * @param	CI_DB_Query object
 * @return	array of integers
 */

if ( ! function_exists('get_query_result_size'))
{
	function get_query_result_size($query){
		$result_sizes = array();
		foreach($query->select('COUNT(id)')->get()->result_array()[0] as $key => $value){
			$result_sizes[] = $value;
		}
		
		return($result_sizes);
	}
}


// /**
//  * get_query_index
//  *
//  * (under construction)
//  *
//  * @author	Rob Marini
//  * @access	public
//  * @param	CI_DB_Query object
//  * @return	
//  */

// if ( ! function_exists('get_query_index'))
// {
// 	function get_query_index($query){
// 		$indices = array();
// 		foreach($query->select('id')->get()->result_array() as $key => $value){
// 			$indices[] = $value;
// 		}
		
// 		//compress id's into ranges
		
		
// 		return($result_sizes);
// 	}
// }

// /**
//  * compress_query_index
//  *
//  * (under construction)
//  *
//  * @author	Rob Marini
//  * @access	public
//  * @param	CI_DB_Query object
//  * @return	
//  */

// if ( ! function_exists('compress_query_index'))
// {
// 	function compress_query_index($query_index){
// 		$result_sizes = array();
		
// 		//compress id's into ranges
		
// 		return($result_sizes);
// 	}
// }

// /**
//  * construct_query_idx_range
//  *
//  * (under construction)
//  *
//  * @author	Rob Marini
//  * @access	public
//  * @param	CI_DB_Query object
//  * @return	array of integers
//  */

// if ( ! function_exists('construct_query_idx_range'))
// {
// 	function construct_query_idx_range($orig_query, $chunk_size){
		
		
// 		//compress id's into ranges
		
// 		return();
// 	}
// }
//

/* End of file query_helper.php */
/* Location: ./application/helpers/query_helper.php */