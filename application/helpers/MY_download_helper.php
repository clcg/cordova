<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * generate_download_header
 *
 * Generates headers that force a download to happen with chunking
 *
 * @author	Rob Marini
 * @access    public
 * @param    
 * @param    
 * @return    Boolean
 */
if ( ! function_exists('generate_download_header'))
{
	function generate_download_header($filename='', $extension='', $dloadsize='')
	{
		if($filename == '' OR $extension == '' OR $dloadsize == ''){
			return FALSE; //header generation failed
		} else {
			
			// Load the mime types
			@include(APPPATH.'config/mimes'.EXT);
	
			// Set a default mime if we can't find it
			if ( ! isset($mimes[$extension]))
				{
				$mime = 'application/octet-stream';
			}
			else
				{
				$mime = (is_array($mimes[$extension])) ? $mimes[$extension][0] : $mimes[$extension];
			}
	
			// Generate the server headers
			$epoc1 = new DateTime('1970-01-01 12:00:00',new DateTimezone(date_default_timezone_get()));
			$expire_str = $epoc1->format('D, d M Y H:i:s');
			
			if (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE") !== FALSE)
				{
				header('Content-Type: "'.$mime.'"');
				header('Content-Disposition: attachment; filename="'.$filename.'"');
				header('Expires: ' . $expire_str);
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header("Content-Transfer-Encoding: binary");
				header('Pragma: public');
				header("Content-Length: ".$filesize);
			}
			else
				{
				header('Content-Type: "'.$mime.'"');
				header('Content-Disposition: attachment; filename="'.$filename.'"');
				header("Content-Transfer-Encoding: binary");
				header('Expires: ' . $expire_str);
				header('Pragma: no-cache');
				header("Content-Length: ".$filesize);
			}
			
			return TRUE;
		}
	}
}

/**
 * prog_load_query_results
 *
 * 
 *
 * @author	Rob Marini
 * @access    public
 * @param
 * @param
 * @return	CI_DB_Object		$result
 */
if ( ! function_exists('prog_load_query_results'))
{
	function prog_load_query_results($query, $lower_lim, $num_rows, $return_as_array = FALSE){
		
		$result = $query->limit($num_rows)
						->offset($lower_lim)
						->get();
		if($return_as_array){
			$result = $result->result_array();
		}
		return($result);
	}
}

/**
 * force_prog_dload
 *
 *
 *
 * @author	Rob Marini
 * @access    public
 * @param	$query	a cI_DB_query object with the raw-total query (no limits)
 * @param	$chunk_size	the number or rows allowed to be held in memory at any 1 time
 * @param	$bounds	a 2 item array containing the lower [0], and upper [1] bounds. 
 * 				used to bound the searchs, and provide a starting and stopping point
 * 				for downloads
 * @param	dload_filename	a string indicating the name of the downloaded file 
 * @return
 */
if ( ! function_exists('force_prog_dload'))
{
	function force_prog_dload($query, $chunk_size, $bounds, $filename, $col_delim, $newline){
		
		if($bounds[1] >= $bounds[0]){
			//valid bounds
			
			//start stream
			$extension = end(explode($filename,"."));
			
			// Load the mime types
			@include(APPPATH.'config/mimes'.EXT);
			
			// Set a default mime if we can't find it
			if ( ! isset($mimes[$extension]))
			{
				$mime = 'application/octet-stream';
			}
			else
			{
				$mime = (is_array($mimes[$extension])) ? $mimes[$extension][0] : $mimes[$extension];
			}
			
			// Generate the server headers
			$datetime_cur = new DateTime();
			$datetime_cur->setTimestamp(time() - (60 * 60)); //set for an hour before download happens so that it is not cached and keeps the page relevant for search engines
			$expire_str = $datetime_cur->format('D, d M Y H:i:s');
			
			if (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE") !== FALSE)
			{
				header('Content-Type: "'.$mime.'"');
				header('Content-Disposition: attachment; filename="'.$filename.'"');
				header('Expires: ' . $expire_str);
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header("Content-Transfer-Encoding: binary");
				header('Pragma: no-cache'); //public
			}
			else
			{
				header('Content-Type: "'.$mime.'"');
				header('Content-Disposition: attachment; filename="'.$filename.'"');
				header("Content-Transfer-Encoding: binary");
				header('Expires: ' . $expire_str);
				header('Pragma: no-cache');
			}
				
			//******************************
			
			$i = $bounds[0];
			$continue_loop = TRUE;
			
			$orig_query = clone($query);
			
			while($continue_loop){
				if($i > $bounds[1] && ($i - $bounds[1] <= $chunk_size)){
					//getting the last results
					$i = $bounds[1];
					$continue_loop = FALSE;
				} elseif ($i > $bounds[1]){
					//done getting results
					$i = $bounds[1];
					$continue_loop = FALSE;
					break;
				}
				
				$results = prog_load_query_results($query, $i, $chunk_size, TRUE); //get chunk sized results from mysql
				
				$query = clone($orig_query); //resets query to the original CI_DB_mysql_driver object
				
				if(($i === $bounds[0]) && (sizeof($results) > 0)){
					//clean up confusing child elements' pubmed_id lists for all "parent" array elements
					$i2 = 0;
					while($i2 < sizeof($results)){
						$results[$i2]['pubmed_id'] = str_replace(",","|",$results[$i2]['pubmed_id']);
						foreach($results[$i2] as $key => $value){
							$results[$i2][$key] = str_replace(","," -",$value);
						}
						$i2 += 1;
					}
					
					//write header
					$keys = array_keys($results[0]);
					$buffer = implode($col_delim,$keys) . $newline;
					
				} else {
					if(sizeof($results) > 0){
						//clean up confusing child elements' pubmed_id lists for all "parent" array elements
						$i2 = 0;
						while($i2 < sizeof($results)){
							$results[$i2]['pubmed_id'] = str_replace(",","|",$results[$i2]['pubmed_id']);
							foreach($results[$i2] as $key => $value){
								$results[$i2][$key] = str_replace(","," -",$value);
							}
							$i2 += 1;
						}

					} elseif(sizeof($results) === 0){
						$continue_loop = FALSE;
						break;
					}
					$buffer = "";
				}
				
				foreach ($results as $result){
					$buffer .= implode($col_delim,$result) . $newline;
				}
							
// 				$buffer .= $newline . "i = ". strval($i);
// 				print_r($newline . "i = ". strval($i) . $newline);

				//stream the buffer, flush it
				echo $buffer;
				ob_flush();
				flush();
				
				//increment
				$i += $chunk_size;
				
			}
			
			//end of stream
		}
		
		exit;
		
	}
}

/**
 * force_download_file_chunked
 *
 * Generates headers that force a download to happen
 *
 * @author	Unknown
 * @access    public
 * @param    string    filename
 * @param    mixed    the data to be downloaded
 * @return    void
 */
if ( ! function_exists('force_download_file_chunked'))
{
    function force_download_file_chunked($filename = '', $file = '')
    {
        if ($filename == '' OR $file == '')
        {
            return FALSE;
        }

        // Try to determine if the filename includes a file extension.
        // We need it in order to set the MIME type
        if (FALSE === strpos($filename, '.'))
        {
            return FALSE;
        }

        // Grab the file extension
        $x = explode('.', $filename);
        $extension = end($x);

        // Load the mime types
        @include(APPPATH.'config/mimes'.EXT);

        // Set a default mime if we can't find it
        if ( ! isset($mimes[$extension]))
        {
            $mime = 'application/octet-stream';
        }
        else
        {
            $mime = (is_array($mimes[$extension])) ? $mimes[$extension][0] : $mimes[$extension];
        }

        // Generate the server headers
        if (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE") !== FALSE)
        {
            header('Content-Type: "'.$mime.'"');
            header('Content-Disposition: attachment; filename="'.$filename.'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header("Content-Transfer-Encoding: binary");
            header('Pragma: public');
            header("Content-Length: ".filesize($file));
        }
        else
        {
            header('Content-Type: "'.$mime.'"');
            header('Content-Disposition: attachment; filename="'.$filename.'"');
            header("Content-Transfer-Encoding: binary");
            header('Expires: 0');
            header('Pragma: no-cache');
            header("Content-Length: ".filesize($file));
        }

        readfile_chunked($file);
        exit;
    }
}

/**
 * readfile_chunked
 *
 * Reads file in chunks so big downloads are possible without changing PHP.INI
 *
 * @author	Unknown
 * @access    public
 * @param    string    file
 * @param    boolean    return bytes of file
 * @return    void
 */
if ( ! function_exists('readfile_chunked'))
{
    function readfile_chunked($file, $retbytes=TRUE)
    {
       $chunksize = 1 * (1024 * 1024);
       $buffer = '';
       $cnt =0;

       $handle = fopen($file, 'r');
       if ($handle === FALSE)
       {
           return FALSE;
       }

       while (!feof($handle))
       {
           $buffer = fread($handle, $chunksize);
           echo $buffer;
           ob_flush();
           flush();

           if ($retbytes)
           {
               $cnt += strlen($buffer);
           }
       }

       $status = fclose($handle);

       if ($retbytes AND $status)
       {
           return $cnt;
       }

       return $status;
    }
}

/* End of file MY_download_helper.php */
/* Location: ./application/helpers/MY_download_helper.php */