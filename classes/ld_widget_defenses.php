<?php
// Register and load the widget
function load_ld_widget_defenses() {
	register_widget( 'ld_widget_defenses' );
}
add_action( 'widgets_init', 'load_ld_widget_defenses' );

// Enable shortcodes in these widgets
add_filter('ld_widget_defenses','do_shortcode');


// Creating the widget
class ld_widget_defenses extends WP_Widget {

	function __construct() {
		parent::__construct(

		// Base ID of your widget
			'ld_widget_defenses',

			// Widget name will appear in UI
			/* translators: This title should be short to stay on one line when appearing in sidebar widget.*/
			__('Future Defenses list', 'lab-directory'),

			// Widget description
			array( 'description' => __( 'This widget displays a compact list of defenses', 'lab-directory' ), )
		);
	}

	// Creating widget front-end

	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );

		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];

			// This is where you run the code and display the output
            Lab_Directory_Shortcode::$current_template = 'defense_widget_list'; 
            echo  Lab_Directory_Shortcode::retrieve_template_html('defense_widget_list');
			echo $args['after_widget'];
	}
	 
	// Widget Backend
	public function form( $instance ) {
		if ( ! isset( $instance[ 'title' ] ) ) {
			$instance[ 'title' ] = __( 'Next Defenses', 'lab-directory' );
		}
		if ( ! isset( $instance[ 'period' ] ) ) {
			$instance[ 'period' ] = 'future';
		}
		// Widget admin form
		?>
<p>
<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance[ 'title' ] ); ?>" />
</p>
<p>

<label for="<?php echo $this->get_field_id( 'period' ); ?>"><?php _e( 'List for time period :' ); ?></label>  
        <select id="<?php echo $this->get_field_id('period'); ?>" name="<?php echo $this->get_field_name('period'); ?>">
            <option <?php selected($instance['period'], 'all');?> value="all"><?php /* translators: for all defenses*/echo __('all');?></option>
            <option <?php selected($instance['period'], 'future');?> value="future"><?php /* translators: for future defenses*/echo __('future','lab-directory');?></option>
            <option <?php selected($instance['period'], 'past');?> value="past"><?php echo /* translators: for past defenses*/ __('past','lab-directory');?></option>
        </select>
</p>
<?php 
}
     
// Updating widget replacing old instances with new
public function update( $new_instance, $old_instance ) {
	$instance = array();
	$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
	$instance['period'] = ( ! empty( $new_instance['period'] ) ) ? strip_tags( $new_instance['period'] ) : '';
	return $instance;
}
} // Class ld_widget_defenses ends here