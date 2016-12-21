<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

/* Authentication */
$route['auth'] = 'auth/index';
$route['editor'] = 'auth/index';
$route['login'] = 'auth/login';
$route['logout'] = 'auth/logout';
$route['logs'] = 'auth/logs';
$route['forgotpassword'] = 'auth/forgot_password';
$route['changepassword'] = 'auth/change_password';
$route['resetpassword'] = 'auth/reset_password';
$route['groups'] = 'auth/groups';
$route['creategroup'] = 'auth/create_group';
$route['deletegroup'] = 'auth/create_group';
$route['editgroup'] = 'auth/edit_group';
$route['edituser'] = 'auth/edit_user';
$route['createuser'] = 'auth/create_user';
$route['deleteuser'] = 'auth/delete_user';
$route['deactivateuser'] = 'auth/deactivate_user';
/* External Authentication */
$route['auth/ext/(:any)'] = 'auth/external_auth/$1';
/* Genes Editor */
$route['genes/letters'] = 'genes/letters';
$route['genes/(:any)'] = 'genes/show_genes/$1';
$route['genes'] = 'genes/show_genes';
/* Variations Editor */
$route['variations/view/(:any)'] = 'variations/view/$1';
$route['variations/edit/(:any)'] = 'variations/edit/$1';
$route['variations/add'] = 'variations/add';
$route['variations/upload_genes'] = 'variations/upload_genes';
$route['variations/query_public_database'] = 'variations/query_public_database';
$route['variations/query_public_database/(:any)'] = 'variations/query_public_database/$1';
$route['variations/submit'] = 'variations/submit_changes';
$route['variations/unreleased'] = 'variations/show_unreleased';
$route['variations/unreleased/(:any)'] = 'variations/show_unreleased/$1';
$route['variations/unreleased/(:any)/(:any)'] = 'variations/show_unreleased/$1/$2';
$route['variations/(:any)'] = 'variations/show_variants/$1';
$route['variations'] = 'variations/index';
/* Public Pages */
$route['doc'] = 'pages/doc';
$route['letter/(:any)'] = 'variations/letter/$1';
$route['gene/(:any)'] = 'variations/variations_table/$1';
$route['variant/freq'] = 'variations/frequency';
$route['variant/(:any)'] = 'variations/show_variant/$1';
$route['pdf/(:any)'] = 'variations/download_variant_pdf/$1';
$route['api'] = 'api/index';
$route['email'] = 'email/index';

/* Controller/function route (w/ 2 parameters) */
$route['(:any)/(:any)/(:any)/(:any)'] = '$1/$2/$3/$4';
/* Controller/function route (w/ 1 parameter) */
$route['(:any)/(:any)/(:any)'] = '$1/$2/$3';
/* Controller/function route */
$route['(:any)/(:any)'] = '$1/$2';

/* Static pages */
$route['(:any)'] = 'pages/view/$1';

/* Default home page */
$route['default_controller'] = 'pages/view';

/* End of file routes.php */
/* Location: ./application/config/routes.php */
