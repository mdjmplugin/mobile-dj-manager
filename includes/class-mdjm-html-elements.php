<?php
/**
 * HTML Elements
 *
 * A helper class for outputting common HTML elements.
 *
 * @package     MDJM
 * @subpackage  Classes/HTML
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * MDJM_HTML_Elements Class
 *
 * @since	1.3.7
 */
class MDJM_HTML_Elements {

	/**
	 * Renders an HTML Dropdown of all the event Post Statuses
	 *
	 * @access	public
	 * @since	1.3.7
	 * @param	str		$name		Name attribute of the dropdown
	 * @param	str		$selected	Status to select automatically
	 * @return	str		$output		Status dropdown
	 */
	public function event_status_dropdown( $name = 'post_status', $selected = 0 ) {
		$event_statuses = mdjm_get_post_statuses( 'labels' );
		$options        = array();
		
		foreach ( $event_statuses as $event_statuses ) {
			$options[ $event_statuses->name ] = esc_html( $event_statuses->label );
		}

		$output = $this->select( array(
			'name'             => $name,
			'selected'         => $selected,
			'options'          => $options,
			'show_option_all'  => false,
			'show_option_none' => false
		) );

		return $output;
	} // event_status_dropdown

	/**
	 * Renders an HTML Dropdown of all the Enquiry Sources
	 *
	 * @access	public
	 * @since	1.3.7
	 * @param	str		$name		Name attribute of the dropdown
	 * @param	int		$selected	Category to select automatically
	 * @return	str		$output		Category dropdown
	 */
	public function event_type_dropdown( $args = array() ) {

		$defaults = array(
			'name'             => null,
			'class'            => '',
			'id'               => '',
			'selected'         => mdjm_get_option( 'event_type_default' ),
			'chosen'           => false,
			'placeholder'      => null,
			'multiple'         => false,
			'show_option_all'  => false,
			'show_option_none' => false
		);

		$args = wp_parse_args( $args, $defaults );

		$cat_args = array(
			'hide_empty' => false
		);

		$categories      = get_terms( 'event-types', apply_filters( 'mdjm_event_types_dropdown', $cat_args ) );
		$args['options'] = array();

		foreach ( $categories as $category ) {
			$args['options'][ absint( $category->term_id ) ] = esc_html( $category->name );
		}

		$category_labels = mdjm_get_taxonomy_labels( 'event-types' );
		$output = $this->select( $args );

		return $output;

	} // event_type_dropdown

	/**
	 * Renders an HTML Dropdown of all the Enquiry Sources
	 *
	 * @access	public
	 * @since	1.3.7
	 * @param	str		$name		Name attribute of the dropdown
	 * @param	int		$selected	Category to select automatically
	 * @return	str		$output		Category dropdown
	 */
	public function enquiry_source_dropdown( $name = 'mdjm_enquiry_source', $selected = 0 ) {

		$args = array(
			'hide_empty' => false
		);

		$categories = get_terms( 'enquiry-source', apply_filters( 'mdjm_enquiry_source_dropdown', $args ) );
		$options    = array();

		if ( empty( $selected ) )	{
			$selected = mdjm_get_option( 'enquiry_source_default' );
		}

		foreach ( $categories as $category ) {
			$options[ absint( $category->term_id ) ] = esc_html( $category->name );
		}

		$category_labels = mdjm_get_taxonomy_labels( 'enquiry-source' );
		$output = $this->select( array(
			'name'             => $name,
			'selected'         => $selected,
			'options'          => $options,
			'show_option_all'  => false,
			'show_option_none' => false
		) );

		return $output;
	} // enquiry_source_dropdown

	/**
	 * Renders an HTML Dropdown of all Transaction Types
	 *
	 * @access	public
	 * @since	1.3.7
	 * @param	str		$name		Name attribute of the dropdown
	 * @param	int		$selected	Category to select automatically
	 * @return	str		$output		Category dropdown
	 */
	public function txn_type_dropdown( $name = 'mdjm_txn_for', $selected = 0 ) {

		$args = array(
			'hide_empty' => false
		);

		$categories = get_terms( 'transaction-types', apply_filters( 'mdjm_txn_types_dropdown', $args ) );
		$options    = array();

		foreach ( $categories as $category ) {
			$options[ absint( $category->term_id ) ] = esc_html( $category->name );
		}

		$category_labels = mdjm_get_taxonomy_labels( 'transaction-types' );
		$output = $this->select( array(
			'name'             => $name,
			'selected'         => $selected,
			'options'          => $options,
			'show_option_all'  => false,
			'show_option_none' => __( ' - Select Txn Type - ', 'mobile-dj-manager' )
		) );

		return $output;
	} // txn_type_dropdown
	
