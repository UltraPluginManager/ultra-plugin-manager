<?php

/**

* Alternative WordPress Plugin and Theme Manager plugin class file.

*

* @package Alternative WordPress Plugin and Theme Manager

* @author Derek Olalehe

* @license GPL2

* @copyright 2023

*/

class ULTRAPM {

    protected static $version = '1.0.0';

    protected static $plugin_slug = 'ultrapm';

    protected static $instance = null;

    private function __construct() {

        function ultrapm_admin_scripts_styles( $hook_suffix ) {
            
            if( !is_admin() && !current_user_can( 'manage_options' ) ) {
                return;
            }

            if($hook_suffix == 'plugin-manager_page_ultrapm-search-result') {
                wp_enqueue_style( 'ultrapm-plugcard', plugins_url( 'assets/css/plugin-card.css?v=' . (string)microtime(), __FILE__ ), false, false );
            }

            $allowed_hooksuffix = array(

                'toplevel_page_ultrapm-dashboard',
                'plugin-manager_page_ultrapm-search-result',
                'plugin-manager_page_ultrapm-must-have',
                'plugin-manager_page_ultrapm-installed-apps',

            );

            if( in_array( $hook_suffix, $allowed_hooksuffix ) ) {

                wp_enqueue_script('jquery','', false, true );

                //Make ajax url available on the front end
                $protocol = isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://';        

                $params = array(

                    'ajaxurl' => admin_url( 'admin-ajax.php', $protocol ),
                    'home_url' => home_url(),
                    'theme_url' => get_template_directory_uri(),
                    'plugins_url' => plugins_url(),
                    'api_url' => get_option( 'ultrapm_api_url' ),
                    'assets_url' => plugins_url( 'assets', __FILE__ ),
                    'uploads_dir' => wp_upload_dir(),
                    'content_dir' => WP_CONTENT_DIR

                );   
                
                wp_enqueue_script( 'simplebar-js', 'https://cdn.jsdelivr.net/npm/simplebar@latest/dist/simplebar.min.js', false, true );
                wp_enqueue_style( 'simplebar-style', 'https://cdn.jsdelivr.net/npm/simplebar@latest/dist/simplebar.css', false, false ); 
                    
                wp_enqueue_style( 'ultrapm-bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css' );

                wp_enqueue_script( 'ultrapm-bootrap-popper-js', 'https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js', false, true );
                wp_enqueue_script( 'ultrapm-bootrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js', false, true );

                wp_enqueue_script( 'ultrapm-tippy-popper-js', 'https://unpkg.com/popper.js@1', false, true );
                wp_enqueue_script( 'ultrapm-tippy-js', 'https://unpkg.com/tippy.js@5', false, true );

                wp_enqueue_script( 'ultrapm-swal', plugins_url( 'js/sweetalert2.js?v=' . (string)microtime(), __FILE__ ), array(), '1.0.0', true);
                
                wp_enqueue_script( 'ultrapm-admin', plugins_url( 'js/admin.js?v=' . (string)microtime(), __FILE__ ), array(), '1.0.0', true);
                wp_localize_script( 'ultrapm-admin', 'ultrapm_urls', $params ); 
                
                wp_enqueue_script( 'ultrapm-list', plugins_url( 'js/list.js?v=' . (string)microtime(), __FILE__ ), array(), '1.0.0', true);
                wp_localize_script( 'ultrapm-list', 'ultrapm_urls', $params ); 

                wp_enqueue_script( 'ultrapm-zip-upload', plugins_url( 'js/zip-upload.js?v=' . (string)microtime(), __FILE__ ), array(), '1.0.0', true);
                wp_localize_script( 'ultrapm-zip-upload', 'ultrapm_urls', $params ); 
                
                wp_enqueue_style( 'ultrapm-admin-style', plugins_url( 'style.css?v=' . (string)microtime(), __FILE__ ), false, false ); 

                // <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
                wp_enqueue_script( 'ultrapm-jquery', 'https://code.jquery.com/jquery-3.7.1.js', false, true );

                // https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700
                wp_enqueue_style( 'ultrapm-fonts', 'https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700', false, false );

                // assets/plugins/global/plugins.bundle.css
                wp_enqueue_style( 'ultrapm-global', plugins_url( 'assets/plugins/global/plugins.bundle.css', __FILE__ ), false, false );

                // assets/css/style.bundle.css
                wp_enqueue_style( 'ultrapm-style', plugins_url( 'assets/css/style.bundle.css', __FILE__ ), false, false );

                // assets/plugins/global/plugins.bundle.js?
                wp_enqueue_script( 'ultrapm-global-js', plugins_url( 'assets/plugins/global/plugins.bundle.js?v=' . (string)microtime(), __FILE__ ), array(), '1.0.0', true);

                // assets/js/scripts.bundle.js
                wp_enqueue_script( 'ultrapm-scripts-js', plugins_url( 'assets/js/scripts.bundle.js?v=' . (string)microtime(), __FILE__ ), array(), '1.0.0', true);

                // assets/js/ultrapm.js
                // wp_enqueue_script( 'ultrapm-js', plugins_url( 'assets/js/ultrapm.js?v=' . (string)microtime(), __FILE__ ), array(), '1.0.0', true);

                // assets/js/widgets.bundle.js
                wp_enqueue_script( 'ultrapm-widgets-js', plugins_url( 'assets/js/widgets.bundle.js?v=' . (string)microtime(), __FILE__ ), array(), '1.0.0', true);

                // assets/js/custom/widgets.js
                wp_enqueue_script( 'ultrapm-custom-widgets-js', plugins_url( 'assets/js/custom/widgets.js?v=' . (string)microtime(), __FILE__ ), array(), '1.0.0', true);

                // assets/plugins/custom/fslightbox/fslightbox.bundle.js
                wp_enqueue_script( 'ultrapm-fslightbox', plugins_url( 'assets/plugins/custom/fslightbox/fslightbox.bundle.js', __FILE__ ),false, false );

                // assets/plugins/custom/datatables/datatables.bundle.css
                wp_enqueue_style( 'ultrapm-datatables', plugins_url( 'assets/plugins/custom/datatables/datatables.bundle.css', __FILE__ ), false, false );

                // assets/plugins/custom/datatables/datatables.bundle.js
                wp_enqueue_script( 'ultrapm-datatables-js', plugins_url( 'assets/plugins/custom/datatables/datatables.bundle.js', __FILE__ ), false, false );

                // assets/plugins/custom/typedjs/typedjs.bundle.js
                wp_enqueue_script( 'ultrapm-typedjs', plugins_url( 'assets/plugins/custom/typedjs/typedjs.bundle.js', __FILE__ ), false, false );

                
                // ../custom.css
                wp_enqueue_style( 'ultrapm-custom', plugins_url( 'custom.css', __FILE__ ), false, false );

                // ../custom.js
                wp_enqueue_script( 'ultrapm-custom-js', plugins_url( 'custom.js', __FILE__ ), array(), '1.0.0', true);


                
                $brokenThemes = wp_get_themes(array('errors' => true));
                foreach ($brokenThemes as $theme) {
                    $themeName = $theme->get('Name');
                    $desc = $theme->errors()->get_error_message();
                    $is_erParrent = false;
                    if (strpos($desc, 'The parent theme is missing') !== false) {
                        $is_erParrent = true;
                        $parrentSlug = $theme->get('Template');
                    }
                    add_action('admin_notices', function () use ($themeName, $desc, $is_erParrent, $parrentSlug) {
                        echo '<div class="alert alert-dismissible bg-danger text-white d-flex flex-column flex-sm-row p-5 mb-10" style="margin: 15px 15px 15px 15px;">';
                        echo '<i class="ki-duotone ki-message-notif fs-2hx text-white me-4"><span class="path1"></span><span class="path2"></span></i>';
                        echo '<div class="d-flex flex-column pe-0 pe-sm-10">';
                        echo "<span>The theme '$themeName' is broken. Error: $desc</span>";
                        if ($is_erParrent) {
                            echo "<span>Click <button class='btn btn-icon btn-primary btn-sm' id=\"install-$parrentSlug\" onclick=\"ultrapm_crut_ajax_install_theme('$parrentSlug')\">here</button>to install the parent theme.</span>";
                        }
                        echo '</div>';
                        echo '</div>';
                    });
                }

                // make page fit to screen
                add_action('admin_head', function () {
                    echo '<style>
                    .page-content {
                        height: 100vh;
                        overflow: hidden;
                    }
                    </style>';
                });
            }

        }       
        
        add_action( 'admin_enqueue_scripts', 'ultrapm_admin_scripts_styles' );
        add_action( 'admin_menu', 'add_custom_menu_pages' );
        add_action( 'admin_menu', 'add_custom_submenu' );
        function add_custom_menu_pages(){ 
            if( is_admin() && current_user_can( 'manage_options' ) ) {
                add_menu_page( 
                    'Plugin Manager', 
                    'Plugin Manager', 
                    'manage_options',
                    ULTRAPM_SLUG_ADMIN,
                    'ultrapm_admin',  
                    plugins_url() . '/ultra-plugin-manager/assets/icons/ultrapm-logo-hover-19px.png',
                    '59.1'
                );
            }
        
        }
        function add_custom_submenu(){
            if( is_admin() && current_user_can( 'manage_options' ) ) {
                add_submenu_page(
                    ULTRAPM_SLUG_ADMIN,
                    'Dashboard',
                    'Dashboard',
                    'manage_options',
                    ULTRAPM_SLUG_ADMIN,
                    'ultrapm_admin'
                );
                add_submenu_page(
                    ULTRAPM_SLUG_ADMIN, // Menu utama
                    'Installed Ones', // Judul halaman
                    'Installed Ones', // Label di menu
                    'manage_options', // Hak akses yang diperlukan untuk melihat halaman
                    ULTRAPM_SLUG_INSTALLED_APPS, // Slug halaman
                    'ultrapm_installed_apps_page_callback' // Fungsi yang dipanggil untuk menampilkan halaman
                );
                add_submenu_page(
                    ULTRAPM_SLUG_ADMIN, // Slug menu utama
                    'Must Have', // Judul halaman
                    'Must Have', // Label di menu
                    'manage_options', // Hak akses yang diperlukan untuk melihat halaman
                    ULTRAPM_SLUG_MUST_HAVE, // Slug halaman
                    'ultrapm_must_have_page_callback' // Fungsi yang dipanggil untuk menampilkan halaman
                );
                add_submenu_page(
                    ULTRAPM_SLUG_ADMIN, // Slug menu utama
                    'Search Result', // Judul halaman
                    'Search Result', // Label di menu
                    'manage_options', // Hak akses yang diperlukan untuk melihat halaman
                    ULTRAPM_SLUG_SEARCH_RESULT, // Slug halaman
                    'ultrapm_search_result_page_callback' // Fungsi yang dipanggil untuk menampilkan halaman
                );
            }
        }

        require_once( 'includes/methods.php' );

        require_once( 'includes/page/admin-pages.php' );
        require_once( 'includes/page/search-result.php' );
        require_once( 'includes/page/must-have.php' );
        require_once( 'includes/page/installed-apps.php' );

        /**
        * Table Names
        */
        global $wpdb;

        if ( ! isset( $wpdb->qs_configs_table_name ) ) {
            $wpdb->qs_configs_table_name = $wpdb->prefix . 'ultrapm_qs_configs';
        }

        add_action( 'rest_api_init', function () {

            register_rest_route( 'ultra-plugin-manager/v1', '/validateplugin/(?P<folder>[\W|\w|\d]*)', array(
                'methods' => 'GET',
                'callback' => 'validateplugin',
                'permission_callback' => '__return_true',
            //   'permission_callback' => function () {
            //     return current_user_can( 'manage_options' );
            //   }
            ) );

        } );

        //AJAX
        add_action( 'wp_ajax_get_ultrapm_config', 'get_ultrapm_config' );
        add_action( 'wp_ajax_items_stage_install', 'items_stage_install' );
        add_action( 'wp_ajax_item_activate', 'item_activate' );
        add_action( 'wp_ajax_item_deactivate', 'item_deactivate' );
        add_action( 'wp_ajax_activate_theme', 'activate_theme' );
        add_action( 'wp_ajax_remove_theme', 'remove_theme' );
        add_action( 'wp_ajax_remove_plugin', 'remove_plugin' );
        add_action( 'wp_ajax_add_ultrapm_config', 'add_ultrapm_config' );
        add_action( 'wp_ajax_delete_ultrapm_config', 'delete_ultrapm_config' );
        add_action( 'wp_ajax_analyze_item_from_zip', 'analyze_item_from_zip' );
        add_action( 'wp_ajax_install_item_from_zip', 'install_item_from_zip' );

    }

    public static function get_instance() {    

        if ( null == self::$instance ) {

            self::$instance = new self;

        }
        return self::$instance;        

    }

}

include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
if( !class_exists( 'ULTRAPM_WP_Upgrader_Skin' ) ){

    class ULTRAPM_WP_Upgrader_Skin extends WP_Upgrader_Skin {

        function feedback( $string, ...$args ) {
            return;
        }

    }

}