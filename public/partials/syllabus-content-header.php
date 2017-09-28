<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://it.clas.ufl.edu/
 * @since      0.0.0
 *
 * @package    Syllabus_Manager
 * @subpackage Syllabus_Manager/public/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div id="syllabus-main-wrap">

<div id="main" class="container main-content syllabus-main-content">
<div class="row">
  <div class="col-sm-12">
    <?php //ufclas_ufl_2015_breadcrumbs(); ?>
    <header class="entry-header">
	<?php 
	  	if ( is_archive() ){
			the_archive_title( '<h1 class="page-title">', '</h1>' );
	  	}
		else {
			the_title( '<h1 class="page-title">', '</h1>' );
		}
	?>
    </header>
    <!-- .entry-header --> 
  </div>
</div>
<div class="row">
<div class="col-md-12">