	/**
	 * Renders an HTML Dropdown of all Transaction Types
	 *
	 * @access	public
	 * @since	1.3.7
	 * @param	arr		$args		See @defaults
	 * @return	str		$output		Venue dropdown
	 */
	public function venue_dropdown( $args = array() ) {

		$defaults = array(
			'name'             => 'venue_id',
			'class'            => 'mdjm-venue-select',
			'id'               => '',
			'selected'         => 0,
			'chosen'           => false,
			'placeholder'      => null,
			'multiple'         => false,
			'allow_add'        => true,
			'show_option_all'  => false,
			'show_option_none' => __( ' - Select Venue - ', 'mobile-dj-manager' ),
			'data'             => array()
		);
		
		$args = wp_parse_args( $args, $defaults );

		if ( $args['allow_add'] )	{
			$args['options']  = array(
				'manual' => __( '  - Enter Manually - ', 'mobile-dj-manager' ),
				'client' => __( '  - Use Client Address - ', 'mobile-dj-manager' )
			);
		}

		$venues = mdjm_get_venues();

		if ( $venues )	{
			foreach ( $venues as $venue ) {
				$args['options'][ $venue->ID ] = $venue->post_title;
			}
		}

		$output = $this->select( $args );

		return $output;
	} // venue_dropdown

	/**
	 * Renders an HTML Dropdown of years
	 *
	 * @access	public
	 * @since	1.3.7
	 * @param	str		$name			Name attribute of the dropdown
	 * @param	int		$selected		Year to select automatically
	 * @param	int		$years_before	Number of years before the current year the dropdown should start with
	 * @param	int		$years_after	Number of years after the current year the dropdown should finish at
	 * @return	str		$output			Year dropdown
	 */
	public function year_dropdown( $name = 'year', $selected = 0, $years_before = 5, $years_after = 0 ) {
		$current     = date( 'Y' );
		$start_year  = $current - absint( $years_before );
		$end_year    = $current + absint( $years_after );
		$selected    = empty( $selected ) ? date( 'Y' ) : $selected;
		$options     = array();

		while ( $start_year <= $end_year ) {
			$options[ absint( $start_year ) ] = $start_year;
			$start_year++;
		}

		$output = $this->select( array(
			'name'             => $name,
			'selected'         => $selected,
			'options'          => $options,
			'show_option_all'  => false,
			'show_option_none' => false
		) );

		return $output;
	} // year_dropdown

	/**
	 * Renders an HTML Dropdown of months
	 *
	 * @access	public
	 * @since	1.3.7
	 * @param	arr		$args		see @defaults.
	 * @return	str		$output		Month dropdown
	 */
	public function month_dropdown( $args ) {

		$defaults = array(
			'name'        => 'month',
			'selected'    => 0,
			'fullname'    => false,
			'multiple'    => false,
			'chosen'      => false,
			'placeholder' => __( 'Select a Month', 'mobile-dj-manager' )
		);

		$args = wp_parse_args( $args, $defaults );

		$month   = 1;
		$options = array();
		$selected = ( empty( $args['selected'] ) && ! $args['multiple'] ) ? date( 'n' ) : $args['selected'];

		while ( $month <= 12 ) {
			$options[ absint( $month ) ] = mdjm_month_num_to_name( $month, $args['fullname'] );
			$month++;
		}

		$output = $this->select( array(
			'name'             => $args['name'],
			'selected'         => $selected,
			'options'          => $options,
			'show_option_all'  => false,
			'show_option_none' => false,
			'multiple'         => $args['multiple'],
			'chosen'           => $args['chosen'],
			'placeholder'      => $args['placeholder']
		) );

		return $output;
	} // month_dropdown

	/**
	 * Renders a dropdown list of employees.
	 *
	 * @since	1.3.7
	 * @param	arr		$args	@see $default
	 * @return	str
	 */
	public function employee_dropdown( $args = array() )	{
		global $wp_roles;

		$defaults = array(
			'name'             => '_mdjm_event_dj',
			'id'               => '',
			'class'            => '',
			'selected'         => '',
			'show_option_none' => '',
			'show_option_all'  => false,
			'chosen'           => false,
			'employee'         => false,
			'role'             => '',
			'group'            => false,
			'exclude'          => false,
			'placeholder'      => __( 'Select an Employee', 'mobile-dj-manager' ),
			'multiple'         => false,
			'blank_first'      => true,
			'data'             => array()
		);
		
		$args    = wp_parse_args( $args, $defaults );
		$options = array();

		$employees = mdjm_get_employees( $args['role'] );

		if ( $employees )	{
			foreach( $employees as $employee )	{
				$roles = array_values( $employee->roles );
				$role  = $roles[0];

				if( $role == 'administrator' )	{
					if ( ! empty( $roles[1] ) )	{
						$role = $roles[1];
					} else	{
						$role = 'dj';
					}
				}

				if( ! empty( $args['exclude'] ) && in_array( $employee->ID, $args['exclude'] ) )	{
					continue;
				}

				if ( ! empty( $args['group'] ) )	{
					$options['groups'][ translate_user_role( $wp_roles->roles[ $role ]['name'] ) ][] = array( $employee->ID => $employee->display_name );
				} else	{
					$options[ $user->ID ] = $user->display_name;
				}
			}
		}

		if ( ! empty( $options ) )	{
			if ( ! empty( $options['groups'] ) )	{
				ksort( $options['groups'] );
			} else	{
				asort( $options );
			}
		}

		$output = $this->select( array(
			'name'             => $args['name'],
			'class'            => $args['class'],
			'id'               => $args['id'],
			'selected'         => $args['selected'],
			'options'          => $options,
			'chosen'           => $args['chosen'],
			'placeholder'      => $args['placeholder'],
			'multiple'         => $args['multiple'],
			'show_option_none' => $employees ? $args['show_option_none'] : __( 'No Employees', 'mobile-dj-manager' ),
			'show_option_all'  => $args['show_option_all'],
			'blank_first'      => $args['blank_first'],
			'data'             => $args['data']
		) );

		return $output;

	} // employee_dropdown

