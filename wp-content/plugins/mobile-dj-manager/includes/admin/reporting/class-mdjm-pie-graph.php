<?php
/**
 * This plugin utilizes Open Source code. Details of these open source projects along with their licenses can be found below.
 * We acknowledge and are grateful to these developers for their contributions to open source.
 *
 * Project: mobile-dj-manager https://github.com/deckbooks/mobile-dj-manager
 * License: (GNU General Public License v2.0) https://github.com/deckbooks/mobile-dj-manager/blob/master/license.txt
 *
 * @author: Mike Howard, Jack Mawhinney, Dan Porter
 *
 * Graphs
 *
 * This class handles building pretty report graphs
 *
 * @package     MDJM
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.4
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MDJM_Graph Class
 *
 * @since   1.0.4
 */
class MDJM_Pie_Graph extends MDJM_Graph {

	/*
	Simple example:

	data format for each point: array( location on x, location on y )

	$data = array(
		array( 'Label'   => 'value' ),
		array( 'Label 2' => 'value 2' ),
	);

	$graph = new MDJM_Pie_Graph( $data );
	$graph->display();

	*/

	/**
	 * Data to graph
	 *
	 * @var     arr
	 * @since   1.0.4
	 */
	private $data;

	/**
	 * Unique ID for the graph
	 *
	 * @var     str
	 * @since   1.0.4
	 */
	private $id = '';

	/**
	 * Graph options
	 *
	 * @var     arr
	 * @since   1.0.4
	 */
	private $options = array();

	/**
	 * Get things started
	 *
	 * @since   1.0.4
	 */
	public function __construct( $_data, $options = array() ) {

		$this->data = $_data;
		// Set this so filters recieving $this can quickly know if it's a graph they want to modify
		$this->type = 'pie';

		// Generate unique ID, add 'a' since md5 can leave a numerical first character
		$this->id = 'a' . md5( rand() );

		// Setup default options;
		$defaults = array(
			'radius'           => 1,
			'legend'           => true,
			'legend_formatter' => false,
			'legend_columns'   => 3,
			'legend_position'  => 's',
			'show_labels'      => false,
			'label_threshold'  => 0.01,
			'label_formatter'  => 'mdjmLabelFormatter',
			'label_bg_opacity' => 0.75,
			'label_radius'     => 1,
			'height'           => '300',
			'hoverable'        => true,
			'clickable'        => false,
			'threshold'        => false,
		);

		$this->options = wp_parse_args( $options, $defaults );

		add_action( 'mdjm_graph_load_scripts', array( $this, 'load_additional_scripts' ) );

	} // __construct

	/**
	 * Load the graphing library script
	 *
	 * @since   1.0.4
	 */
	public function load_additional_scripts() {
		// Use minified libraries if SCRIPT_DEBUG is turned off
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		wp_enqueue_script( 'jquery-flot-pie', MDJM_PLUGIN_URL . '/assets/js/jquery.flot.pie' . $suffix . '.js' );
	} // load_additional_scripts

	/**
	 * Build the graph and return it as a string
	 *
	 * @var     arr
	 * @since   1.0.4
	 * @return  str
	 */
	public function build_graph() {

		if ( count( $this->data ) ) {
			$this->load_scripts();

			ob_start();
			?>
			<script type="text/javascript">
				var <?php echo esc_attr( $this->id ); ?>_data = [
				<?php foreach ( $this->data as $label => $value ) : ?>
					<?php echo '{ label: "' . esc_attr( $label ) . '", data: "' . esc_html( $value ) . '" },' . "\n"; ?>
				<?php endforeach; ?>
				];

				var <?php echo esc_attr( $this->id ); ?>_options = {
					series: {
						pie: {
							show: true,
							radius: <?php echo esc_html( $this->options['radius'] ); ?>,
							label: [],
						},
						mdjm_vars: {
							id: '<?php echo esc_html( $this->id ); ?>',
						}
					},
					legend: {
						show: <?php echo esc_html( $this->options['legend'] ); ?>,
					},
					grid: {},
				};

				<?php if ( true === $this->options['show_labels'] ) : ?>
					<?php echo esc_attr( $this->id ); ?>_options.series.pie.label.show = true;
					<?php echo esc_attr( $this->id ); ?>_options.series.pie.label.formatter = <?php echo esc_html( $this->options['label_formatter'] ); ?>;
					<?php echo esc_attr( $this->id ); ?>_options.series.pie.label.threshold = <?php echo esc_html( $this->options['label_threshold'] ); ?>;
					<?php echo esc_attr( $this->id ); ?>_options.series.pie.label.radius = <?php echo esc_html( $this->options['label_radius'] ); ?>;
					<?php echo esc_attr( $this->id ); ?>_options.series.pie.label.background = { opacity: <?php echo esc_html( $this->options['label_bg_opacity'] ); ?> };
				<?php endif; ?>

				<?php if ( true === $this->options['legend'] && ! empty( $this->options['legend_formatter'] ) ) : ?>
					<?php echo esc_attr( $this->id ); ?>_options.legend.labelFormatter = <?php echo esc_html( $this->options['legend_formatter'] ); ?>;
					<?php echo esc_attr( $this->id ); ?>_options.legend.noColumns = <?php echo esc_html( $this->options['legend_columns'] ); ?>;
					<?php echo esc_attr( $this->id ); ?>_options.legend.position = "<?php echo esc_html( $this->options['legend_position'] ); ?>";
				<?php endif; ?>

				<?php if ( true === $this->options['hoverable'] ) : ?>
					<?php echo esc_attr( $this->id ); ?>_options.grid.hoverable = true;
				<?php endif; ?>

				<?php if ( true === $this->options['clickable'] ) : ?>
					<?php echo esc_attr( $this->id ); ?>_options.grid.clickable = true;
				<?php endif; ?>

				jQuery( document ).ready( function($) {
					var <?php echo esc_attr( $this->id ); ?>Chart = $('#mdjm-pie-graph-<?php echo esc_attr( $this->id ); ?>');
					$.plot( <?php echo esc_attr( $this->id ); ?>Chart, <?php echo esc_attr( $this->id ); ?>_data, <?php echo esc_attr( $this->id ); ?>_options );
					<?php if ( ! wp_is_mobile() ) : ?>
					$(<?php echo esc_attr( $this->id ); ?>Chart).on('plothover', function (event, pos, item) {
						$('.mdjm-legend-item-wrapper').css('background-color', 'inherit');
						if ( item ) {
							var label = item.series.label;
							var id    = item.series.mdjm_vars.id;

							var slug = label.toLowerCase().replace(/\s/g, '-');
							var legendTarget = '#' + id + slug;

							$('.mdjm-legend-item-wrapper' + legendTarget).css('background-color', '#f0f0f0');
						}
					});
					<?php endif; ?>
				});

			</script>
			<div class="mdjm-pie-graph-wrap">
				<div id="mdjm-pie-graph-<?php echo esc_attr( $this->id ); ?>" class="mdjm-pie-graph" style="height: <?php echo esc_attr( $this->options['height'] ); ?>px;"></div>
				<div id="mdjm-pie-legend-<?php echo esc_attr( $this->id ); ?>" class="mdjm-pie-legend"></div>
			</div>
			<?php
		}
		return apply_filters( 'mdjm_pie_graph_output', ob_get_clean(), $this->id, $this->data, $this->options );

	} // build_graph

} // MDJM_Pie_Graph
