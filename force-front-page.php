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

    var $option_name;
    var $default_value;
    
    function Force_Front_Page() {
        $this->option_name = 'force_front_page_posts_page';
        $this->default_value = 'blog';
        
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        if ( is_admin() ) {
            add_action( 'admin_init', array( $this, 'admin_init' ) );
        }
        add_action( 'rewrite_rules_array', array( $this, 'rewrite_rules_array' ), 9999 );
        add_filter( 'option_show_on_front', array( $this, 'filter_show_on_front' ) );
        add_filter( 'query_vars', array( $this, 'query_vars' ) );
        add_action( 'admin_print_footer_scripts', array( $this, 'remove_reading_option' ) );
        
        //TODO: using admin_head instead to avoid beeing the first meta_box
        //maybe reorder it before printing so it is the second metabox
        add_action( 'admin_head', array( $this, 'add_meta_box' ) );
    }
    
    function get_option() {
        $option = get_option($this->option_name);
        return !$option || empty($option) ? $this->default_value : $option;
    }
    
    function activate() {
        //TODO: Flush Rules
    }

    function rewrite_rules_array( $rules ) {
        $option = $this->get_option();
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

    // If it existed, we could filter is_front_page, but since it relies on this option, we filter it here
    function filter_show_on_front($value) {
        
        // TODO: DO we really need to bother with this? Is this a good thingo to do?
        // The point here is to filter only when is_front_page() calls it
        #$callStack = debug_backtrace();
        #if (!is_array($callStack) || !isset($callStack[4]) || !is_array($callStack[4]) || !isset($callStack[4]['function']) || $callStack[4]['function'] != 'is_front_page')
        #    return $value;
        ///////////////////////////////////////////////////////////////////////////////////
        $value = 'posts';
        if (is_home() && get_query_var('force_home') == 1)
            $value = 'force';
        return $value;
    }
    
    function admin_init() {
        
        add_settings_field( $this->option_name, __( 'Post Home Page', 'force_front_page' ),
            array( $this, 'output_setting_form' ), 'permalink', 'optional' );
            
        // sadly register_setting is useless in the permalink page, so we will have to save it on our own
        //register_setting( 'permalink', $this->option_name, array($this, 'sanitize_option') );
        
        global $pagenow;
        if ($pagenow == 'options-permalink.php') {
            if (isset($_POST[$this->option_name])) {
                $value = $this->sanitize_option($_POST[$this->option_name]);
                update_option($this->option_name, $value);
            } else {
                delete_option($this->option_name);
            }
            
        }
    }
    
    function sanitize_option($value) {
        return sanitize_title($value);
    }

    function output_setting_form() {
        $option = $this->get_option();
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
    
    
    // Nav menu methods
    
    function add_meta_box() {
        add_meta_box( "add-home-and-blog", __('Home and Blog', 'force_front_page'), array($this, 'nav_menu_meta_box'), 'nav-menus', 'side', 'default' );
    }
    
    function nav_menu_meta_box() {
        #var_dump($x, $y);
        global $_nav_menu_placeholder, $nav_menu_selected_id;

        ?>
        <div id="home-and-blog" class="posttypediv">
            
            <p class="howto"><?php _e(''); ?></p>

            <div id="tabs-panel-home-and-blog" class="tabs-panel">
                <ul id="<?php echo $post_type_name; ?>checklist-most-recent" class="categorychecklist form-no-clear">
                   <li>
                        <label class="menu-item-title"><input type="checkbox" class="menu-item-checkbox" name="menu-item[-1][menu-item-object-id]" value="1"> <?php _e('Home'); ?></label>
                        <input type="hidden" class="menu-item-db-id" name="menu-item[-1][menu-item-db-id]" value="0">
                        <input type="hidden" class="menu-item-object" name="menu-item[-1][menu-item-object]" value="home-and-blog">
                        <input type="hidden" class="menu-item-parent-id" name="menu-item[-1][menu-item-parent-id]" value="0">
                        <input type="hidden" class="menu-item-type" name="menu-item[-1][menu-item-type]" value="extended">
                        <input type="hidden" class="menu-item-title" name="menu-item[-1][menu-item-title]" value="<?php _e('Home'); ?>">
                        <input type="hidden" class="menu-item-url" name="menu-item[-1][menu-item-url]" value="<?php echo site_url(); ?>">
                        <input type="hidden" class="menu-item-target" name="menu-item[-1][menu-item-target]" value="">
                        <input type="hidden" class="menu-item-attr_title" name="menu-item[-1][menu-item-attr_title]" value="">
                        <input type="hidden" class="menu-item-classes" name="menu-item[-1][menu-item-classes]" value="">
                        <input type="hidden" class="menu-item-xfn" name="menu-item[-1][menu-item-xfn]" value="">
                    </li>
                    <li>
                        <label class="menu-item-title"><input type="checkbox" class="menu-item-checkbox" name="menu-item[-2][menu-item-object-id]" value="2"> <?php _e('Blog'); ?></label>
                        <input type="hidden" class="menu-item-db-id" name="menu-item[-2][menu-item-db-id]" value="0">
                        <input type="hidden" class="menu-item-object" name="menu-item[-2][menu-item-object]" value="home-and-blog">
                        <input type="hidden" class="menu-item-parent-id" name="menu-item[-2][menu-item-parent-id]" value="0">
                        <input type="hidden" class="menu-item-type" name="menu-item[-2][menu-item-type]" value="extended">
                        <input type="hidden" class="menu-item-title" name="menu-item[-2][menu-item-title]" value="<?php _e('Blog'); ?>">
                        <input type="hidden" class="menu-item-url" name="menu-item[-2][menu-item-url]" value="<?php echo home_url( get_option( $this->option_name ) ); ?>">
                        <input type="hidden" class="menu-item-target" name="menu-item[-2][menu-item-target]" value="">
                        <input type="hidden" class="menu-item-attr_title" name="menu-item[-2][menu-item-attr_title]" value="">
                        <input type="hidden" class="menu-item-classes" name="menu-item[-2][menu-item-classes]" value="">
                        <input type="hidden" class="menu-item-xfn" name="menu-item[-2][menu-item-xfn]" value="">
                    </li>
                </ul>
            </div><!-- /.tabs-panel -->

            

            <p class="button-controls">
                <span class="add-to-menu">
                    <img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
                    <input type="submit"<?php disabled( $nav_menu_selected_id, 0 ); ?> class="button-secondary submit-add-to-menu" value="<?php esc_attr_e('Add to Menu'); ?>" name="add-extended-menu-item" id="submit-extended-home-and-blog" />
                </span>
            </p>

        </div><!-- /.posttypediv -->
        <?php
    }
    

}

function force_front_page_init() {
    //This plugin only makes sense if you have a front_page.php in your theme
    if (locate_template('front-page.php'))
        new Force_Front_Page();
}
add_action( 'init', 'force_front_page_init' );