	/**
	 * Renders an HTML Dropdown of employee roles
	 *
	 * @access	public
	 * @since	1.3.7
	 * @param	arr		$args		Select list arguments. See @defaults.
	 * @return	str		$output		Client dropdown
	 */
	public function roles_dropdown( $args = array() ) {

		$defaults = array(
			'name'        => 'mdjm_employee_roles',
			'value'       => '',
			'options'     => mdjm_get_roles(),
			'blank_first' => true,
			'placeholder' => __( 'Select Role', 'mobile-dj-manager' ),
			'chosen'      => false
		);

		$args             = wp_parse_args( $args, $defaults );
		$args['options']  = mdjm_get_roles();

		$output = $this->select( $args );

		return $output;

	} // roles_dropdown

	/**
	 * Renders an HTML Dropdown of clients
	 *
	 * @access	public
	 * @since	1.3.7
	 * @param	arr		$args		Select list arguments. See @defaults.
	 * @return	str		$output		Client dropdown
	 */
	public function client_dropdown( $args = array() ) {
		$options  = array();

		$defaults = array(
			'name'             => 'client_name',
			'class'            => '',
			'id'               => '',
			'selected'         => 0,
			'roles'            => array( 'client', 'inactive_client' ),
			'chosen'           => false,
			'placeholder'      => null,
			'multiple'         => false,
			'null_value'       => false,
			'add_new'          => false,
			'show_option_all'  => _x( 'All Clients', 'all dropdown items', 'mobile-dj-manager' ),
			'show_option_none' => _x( 'Select a Client', 'no dropdown items', 'mobile-dj-manager' ),
			'data'             => array()
		);

		$args = wp_parse_args( $args, $defaults );

		$selected = empty( $args['selected'] ) ? 0 : $args['selected'];

		$clients = mdjm_get_clients( $args['roles'] );
		
		if ( ! empty( $args['null_value'] ) )	{
			foreach( $args['null_value'] as $key => $value )	{
				$options[ $key ] = $value;
			}
		}
		
		if ( ! empty( $args['add_new'] ) )	{
			$options['mdjm_add_client'] = __( 'Add New Client', 'mobile-dj-manager' );
		}
		
		if ( $clients )	{
			foreach( $clients as $client )	{
				$options[ $client->ID ] = $client->display_name;
			}
		}

		$output = $this->select( array(
			'name'             => $args['name'],
			'class'            => $args['class'],
			'id'               => $args['id'],
			'selected'         => $selected,
			'options'          => $options,
			'chosen'           => $args['chosen'],
			'placeholder'      => $args['placeholder'],
			'multiple'         => $args['multiple'],
			'show_option_all'  => $args['show_option_all'],
			'show_option_none' => $args['show_option_none'],
			'data'             => $args['data']
		) );

		return $output;
	} // client_dropdown

	/**
	 * Renders an HTML Dropdown of all users
	 *
	 * @access	public
	 * @since	1.4
	 * @param	arr		$args		Select list arguments. See @defaults.
	 * @return	str		$output		Users dropdown
	 */
	public function users_dropdown( $args = array() ) {
		$options  = array();

		$defaults = array(
			'name'             => 'users',
			'class'            => '',
			'id'               => '',
			'selected'         => 0,
			'chosen'           => false,
			'placeholder'      => null,
			'multiple'         => false,
			'show_option_all'  => _x( 'All Users', 'all dropdown items', 'mobile-dj-manager' ),
			'show_option_none' => _x( 'Select a User', 'no dropdown items', 'mobile-dj-manager' ),
			'data'             => array()
		);

		$args = wp_parse_args( $args, $defaults );

		$selected = empty( $args['selected'] ) ? 0 : $args['selected'];

		$users = get_users();
				
		if ( $users )	{
			foreach( $users as $user )	{
				$options[ $user->ID ] = $user->display_name;
			}
		}

		$output = $this->select( array(
			'name'             => $args['name'],
			'class'            => $args['class'],
			'id'               => $args['id'],
			'selected'         => $selected,
			'options'          => $options,
			'chosen'           => $args['chosen'],
			'placeholder'      => $args['placeholder'],
			'multiple'         => $args['multiple'],
			'show_option_all'  => $args['show_option_all'],
			'show_option_none' => $args['show_option_none'],
			'data'             => $args['data']
		) );

		return $output;
	} // users_dropdown

