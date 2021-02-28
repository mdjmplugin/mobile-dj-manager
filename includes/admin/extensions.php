<?php
/**
 * Admin Extensions
 *
 * @package     MDJM
 * @subpackage  Admin/Extensions
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4.7
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Display the addons page.
 *
 * @since	1.4.7
 * @return	void
 */
function mdjm_extensions_page()	{
	setlocale( LC_MONETARY, get_locale() );
	$extensions     = mdjm_get_extensions();
	$extensions_url = esc_url( add_query_arg( array(
		'utm_source'   => 'plugin-addons-page',
		'utm_medium'   => 'plugin',
		'utm_campaign' => 'MDJM_Addons_Page',
		'utm_content'  => 'All Addons'
	), 'https://mdjm.co.uk/add-ons/' ) );

	$donate_url = esc_url( add_query_arg( array(
		'utm_source'   => 'plugin-addons-page',
		'utm_medium'   => 'plugin',
		'utm_campaign' => 'MDJM_Addons_Page',
		'utm_content'  => 'All Addons'
	), 'https://mdjm.co.uk/donate/' ) );

	?>
	<div class="wrap about-wrap mdjm-about-wrapp">
		<h1>
			<?php esc_html_e( 'Extensions for MDJM Event Management', 'mobile-dj-manager' ); ?>
		</h1>
		<div>
			<p><?php esc_html_e( 'These extensions', 'mobile-dj-manager' ); ?> <em><strong><?php esc_html_e( 'add even more functionality', 'mobile-dj-manager' ); ?></em></strong> <?php esc_html_e( 'to your MDJM Event Management solution.', 'mobile-dj-manager' ); ?></p>
			<p><?php printf( __( '<em><strong>Remember</strong></em> your donations help pay for the development of the MDJM Event Management plugin, it\'s extensions, and allows us to provide these extensions for free. <a href="%s" target="_blank">Please make a donation today</a>.', 'mobile-dj-manager'), $donate_url ); ?></p>
		</div>

		<div class="mdjm-extension-wrapper grid3">
			<?php foreach ( $extensions as $key => $extension ) :
				$link        = 'https://mdjm.co.uk/downloads/';
				$link        = esc_url( add_query_arg( array(
					'utm_source'   => 'plugin-addons-page',
					'utm_medium'   => 'plugin',
					'utm_campaign' => 'MDJM_Addons_Page',
					'utm_content'  => $extension->title
				), $link ) );
				$price       = false;

				if ( isset( $extension->price ) ) {
					if ( '0.00' == $extension->price )	{
						$price = false;
					} else	{
						$price = '&pound;' . number_format( $extension->price, 2 );
					}
				}

				$slug = $extension->slug;
				$title = $extension->title;
				$image = $extension->image;
				$summary = $extension->summary;
			?>

                <article class="col">
                    <div class="mdjm-extension-item">
                        <div class="mdjm-extension-item-img" style="background-image: url(<?php echo esc_url( $image ); ?>);">

                        </div>
                        <div class="mdjm-extension-item-desc">
                            <p class="mdjm-extension-item-heading"><strong><?php echo esc_html( $title ); ?></strong></p>
                            <div class="mdjm-extension-item-excerpt">
                            	<p><?php echo esc_html( $summary ); ?></p>
                            </div>
                            <div class="mdjm-extension-buy-now">
                                <?php if ( ! is_plugin_active( $slug . '/' . $slug . '.php' ) ) : ?>
                                	<?php if ( ! $price ) : ?>
                                    	<?php
										$link = add_query_arg( array(
											's'    => $slug,
											'tab'  => 'search',
											'type' => 'term'
										), admin_url( 'plugin-install.php' ) );
										?>
                                    	<a href="<?php echo esc_url( $link ); ?>" class="button-primary"><?php esc_html_e( 'Download Now for Free', 'mobile-dj-manager' ); ?></a>
                                    <?php else : ?>
                                        <a href="<?php echo esc_url( $link ); ?>" class="button-primary" target="_blank"><?php printf( esc_html__( 'Buy Now for %s', 'mobile-dj-manager' ), esc_html( $price ) ); ?></a>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <p class="button-primary" disabled="disabled"><?php esc_html_e( 'Already Installed', 'mobile-dj-manager' ); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </article>
			<?php endforeach; ?>
		</div>
	</div>
	<?php

} // mdjm_extensions_page

/**
 * Retrieve the published extensions from mobile-dj-manager.com and store within transient.
 *
 * @since	1.0.3
 * @return	void
 */
function mdjm_get_extensions()	{
	$extensions = get_transient( '_mdjm_extensions_feed' );
	$extensions = false;

	if ( false === $extensions || doing_action( 'mdjm_daily_scheduled_events' ) )	{
		$route    = esc_url( 'https://mdjm.co.uk/api/extensions.json' );
		$response = wp_remote_get( $route );

		if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
			$body    = wp_remote_retrieve_body( $response );
			$content = json_decode( $body );

			if ( is_object( $content ) && isset( $content->extensions ) ) {
				set_transient( '_mdjm_extensions_feed', $content->extensions, DAY_IN_SECONDS / 2 ); // Store for 12 hours
				$extensions = $content->extensions;
			}
		}
	}

	return $extensions;
} // mdjm_get_extensions
add_action( 'mdjm_daily_scheduled_events', 'mdjm_get_extensions' );
