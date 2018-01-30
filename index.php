<?php 
    /*
    Plugin Name: Dolan Digital Top Trumps
    Plugin URI: http://dolan.digital
    Description: A WordPress plugin to replicate the classic game of Top Trumps ~ in 6 hours! 
    Author: Jeremy Dolan
    Version: 1.0
    Author URI: http://dolan.digital
    */
	//first up create a CPT and use ACF to populate with some fields
	include 'structure.php';
	
	//plugins load before init leaving some functions unavailable
	add_action('init','init_toptrumps');
	function init_toptrumps(){
		include 'functions.php';
		include 'view.php';
	}
?>