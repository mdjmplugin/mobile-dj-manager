<?php
/**
 * class-mdjm-dashboard.php
 * MDJM_Dashboard Class
 * 21/02/2015
 * @since 1.1
 * A class to produce the MDJM Dashboard Overview
 * 
 * @version 1.0
 * @21/02/2015
 *
 * TODO 7 day status (admin & DJ)
 *	Status overview for month (admin & DJ)
 *	To do list (admin only)
 * 	Availability check
 *	Recent activity (payments etc..)
 * 	Latest news
 */

	class MDJM_Dashboard	{
		
		/*
		 * The Contact Form constructor
		 *
		 *
		 */
		public function __construct()	{
			
		} // __construct
		
		/*
		 * Retrieve all events listed by status for specified period
		 *
		 * @param		str		$status		The event status
		 *				str		$period		month or year (current)
		 *				bool	$str		all, dj, client
		 *
		 */
		public function all_events_by_status( $status='', $period='', $type=false, $user_id='' )	{
			global $mdjm;
			
			if( empty( $status ) || empty( $period ) )	{
				if( MDJM_DEBUG == true )
					$mdjm->debug_logger( 'ERROR: No ' . ( empty( $status ) ? 'status' : 'period' ) . 
						' was provided in ' . __METHOD__, true );
				
				return false;	
			}
			
			if( !empty( $type ) && empty( $user_id ) )	{
				if( MDJM_DEBUG == true )
					$mdjm->debug_logger( 'ERROR: No user ID was provided in ' . __METHOD__, true );
				
				return false;	
			}
						
			$start_date = $period == 'year' ? date( 'Y-01-01' ) : date( 'Y-m-01' );
			$end_date = $period == 'year' ? date( 'Y-12-31' ) : date( 'Y-m-t' );
						
			$args = array( 
						'posts_per_page'	=> -1,
						'post_type'			=> MDJM_EVENT_POSTS,
						'post_status'		=> $status,
						'meta_key'			=> '_mdjm_event_date',
						'orderby'			=> 'meta_value',
						'order'				=> 'DESC',
						);
						
			if( !empty( $type ) )	{	
				$args['meta_query'] = array(
								'relation'	=> 'AND',
								array(
									'key'		=> '_mdjm_event_date',
									'value'		=> array( $start_date, $end_date ),
									'type'		=> 'date',
									'compare'	=> 'BETWEEN',
								),
								array(
									'key'		=> '_mdjm_event_' . $type,
									'value'		=> $user_id,
									'compare'	=> '=',
								)
							);
			}
			else	{
				$args['meta_query'] = array(
									'key'		=> '_mdjm_event_date',
									'value'		=> array( $start_date, $end_date ),
									'type'		=> 'date',
									'compare'	=> 'BETWEEN',
								);
			}
									
			$events = get_posts( $args );
			
			return $events;
			
		} // all_events_by_status
		
		/*
		 * Retrieve earnings by the given period
		 *
		 * @param	str		$period		Required: week, month or year
		 *			int		$user_id	Optional: ID of the DJ to check, otherwise check all
		 *			bool	$earned		true for already earned, false for possible
		 * @return	
		 */
		public function period_earnings( $period='', $user_id='', $earned='' )	{
			global $mdjm;
			
			if( empty( $period ) )	{
				if( MDJM_DEBUG == true )
					$mdjm->debug_logger( 'ERROR: No period was provided in ' . __METHOD__, true );	
			}
			elseif( $period == 'week' )	{
				$start_date = date( 'Y-m-d', strtotime( "-7 day" ) );
				$end_date = date( 'Y-m-d' );
			}
			elseif( $period == 'month' )	{
				$start_date = date( 'Y-m-01' );
				$end_date = date( 'Y-m-t' );
			}
			else	{
				$start_date = date( 'Y-01-01' );
				$end_date = date( 'Y-12-31' );	
			}
			
			$user_id = !empty( $user_id ) ? $user_id : '';
			
			$args = array(
						'post_type'			=> MDJM_EVENT_POSTS,
						'posts_per_page'	=> -1,
						'meta_key'			=> '_mdjm_event_date',
						'orderby'			=> 'meta_value',
						'order'				=> 'DESC',
						);
			$args['post_status'] = empty( $earned ) ? array( 'mdjm-enquiry',
														     'mdjm-unattended',
														     'mdjm-contract',
														     'mdjm-approved',
															 'mdjm-completed' ) : 'mdjm-completed';
						
			if( !empty( $user_id ) )	{
				$args['meta_query'] = array(
								'relation'	=> 'AND',
								array(
									'key'		=> '_mdjm_event_date',
									'value'		=> array( $start_date, $end_date ),
									'type'		=> 'date',
									'compare'	=> 'BETWEEN',
								),
								array(
									'key'		=> '_mdjm_event_dj',
									'value'		=> $user_id,
									'compare'	=> '=',
								),
							);
			} // End if( !empty( $user ) )
			else	{
				$args['meta_query'] = array(
										'key'		=> '_mdjm_event_date',
										'value'		=> array( $start_date, $end_date ),
										'type'		=> 'date',
										'compare'	=> 'BETWEEN',
										);
			}
			$earnings = '0.00';
			$events = get_posts( $args );
			foreach( $events as $event )	{
				$event_cost = get_post_meta( $event->ID, '_mdjm_event_cost', true );
				$earnings += $event_cost;
				
				if( !empty( $earnings ) )	{
					if( !class_exists( 'MDJM_Transactions' ) )	{
						require_once( MDJM_PLUGIN_DIR . '/admin/includes/class/class-mdjm-transactions.php' );
					}
					$mdjm_trans = new MDJM_Transactions();
					$transactions = $mdjm_trans->get_event_transactions( $event->ID );
					foreach( $transactions as $transaction )	{
						$status = get_post_meta( $transaction->ID, '_mdjm_txn_status', true );
						if( empty( $status ) || $status == 'Completed' )
							$txn_total = get_post_meta( $transaction->ID, '_mdjm_txn_total', true );
							
						if( !empty( $txn_total ) )	{
							if( $transaction->post_status == 'mdjm-income' )
								$earnings += $txn_total;
							else
								$earnings -= $txn_total;	
						}
					} // foreach( $transactions as $transaction )
				} // if( !empty( $earnings ) )
					
			} // End foreach( $events as $event )
			
			return display_price( $earnings );
		} // period_earnings
				
	} // Class
	
	/* -- Insantiate the MDJM_Dashboard class -- */
	$mdjm_dash = new MDJM_Dashboard();
?>