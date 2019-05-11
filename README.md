# Recent Global Posts Widget

**INACTIVE NOTICE: This plugin is unsupported by WPMUDEV, we've published it here for those technical types who might want to fork and maintain it for their needs.**

## Translations

Translation files can be found at https://github.com/wpmudev/translations

## Recent Global Posts Widget shows all the latest posts from across your entire Multisite or BuddyPress network on your main site - simply, efficiently and quickly.

### Amazing [Post Indexer](http://premium.wpmudev.org/project/post-indexer/ "Post Indexer") Extension

##### This simple powerful plugin allows you to:

*   Use a simple widget to display the **latest posts across your entire network**
*   Choice of displaying **title + content, title only or content only**
*   Option to change the **number of posts displayed**
*   Set **number of title and content characters allowed**
*   Option to **display avatars** and preset avatar size
*   Ability to **display custom post types**

### Familiar Settings

A Recent Global Posts Widget id added to** '**Widgets' in the dashboard of your main site. 

![Recent Global Posts Widget - Widget Settings](http://premium.wpmudev.org/wp-content/uploads/2009/03/recent-global-posts-widget-3050-widget-settings.png)

 Drag-n-drop to activate and simple configuration

 Drag the Recent Global Posts Widget into your sidebar, choose your configuration options and the latest posts from across your entire network will be pulled into your main site. 

![Recent Global Posts Widget In Sidebar](http://premium.wpmudev.org/wp-content/uploads/2009/03/recent-global-posts-widget-3050-front.png)

 A powerful tool for discovering new content

 Recent Global Posts Widget adds a fun way for users to see and discover new content from across your entire network.


## Usage
 
For help with installing plugins please refer to our [Plugin installation guide.](https://premium.wpmudev.org/wpmu-manual/installing-regular-plugins-on-wpmu/)

### To Install:

1\. Install the [Post Indexer](https://premium.wpmudev.org/project/post-indexer) 2.  Install the Recent Posts Plugin.

*   Once uploaded visit **Network Admin -> Plugins** to Network Activate the Recent Global Posts Widget Plugin.
*   Your Recent Global Post widget is added to **Appearance > Widgets** of your main site.
*   By default, the widget is available only on the main site of your network. This behavior can be changed by following the instructions below to edit your _wp-config.php_ file.
*   The Recent Global posts widget list public posts only.  Posts from privates sites aren't displayed.

3.  Install the [Avatars plugin](https://premium.wpmudev.org/project/avatars/installation) (if you want to display avatars).

### To Use:

1.  Once installed you just go **Appearance > Widgets.** 2.  Add the Recent Global posts widget to your sidebar. 3\. Check out the configuration options below: 

![Recent Global Posts Widget - Widget Settings](https://premium.wpmudev.org/wp-content/uploads/2009/03/recent-global-posts-widget-3050-widget-settings.png)

 Once configured, it could look like this in your sidebar: 

![Recent Global Posts Widget In Sidebar](https://premium.wpmudev.org/wp-content/uploads/2009/03/recent-global-posts-widget-3050-front.png)

### Enabling Widget for all sites

By default the Recent Global Posts widget is only enabled for use by the man site. You can enable it for all sites on your network as follows: 1\. Open up the ****wp-config.php**** in the root of your WordPress install 2\. Just before the line that says _That's all, stop editing!_, add the following:

define('RECENT_GLOBAL_POSTS_WIDGET_MAIN_BLOG_ONLY', false);

3\. Save and re-upload your amended wp-config.php file
