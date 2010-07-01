<?php
/*
Plugin Name: Recent Posts Widget
Description:
Author: Andrew Billits (Incsub)
Version: 1.0.1
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

//------------------------------------------------------------------------//
//---Config---------------------------------------------------------------//
//------------------------------------------------------------------------//
$recent_global_posts_widget_main_blog_only = 'yes'; //Either 'yes' or 'no'
//------------------------------------------------------------------------//
//---Hook-----------------------------------------------------------------//
//------------------------------------------------------------------------//

//------------------------------------------------------------------------//
//---Functions------------------------------------------------------------//
//------------------------------------------------------------------------//
function widget_recent_global_posts_init() {
	global $wpdb, $recent_global_posts_widget_main_blog_only;
		
	// Check for the required API functions
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return;

	// This saves options and prints the widget's config form.
	function widget_recent_global_posts_control() {
		global $wpdb;
		$options = $newoptions = get_option('widget_recent_global_posts');
		if ( $_POST['recent-global-posts-submit'] ) {
			$newoptions['recent-global-posts-title'] = $_POST['recent-global-posts-title'];
			$newoptions['recent-global-posts-display'] = $_POST['recent-global-posts-display'];
			$newoptions['recent-global-posts-number'] = $_POST['recent-global-posts-number'];
			$newoptions['recent-global-posts-title-characters'] = $_POST['recent-global-posts-title-characters'];
			$newoptions['recent-global-posts-content-characters'] = $_POST['recent-global-posts-content-characters'];
			$newoptions['recent-global-posts-avatars'] = $_POST['recent-global-posts-avatars'];
			$newoptions['recent-global-posts-avatar-size'] = $_POST['recent-global-posts-avatar-size'];
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_recent_global_posts', $options);
		}
	?>
				<div style="text-align:left">
                
				<label for="recent-global-posts-title" style="line-height:35px;display:block;"><?php _e('Title', 'widgets'); ?>:<br />
                <input class="widefat" id="recent-global-posts-title" name="recent-global-posts-title" value="<?php echo $options['recent-global-posts-title']; ?>" type="text" style="width:95%;">
                </select>
                </label>
				<label for="recent-global-posts-display" style="line-height:35px;display:block;"><?php _e('Display', 'widgets'); ?>:
                <select name="recent-global-posts-display" id="recent-global-posts-display" style="width:95%;">
                <option value="title_content" <?php if ($options['recent-global-posts-display'] == 'title_content'){ echo 'selected="selected"'; } ?> ><?php _e('Title + Content'); ?></option>
                <option value="title" <?php if ($options['recent-global-posts-display'] == 'title'){ echo 'selected="selected"'; } ?> ><?php _e('Title Only'); ?></option>
                <option value="content" <?php if ($options['recent-global-posts-display'] == 'content'){ echo 'selected="selected"'; } ?> ><?php _e('Content Only'); ?></option>
                </select>
                </label>
				<label for="recent-global-posts-number" style="line-height:35px;display:block;"><?php _e('Number', 'widgets'); ?>:<br />
                <select name="recent-global-posts-number" id="recent-global-posts-number" style="width:95%;">
                <?php
					if ( empty($options['recent-global-posts-number']) ) {
						$options['recent-global-posts-number'] = 5;
					}
					$counter = 0;
					for ( $counter = 1; $counter <= 25; $counter += 1) {
						?>
                        <option value="<?php echo $counter; ?>" <?php if ($options['recent-global-posts-number'] == $counter){ echo 'selected="selected"'; } ?> ><?php echo $counter; ?></option>
                        <?php
					}
                ?>
                </select>
                </label>
				<label for="recent-global-posts-title-characters" style="line-height:35px;display:block;"><?php _e('Title Characters', 'widgets'); ?>:<br />
                <select name="recent-global-posts-title-characters" id="recent-global-posts-title-characters" style="width:95%;">
                <?php
					if ( empty($options['recent-global-posts-title-characters']) ) {
						$options['recent-global-posts-title-characters'] = 30;
					}
					$counter = 0;
					for ( $counter = 1; $counter <= 200; $counter += 1) {
						?>
                        <option value="<?php echo $counter; ?>" <?php if ($options['recent-global-posts-title-characters'] == $counter){ echo 'selected="selected"'; } ?> ><?php echo $counter; ?></option>
                        <?php
					}
                ?>
                </select>
                </label>
				<label for="recent-global-posts-content-characters" style="line-height:35px;display:block;"><?php _e('Content Characters', 'widgets'); ?>:<br />
                <select name="recent-global-posts-content-characters" id="recent-global-posts-content-characters" style="width:95%;">
                <?php
					if ( empty($options['recent-global-posts-content-characters']) ) {
						$options['recent-global-posts-content-characters'] = 100;
					}
					$counter = 0;
					for ( $counter = 1; $counter <= 500; $counter += 1) {
						?>
                        <option value="<?php echo $counter; ?>" <?php if ($options['recent-global-posts-content-characters'] == $counter){ echo 'selected="selected"'; } ?> ><?php echo $counter; ?></option>
                        <?php
					}
                ?>
                </select>
                </label>
				<label for="recent-global-posts-avatars" style="line-height:35px;display:block;"><?php _e('Avatars', 'widgets'); ?>:<br />
                <select name="recent-global-posts-avatars" id="recent-global-posts-avatars" style="width:95%;">
                <option value="show" <?php if ($options['recent-global-posts-avatars'] == 'show'){ echo 'selected="selected"'; } ?> ><?php _e('Show'); ?></option>
                <option value="hide" <?php if ($options['recent-global-posts-avatars'] == 'hide'){ echo 'selected="selected"'; } ?> ><?php _e('Hide'); ?></option>
                </select>
                </label>
				<label for="recent-global-posts-avatar-size" style="line-height:35px;display:block;"><?php _e('Avatar Size', 'widgets'); ?>:<br />
                <select name="recent-global-posts-avatar-size" id="recent-global-posts-avatar-size" style="width:95%;">
                <option value="16" <?php if ($options['recent-global-posts-avatar-size'] == '16'){ echo 'selected="selected"'; } ?> ><?php _e('16px'); ?></option>
                <option value="32" <?php if ($options['recent-global-posts-avatar-size'] == '32'){ echo 'selected="selected"'; } ?> ><?php _e('32px'); ?></option>
                <option value="48" <?php if ($options['recent-global-posts-avatar-size'] == '48'){ echo 'selected="selected"'; } ?> ><?php _e('48px'); ?></option>
                <option value="96" <?php if ($options['recent-global-posts-avatar-size'] == '96'){ echo 'selected="selected"'; } ?> ><?php _e('96px'); ?></option>
                <option value="128" <?php if ($options['recent-global-posts-avatar-size'] == '128'){ echo 'selected="selected"'; } ?> ><?php _e('128px'); ?></option>
                </select>
                </label>
				<input type="hidden" name="recent-global-posts-submit" id="recent-global-posts-submit" value="1" />
				</div>
	<?php
	}
// This prints the widget
	function widget_recent_global_posts($args) {
		global $wpdb, $current_site;
		extract($args);
		$defaults = array('count' => 10, 'username' => 'wordpress');
		$options = (array) get_option('widget_recent_global_posts');

		foreach ( $defaults as $key => $value )
			if ( !isset($options[$key]) )
				$options[$key] = $defaults[$key];

		?>
		<?php echo $before_widget; ?>
			<?php echo $before_title . __($options['recent-global-posts-title']) . $after_title; ?>
            <br />
            <?php
				//=================================================//
				$query = "SELECT * FROM " . $wpdb->base_prefix . "site_posts WHERE blog_public = '1' ORDER BY post_published_stamp DESC LIMIT " . $options['recent-global-posts-number'];
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
	}
	// Tell Dynamic Sidebar about our new widget and its control
	if ( $recent_global_posts_widget_main_blog_only == 'yes' ) {
		if ( $wpdb->blogid == 1 ) {
			register_sidebar_widget(array(__('Recent Global Posts'), 'widgets'), 'widget_recent_global_posts');
			register_widget_control(array(__('Recent Global Posts'), 'widgets'), 'widget_recent_global_posts_control');
		}
	} else {
		register_sidebar_widget(array(__('Recent Global Posts'), 'widgets'), 'widget_recent_global_posts');
		register_widget_control(array(__('Recent Global Posts'), 'widgets'), 'widget_recent_global_posts_control');
	}
}

add_action('widgets_init', 'widget_recent_global_posts_init');

?>