	/**
	 * Renders a dropdown list of packages.
	 *
	 * @since	1.3.7
	 * @param	arr		$args	@see $default
	 * @return	str
	 */
	public function packages_dropdown( $args = array() )	{
		$defaults = array(
			'name'             => '_mdjm_event_package',
			'id'               => '',
			'class'            => '',
			'selected'         => '',
			'show_option_none' => __( 'No Package', 'mobile-dj-manager' ),
			'show_option_all'  => false,
			'chosen'           => false,
			'employee'         => false,
			'event_type'       => false,
			'event_date'       => false,
			'placeholder'      => __( 'Select a Package', 'mobile-dj-manager' ),
			'multiple'         => false,
			'cost'             => true,
			'titles'           => false,
			'options_only'     => false,
			'blank_first'      => true,
			'data'             => array()
		);
		
		$args    = wp_parse_args( $args, $defaults );
		$options = array();
		
		$args['id']       = ! empty( $args['id'] )       ? $args['id'] : $args['name'];
		
		$packages = mdjm_get_packages();

		if ( $packages )	{

			foreach( $packages as $package )	{

				if( $args['employee'] )	{	
					if( ! mdjm_employee_has_package( $package->ID, $args['employee'] ) )	{
						continue;
					}
				}

				if ( $args['event_type'] )	{
					if ( ! mdjm_package_is_available_for_event_type( $package->ID, $args['event_type'] ) )	{
						continue;
					}
				}

				if ( $args['event_date'] )	{
					if ( ! mdjm_package_is_available_for_event_date( $package->ID, $args['event_date'] ) )	{
						continue;
					}
				} else	{
					$args['event_date'] = NULL;
				}

				$price = '';
				if( $args['cost'] == true )	{
					$price .= ' - ' . mdjm_currency_filter( mdjm_format_amount( mdjm_get_package_price( $package->ID, $args['event_date'] ) ) ) ;
				}

				$args['options'][ $package->ID ] = $package->post_title . '' . $price;

				if ( $args['titles'] )	{
					$titles[ $package->ID ] = mdjm_get_package_excerpt( $package->ID );
				}

			}

		} 

		if ( empty( $args['options'] ) )	{
			$args['placeholder'] = __( 'No Packages Available', 'mobile-dj-manager' );
		}

		if ( ! empty( $titles ) )	{
			$args['titles'] = $titles;
		}

		$output = $this->select( $args );

		return $output;

	} // packages_dropdown

	/**
	 * Renders a dropdown list of equipment add-ons.
	 *
	 * @since	1.3.7
	 * @param	arr		$args	@see $default
	 * @return	str
	 */
	public function addons_dropdown( $args = array() )	{
		$defaults = array(
			'name'             => 'event_addons',
			'id'               => '',
			'class'            => '',
			'selected'         => '',
			'show_option_none' => __( 'No Addons', 'mobile-dj-manager' ),
			'show_option_all'  => false,
			'chosen'           => false,
			'employee'         => false,
			'event_type'       => false,
			'event_date'       => false,
			'placeholder'      => null,
			'multiple'         => true,
			'package'          => '',
			'cost'             => true,
			'desc'             => false,
			'titles'           => false,
			'options_only'     => false,
			'blank_first'      => false,
			'data'             => array()
		);
		
		$args    = wp_parse_args( $args, $defaults );
		$options = array();
		$titles  = array();
		$addons  = mdjm_get_addons();

		if ( $addons )	{
			foreach( $addons as $addon )	{
				if ( ! empty( $args['package'] ) )	{
					if ( is_numeric( $args['package'] ) )	{
						$package = mdjm_get_package( $args['package'] );
					} else	{
						$package = mdjm_get_package_by( 'slug', $args['package'] );
					}

					if ( $package )	{
						$package_items = mdjm_get_package_addons( $package->ID );
					}

					if ( ! empty( $package_items ) && in_array( $addon->ID, $package_items ) )	{
						continue;
					}
				}

				if ( ! empty( $args['employee'] ) )	{
					if ( ! mdjm_employee_has_addon( $addon->ID, $args['employee'] ) )	{
						continue;
					}
				}

				if ( $args['event_type'] )	{
					if ( ! mdjm_addon_is_available_for_event_type( $addon->ID, $args['event_type'] ) )	{
						continue;
					}
				}

				if ( $args['event_date'] )	{
					if ( ! mdjm_addon_is_available_for_event_date( $addon->ID, $args['event_date'] ) )	{
						continue;
					}
				} else	{
					$args['event_date'] = NULL;
				}

				$price = '';
				if( $args['cost'] == true )	{
					$price .= ' - ' . mdjm_currency_filter( mdjm_format_amount( mdjm_get_addon_price( $addon->ID, $args['event_date'] ) ) ) ;
				}
				$desc           = '';

				if( $args['desc'] )	{
					$desc .= ' - ' . mdjm_get_addon_excerpt( $addon->ID, $args['desc'] );
				}

				$term  = '';
				$terms = get_the_terms( $addon->ID, 'addon-category' );

				if ( ! empty( $terms ) )	{
					$term = esc_html( $terms[0]->name );
				}

				$args['options']['groups'][ $term ][] = array( $addon->ID => $addon->post_title . $price . $desc );

				if ( $args['titles'] )	{
					$titles[ $addon->ID ] = mdjm_get_addon_excerpt( $addon->ID );
				}

			}
		}

		if ( ! empty( $args['options']['groups'] ) )	{
			ksort( $args['options']['groups'] );
		}

		if ( ! empty( $titles ) )	{
			$args['titles'] = $titles;
		}

		$output = $this->select( $args );

		return $output;

	} // addons_dropdown

