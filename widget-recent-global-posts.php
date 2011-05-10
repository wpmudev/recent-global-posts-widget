<?php
/*
Plugin Name: Recent Global Posts Widget
Description:
Author: Andrew Billits (Incsub)
Version: 2.1
Author URI:
WDP ID: 66
*/

/*
Copyright 2007-2009 Incsub (http://incsub.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/* -------------------- Update Notifications Notice -------------------- */
if ( !function_exists( 'wdp_un_check' ) ) {
  add_action( 'admin_notices', 'wdp_un_check', 5 );
  add_action( 'network_admin_notices', 'wdp_un_check', 5 );
  function wdp_un_check() {
    if ( !class_exists( 'WPMUDEV_Update_Notifications' ) && current_user_can( 'edit_users' ) )
      echo '<div class="error fade"><p>' . __('Please install the latest version of <a href="http://premium.wpmudev.org/project/update-notifications/" title="Download Now &raquo;">our free Update Notifications plugin</a> which helps you stay up-to-date with the most stable, secure versions of WPMU DEV themes and plugins. <a href="http://premium.wpmudev.org/wpmu-dev/update-notifications-plugin-information/">More information &raquo;</a>', 'wpmudev') . '</a></p></div>';
  }
}
/* --------------------------------------------------------------------- */

//------------------------------------------------------------------------//
//---Config---------------------------------------------------------------//
//------------------------------------------------------------------------//
$recent_global_posts_widget_main_blog_only = 'no'; //Either 'yes' or 'no'
//------------------------------------------------------------------------//
//---Hook-----------------------------------------------------------------//
//------------------------------------------------------------------------//

class widget_recent_global_posts extends WP_Widget {

	function widget_recent_global_posts() {

		$locale = apply_filters( 'rgpwidget_locale', get_locale() );
		$mofile = dirname(__FILE__) . "/languages/rgpwidget-$locale.mo";

		if ( file_exists( $mofile ) )
			load_textdomain( 'rgpwidget', $mofile );

		$widget_ops = array( 'classname' => 'rgpwidget', 'description' => __('Recent Global Posts', 'rgpwidget') );
		$control_ops = array('width' => 400, 'height' => 350, 'id_base' => 'rgpwidget');
		$this->WP_Widget( 'rgpwidget', __('Recent Global Posts', 'rgpwidget'), $widget_ops, $control_ops );

	}

	function widget( $args, $instance ) {

		global $wpdb, $current_site;

		extract($args);

		$defaults = array(	'recentglobalpoststitle' => '',
							'recentglobalpostsdisplay'	=>	'',
							'recentglobalpostsnumber'	=>	'',
							'recentglobalpoststitlecharacters'	=>	'',
							'recentglobalpostscontentcharacters'	=>	'',
							'recentglobalpostsavatars'	=>	'',
							'recentglobalpostsavatarsize'	=>	'',
							'count' => 10,
							'username' => 'wordpress',
							'post_type' => 'post'
						);

		foreach($defaults as $key => $value) {
			if(isset($instance[$key])) {
				$defaults[$key] = $instance[$key];
			}
		}

		extract($defaults);

		$title = apply_filters('widget_title', $recentglobalpoststitle );

		?>
		<?php echo $before_widget; ?>
			<?php echo $before_title . __($title) . $after_title; ?>
            <br />
            <?php
				//=================================================//
				$query = "SELECT * FROM " . $wpdb->base_prefix . "site_posts WHERE blog_public = '1' AND post_type ='" . $post_type . "' ORDER BY post_published_stamp DESC LIMIT " . $options['recent-global-posts-number'];
				$posts = $wpdb->get_results( $query, ARRAY_A );
				if (count($posts) > 0){
					echo '<ul>';
					foreach ($posts as $post){
						echo '<li>';
						if ( $options['recent-global-posts-avatars'] == 'show' ) {
							echo '<a href="' . $post['post_permalink'] . '">' . get_avatar( $post['post_author'], $options['recent-global-posts-avatar-size'], '' ) . '</a>';
							echo ' ';
						}
						if ( $options['recent-global-posts-display'] == 'title_content' ) {
							echo '<a href="' . $post['post_permalink'] . '">' . substr( $post['post_title'], 0, $options['recent-global-posts-title-characters'] ) . '</a>';
							echo '<br />';
							echo substr( strip_tags( $post['post_content'] ), 0, $options['recent-global-posts-content-characters'] );
						} else if ( $options['recent-global-posts-display'] == 'title' ) {
							echo '<a href="' . $post['post_permalink'] . '">' . substr( $post['post_title'], 0, $options['recent-global-posts-title-characters'] ) . '</a>';
						} else if ( $options['recent-global-posts-display'] == 'content' ) {
							echo substr( strip_tags( $post['post_content'] ), 0, $options['recent-global-posts-content-characters'] );
							echo ' (<a href="' . $post['post_permalink'] . '">' . __('More') . '</a>)';
						}
						echo '</li>';
					}
					echo '</ul>';
				}
				//=================================================//
			?>
		<?php echo $after_widget; ?>
<?php

		extract( $args );

		// build the check array
		$defaults = array(
			'title' 		=> '',
			'content' 		=> '',
			'level'		 	=> 'none'
		);

		foreach($defaults as $key => $value) {
			if(isset($instance[$key])) {
				$defaults[$key] = $instance[$key];
			}
		}

		extract($defaults);

		$show = false;

		switch($level) {

			case 'none':	if(!is_user_logged_in() || !current_user_is_member()) {
								$show = true;
							}
							break;

			default:		if(current_user_on_level($level)) {
								$show = true;
							}
							break;

		}

		if($show) {
			echo $before_widget;
			$title = apply_filters('widget_title', $title );

			if ( !empty($title) ) {
				echo $before_title . $title . $after_title;
			}

			echo apply_filters('the_content', $content);

			echo $after_widget;
		}

	}

