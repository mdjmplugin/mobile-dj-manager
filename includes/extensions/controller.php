<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
	
/**
 * MDJM_CTRL class
 *
 *
 *
 */
	if( !class_exists( 'MDJM_CTRL' ) )	{
		class MDJM_CTRL	{
			/**
			 * Check if this instance has the specified extension
			 *
			 * @param:	str		$ext	Required: The extension slug
			 * @return	bool			true if enabled, false if not
			 */
			function has_extension( $ext )	{
				// If not installed return false
				if( !file_exists( plugin_dir_path( $ext ) ) )
					return false;
				
				elseif( !is_plugin_active( $ext . '/' . $ext . '.php' )	 )
					return false;
					
				else
					return ( in_array( $ext, $GLOBALS['mdjm_ext'] ) ? true : false );	
			} // has_extension			
		}
	} // if( !class_exists( 'MDJM_CTRL' ) )
	
	new MDJM_CTRL();