	/**
	 * Renders an Time Dropdown of Hours
	 *
	 * @since	1.3.7
	 *
	 * @param	arr		$args
	 *
	 * @return	str
	 */
	public function time_hour_select( $args = array() )	{
		$options  = array();
		$defaults = array(
			'name'             => 'event_start_hr',
			'class'            => 'mdjm-time',
			'id'               => '',
			'selected'         => 0
		);

		$args = wp_parse_args( $args, $defaults );
		
		$args['id'] = ! empty( $args['id'] ) ? $args['id'] : $args['name'];
		
		if( 'H:i' == mdjm_get_option( 'time_format', 'H:i' ) )	{
			$i      = '00';
			$x      = '23';
			$format = 'H';
		} else	{
			$i      = '1';
			$x      = '12';
			$format = 'g';	
		}

		while( $i <= $x )	{
			if( $i != 0 && $i < 10 && $format == 'H' )	{
				$i = '0' . $i;
			}
			$options[ $i ] = $i;
			$i++;
		}

		$output = $this->select( array(
			'name'     => $args['name'],
			'selected' => $args['selected'],
			'class'    => $args['class'],
			'options'  => $options,
			'show_option_all'  => false,
			'show_option_none' => false
		) );

		return $output;

	} // time_hour_select
	
	/**
	 * Renders an Time Dropdown of Minutes
	 *
	 * @since	1.3.7
	 *
	 * @param	arr		$args
	 *
	 * @return	str
	 */
	public function time_minute_select( $args = array() )	{
		$options  = array();
		$minutes  = apply_filters( 'mdjm_time_minutes', array( '00', '15', '30', '45' ) );
		$defaults = array(
			'name'      => 'event_start_min',
			'class'     => 'mdjm-time',
			'id'        => '',
			'selected'  => 0
		);

		$args = wp_parse_args( $args, $defaults );
		
		$args['id'] = ! empty( $args['id'] ) ? $args['id'] : $args['name'];

		foreach( $minutes as $minute )	{
			$options[ $minute ] = $minute;
		}

		$output = $this->select( array(
			'name'     => $args['name'],
			'selected' => $args['selected'],
			'class'    => $args['class'],
			'options'  => $options,
			'show_option_all'  => false,
			'show_option_none' => false
		) );

		return $output;

	} // time_minute_select

	/**
	 * Renders a Time Period Dropdown
	 *
	 * @since	1.3.7
	 *
	 * @param	arr		$args
	 *
	 * @return	str
	 */
	public function time_period_select( $args = array() )	{
		$options  = array();
		$minutes  = apply_filters( 'mdjm_time_minutes', array( '00', '15', '30', '45' ) );
		$defaults = array(
			'name'             => 'event_start_period',
			'class'            => 'mdjm-time',
			'id'               => '',
			'selected'         => 0
		);

		$args = wp_parse_args( $args, $defaults );
		
		$args['id'] = ! empty( $args['id'] ) ? $args['id'] : $args['name'];

		$options = array(
			'AM' => __( 'AM', 'mobile-dj-manager' ),
			'PM' => __( 'PM', 'mobile-dj-manager' )
		);

		$output = $this->select( array(
			'name'             => $args['name'],
			'selected'         => $args['selected'],
			'class'    => $args['class'],
			'options'          => $options,
			'show_option_all'  => false,
			'show_option_none' => false
		) );

		return $output;

	} // time_period_select

	/**
	 * Renders an HTML Dropdown
	 *
	 * @since	1.3.7
	 *
	 * @param	arr		$args
	 *
	 * @return	str
	 */
	public function select( $args = array() ) {
		$defaults = array(
			'options'          => array(),
			'name'             => null,
			'class'            => '',
			'id'               => '',
			'selected'         => 0,
			'chosen'           => false,
			'placeholder'      => null,
			'multiple'         => false,
			'blank_first'      => false,
			'show_option_all'  => false,
			'show_option_none' => false,
			'options_only'     => false,
			'titles'           => false,
			'data'             => array(),
		);

		$args = wp_parse_args( $args, $defaults );
		
		$args['id'] = ! empty( $args['id'] ) ? $args['id'] : $args['name'];

		$data_elements = '';
		foreach ( $args['data'] as $key => $value ) {
			$data_elements .= ' data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
		}

		if( $args['multiple'] ) {
			$multiple = ' MULTIPLE';
			$multi    = '[]';
		} else {
			$multiple = '';
			$multi    = '';
		}

		if( $args['chosen'] ) {
			$args['class'] .= ' mdjm-select-chosen';
		}

		if( $args['placeholder'] ) {
			$placeholder = $args['placeholder'];
		} else {
			$placeholder = '';
		}

		$output = '';
		$class  = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );

