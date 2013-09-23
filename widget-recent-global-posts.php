<?php
/*
Plugin Name: Recent Global Posts Widget
Plugin URI: http://premium.wpmudev.org/project/recent-global-posts-widget/
Description: Show the most recent global posts in a widget
Author: Incsub
Author URI: http://premium.wpmudev.org/
Version: 3.0.1
WDP ID: 66
*/

// +----------------------------------------------------------------------+
// | Copyright Incsub (http://incsub.com/)                                |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU General Public License, version 2, as  |
// | published by the Free Software Foundation.                           |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program; if not, write to the Free Software          |
// | Foundation, Inc., 51 Franklin St, Fifth Floor, Boston,               |
// | MA 02110-1301 USA                                                    |
// +----------------------------------------------------------------------+

// define default constan value
if ( !defined( 'RECENT_GLOBAL_POSTS_WIDGET_MAIN_BLOG_ONLY' ) ) {
	define( 'RECENT_GLOBAL_POSTS_WIDGET_MAIN_BLOG_ONLY', true );
}

add_action( 'widgets_init', 'rgpwidget_register_widget' );
if ( !function_exists( 'rgpwidget_register_widget' ) ) :
	/**
	 * Registers widget in the system.
	 *
	 * @global wpdb $wpdb Current database connection instance.
	 */
	function rgpwidget_register_widget() {
		global $wpdb;

		// don't register the widget if Network_Query class is not loaded
		if ( !class_exists( 'Network_Query', false ) ) {
			return;
		}

		if ( RECENT_GLOBAL_POSTS_WIDGET_MAIN_BLOG_ONLY == 'yes' ) {
			if ( $wpdb->blogid == 1 ) {
				register_widget( Recent_Global_Posts_Widget::NAME );
			}
		} else {
			register_widget( Recent_Global_Posts_Widget::NAME );
		}
	}
endif;