	function update( $new_instance, $old_instance ) {

		$defaults = array(	'recentglobalpoststitle' => '',
							'recentglobalpostsdisplay'	=>	'',
							'recentglobalpostsnumber'	=>	'',
							'recentglobalpoststitlecharacters'	=>	'',
							'recentglobalpostscontentcharacters'	=>	'',
							'recentglobalpostsavatars'	=>	'',
							'recentglobalpostsavatarsize'	=>	'',
							'count' => 10,
							'username' => 'wordpress',
							'post_type' => 'post'
						);

		foreach ( $defaults as $key => $val ) {
			$instance[$key] = $new_instance[$key];
		}

		return $instance;

	}

	function form( $instance ) {

		$defaults = array(	'recentglobalpoststitle' => '',
							'recentglobalpostsdisplay'	=>	'',
							'recentglobalpostsnumber'	=>	'',
							'recentglobalpoststitlecharacters'	=>	'',
							'recentglobalpostscontentcharacters'	=>	'',
							'recentglobalpostsavatars'	=>	'',
							'recentglobalpostsavatarsize'	=>	'',
							'count' => 10,
							'username' => 'wordpress',
							'post_type' => 'post'
						);

		$instance = wp_parse_args( (array) $instance, $defaults );

		extract($instance);

		?>
					<div style="text-align:left">

					<label for="<?php echo $this->get_field_name( 'recentglobalpoststitle' ); ?>" style="line-height:35px;display:block;"><?php _e('Title', 'rgpwidget'); ?>:<br />
	                <input class="widefat" id="<?php echo $this->get_field_id( 'recentglobalpoststitle' ); ?>" name="<?php echo $this->get_field_name( 'recentglobalpoststitle' ); ?>" value="<?php echo esc_attr(stripslashes($instance['recentglobalpoststitle'])); ?>" type="text" style="width:95%;">
	                </label>

					<label for="<?php echo $this->get_field_name( 'recentglobalpostsdisplay' ); ?>" style="line-height:35px;display:block;"><?php _e('Display', 'rgpwidget'); ?>:
	                <select name="<?php echo $this->get_field_name( 'recentglobalpostsdisplay' ); ?>" id="<?php echo $this->get_field_id( 'recentglobalpostsdisplay' ); ?>" style="width:95%;">
	                <option value="title_content" <?php selected( $instance['recentglobalpostsdisplay'], 'title_content'); ?> ><?php _e('Title + Content', 'rgpwidget'); ?></option>
	                <option value="title" <?php selected( $instance['recentglobalpostsdisplay'], 'title'); ?> ><?php _e('Title Only', 'rgpwidget'); ?></option>
	                <option value="content" <?php selected( $instance['recentglobalpostsdisplay'], 'content'); ?> ><?php _e('Content Only', 'rgpwidget'); ?></option>
	                </select>
	                </label>

					<label for="<?php echo $this->get_field_name( 'recentglobalpostsnumber' ); ?>" style="line-height:35px;display:block;"><?php _e('Number', 'rgpwidget'); ?>:<br />
	                <select name="<?php echo $this->get_field_name( 'recentglobalpostsnumber' ); ?>" id="<?php echo $this->get_field_id( 'recentglobalpostsnumber' ); ?>" style="width:95%;">
	                <?php
						if ( empty($instance['recentglobalpostsnumber']) ) {
							$instance['recentglobalpostsnumber'] = 5;
						}
						$counter = 0;
						for ( $counter = 1; $counter <= 25; $counter += 1) {
							?>
	                        <option value="<?php echo $counter; ?>" <?php selected( $instance['recentglobalpostsnumber'], $counter); ?> ><?php echo $counter; ?></option>
	                        <?php
						}
	                ?>
	                </select>

	                </label>
					<label for="<?php echo $this->get_field_name( 'recentglobalpoststitlecharacters' ); ?>" style="line-height:35px;display:block;"><?php _e('Title Characters', 'rgpwidget'); ?>:<br />
	                <select name="<?php echo $this->get_field_name( 'recentglobalpoststitlecharacters' ); ?>" id="<?php echo $this->get_field_id( 'recentglobalpoststitlecharacters' ); ?>" style="width:95%;">
	                <?php
						if ( empty($instance['recentglobalpoststitlecharacters']) ) {
							$instance['recentglobalpoststitlecharacters'] = 30;
						}
						$counter = 0;
						for ( $counter = 1; $counter <= 200; $counter += 1) {
							?>
	                        <option value="<?php echo $counter; ?>" <?php selected($instance['recentglobalpoststitlecharacters'], $counter); ?> ><?php echo $counter; ?></option>
	                        <?php
						}
	                ?>
	                </select>
	                </label>

					<label for="<?php echo $this->get_field_name( 'recentglobalpostscontentcharacters' ); ?>" style="line-height:35px;display:block;"><?php _e('Content Characters', 'rgpwidget'); ?>:<br />
	                <select name="<?php echo $this->get_field_name( 'recentglobalpostscontentcharacters' ); ?>" id="<?php echo $this->get_field_id( 'recentglobalpostscontentcharacters' ); ?>" style="width:95%;">
	                <?php
						if ( empty($instance['recentglobalpostscontentcharacters']) ) {
							$instance['recentglobalpostscontentcharacters'] = 100;
						}
						$counter = 0;
						for ( $counter = 1; $counter <= 500; $counter += 1) {
							?>
	                        <option value="<?php echo $counter; ?>" <?php selected( $instance['recentglobalpostscontentcharacters'], $counter ); ?> ><?php echo $counter; ?></option>
	                        <?php
						}
	                ?>
	                </select>
	                </label>

					<label for="<?php echo $this->get_field_name( 'recentglobalpostsavatars' ); ?>" style="line-height:35px;display:block;"><?php _e('Avatars', 'rgpwidget'); ?>:<br />
	                <select name="<?php echo $this->get_field_name( 'recentglobalpostsavatars' ); ?>" id="<?php echo $this->get_field_id( 'recentglobalpostsavatars' ); ?>" style="width:95%;">
	                <option value="show" <?php selected( $instance['recentglobalpostsavatars'], 'show' ); ?> ><?php _e('Show', 'rgpwidget'); ?></option>
	                <option value="hide" <?php selected( $instance['recentglobalpostsavatars'], 'hide' ); ?> ><?php _e('Hide', 'rgpwidget'); ?></option>
	                </select>
	                </label>
					<label for="<?php echo $this->get_field_name( 'recentglobalpostsavatarsize' ); ?>" style="line-height:35px;display:block;"><?php _e('Avatar Size', 'rgpwidget'); ?>:<br />
	                <select name="<?php echo $this->get_field_name( 'recentglobalpostsavatarsize' ); ?>" id="<?php echo $this->get_field_id( 'recentglobalpostsavatarsize' ); ?>" style="width:95%;">
	                <option value="16" <?php selected( $instance['recentglobalpostsavatarsize'], '16'); ?> ><?php _e('16px', 'rgpwidget'); ?></option>
	                <option value="32" <?php selected( $instance['recentglobalpostsavatarsize'], '32'); ?> ><?php _e('32px', 'rgpwidget'); ?></option>
	                <option value="48" <?php selected( $instance['recentglobalpostsavatarsize'], '48'); ?> ><?php _e('48px', 'rgpwidget'); ?></option>
	                <option value="96" <?php selected( $instance['recentglobalpostsavatarsize'], '96'); ?> ><?php _e('96px', 'rgpwidget'); ?></option>
	                <option value="128" <?php selected( $instance['recentglobalpostsavatarsize'], '128'); ?> ><?php _e('128px', 'rgpwidget'); ?></option>
	                </select>
	                </label>
					<input type="hidden" name="<?php echo $this->get_field_name( 'recentglobalpostssubmit' ); ?>" id="<?php echo $this->get_field_id( 'recentglobalpostssubmit' ); ?>" value="1" />
					</div>
		<?php
	}
}

function widget_recent_global_posts_register() {
	global $recent_global_posts_widget_main_blog_only, $wpdb;

	if ( $recent_global_posts_widget_main_blog_only == 'yes' ) {
		if ( $wpdb->blogid == 1 ) {
			register_widget( 'widget_recent_global_posts' );
		}
	} else {
		register_widget( 'widget_recent_global_posts' );
	}
}

add_action( 'widgets_init', 'widget_recent_global_posts_register' );

?>