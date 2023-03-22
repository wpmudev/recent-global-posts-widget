<?php
/*
Plugin Name: Recent Global Posts Widget
Plugin URI: http://premium.wpmudev.org/project/recent-global-posts-widget/
Description: Show the most recent global posts in a widget
Author: WPMU DEV
Author URI: http://premium.wpmudev.org/
Version: 3.1.0
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

		if ( filter_var( RECENT_GLOBAL_POSTS_WIDGET_MAIN_BLOG_ONLY, FILTER_VALIDATE_BOOLEAN ) ) {
			if ( '1' === $wpdb->blogid ) {
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

add_filter( 'network_posts_where', 'rgpwidget_exclude_blogs', 10, 2 );
if ( !function_exists( 'rgpwidget_exclude_blogs' ) ) :
	/**
	 * Excludes blogs from network query.
	 *
	 * @since 3.0.4
	 *
	 * @param string $where Initial WHERE clause of the network query.
	 * @param Network_Query $query The network query object.
	 * @return string Updated WHERE clause.
	 */
	function rgpwidget_exclude_blogs( $where, Network_Query $query ) {
		return !empty( $query->query_vars['blogs_not_in'] )
			? $where . sprintf( ' AND %s.BLOG_ID NOT IN (%s) ', $query->network_posts, implode( ', ', (array)$query->query_vars['blogs_not_in'] ) )
			: $where;
	}
endif;

/**
 * Recent Global Posts Widget class.
 */
class Recent_Global_Posts_Widget extends WP_Widget {

	const NAME = __CLASS__;

	const DISPLAY_TITLE_CONTENT      = 'title_content';
	const DISPLAY_TITLE_BLOG_CONTENT = 'title_blog_content';
	const DISPLAY_TITLE              = 'title';
	const DISPLAY_TITLE_BLOG         = 'title_blog';
	const DISPLAY_CONTENT            = 'content';
	const DISPLAY_BLOG_CONTENT       = 'blog_content';

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

		parent::__construct( 'rgpwidget', __( 'Recent Global Posts', 'rgpwidget' ), $widget_options, $control_options );
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

		$substr = function_exists( 'mb_substr' ) ? 'mb_substr' : 'substr';
		$instance = wp_parse_args(
			$instance,
			array(
				'recentglobalpoststitle'             => __( 'Recent Global Posts' ),
				'recentglobalpostsdisplay'           => 5,
				'recentglobalpostsnumber'            => 10,
				'recentglobalpoststitlecharacters'   => 30,
				'recentglobalpostscontentcharacters' => 100,
				'recentglobalpostsavatars'           => 'show',
				'recentglobalpostsavatarsize'        => '32',
				'recentglobalpoststype'              => 'post',
				'post_type'                          => 'post',
				'exclude_blogs'                      => '',
			)
		);

		$title = $instance['recentglobalpoststitle'];
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$recentglobalpoststype   = $instance['recentglobalpoststype'];
		$recentglobalpostsnumber = $instance['recentglobalpostsnumber'];

		$exclude_blogs = array_filter( array_map( 'intval', explode( ',', $instance['exclude_blogs'] ) ) );

		$network_query = network_query_posts(
			array(
				'post_type'      => $instance['recentglobalpoststype'],
				'posts_per_page' => $instance['recentglobalpostsnumber'],
				'blogs_not_in'   => $exclude_blogs,
			)
		);
		
		echo $args['before_widget'];
			echo $args['before_title'], $title, $args['after_title'];
			if ( network_have_posts() ) :
				echo '<ul class="rgpwidget_ul">';
				while ( network_have_posts() ) :
					network_the_post();
					echo '<li class="rgpwidget_li" style="padding-bottom: 15px">';
						$post = network_get_post();
						$the_permalink = network_get_permalink();
						$the_title = network_get_the_title();
						$the_content = network_get_the_content();

						if ( 'show' === $instance['recentglobalpostsavatars'] ) :
							echo '<a href="', $the_permalink, '">', get_avatar( network_get_the_author_id(), $instance['recentglobalpostsavatarsize'], '' ), '</a>&nbsp;&nbsp;';
						endif;