		if ( ! $args['options_only'] )	{
			$output .= '<select name="' . esc_attr( $args['name'] ) . '' . $multi . '" id="' . esc_attr( mdjm_sanitize_key( str_replace( '-', '_', $args['id'] ) ) ) . '" class="mdjm-select ' . $class . '"' . $multiple . ' data-placeholder="' . $placeholder . '"'. $data_elements . '>' . "\r\n";
		}

		if ( $args['blank_first'] )	{
			$output .= '<option value=""></option>' . "\n";
		}

		if ( $args['show_option_all'] ) {
			if( $args['multiple'] ) {
				$selected = selected( true, in_array( 'all', $args['selected'] ), false );
			} else {
				$selected = selected( $args['selected'], 'all', false );
			}
			$output .= '<option value="all"' . $selected . '>' . esc_html( $args['show_option_all'] ) . '</option>' . "\r\n";
		}

		if ( ! empty( $args['options'] ) ) {

			if ( $args['show_option_none'] ) {
				if( $args['multiple'] ) {
					$selected = selected( true, in_array( 0, $args['selected'] ), false );
				} else {
					$selected = selected( $args['selected'], 0, false );
				}
				$output .= '<option value="0"' . $selected . '>' . esc_html( $args['show_option_none'] ) . '</option>' . "\r\n";
			}

			if ( ! isset( $args['options']['groups'] ) )	{

				foreach( $args['options'] as $key => $option ) {
					if( $args['multiple'] && is_array( $args['selected'] ) ) {
						$selected = selected( true, in_array( $key, $args['selected'] ), false );
					} else {
						$selected = selected( $args['selected'], $key, false );
					}

					$title = '';
					if ( ! empty( $args['titles'] ) && array_key_exists( $key, $args['titles'] ) )	{
						$title = ' title="' . $args['titles'][ $key ] . '"';
					}

					$output .= '<option value="' . esc_attr( $key ) . '"' . $title . '' . $selected . '>' . esc_html( $option ) . '</option>' . "\r\n";
				}
				
			} else	{

				$i = 0;
				foreach( $args['options']['groups'] as $group => $items )	{

					if ( $i == 0 )	{
						$output .= '<optgroup label="' . esc_html( $group ) . '">' . "\r\n";
					}

					foreach( $items as $options ) {
						foreach ( $options as $key => $option )	{

							$title = '';
							if( $args['multiple'] && is_array( $args['selected'] ) ) {
								$selected = selected( true, in_array( $key, $args['selected'] ), false );
							} else {
								$selected = selected( $args['selected'], $key, false );
							}

							if ( ! empty( $args['titles'] ) && array_key_exists( $key, $args['titles'] ) )	{
								$title = ' title="' . $args['titles'][ $key ] . '"';
							}

							$output .= '<option value="' . esc_attr( $key ) . '"' . $title . '' . $selected . '>' . esc_html( $option ) . '</option>' . "\r\n";
						}
					}

					$i++;

					if ( $i == count( $options ) )	{
						$output .= '</optgroup>' . "\r\n";
						$i = 0;
					}

				}
	
			}
		}

		if ( ! $args['options_only'] )	{
			$output .= '</select>' . "\r\n";
		}