add_action( 'plugins_loaded', 'rgpwidget_load_text_domain' );
if ( !function_exists( 'rgpwidget_load_text_domain' ) ) :
	/**
	 * Loads text domain for Recent Global Posts Widget
	 *
	 * @since 3.0.2
	 */
	function rgpwidget_load_text_domain() {
		load_plugin_textdomain( 'rgpwidget', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
endif;

/**
 * Recent Global Posts Widget class.
 */
class Recent_Global_Posts_Widget extends WP_Widget {

	const NAME = __CLASS__;

	/**
	 * Constructor.
	 *
	 * @since 3.0.2
	 *
	 * @access public
	 * @param array $widget_options Array of widget options.
	 * @param array $control_options Array of control options.
	 */
	public function __construct( $widget_options = array(), $control_options = array() ) {
		$widget_options = array_merge( array(
			'classname'   => 'rgpwidget',
			'description' => __( 'Recent Global Posts', 'rgpwidget' ),
		), $widget_options );

		$control_options = array_merge( array(
			'id_base' => 'rgpwidget',
		), $control_options );

		parent::__construct( 'rgpwidget', __('Recent Global Posts', 'rgpwidget'), $widget_options, $control_options );
	}

	/**
	 * Renders widget content.
	 *
	 * @access public
	 * @global Network_Query $network_query
	 * @param array $args The array of widget arguments.
	 * @param array $instance The array of widget instance settings.
	 */
	public function widget( $args, $instance ) {
		global $network_query;

		extract( array_merge( array(
			'recentglobalpostsdisplay'           => '',
			'recentglobalpostsnumber'            => '',
			'recentglobalpoststitlecharacters'   => '',
			'recentglobalpostscontentcharacters' => '',
			'recentglobalpostsavatars'           => '',
			'recentglobalpostsavatarsize'        => '',
		), $instance ) );

		$title = !empty( $instance['title'] ) ? $instance['title'] : __( 'Recent Global Posts' );
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$recentglobalpoststype = !empty( $instance['recentglobalpoststype'] ) ? $instance['recentglobalpoststype'] : 'post';
		$recentglobalpostsnumber = !empty( $instance['recentglobalpostsnumber'] ) ? absint( $instance['recentglobalpostsnumber'] ) : 10;
		if ( !$recentglobalpostsnumber ) {
 			$recentglobalpostsnumber = 10;
		}

		$network_query = network_query_posts( array(
			'post_type'      => $recentglobalpoststype,
			'posts_per_page' => $recentglobalpostsnumber,
		) );

		echo $args['before_widget'];
			echo $args['before_title'], $title, $args['after_title'];
			if ( network_have_posts() ) :
				echo '<ul>';
				while ( network_have_posts() ) :
					network_the_post();
					echo '<li>';
						$the_permalink = network_get_permalink();
						$the_title = network_get_the_title();
						$the_content = network_get_the_content();

						if ( $recentglobalpostsavatars == 'show' ) :
							echo '<a href="', $the_permalink, '">', get_avatar( network_get_the_author_id(), $recentglobalpostsavatarsize, '' ), '</a> ';
						endif;

						if ( $recentglobalpostsdisplay == 'title_content' ) :
							echo '<a href="', $the_permalink, '">', substr( $the_title, 0, $recentglobalpoststitlecharacters ), '</a>';
							echo '<br>';
							echo substr( strip_tags( $the_content ), 0, $recentglobalpostscontentcharacters );
						elseif ( $recentglobalpostsdisplay == 'title' ) :
							echo '<a href="', $the_permalink, '">', substr( $the_title, 0, $recentglobalpoststitlecharacters ), '</a>';
						elseif ( $recentglobalpostsdisplay == 'content' ) :
							echo substr( strip_tags( $the_content ), 0, $recentglobalpostscontentcharacters ), '&hellip;';
							echo '<br><a href="', $the_permalink, '">', __( 'Read More', 'rgpwidget' ), ' &raquo;</a>';
						endif;
					echo '</li>';
				endwhile;
				echo '</ul>';
			endif;
		echo $args['after_widget'];
	}

	/**
	 * Renders widget settings form.
	 *
	 * @access public
	 * @param array $instance The array of current widget instance settings.
	 */
	public function form( $instance ) {
		$post_types = $this->get_post_types();
		$instance = wp_parse_args( $instance, array(
			'recentglobalpoststitle'             => '',
			'recentglobalpostsdisplay'           => '',
			'recentglobalpostsnumber'            => '',
			'recentglobalpoststitlecharacters'   => '',
			'recentglobalpostscontentcharacters' => '',
			'recentglobalpostsavatars'           => '',
			'recentglobalpostsavatarsize'        => '',
			'recentglobalpoststype'              => 'post',
			'post_type'                          => 'post'
		) );

		if ( !absint( $instance['recentglobalpostsnumber'] ) ) {
			$instance['recentglobalpostsnumber'] = 5;
		}

		if ( !absint( $instance['recentglobalpoststitlecharacters'] ) ) {
			$instance['recentglobalpoststitlecharacters'] = 30;
		}

		if ( !absint( $instance['recentglobalpostscontentcharacters'] ) ) {
			$instance['recentglobalpostscontentcharacters'] = 100;
		}

		?><p>
			<label for="<?php echo $this->get_field_id( 'recentglobalpoststitle' ) ?>"><?php _e( 'Title', 'rgpwidget' ) ?>:</label>
			<input type="text" id="<?php echo $this->get_field_id( 'recentglobalpoststitle' ) ?>" class="widefat" name="<?php echo $this->get_field_name( 'recentglobalpoststitle' ); ?>" value="<?php echo esc_attr( stripslashes( $instance['recentglobalpoststitle'] ) ) ?>">
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'recentglobalpostsdisplay' ) ?>"><?php _e( 'Display', 'rgpwidget' ) ?>:</label>
			<select id="<?php echo $this->get_field_id( 'recentglobalpostsdisplay' ) ?>" class="widefat" name="<?php echo $this->get_field_name( 'recentglobalpostsdisplay' ) ?>">
				<option value="title_content"<?php selected( $instance['recentglobalpostsdisplay'], 'title_content' ) ?>><?php _e( 'Title + Content', 'rgpwidget' ) ?></option>
				<option value="title"<?php selected( $instance['recentglobalpostsdisplay'], 'title' ) ?>><?php _e( 'Title Only', 'rgpwidget' ) ?></option>
				<option value="content"<?php selected( $instance['recentglobalpostsdisplay'], 'content' ) ?>><?php _e( 'Content Only', 'rgpwidget' ) ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'recentglobalpostsnumber' ) ?>"><?php _e( 'Number', 'rgpwidget' ) ?>:</label>
			<select id="<?php echo $this->get_field_id( 'recentglobalpostsnumber' ) ?>" class="widefat" name="<?php echo $this->get_field_name( 'recentglobalpostsnumber' ) ?>">
				<?php for ( $counter = 1; $counter <= 25; $counter++ ) : ?>
					<option value="<?php echo $counter ?>"<?php selected( $counter, $instance['recentglobalpostsnumber'] ) ?>><?php echo $counter ?></option>
				<?php endfor; ?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'recentglobalpoststitlecharacters' ) ?>"><?php _e( 'Title Characters', 'rgpwidget' ); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'recentglobalpoststitlecharacters' ) ?>" class="widefat" name="<?php echo $this->get_field_name( 'recentglobalpoststitlecharacters' ) ?>">
				<?php for ( $counter = 1; $counter <= 200; $counter++ ) : ?>
					<option value="<?php echo $counter ?>"<?php selected( $counter, $instance['recentglobalpoststitlecharacters'] ) ?>><?php echo $counter ?></option>
				<?php endfor; ?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'recentglobalpostscontentcharacters' ) ?>"><?php _e( 'Content Characters', 'rgpwidget' ); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'recentglobalpostscontentcharacters' ) ?>" class="widefat" name="<?php echo $this->get_field_name( 'recentglobalpostscontentcharacters' ) ?>">
				<?php for ( $counter = 1; $counter <= 500; $counter++ ) : ?>
					<option value="<?php echo $counter ?>"<?php selected( $counter, $instance['recentglobalpostscontentcharacters'] ) ?>><?php echo $counter ?></option>
				<?php endfor; ?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'recentglobalpostsavatars' ) ?>"><?php _e( 'Avatars', 'rgpwidget' ) ?>:</label>
			<select id="<?php echo $this->get_field_id( 'recentglobalpostsavatars' ) ?>" class="widefat" name="<?php echo $this->get_field_name( 'recentglobalpostsavatars' ) ?>">
				<option value="show"<?php selected( $instance['recentglobalpostsavatars'], 'show' ) ?> ><?php _e( 'Show', 'rgpwidget' ) ?></option>
				<option value="hide"<?php selected( $instance['recentglobalpostsavatars'], 'hide' ) ?> ><?php _e( 'Hide', 'rgpwidget' ) ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'recentglobalpostsavatarsize' ) ?>"><?php _e( 'Avatar Size', 'rgpwidget' ) ?>:</label>
			<select id="<?php echo $this->get_field_id( 'recentglobalpostsavatarsize' ) ?>" class="widefat" name="<?php echo $this->get_field_name( 'recentglobalpostsavatarsize' ) ?>">
				<option value="16"<?php selected( $instance['recentglobalpostsavatarsize'], '16' ) ?>>16px</option>
				<option value="32"<?php selected( $instance['recentglobalpostsavatarsize'], '32' ) ?>>32px</option>
				<option value="48"<?php selected( $instance['recentglobalpostsavatarsize'], '48' ) ?>>48px</option>
				<option value="96"<?php selected( $instance['recentglobalpostsavatarsize'], '96' ) ?>>96px</option>
				<option value="128"<?php selected( $instance['recentglobalpostsavatarsize'], '128' ) ?>>128px</option>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'recentglobalpoststype' ) ?>"><?php _e( 'Post Type', 'rgpwidget' ) ?>:</label>
			<select id="<?php echo $this->get_field_id( 'recentglobalpoststype' ) ?>" class="widefat" name="<?php echo $this->get_field_name( 'recentglobalpoststype' ) ?>">
				<?php if ( !empty( $post_types ) ) : ?>
					<?php foreach ( $post_types as $r ) : ?>
						<option value="<?php echo $r ?>"<?php selected( $instance['recentglobalpoststype'], $r ) ?>><?php echo esc_html( $r ) ?></option>
					<?php endforeach; ?>
				<?php else : ?>
					<option value="post"<?php selected( $instance['recentglobalpoststype'], 'post' ) ?>><?php _e( 'post' ) ?></option>
				<?php endif; ?>
			</select>
		</p>
		
		<input type="hidden" name="<?php echo $this->get_field_name( 'recentglobalpostssubmit' ) ?>" value="1"><?php
	}

	/**
	 * Returns array of available post types.
	 *
	 * @access public
	 * @global wpdb $wpdb The current database connection instance.
	 * @return array The array of available post types.
	 */
	public function get_post_types() {
		global $wpdb;

		$prefix = isset( $wpdb->base_prefix ) ? $wpdb->base_prefix : $wpdb->prefix;

		return $wpdb->get_col( "SELECT DISTINCT post_type FROM {$prefix}network_posts" );
	}

}