						$blog = get_blog_details( $post->BLOG_ID );
						$blog_title = $blog ? $blog->blogname : '';
						if ( $blog_title == '' ) {
							$blog_title = $the_permalink;
						}
						$title = $substr( $the_title, 0, $instance['recentglobalpoststitlecharacters'] );

				$content = $substr( strip_tags( $the_content ), 0, $instance['recentglobalpostscontentcharacters'] );
				$content = do_shortcode( $content );				
						switch ( $instance['recentglobalpostsdisplay'] ) {
							case self::DISPLAY_BLOG_CONTENT:
								echo '<a href="', $the_permalink, '">', '[', $blog_title, ']</a>';
								echo '<br>';
								echo $content, $instance['recentglobalpostscontentcharacters'] < strlen( $the_content ) ? '&hellip;' : '';
								echo '<br><a href="', $the_permalink, '">', __( 'Read More', 'rgpwidget' ), ' &raquo;</a>';
								break;
							case self::DISPLAY_CONTENT:
								echo $content, $instance['recentglobalpostscontentcharacters'] < strlen( $the_content ) ? '&hellip;' : '';
								echo '<br><a href="', $the_permalink, '">', __( 'Read More', 'rgpwidget' ), ' &raquo;</a>';
								break;
							case self::DISPLAY_TITLE:
								echo '<a href="', $the_permalink, '">', $title, '</a>';
								break;
							case self::DISPLAY_TITLE_BLOG:
								echo '<a href="', $the_permalink, '">', $title, ' [', $blog_title, ']</a>';
								break;
							case self::DISPLAY_TITLE_BLOG_CONTENT:
								echo '<a href="', $the_permalink, '">', $title, ' [', $blog_title, ']</a>';
								echo '<br>';
								echo $content;
								break;
							case self::DISPLAY_TITLE_CONTENT:
							default:
								echo '<a href="', $the_permalink, '">', $title, '</a>';
								echo '<br>';
								echo $content;
								break;
						}
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
		$instance   = wp_parse_args(
			$instance,
			array(
				'recentglobalpoststitle'             => __( 'Recent Global Posts' ),
				'recentglobalpostsdisplay'           => 5,
				'recentglobalpostsnumber'            => 10,
				'recentglobalpoststitlecharacters'   => 30,
				'recentglobalpostscontentcharacters' => 100,
				'recentglobalpostsavatars'           => 'show',
				'recentglobalpostsavatarsize'        => '32',
				'recentglobalpoststype'              => 'post',
				'post_type'                          => 'post',
				'exclude_blogs'                      => '',
			)
		);

		$displays = array(
			self::DISPLAY_TITLE_CONTENT      => __( 'Title and content', 'rgpwidget' ),
			self::DISPLAY_TITLE_BLOG_CONTENT => __( 'Title, blog name and content', 'rgpwidget' ),
			self::DISPLAY_TITLE              => __( 'Title only', 'rgpwidget' ),
			self::DISPLAY_TITLE_BLOG         => __( 'Title and blog name', 'rgpwidget' ),
			self::DISPLAY_CONTENT            => __( 'Content only', 'rgpwidget' ),
			self::DISPLAY_BLOG_CONTENT       => __( 'Blog name and content', 'rgpwidget' ),
		);

		

