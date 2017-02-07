<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title><?php echo $site_full_name; ?></title>
    <link rel="stylesheet" href="<?php echo site_url("assets/public/css/jquery.tipsy.css"); ?>" type="text/css" media="screen"/>
    <link rel="stylesheet" href="<?php echo site_url("assets/public/css/jquery.shadow.css"); ?>" type="text/css" media="screen"/>
    <link rel="stylesheet" href="<?php echo site_url("assets/public/css/styles.css"); ?>" type="text/css" media="screen"/>
    <link rel="stylesheet" href="<?php echo site_url("assets/public/css/override.css"); ?>" type="text/css" media="screen"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  </head>
  <body>
    <?php include_analytics(); ?>
    <div id="sidebar">
      <div id="logo"><a href="<?php echo base_url(); ?>" id="logo-splash"><span><?php echo $site_full_name; ?></span></a></div>
      <div id="sidebar-sorters">
      	<div id="sidebar-sorters-alphabet">
    		  <?php echo print_letter_table(); ?>
    	  </div>
        <fieldset class="sidebar-collapsible">
          <legend>Information</legend>
          <div id="sidebar-sorters-information">
            <ul>
              <li><a href="<?php echo base_url(); ?>" id="about">About</a></li>
              <li><a href="<?php echo site_url('help'); ?>" id="help">How to use this site</a></li>
              <li><a href="<?php echo site_url('doc'); ?>" id="api">API Documentation</a></li>
              <li><a href="javascript://" class="contact-popup">Contact Us</a></li>
              <li>
              	<!-- trying to make search bar -->
              	<br/><br/>
		        <form name="positionSearchBar" method="get" action="positionSearch">
		      		Search for Variant by Position: <input name="searchPosition" value="" type="text" size="20" maxlength="40"/>
		      		<br/>
		      		<input type="submit" name="submit" value="Search"/>
		      	</form>
		        <!-- end of trying ot make searc bar section -->
              </li>
            </ul>
            <ul id="sidebar-version">
              <li>Database Version <?php echo $versionId; ?></li> <!-- $version -->
              <li>Updated <?php echo $update_date ; ?></li>
            </ul>
          </div>
          
          
          
        </fieldset>
      </div><!-- #sidebar-sorters -->
    </div><!-- #sidebar -->
    
    <div id="corpus"> <!-- begin corpus -->
      <?php echo display_proper_logo(); ?>
      <div id="content"> <!-- begin content -->
