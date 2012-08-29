<?php
/*
 * Plugin Name: Force Front Page
 * Plugin URI: http://github.com/vmassuchetto/wordpress-force-front-page
 * Description: Force the front page to the <code>front-page.php</code> template file without any user interference.
 * Version: 0.01
 * Author: Leo Germani, Vinicius Massuchetto
 * Author URI: http://github.com/vmassuchetto/wordpress-force-front-page
 */

class Force_Front_Page {

    function Force_Front_Page() {
        if ( is_admin() ) {
            add_action( 'admin_init', array( $this, 'admin_init' ) );
            add_filter( 'whitelist_options', array( $this, 'whilelist_options' ), 10, 3 );
        }
        add_action( 'init', array( $this, 'init' ) );
    }

    function activate() {
        if ( ! $option = get_option( 'force_front_page_posts_page' ) )
            update_option( 'force_front_page_posts_page', 'posts' );
    }

    function init() {
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        $option = get_option( 'force_front_page_posts_page' );
        add_rewrite_rule( '^' . $option . '/?$', 'index.php?post_type=post', 'top' );
    }

    function admin_init() {
        add_settings_field( 'force-front-page-posts-page', __( 'Post Home Page', 'force_front_page' ),
            array( $this, 'register_setting_reading' ), 'reading', 'default' );
    }

    function whilelist_options( $options ) {
        $options['reading'][] = 'force_front_page_posts_page';
        return $options;
    }

    function register_setting_reading() {
        if ( ! $option = get_option( 'force_front_page_posts_page' ) ) {
            update_option( 'force_front_page_posts_page', 'posts' );
            $option = 'posts';
        }
        ?>
        <p class="force-front-page-posts-page"><label>
            <?php echo home_url(); ?>/<input name="force_front_page_posts_page" type="text" value="<?php echo $option; ?>" class="tog" />
        </label></p>
        <p class="description"><?php _e( 'This will be the posts home URL.', 'force_front_page' ); ?></p>
        <style type="text/css">
            .force-front-page-posts-page input { float:none !important; }
        </style>
        <?
    }

}

function force_front_page_init() {
    new Force_Front_Page();
}
add_action( 'plugins_loaded', 'force_front_page_init' );

