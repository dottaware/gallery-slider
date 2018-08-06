<?php

/**
Plugin Name: Gallery Slider
Description: Wordpress plugin that lets you create an image carousel
Author: Stefano Dotta
Text Domain: image-carousel
Domain Path: /languages
Version: 1.2
*/

if ( ! defined('ABSPATH') ) {
	die('Please do not load this file directly.');
}


class Gallery_Slider_Widget extends WP_Widget {
  
    public $default_item_class = 'slider-item gallery-item';    

	/**
	 * Sets up a new widget instance.
	 *
	 * @since 2.8.0
	 * @access public
	 */
	public function __construct() {
		$widget_ops = array('classname' => 'gallery-slider-widget', 
				    'description' => __('Create a gallery slider based on bxSlider.'));

		$control_ops = array('width' => 400, 'height' => 350);
		
		parent::__construct('gallery-slider', __('Gallery Slider'), $widget_ops, $control_ops);	
			
	}
	
	
	
	

	/**
	 * Outputs the content for the current Text widget instance.
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance Settings for the current Text widget instance.
	 */
	public function widget( $args, $instance ) {
	
	    $count = isset( $instance['count'] ) ? (int) $instance['count'] : 0;		
		
		$args = array( 
			'post_parent'    => get_the_ID(),
			'post_type'      => 'attachment',
			'numberposts'    => -1, // show all
			'post_status'    => 'inherit',
			'post_mime_type' => 'image',
			// 'orderby'        => 'rand',
			// 'order'          => 'DESC',
			'exclude'     	 => get_post_thumbnail_id(),
		);
		
		$attachments = get_posts( $args );
	    
        // do not display widget if not enough images
        if ( count( $attachments ) < $count ) {
            return;
        }
		
	  	/** load all required bxslider scripts */
		$this->enqueue_frontend_scripts();

				
		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
	    $title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		
	    // echo '<h3 class="widgettitle widget-title">Localisation sur la carte</h3>';
	  
		echo $args['before_widget'];
	  
	    // Display the widget title.
	  // if ( ! empty( $instance['title'] ) ) {
	  //	    echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
	  // }
	    if ( ! empty( $title ) ) {
	    	echo $args['before_title'] . '<h3 class="widgettitle widget-title">' . $title . '</h3>' . $args['after_title'];
	    }	  

        $class = "slider-item gallery-item";
	  
		if ( $attachments ) {
			echo '<div class="galley-image-carousel gallery">';		    
			echo '<ul class="bxslider">';

			foreach ( $attachments as $attachment ) {				
			    // $img = wp_get_attachment_link( $attachment->ID, 'medium_large', false );
			    $img_src = wp_get_attachment_image_src( $attachment->ID, 'featured-small' );
			    // $img_src = wp_get_attachment_image_src( $attachment->ID, 'medium_large' );
			    // $img_src]0] = wp_get_attachment_url( $attachment->ID );
			    $img = '<img class="alignnone" src="' . $img_src[0] . '">';
			    // $img = wp_get_attachment_image( $attachment->ID, 'medium_large' );
			  
				echo '<li class="' . $class . '">' . $img . '</li>';
			}
			echo '</ul>';
			echo '</div>';
		
		}
		
		echo $args['after_widget'];
	}

	/**
	 * Handles updating settings for the current widget instance.
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Settings to save or bool false to cancel saving.
	 */
	public function update( $new_instance, $old_instance ) {
	  
		$instance = $old_instance;
		
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['filter'] = ! empty( $new_instance['filter'] );
		$instance['dropdown'] = !empty($new_instance['dropdown']) ? 1 : 0;
	  
		$instance['count'] = (int) $new_instance['count'];
		if ( $instance['count'] < 1 || 100 < $instance['count'] ) {
			$instance['count'] = 10;
		}

		 
		return $instance;
	}

	/**
	 * Outputs the widget settings form.
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {
	  
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'text' => '' ) );
		$filter = isset( $instance['filter'] ) ? $instance['filter'] : 0;
		$title = sanitize_text_field( $instance['title'] );
		$dropdown = isset( $instance['dropdown'] ) ? (bool) $instance['dropdown'] : false;
		
	    $count = isset( $instance['count'] ) ? (int) $instance['count'] : 10;
		
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php esc_html_e( 'Minimum number of images attached to show widget:', 'summit' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" type="number" value="<?php echo (int) $count; ?>" min="1" max="100" />
		</p>

		 <p>
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('dropdown'); ?>" name="<?php echo $this->get_field_name('dropdown'); ?>"<?php checked( $dropdown ); ?> />
            <label for="<?php echo $this->get_field_id('dropdown'); ?>"><?php _e( 'Display as dropdown' ); ?></label><br />
		</p>

		<p>
			<input id="<?php echo $this->get_field_id('filter'); ?>" name="<?php echo $this->get_field_name('filter'); ?>" type="checkbox"<?php checked( $filter ); ?> />
			<label for="<?php echo $this->get_field_id('filter'); ?>"><?php _e('Automatically add paragraphs'); ?></label>
		</p>

		<?php
	}
	
	/**
	 * Outputs the bxslider scripts and css on the frontend
	 */
	public function enqueue_frontend_scripts() {
	
		wp_register_script( 'bxslider', plugins_url( '/js/jquery/jquery.bxslider.js', __FILE__ ), array('jquery') );
		wp_enqueue_script( 'bxslider' );
		
		wp_register_style('bxslider-style', plugins_url( '/css/jquery.bxslider.css', __FILE__ ), array(), '1.0.0' );
		wp_enqueue_style('bxslider-style');
		
		add_action( 'print_footer_scripts', array( $this, 'print_footer_scripts' ), 999 );
		
	}	
	
	/**
	 * Outputs the bxslider settings on the frontend
	 */	
	public function print_footer_scripts() {
	?>	
    <script type="text/javascript">
        jQuery(document).ready(function(){
            jQuery('.bxslider').bxSlider({
					mode: 'horizontal',
					easing: 'linear',
					infiniteLoop: true,
					startSlide: 0,
					slideWidth: 275,
					slideMargin: 10,
					minSlides: 1,
					maxSlides: 4,
					moveSlides: 0,
					speed: 2000,
					auto: false,
					pager: true,
					captions: false,
					preloadImages: 'visible',
					controls: true
				});
            });
    </script>
    <?php	
	}
	
} // end Widget class


add_action( 'widgets_init', function(){
     register_widget( 'Gallery_Slider_Widget' );
});