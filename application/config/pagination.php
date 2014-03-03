<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
|--------------------------------------------------------------------------
| Pagination Configuration
|--------------------------------------------------------------------------
|
| Refer to
| http://ellislab.com/codeigniter/user-guide/libraries/pagination.html
| for more information on CodeIgniter's pagination system.
|
| Refer to
| http://getbootstrap.com/2.3.2/components.html#pagination
| for more information on Bootstrap's pagination system.
|
*/
$config['use_page_numbers'] = TRUE;
$config['uri_segment'] = 4;
$config['num_links'] = 6;
// Configure all tags to comply with Bootstrap's pagination
$config['full_tag_open'] = '<div class="pagination"><ul>';
$config['full_tag_close'] = '</ul></div>';
$config['first_tag_open'] = '<li>';
$config['first_tag_close'] = '</li>';
$config['last_tag_open'] = '<li>';
$config['last_tag_close'] = '</li>';
$config['next_link'] = '&raquo;';
$config['next_tag_open'] = '<li>';
$config['next_tag_close'] = '</li>';
$config['prev_link'] = '&laquo;';
$config['prev_tag_open'] = '<li>';
$config['prev_tag_close'] = '</li>';
$config['cur_tag_open'] = '<li class="active"><a href="#">';
$config['cur_tag_close'] = '</a></li>'; 
$config['num_tag_open'] = '<li>';
$config['num_tag_close'] = '</li>';

/* End of file pagination.php */
/* Location: ./application/config/pagination.php */