		?><p>
			<label for="<?php echo $this->get_field_id( 'recentglobalpoststitle' ); ?>"><?php _e( 'Title', 'rgpwidget' ); ?>:</label>
			<input type="text" id="<?php echo $this->get_field_id( 'recentglobalpoststitle' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'recentglobalpoststitle' ); ?>" value="<?php echo esc_attr( stripslashes( $instance['recentglobalpoststitle'] ) ); ?>">
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'recentglobalpostsdisplay' ); ?>"><?php _e( 'Display', 'rgpwidget' ); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'recentglobalpostsdisplay' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'recentglobalpostsdisplay' ) ;?>">
				<?php foreach ( $displays as $key => $label ) : ?>
				<option value="<?php echo $key;?>"<?php selected( $key, $instance['recentglobalpostsdisplay'] ); ?>><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'recentglobalpostsnumber' ); ?>"><?php _e( 'Number', 'rgpwidget' ); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'recentglobalpostsnumber' );?>" class="widefat" name="<?php echo $this->get_field_name( 'recentglobalpostsnumber' ); ?>">
				<?php for ( $counter = 1; $counter <= 25; $counter++ ) : ?>
					<option value="<?php echo $counter; ?>"<?php selected( $counter, $instance['recentglobalpostsnumber'] ); ?>><?php echo $counter; ?></option>
				<?php endfor; ?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'recentglobalpoststitlecharacters' ); ?>"><?php _e( 'Title Characters', 'rgpwidget' ); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'recentglobalpoststitlecharacters' ) ;?>" class="widefat" name="<?php echo $this->get_field_name( 'recentglobalpoststitlecharacters' ); ?>">
				<?php for ( $counter = 1; $counter <= 200; $counter++ ) : ?>
					<option value="<?php echo $counter; ?>"<?php selected( $counter, $instance['recentglobalpoststitlecharacters'] ); ?>><?php echo $counter; ?></option>
				<?php endfor; ?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'recentglobalpostscontentcharacters' ); ?>"><?php _e( 'Content Characters', 'rgpwidget' ); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'recentglobalpostscontentcharacters' ) ;?>" class="widefat" name="<?php echo $this->get_field_name( 'recentglobalpostscontentcharacters' ); ?>">
				<?php for ( $counter = 1; $counter <= 500; $counter++ ) : ?>
					<option value="<?php echo $counter; ?>"<?php selected( $counter, $instance['recentglobalpostscontentcharacters'] ) ;?>><?php echo $counter; ?></option>
				<?php endfor; ?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'recentglobalpostsavatars' ); ?>"><?php _e( 'Avatars', 'rgpwidget' ); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'recentglobalpostsavatars' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'recentglobalpostsavatars' ); ?>">
				<option value="show"<?php selected( $instance['recentglobalpostsavatars'], 'show' ); ?> ><?php _e( 'Show', 'rgpwidget' ); ?></option>
				<option value="hide"<?php selected( $instance['recentglobalpostsavatars'], 'hide' ); ?> ><?php _e( 'Hide', 'rgpwidget' ); ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'recentglobalpostsavatarsize' ); ?>"><?php _e( 'Avatar Size', 'rgpwidget' ); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'recentglobalpostsavatarsize' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'recentglobalpostsavatarsize' ) ;?>">
				<option value="16"<?php selected( $instance['recentglobalpostsavatarsize'], '16' ); ?>>16px</option>
				<option value="32"<?php selected( $instance['recentglobalpostsavatarsize'], '32' ); ?>>32px</option>
				<option value="48"<?php selected( $instance['recentglobalpostsavatarsize'], '48' ); ?>>48px</option>
				<option value="96"<?php selected( $instance['recentglobalpostsavatarsize'], '96' ); ?>>96px</option>
				<option value="128"<?php selected( $instance['recentglobalpostsavatarsize'], '128' ) ;?>>128px</option>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'recentglobalpoststype' ); ?>"><?php _e( 'Post Type', 'rgpwidget' ) ;?>:</label>
			<select id="<?php echo $this->get_field_id( 'recentglobalpoststype' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'recentglobalpoststype' ); ?>">
				<?php if ( !empty( $post_types ) ) : ?>
					<?php foreach ( $post_types as $r ) : ?>
						<option value="<?php echo $r; ?>"<?php selected( $instance['recentglobalpoststype'], $r ); ?>><?php echo esc_html( $r ); ?></option>
					<?php endforeach; ?>
				<?php else : ?>
					<option value="post"<?php selected( $instance['recentglobalpoststype'], 'post' ); ?>><?php _e( 'post' ); ?></option>
				<?php endif; ?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'exclude_blogs' ); ?>"><?php _e( 'Exclude Blogs', 'rgpwidget' ); ?>:</label>
			<input type="text" id="<?php echo $this->get_field_id( 'exclude_blogs' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'exclude_blogs' ); ?>" value="<?php echo esc_attr( $instance['exclude_blogs'] ); ?>"><br>
			<small><?php esc_html_e( 'Blog IDs, separated by commas.', 'rgpwidget' ); ?></small>
		</p>

		<input type="hidden" name="<?php echo $this->get_field_name( 'recentglobalpostssubmit' ); ?>" value="1">
		<?php
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
