<?php
/*
 * Plugin Name: Force Front Page
 * Plugin URI: http://github.com/vmassuchetto/wordpress-force-front-page
 * Description: Force the front page to the <code>front-page.php</code> template file without any user interference or dummy pages.
 * Version: 0.02
 * Author: Leo Germani, Vinicius Massuchetto
 * Author URI: http://github.com/vmassuchetto/wordpress-force-front-page
 */

class Force_Front_Page {

    var $option_name;
    var $default_value;

    function Force_Front_Page() {
        $this->option_name = 'blog_base';
        $this->default_value = 'blog';

        if ( is_admin() ) {
            add_action( 'admin_init', array( $this, 'admin_init' ) );
        }

        add_action( 'rewrite_rules_array', array( $this, 'rewrite_rules_array' ), 9999 );
        add_filter( 'option_show_on_front', array( $this, 'filter_show_on_front' ) );
        add_filter( 'query_vars', array( $this, 'query_vars' ) );
        add_action( 'admin_print_footer_scripts', array( $this, 'remove_reading_option' ) );
    }

    function activate() {
        flush_rewrite_rules();
    }

    function deactivate() {
        flush_rewrite_rules();
    }

    function uninstall() {
        $f = new Force_Front_Page();
        delete_option( $f->option_name );
        flush_rewrite_rules();
    }

    function get_option() {
        $option = get_option( $this->option_name );
        return ! $option || empty( $option ) ? $this->default_value : $option;
    }

    function rewrite_rules_array( $rules ) {
        $option = $this->get_option();
        $new_rules = array(
            $option . '/?$' => 'index.php?force_home=1',
            $option . '/page/?([0-9]{1,})/?$' => 'index.php?force_home=1&paged=$matches[1]',
        );
        return array_merge( $new_rules, $rules );
    }

    function query_vars( $vars ) {
        array_push( $vars, 'force_home' );
        return $vars;
    }

    // If it existed, we could filter is_front_page, but since it relies on this option, we filter it here
    function filter_show_on_front($value) {

        // TODO: Do we really need to bother with this? Is this a good thingo to do?
        // The point here is to filter only when is_front_page() calls it
        #$callStack = debug_backtrace();
        #if (!is_array($callStack) || !isset($callStack[4]) || !is_array($callStack[4]) || !isset($callStack[4]['function']) || $callStack[4]['function'] != 'is_front_page')
        #    return $value;
        ///////////////////////////////////////////////////////////////////////////////////
        $value = 'posts';
        if ( is_home() && get_query_var( 'force_home' ) == 1 )
            $value = 'force';
        return $value;
    }

    function admin_init() {

        add_settings_field( $this->option_name, __( 'Post Home Page', 'force_front_page' ),
            array( $this, 'output_setting_form' ), 'permalink', 'optional' );

        // sadly register_setting is useless in the permalink page, so we will have to save it on our own
        // register_setting( 'permalink', $this->option_name, array($this, 'sanitize_option') );

        global $pagenow;
        if ( $pagenow == 'options-permalink.php' ) {
            if ( isset( $_POST[$this->option_name] ) ) {
                $value = $this->sanitize_option( $_POST[$this->option_name] );
                update_option( $this->option_name, $value );
            } else {
                delete_option( $this->option_name );
            }
        }
    }

    function sanitize_option( $value ) {
        return sanitize_title( $value );
    }

    function output_setting_form() {
        $option = $this->get_option();
        ?>
        <p class="force-front-page-posts-page"><label>
            <?php echo home_url(); ?>/<input name="<?php echo $this->option_name; ?>" type="text" value="<?php echo $option; ?>" class="tog" />
        </label></p>
        <p class="description"><?php _e( 'This will be the home for your posts', 'force_front_page' ); ?></p>
        <style type="text/css">
            .force-front-page-posts-page input { float:none !important; }
        </style>
        <?php
    }

    // ok, This is ugly
    function remove_reading_option() {
        ?>
        <script>
        jQuery(document).ready(function() { jQuery('#front-static-pages').parent('tr').remove() });
        </script>
        <?php
    }

}

function force_front_page_init() {
    // This plugin only makes sense if you have a front_page.php in your theme
    if ( file_exists( get_stylesheet_directory() . '/front-page.php' ) )
        new Force_Front_Page();
}
add_action( 'plugins_loaded', 'force_front_page_init' );

register_activation_hook( __FILE__, array( 'Force_Front_Page', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Force_Front_Page', 'deactivate' ) );
register_uninstall_hook( __FILE__, array( 'Force_Front_Page', 'uninstall' ) );

function the_posts_home_url() {
    echo get_the_posts_home_url();
}

function get_the_posts_home_url() {
    return home_url( get_option( $this->option_name ) );
}
