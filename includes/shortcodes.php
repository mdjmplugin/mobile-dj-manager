<?php
	function f_mdjm_shortcode( $atts )	{
		 $pages = shortcode_atts( array(
 	      'Home' => 'home',
 	      'Profile' => 'profile',
		  'Playlist' => 'playlist',
		  'Contract' => 'contract'
      ), $atts );
      	include_once WPMDJM_PLUGIN_DIR . '/pages/' . $pages[$atts['page']] . '.php';
	}
?>