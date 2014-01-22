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
<!--

  And what precisely did you expect to find here?

-->

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
        </ul>
        <ul id="sidebar-version">
          <li>Database Version <?php echo $version; ?></li>
          <li>Updated <?php echo $update_date ; ?></li>
        </ul>
      </div>
    </fieldset>
    <fieldset class="sidebar-collapsible">
      <legend>Glossary</legend>
      <div id="sidebar-sorters-glossary">
        <dl>
          <dt>Pathogenic</dt>
          <dd>mutation published in the literature as causing disease</dd>
          <dt>Probably Pathogenic</dt>
          <dd>classified as such by the dbSNP database</dd>
          <dt>Unknown Significance</dt>
          <dd>variant reported in dbSNP without a disease association</dd>
        </dl>
      </div>
    </fieldset>
  </div><!-- #sidebar-sorters -->
</div><!-- #sidebar -->

<div id="corpus"> <!-- begin corpus -->
  <?php echo display_proper_logo(); ?>
  <div id="content"> <!-- begin content -->