		return $output;
	} // select

	/**
	 * Renders an HTML Checkbox
	 *
	 * @since	1.3.7
	 *
	 * @param	arr		$args
	 *
	 * @return	string
	 */
	public function checkbox( $args = array() ) {
		$defaults = array(
			'name'     => null,
			'current'  => null,
			'class'    => 'mdjm-checkbox',
			'value'    => 1,
			'options'  => array(
				'disabled' => false,
				'readonly' => false
			)
		);

		$args = wp_parse_args( $args, $defaults );

		$class = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );
		$options = '';
		if ( ! empty( $args['options']['disabled'] ) ) {
			$options .= ' disabled="disabled"';
		} elseif ( ! empty( $args['options']['readonly'] ) ) {
			$options .= ' readonly';
		}

		$output = '<input type="checkbox"' . $options . ' name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['name'] ) . '" value="' . esc_attr( $args['value'] ) . '"class="' . $class . ' ' . esc_attr( $args['name'] ) . '" ' . checked( esc_attr( $args['value'] ), $args['current'], false ) . ' />';

		return $output;
	} // checkbox
	
	/**
	 * Renders an HTML Checkbox List
	 *
	 * @since	1.3.7
	 *
	 * @param	arr		$args
	 *
	 * @return	string
	 */
	public function checkbox_list( $args = array() ) {
		$defaults = array(
			'name'      => null,
			'class'     => 'mdjm-checkbox',
			'label_pos' => 'before',
			'options'   => array()
		);

		$args = wp_parse_args( $args, $defaults );

		$class = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );

		$label_pos = isset( $args['label_pos'] ) ? $args['label_pos'] : 'before';

		$output = '';
		
		if ( ! empty( $args['options'] ) )	{

			$i = 0;

			foreach( $args['options'] as $key => $value )	{

				if ( $label_pos == 'before' )	{
					$output .= $value . '&nbsp';
				}

				$output .= '<input type="checkbox" name="' . esc_attr( $args['name'] ) . '[]" id="' . esc_attr( $args['name'] ) . '-' . $key . '" class="' . $class . ' ' . esc_attr( $args['name'] ) . '" value="' . $key . '" />';

				if ( $label_pos == 'after' )	{
					$output .= '&nbsp' . $value;
				}

				if ( $i < count( $args['options'] ) )	{
					$output .= '<br />';
				}

				$i++;

			}
			
		}

		return $output;
	} // checkbox_list
	
	/**
	 * Renders HTML Radio Buttons
	 *
	 * @since	1.3.7
	 *
	 * @param	arr		$args
	 *
	 * @return	string
	 */
	public function radio( $args = array() ) {
		$defaults = array(
			'name'     => null,
			'current'  => null,
			'class'    => 'mdjm-radio',
			'label_pos' => 'before',
			'options'  => array()
		);

		$args = wp_parse_args( $args, $defaults );

		$class = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );

		$output = '';
		
		if ( ! empty( $args['options'] ) )	{

			$i = 0;

			foreach( $args['options'] as $key => $value )	{

				if ( $label_pos == 'before' )	{
					$output .= $value . '&nbsp';
				}

				$output = '<input type="radio" name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['name'] ) . '-' . $key . '" class="' . $class . ' ' . esc_attr( $args['name'] ) . '" />';

				if ( $label_pos == 'after' )	{
					$output .= '&nbsp' . $value;
				}

				if ( $i < count( $args['options'] ) )	{
					$output .= '<br />';
				}

				$i++;

			}
			
		}

		return $output;
	} // radio

	/**
	 * Renders an HTML Text field
	 *
	 * @since	1.3.7
	 *
	 * @param	arr		$args	Arguments for the text field
	 * @return	str		Text field
	 */
	public function text( $args = array() ) {

		$defaults = array(
			'id'           => '',
			'name'         => isset( $name )  ? $name  : 'text',
			'type'         => 'text',
			'value'        => isset( $value ) ? $value : null,
			'label'        => isset( $label ) ? $label : null,
			'desc'         => isset( $desc )  ? $desc  : null,
			'placeholder'  => '',
			'class'        => 'regular-text',
			'readonly'     => false,
			'disabled'     => false,
			'autocomplete' => '',
			'required'     => false,
			'data'         => false
		);

		$args = wp_parse_args( $args, $defaults );
		
		$args['id'] = ! empty( $args['id'] ) ? $args['id'] : $args['name'];

		$class = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );
		$disabled = '';
		$readonly = '';
		$required = '';
		if( $args['disabled'] ) {
			$disabled = ' disabled="disabled"';
		}
		if( $args['readonly'] ) {
			$readonly = ' readonly="readonly"';
		}
		if( $args['required'] ) {
			$required = ' required';
		}

		$data = '';
		if ( ! empty( $args['data'] ) ) {
			foreach ( $args['data'] as $key => $value ) {
				$data .= 'data-' . mdjm_sanitize_key( $key ) . '="' . esc_attr( $value ) . '" ';
			}
		}

		$output = '<span id="mdjm-' . mdjm_sanitize_key( $args['name'] ) . '-wrap">';

		$output .= '<label for="' . mdjm_sanitize_key( $args['id'] ) . '">' . esc_html( $args['label'] ) . '</label>';

		$output .= '<input type="' . $args['type'] . '" name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['id'] )  . '" autocomplete="' . esc_attr( $args['autocomplete'] )  . '" value="' . esc_attr( $args['value'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" class="' . $class . '" ' . $data . '' . $disabled . '' . $readonly . '' . $required . '/>';
		
		$output .= '</span>';
		
		if ( ! empty( $args['desc'] ) ) {
			$output .= '<br />';
			$output .= '<span class="description">' . esc_html( $args['desc'] ) . '</span>';
		}

		return $output;
	} // text

	/**
	 * Renders a date picker
	 *
	 * @since	1.3.7
	 *
	 * @param	arr		$args	Arguments for the text field
	 * @return	str		Datepicker field
	 */
	public function date_field( $args = array() ) {

		if( empty( $args['class'] ) ) {
			$args['class'] = 'mdjm_datepicker';
		} elseif( ! strpos( $args['class'], 'mdjm_datepicker' ) ) {
			$args['class'] .= ' mdjm_datepicker';
		}

		return $this->text( $args );
	} // date_field

	/**
	 * Renders an HTML textarea
	 *
	 * @since	1.3.7
	 *
	 * @param	arr		$args	Arguments for the textarea
	 * @return	srt		textarea
	 */
	public function textarea( $args = array() ) {
		$defaults = array(
			'name'        => 'textarea',
			'value'       => null,
			'label'       => null,
			'placeholder' => null,
			'desc'        => null,
			'class'       => 'large-text',
			'disabled'    => false
		);

		$args = wp_parse_args( $args, $defaults );

		$class = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );
		$disabled = '';

		if( $args['disabled'] ) {
			$disabled = ' disabled="disabled"';
		}
		
		$placeholder = '';
		if( $args['placeholder'] ) {
			$placeholder = ' placeholder="' . esc_attr( $args['placeholder'] ) . '"';
		}

		$output = '<span id="mdjm-' . mdjm_sanitize_key( $args['name'] ) . '-wrap">';

			$output .= '<label for="' . mdjm_sanitize_key( $args['name'] ) . '">' . esc_html( $args['label'] ) . '</label>';

			$output .= '<textarea name="' . esc_attr( $args['name'] ) . '" id="' . mdjm_sanitize_key( $args['name'] ) . '" class="' . $class . '"' . $disabled . $placeholder . '>' . esc_attr( $args['value'] ) . '</textarea>';

			if ( ! empty( $args['desc'] ) ) {
				$output .= '<span class="mdjm-description">' . esc_html( $args['desc'] ) . '</span>';
			}

		$output .= '</span>';

		return $output;
	} // textarea
	
	/**
	 * Renders an HTML Number field
	 *
	 * @since	1.3.7
	 *
	 * @param	arr		$args	Arguments for the text field
	 * @return	str		Text field
	 */
	public function number( $args = array() ) {

		$defaults = array(
			'id'           => '',
			'name'         => isset( $name )  ? $name  : 'text',
			'value'        => isset( $value ) ? $value : null,
			'label'        => isset( $label ) ? $label : null,
			'desc'         => isset( $desc )  ? $desc  : null,
			'placeholder'  => '',
			'class'        => 'small-text',
			'min'          => '',
			'max'          => '',
			'disabled'     => false,
			'autocomplete' => '',
			'data'         => false
		);

		$args = wp_parse_args( $args, $defaults );
		
		$args['id'] = ! empty( $args['id'] ) ? $args['id'] : $args['name'];

		$class = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );
		$disabled = '';
		if( $args['disabled'] ) {
			$disabled = ' disabled="disabled"';
		}

		$data = '';
		if ( ! empty( $args['data'] ) ) {
			foreach ( $args['data'] as $key => $value ) {
				$data .= 'data-' . mdjm_sanitize_key( $key ) . '="' . esc_attr( $value ) . '" ';
			}
		}
		
		$min = ! empty( $args['min'] ) ? ' min="' . $args['min'] . '"' : '';
		$max = ! empty( $args['max'] ) ? ' max="' . $args['max'] . '"' : '';
		
		if ( $max > 5 )	{
			$max = 5;
		}

		$output = '<span id="mdjm-' . mdjm_sanitize_key( $args['name'] ) . '-wrap">';

			$output .= '<label for="' . mdjm_sanitize_key( $args['id'] ) . '">' . esc_html( $args['label'] ) . '</label>';

			if ( ! empty( $args['desc'] ) ) {
				$output .= '<span class="mdjm-description">' . esc_html( $args['desc'] ) . '</span>';
			}

			$output .= '<input type="number" name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['id'] )  . '" autocomplete="' . esc_attr( $args['autocomplete'] )  . '" value="' . esc_attr( $args['value'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" class="' . $class . '" ' . $data . '' . $min . '' . $max . '' . $disabled . '/>';

		$output .= '</span>';

		return $output;
	} // number
	
	/**
	 * Renders an HTML Hidden field
	 *
	 * @since	1.3.7
	 *
	 * @param	arr		$args	Arguments for the text field
	 * @return	str		Hidden field
	 */
	public function hidden( $args = array() ) {

		$defaults = array(
			'id'           => '',
			'name'         => isset( $name )  ? $name  : 'hidden',
			'value'        => isset( $value ) ? $value : null
		);

		$args = wp_parse_args( $args, $defaults );
		
		$args['id'] = ! empty( $args['id'] ) ? $args['id'] : $args['name'];

		$output = '<input type="hidden" name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['id'] )  . '" value="' . esc_attr( $args['value'] ) . '" />';

		return $output;
	} // hidden

	/**
	 * Renders an ajax user search field
	 *
	 * @since	1.3.7
	 *
	 * @param	arr		$args
	 * @return	str		Text field with ajax search
	 */
	public function ajax_user_search( $args = array() ) {

		$defaults = array(
			'name'        => 'user_id',
			'value'       => null,
			'placeholder' => __( 'Enter username', 'mobile-dj-manager' ),
			'label'       => null,
			'desc'        => null,
			'class'       => '',
			'disabled'    => false,
			'autocomplete'=> 'off',
			'data'        => false
		);

		$args = wp_parse_args( $args, $defaults );

		$args['class'] = 'mdjm-ajax-user-search ' . $args['class'];

		$output  = '<span class="mdjm_user_search_wrap">';
			$output .= $this->text( $args );
			$output .= '<span class="mdjm_user_search_results hidden"><a class="mdjm-ajax-user-cancel" title="' . __( 'Cancel', 'mobile-dj-manager' ) . '" aria-label="' . __( 'Cancel', 'mobile-dj-manager' ) . '" href="#">x</a><span></span></span>';
		$output .= '</span>';

		return $output;
	} // ajax_user_search

} // MDJM_HTML_Elements
