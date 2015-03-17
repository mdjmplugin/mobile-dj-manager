<?php
	class MDJM_Events	{

/*
 * Event functions
 */	
		/*
		 * mdjm_event_by
		 * Retrieve event details by given field
		 * 
		 * @param: $db_field_key, $db_field_value
		 * @return: $event_details => array()
		 */
		public function mdjm_event_by( $field, $data )	{
			global $wpdb, $db_tables;
			
			if( empty( $field ) || empty( $data ) )
				return;
			
			$key = array(
					'ID'	=> array( 'event_id', 'single' ),
					);
					
			if( $key[$field][1] == 'single' )	{
				$event_details = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . 'mdjm_events' . " WHERE `" . $key[$field][0] . "` = '" . $data . "'" );	
			}
			
			return $event_details;
		} // mdjm_event_by
/*
 * Venue functions
 */		
		/*
		 * mdjm_get_venues
		 * Pull all venues from the database
		 * 
		 * return: $venues => array
		 */
		public function mdjm_get_venues()	{
			$venues = get_posts( array(
									'post_type'	=> MDJM_VENUE_POSTS,
									'orderby'	  => 'title',
									'order'		=> 'ASC',
									)
								);
			
			return $venues;
		}
		
		/*
		 * mdjm_add_venue
		 * Add new Venue
		 * 
		 * return: $venue_post_id = the post_id
		 */
		public function mdjm_add_venue( $venue_data, $venue_meta )	{
			if( !current_user_can( 'administrator' ) && !dj_can( 'add_venue' ) )
				return $post_id;
			
			if( empty( $venue_data ) || !is_array( $venue_data ) || empty( $venue_meta ) || !is_array( $venue_meta ) )
				return;	
			
			/* -- Insert the Venue -- */
			$post_data['post_title'] = !empty( $venue_data['name'] ) ? $venue_data['name'] : '';
			$post_data['post_content'] = '';
			$post_data['post_type'] = MDJM_VENUE_POSTS;
			$post_data['post_author'] = get_current_user_id();
			$post_data['post_status'] = 'publish';
			$post_data['ping_status'] = 'closed';
			$post_data['comment_status'] = 'closed';
			
			$venue_post_id = wp_insert_post( $post_data );
			
			/* -- And the meta -- */
			if( $venue_post_id )	{
				foreach( $venue_meta as $meta_key => $meta_value )	{					
					if( !empty( $meta_value ) )
						add_post_meta( $venue_post_id, '_' . $meta_key, $meta_value );	
				}	
			}
			return $venue_post_id;
		} // mdjm_add_venue
		
		/*
		 * mdjm_get_venue_meta
		 * Retrieve all venue meta
		 * 
		 * @param: venue_post_id
		 * @return: $venue_meta => array
		 */
		function mdjm_get_venue_details( $venue_post_id='', $event_id='' )	{
			
			if( empty( $venue_post_id ) && empty( $event_id ) )
				return;
			
			/* -- No post means we use the event database */
			if( false === get_post_status( $venue_post_id ) || !is_numeric( $venue_post_id ) )	{
				$event_details = $this->mdjm_event_by( 'ID', $event_id );
				if( !$event_details )
					return;
				
				$venue_details['name'] = !empty( $event_details->venue ) ? $event_details->venue : '';
				$venue_details['venue_contact'] = !empty( $event_details->venue_contact ) ? $event_details->venue_contact : '';
				$venue_details['venue_phone'] = !empty( $event_details->venue_phone ) ? $event_details->venue_phone : '';
				$venue_details['venue_email'] = !empty( $event_details->venue_email ) ? $event_details->venue_email : '';
				$venue_details['venue_address1'] = !empty( $event_details->venue_addr1 ) ? $event_details->venue_addr1 : '';
				$venue_details['venue_address2'] = !empty( $event_details->venue_addr2 ) ? $event_details->venue_addr2 : '';
				$venue_details['venue_town'] = !empty( $event_details->venue_city ) ? $event_details->venue_city : '';
				$venue_details['venue_county'] = !empty( $event_details->venue_state ) ? $event_details->venue_state : '';
				$venue_details['venue_postcode'] = !empty( $event_details->venue_zip ) ? $event_details->venue_zip : '';
				$venue_details['venue_information'] = '';
			}
			/* -- The venue post exists -- */
			else	{
				$venue_keys = array(
							'_venue_contact',
							'_venue_phone',
							'_venue_email',
							'_venue_address1',
							'_venue_address2',
							'_venue_town',
							'_venue_county',
							'_venue_postcode',
							'_venue_information',
							);
				$venue_name = get_the_title( $venue_post_id );
				$all_meta = get_post_meta( $venue_post_id );
				if( empty( $all_meta ) )
					return;
					
				$venue_details['name'] = ( !empty( $venue_name ) ? $venue_name : '' );
				foreach( $venue_keys as $key )	{
					$venue_details[substr( $key, 1 )] = !empty( $all_meta[$key][0] ) ? $all_meta[$key][0] : '';
				}
			}
			
			return $venue_details;
		} // mdjm_get_venue_meta
	} // class
?>