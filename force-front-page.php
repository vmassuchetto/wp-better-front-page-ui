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
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        if ( is_admin() ) {
            add_action( 'admin_init', array( $this, 'admin_init' ) );
            #add_filter( 'whitelist_options', array( $this, 'whitelist_options' ) );
        }
        add_action( 'rewrite_rules_array', array( $this, 'rewrite_rules_array' ), 9999 );
        add_filter( 'option_show_on_front', array( $this, 'filter_show_on_front' ) );
        add_filter( 'query_vars', array( $this, 'query_vars' ) );
        #add_action( 'template_include', array( $this, 'template' ) );
        add_action( 'admin_print_footer_scripts', array( $this, 'remove_reading_option' ) );
    }

    function activate() {
        if ( ! $option = get_option( 'force_front_page_posts_page' ) )
            update_option( 'force_front_page_posts_page', 'posts' );
    }

    function rewrite_rules_array( $rules ) {
        $option = get_option( 'force_front_page_posts_page' );
        #var_dump($rules); die;
        $posts_home_rule = array(
            $option . '/?$' => 'index.php?force_home=1',
            $option . '/page/?([0-9]{1,})/?$' => 'index.php?force_home=1&paged=$matches[1]' ,
        );
        return array_merge( $posts_home_rule, $rules );
    }

    function query_vars( $vars ) {
        array_push( $vars, 'force_home' );
        return $vars;
    }

    function template( $template ) {
        $templates = array ( 'front-page.php', 'home.php', 'index.php' );
        if ( get_query_var( 'posts_home' ) ) {
            array_shift( $templates );
            return locate_template( $templates );
        } elseif ( is_home() ) {
            return locate_template( $templates );
        }
        return $template;
    }
    
    // If it existed, we could filter is_front_page, but since it relies on this option, we filter it here
    function filter_show_on_front($value) {
        if (is_home() && get_query_var('force_home') == 1)
            $value = 'force';
        return $value;
        
    }
    
    function admin_init() {
        
        add_settings_field( 'force-front-page-posts-page', __( 'Post Home Page', 'force_front_page' ),
            array( $this, 'register_setting_reading' ), 'permalink', 'optional' );
            
        register_setting( 'permalink', 'force-front-page-posts-page', array($this, 'sanitize_option') );
    }
    
    function sanitize_option($value) {
        return 'a' . $value;
    }
    
    function whitelist_options( $options ) {
        $options['permalink'][] = 'force_front_page_posts_page';
        return $options;
    }

    function register_setting_reading() {
        $option = get_option( 'force_front_page_posts_page' );
        ?>
        <p class="force-front-page-posts-page"><label>
            <?php echo home_url(); ?>/<input name="force_front_page_posts_page" type="text" value="<?php echo $option; ?>" class="tog" />
        </label></p>
        <p class="description"><?php _e( 'This will be the home for your posts', 'force_front_page' ); ?></p>
        <style type="text/css">
            .force-front-page-posts-page input { float:none !important; }
        </style>
        <?
    }
    
    //ok, This is ugly
    function remove_reading_option() {
        ?>
        <script>
        jQuery(document).ready(function() { jQuery('#front-static-pages').parent('tr').remove() });
        </script>
        <?php
    }

}

function force_front_page_init() {
    new Force_Front_Page();
}
add_action( 'plugins_loaded', 'force_front_page_init' );
