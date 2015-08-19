/*
Page Name: mdjm-music-library.js
Description: Scripts associated with the Admin Music Library Interface
Since Version: 1.2.3
Date: 13 July 2015
Author: My DJ Planner <contact@mydjplanner.co.uk>
Author URI: http://www.mydjplanner.co.uk
*/
	/*
	 * Toggle the upload file table row for the relevant selection
	 *
	 *
	 *
	 */
	jQuery(document).ready(function($) 	{
		$('#upload_from').on('change', '', function()	{
			var provider = $("#upload_from option:selected").val();
			var upload_options = $("#library_upload_options");
			
			if( provider != "0" )	{
				upload_options.show( "slow" );	
			}
			else	{
				upload_options.hide( "slow" );	
			}
			
		});
		$('#upload_to').on('change', '', function()	{
			var upload_to = $("#upload_to option:selected").val();
			var lib_name = $("#library_name_field");
			
			if( upload_to == 'add_new' )	{
				lib_name.show( "slow" );
			}
			else	{
				lib_name.hide( "fast" );	
			}
			
		});
	});