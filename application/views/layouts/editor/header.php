<!DOCTYPE HTML>
<html>
<head>
  <meta charset="utf-8">
  <title><?php echo $title ?> | <?php echo $site_full_name; ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="">

  <link rel="stylesheet" href="<?php echo site_url("assets/editor/css/bootstrap.min.css"); ?>" type="text/css" media="screen">
  <link rel="stylesheet" href="<?php echo site_url("assets/editor/css/bootstrap-rowlink.min.css"); ?>" type="text/css" media="screen">
  <link rel="stylesheet" href="<?php echo site_url("assets/editor/css/master.css"); ?>" type="text/css" media="screen">

  <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
  <!--[if lt IE 9]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
  <![endif]-->
</head>
<body>
    <?php echo admin_toolbar(); ?>
  <div id="masthead-wrapper" class="container rounded">
    <div id="masthead">
	    <h1><?php echo $site_full_name; ?> | Editor</h1>
      <ul id="nav" class="nav nav-pills">
        <li><a href="<?php echo base_url(); ?>"><i class="icon-home icon-white"></i> Home</a></li>
        <li><a href="<?php echo site_url('variations/add'); ?>"><i class="icon-plus icon-white"></i> Add</a></li>
        <li><a href="<?php echo site_url('genes'); ?>"><i class="icon-pencil icon-white"></i> Edit</a></li>
        <li><a href="<?php echo site_url('variations/unreleased'); ?>"><i class="icon-edit icon-white"></i> Review changes</a></li>
        <li id="nav-login"><a href="<?php echo login_url(); ?>"><i></i> <?php echo login_text(); ?></a></li>
      </ul>
    </div>
  </div>
  <div id="content-wrapper" class="container rounded"> <!-- begin content wrapper -->
    <div id="content"> <!-- begin content -->
    <?php echo all_messages(); ?>
