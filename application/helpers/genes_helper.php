<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('gene_link_to_api'))
{
  /**
  * Gene Link To API
  *
  * Returns a URI to the API
  *
  * @author   Sean Ephraim
  * @author   Rob Marini
  * @access   public
  * @param    string  $gene
  *    Gene name
  * @param    string  $format
  *    API output format (e.g. csv, tab, json, xml)
  * @return   string   HTML string
  */
  function gene_link_to_api($gene, $format, $method = 'plain')
  {
    return site_url("api?type=gene&amp;terms=$gene&amp;method=$method&amp;format=$format");
  }
}

/* End of file genes_helper.php */
/* Location: ./application/helpers/genes_helper.php */  
