<?php

if(!defined('ABSPATH') || !defined('WPINC')){
    exit;
}
DEFINE('ULTRAPM_INC_PATH', plugin_dir_path( __FILE__ ));

function validateplugin( $data ) {
    
    $folder = $data[ 'folder' ];

    $is_plugin = "false";

    if ( validate_file( $folder ) ) {
		return "false";
	}
	if ( ! file_exists( WP_PLUGIN_DIR . '/' . $folder ) ) {
		return "false";
	}

	$installed_plugins = get_plugins();
	foreach( $installed_plugins as $key => $value ){
        
        if( strpos( $key, $folder ) !== false ){
            $is_plugin = $key; 
            break;
        }

    }

    return $is_plugin;

}

function get_ultrapm_config(){

    global $wpdb;

    $normalized_configs = array();

    $configs = $wpdb->get_results(
        "
        SELECT * FROM 
        $wpdb->qs_configs_table_name
        "
    );

    foreach( $configs as $config ){
        $configitemsstring = unserialize( $config->configitems );
        array_push( $normalized_configs, array(
            "id" => $config->id,
            "configname" => $config->configname,
            "configitemsstring" => $configitemsstring
        ) );
    }

    echo json_encode( $normalized_configs );

    wp_die();

}


function ultrapm_time_elapsed_stringe($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

function get_ultrapm_confige() {

    global $wpdb;

    $normalized_configs = array();

    $configs = $wpdb->get_results(
        "
        SELECT * FROM 
        $wpdb->qs_configs_table_name
        "
    );

    foreach( $configs as $config ){
        $configitemsstring = unserialize( $config->configitems );
        array_push( $normalized_configs, array(
            "id" => $config->id,
            "configname" => $config->configname,
            "configitemsstring" => $configitemsstring
        ) );
    }

    return json_encode( $normalized_configs );

}

function item_activate(){

    if( !is_wp_error( activate_plugin( $_POST[ 'slug' ], '', false, true ) ) ){
        echo 'Plugin activated successfully!';
    }
    else {
        echo 'Error activating plugin!';
    }

    wp_die();

}

function item_deactivate(){

    if( !is_wp_error( deactivate_plugins( array( $_POST[ 'slug' ] ) ) ) ){
        echo 'Plugin de-activated successfully!';
    }
    else {
        echo 'Error de-activating plugin!';
    }

    wp_die();

}

function ultrapm_get_suffix(){
    $thispage = get_current_screen();
    $hook_suffix = $thispage->base;
    return $hook_suffix;
}

function items_stage_install(){

    try {
        $install_log = array();
    
        $items = json_decode( stripslashes( $_POST[ 'items' ] ) );
        $activate = $_POST[ 'activate' ];
    
        foreach( $items as $item ){
    
            if( strtolower( $item->item_type ) == 'plugin' ){
                $install_result = item_install( $item, $activate );
                array_push( $install_log, $install_result );
            }
    
        }
    
        echo json_encode( $install_log );
    } catch (Exception $e) {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
    }

    wp_die();

}

function item_install( $item, $activate ){

    if( !is_plugin_installed( $item->slug ) ){
        return install_plugin( $item->slug, $item->zip_url, $activate, $item->itemname );
    }
    else {
        return 'Plugin ' . $item->itemname . ' is already installed!';
    }

}

function is_plugin_installed( $slug ) {
    if ( ! function_exists( 'get_plugins' ) ) {
      require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    $all_plugins = get_plugins();

    foreach( $all_plugins as $key => $value ){
        
        if( strpos( $key, $slug ) !== false ){
            return true; 
        }

    }

    return false;
}

function ultrapm_is_plugin_autoupdate($file) {
    // file like == ultra-plugin-manager/ultrapm.php , classic-editor/classic-editor.php
    $plugins = get_option('auto_update_plugins');
    if( $plugins == false ){
        $plugins = array();
    }
    if( !in_array( $file, $plugins ) ){
        return false;
    } else {
        return true;
    }
}

function ultrapm_update_plugin($file) {
    $current = get_site_transient( 'update_plugins' );
	if ( ! isset( $current->response[ $file ] ) ) {
		return false;
	}
    $plugin = $current->response[ $file ];
    $plugin = (object) $plugin;
    $plugin->package = $plugin->package . '&ultra-plugin-manager=1';
    $upgrader = new Plugin_Upgrader( new ULTRAPM_WP_Upgrader_Skin() );
}

function is_theme_installed( $slug ) {
    $theme = wp_get_theme( $slug );
    if ( $theme->exists() ) {
        return true;
    } else {
        return false;
    }
}

function remove_plugin(){

    deactivate_plugins( array( $_POST[ 'slug_prefix' ] ) );
    
    $result = delete_plugins( array( $_POST[ 'slug_prefix' ] ) );

    if( is_wp_error( $result ) ){
        echo trim( $result->get_error_message() );
    }
    else {
        echo 'Plugin deleted successfully!';
    }
        
    wp_die();

}

function activate_theme(){

    $result = switch_theme( $_POST[ 'stylesheet' ] );

    echo 'Activation successfull!';

    wp_die();

}

function ultrapm_handle_activate_theme_bySlug() {
    if( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] == 'ultrapm_activate_theme_bySlug' ){
        $response = array();
        $stylesheet = $_POST[ 'slug' ];
        $result = switch_theme( $stylesheet );
        if( is_wp_error( $result ) ){
            $response[ 'success' ] = false;
            $response[ 'status' ] = 'info';
            $response[ 'message' ] = trim( $result->get_error_message() );
        }
        else {
            $response[ 'success' ] = true;
            $response[ 'status' ] = 'success';
            $response[ 'message' ] = 'Theme ' . $stylesheet . ' activated successfully!';
        }
        echo json_encode( $response );
        wp_die();
    }
}

function ultrapm_handle_activate_plugin_bySlug() {
    if( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] == 'ultrapm_activate_plugin_bySlug' ){
        $response = array();
        $slug = $_POST[ 'slug' ];
        $data = get_option( 'ultrapm_installed_plugins_from_api' );
        if( $data == false ){
            $data = array();
        }
        array_push( $data, $slug );
        update_option( 'ultrapm_installed_plugins_from_api', $data );
        if( strpos( $slug, '.php' ) !== false ) {
            $slug = explode( '/', $slug );
            $slug = $slug[0];
            $slug = ultrapm_whereis_plugin_folder( $slug );
        }
        $pathe = ultrapm_whereis_mainpluginpath( WP_PLUGIN_DIR . '/' . strtolower($slug) );
        if($pathe == '') {
            $pathe = ultrapm_whereis_mainpluginpath( WP_PLUGIN_DIR . '/' . $slug );
        }
        if($pathe) {
            $result = activate_plugin( $pathe, '', is_network_admin(), true );
            if( is_wp_error( $result ) ){
                $cache_plugins = wp_cache_get( 'plugins', 'plugins' );
                $plugin_info = file_get_contents( WP_UPLOAD_DIR . '/ultra-plugin-manager/' . $slug . '.json' );
                $plugin_info = json_decode( $plugin_info );
                $plugin_name = $plugin_info->name;
                $plugin_uri = $plugin_info->plugin_uri;
                $plugin_version = $plugin_info->version;
                $plugin_description = $plugin_info->description;
                $author_name = $plugin_info->author_name;
                $author_uri = $plugin_info->author_uri;
                if ( !empty( $cache_plugins ) ) {
                    $new_plugin = array(
                    'Name' => $plugin_name,
                    'PluginURI' => $plugin_uri,
                    'Version' => $plugin_version,
                    'Description' => $plugin_description,
                    'Author' => $author_name,
                    'AuthorURI' => $author_uri,
                    'TextDomain' => '',
                    'DomainPath' => '',
                    'Network' => '',
                    'Title' => $plugin_name,
                    'AuthorName' => $author_name,
                    );
                $cache_plugins[''][$plugin_path] = $new_plugin;
                wp_cache_set( 'plugins', $cache_plugins, 'plugins' );
                }
                $result = activate_plugin( $pathe, '', is_network_admin(), true );
                if( is_wp_error( $result ) ){
                    $response[ 'success' ] = false;
                    $response[ 'status' ] = 'info';
                    $response[ 'message' ] = trim( $result->get_error_data() );
                }
                else {
                    // check if plugin is active
                    $is_active = is_plugin_active( $pathe );
                    if( $is_active ){
                        $response[ 'success' ] = true;
                        $response[ 'status' ] = 'success';
                        $response[ 'message' ] = 'Plugin ' . $slug . ' activated successfully!';
                    } else {
                        $response[ 'success' ] = false;
                        $response[ 'status' ] = 'info';
                        $response[ 'message' ] = 'Plugin ' . $slug . ' failed to activate!';
                    }
                }
            }
            else {
                // check if plugin is active
                $is_active = is_plugin_active( $pathe );
                if( $is_active ){
                    $response[ 'success' ] = true;
                    $response[ 'status' ] = 'success';
                    $response[ 'message' ] = 'Plugin ' . $slug . ' activated successfully!';
                } else {
                    $response[ 'success' ] = false;
                    $response[ 'status' ] = 'info';
                    $response[ 'message' ] = 'Plugin ' . $slug . ' failed to activate!';
                }
            }
        } else {
            $response[ 'success' ] = false;
            $response[ 'status' ] = 'info';
            $response[ 'message' ] = 'Plugin ' . $slug . ' not found!';
        }
        echo json_encode( $response );
        wp_die();
    }
}

function ultrapm_handle_force_activate_installed_plugin_from_api(){
    wp_cache_flush();
    $data = get_option( 'ultrapm_installed_plugins_from_api' );
    if( $data == false ){
        return;
    }
    update_option( 'ultrapm_installed_plugins_from_api2', $data );
    $response = array();
    $updatedArray = array();
    foreach( $data as $slug ){
        if( strpos( $slug, '.php' ) !== false ) {
            $slug = explode( '/', $slug );
            $slug = $slug[0];
            $slug = ultrapm_whereis_plugin_folder( $slug );
        }
        $pathe = ultrapm_whereis_mainpluginpath( WP_PLUGIN_DIR . '/' . strtolower($slug) );
        if($pathe == '') {
            $pathe = ultrapm_whereis_mainpluginpath( WP_PLUGIN_DIR . '/' . $slug );
        }
        if($pathe) {
            $result = activate_plugin( $pathe, '', is_network_admin(), true );
            if( is_wp_error( $result ) ){
                $cache_plugins = wp_cache_get( 'plugins', 'plugins' );
                $plugin_info = file_get_contents( WP_UPLOAD_DIR . '/ultra-plugin-manager/' . $slug . '.json' );
                $plugin_info = json_decode( $plugin_info );
                $plugin_name = $plugin_info->name;
                $plugin_uri = $plugin_info->plugin_uri;
                $plugin_version = $plugin_info->version;
                $plugin_description = $plugin_info->description;
                $author_name = $plugin_info->author_name;
                $author_uri = $plugin_info->author_uri;
                if ( !empty( $cache_plugins ) ) {
                    $new_plugin = array(
                    'Name' => $plugin_name,
                    'PluginURI' => $plugin_uri,
                    'Version' => $plugin_version,
                    'Description' => $plugin_description,
                    'Author' => $author_name,
                    'AuthorURI' => $author_uri,
                    'TextDomain' => '',
                    'DomainPath' => '',
                    'Network' => '',
                    'Title' => $plugin_name,
                    'AuthorName' => $author_name,
                    );
                $cache_plugins[''][$plugin_path] = $new_plugin;
                wp_cache_set( 'plugins', $cache_plugins, 'plugins' );
                }
                $result = activate_plugin( $pathe, '', is_network_admin(), true );
                if( is_wp_error( $result ) ){
                    $response[ 'success' ] = false;
                    $response[ 'status' ] = 'info';
                    $response[ 'message' ] = trim( $result->get_error_data() );
                    array_push( $updatedArray, $slug );
                }
                else {
                    // check if plugin is active
                    $is_active = is_plugin_active( $pathe );
                    if( $is_active ){
                        $response[ 'success' ] = true;
                        $response[ 'status' ] = 'success';
                        $response[ 'message' ] = 'Plugin ' . $slug . ' activated successfully!';
                    } else {
                        $response[ 'success' ] = false;
                        $response[ 'status' ] = 'info';
                        $response[ 'message' ] = 'Plugin ' . $slug . ' failed to activate!';
                        array_push( $updatedArray, $slug );
                    }
                }
            } else {
                // check if plugin is active
                $is_active = is_plugin_active( $pathe );
                if( $is_active ){
                    $response[ 'success' ] = true;
                    $response[ 'status' ] = 'success';
                    $response[ 'message' ] = 'Plugin ' . $slug . ' activated successfully!';
                } else {
                    $response[ 'success' ] = false;
                    $response[ 'status' ] = 'info';
                    $response[ 'message' ] = 'Plugin ' . $slug . ' failed to activate!';
                    array_push( $updatedArray, $slug );
                }
            }
        } else {
            $response[ 'success' ] = false;
            $response[ 'status' ] = 'info';
            $response[ 'message' ] = 'Plugin ' . $slug . ' not found!';
        }
    }
    update_option( 'ultrapm_installed_plugins_from_api', array() );
    update_option( 'ultrapm_failed_installed_plugins_from_api', $updatedArray );
    //ultrapm_handle_force_activate_installed_plugin_from_api2();
    //echo json_encode( $response );
    //wp_die();
}

function ultrapm_handle_force_activate_installed_plugin_from_api2(){
    wp_cache_flush();
    $data = get_option( 'ultrapm_installed_plugins_from_api2' );
    if( $data == false ){
        return;
    }
    $response = array();
    $updatedArray = array();
    foreach( $data as $slug ){
        if( strpos( $slug, '.php' ) !== false ) {
            $slug = explode( '/', $slug );
            $slug = $slug[0];
            $slug = ultrapm_whereis_plugin_folder( $slug );
        }
        $pathe = ultrapm_whereis_mainpluginpath( WP_PLUGIN_DIR . '/' . strtolower($slug) );
        if($pathe == '') {
            $pathe = ultrapm_whereis_mainpluginpath( WP_PLUGIN_DIR . '/' . $slug );
        }
        if($pathe) {
            $result = activate_plugin( $pathe, '', is_network_admin(), true );
            if( is_wp_error( $result ) ){
                $cache_plugins = wp_cache_get( 'plugins', 'plugins' );
                $plugin_info = file_get_contents( WP_UPLOAD_DIR . '/ultra-plugin-manager/' . $slug . '.json' );
                $plugin_info = json_decode( $plugin_info );
                $plugin_name = $plugin_info->name;
                $plugin_uri = $plugin_info->plugin_uri;
                $plugin_version = $plugin_info->version;
                $plugin_description = $plugin_info->description;
                $author_name = $plugin_info->author_name;
                $author_uri = $plugin_info->author_uri;
                if ( !empty( $cache_plugins ) ) {
                    $new_plugin = array(
                    'Name' => $plugin_name,
                    'PluginURI' => $plugin_uri,
                    'Version' => $plugin_version,
                    'Description' => $plugin_description,
                    'Author' => $author_name,
                    'AuthorURI' => $author_uri,
                    'TextDomain' => '',
                    'DomainPath' => '',
                    'Network' => '',
                    'Title' => $plugin_name,
                    'AuthorName' => $author_name,
                    );
                $cache_plugins[''][$plugin_path] = $new_plugin;
                wp_cache_set( 'plugins', $cache_plugins, 'plugins' );
                }
                $result = activate_plugin( $pathe, '', is_network_admin(), true );
                if( is_wp_error( $result ) ){
                    $response[ 'success' ] = false;
                    $response[ 'status' ] = 'info';
                    $response[ 'message' ] = trim( $result->get_error_data() );
                    array_push( $updatedArray, $slug );
                }
                else {
                    // check if plugin is active
                    $is_active = is_plugin_active( $pathe );
                    if( $is_active ){
                        $response[ 'success' ] = true;
                        $response[ 'status' ] = 'success';
                        $response[ 'message' ] = 'Plugin ' . $slug . ' activated successfully!';
                    } else {
                        $response[ 'success' ] = false;
                        $response[ 'status' ] = 'info';
                        $response[ 'message' ] = 'Plugin ' . $slug . ' failed to activate!';
                        array_push( $updatedArray, $slug );
                    }
                }
            } else {
                // check if plugin is active
                $is_active = is_plugin_active( $pathe );
                if( $is_active ){
                    $response[ 'success' ] = true;
                    $response[ 'status' ] = 'success';
                    $response[ 'message' ] = 'Plugin ' . $slug . ' activated successfully!';
                } else {
                    $response[ 'success' ] = false;
                    $response[ 'status' ] = 'info';
                    $response[ 'message' ] = 'Plugin ' . $slug . ' failed to activate!';
                    array_push( $updatedArray, $slug );
                }
            }
        } else {
            $response[ 'success' ] = false;
            $response[ 'status' ] = 'info';
            $response[ 'message' ] = 'Plugin ' . $slug . ' not found!';
        }
    }
    //update_option( 'ultrapm_installed_plugins_from_api2', array() );
    update_option( 'ultrapm_failed_installed_plugins_from_api2', $updatedArray );
    //echo json_encode( $response );
    //wp_die();
}

function ultrapm_handle_activate_plugin_bySlugs() {
    if( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] == 'ultrapm_activate_plugin_bySlugs' ){
        $response = array();
        $slugs = $_POST[ 'slugs' ];
        $slugs = explode( ',', $slugs );
        foreach( $slugs as $slug ) {
            if($slug == '') {
                continue;
            }
            if( strpos( $slug, '.php' ) !== false ) {
                $slug = explode( '/', $slug );
                $slug = $slug[0];
                $slug = ultrapm_whereis_plugin_folder( $slug );
            }
            $pathe = ultrapm_whereis_mainpluginpath( WP_PLUGIN_DIR . '/' . strtolower($slug) );
            if($pathe == '') {
                $pathe = ultrapm_whereis_mainpluginpath( WP_PLUGIN_DIR . '/' . $slug );
            }
            if($pathe) {
                $is_active = is_plugin_active( $pathe );
                if($is_active) {
                    continue;
                }
                $result = activate_plugin( $pathe, '', is_network_admin(), true );
                if( is_wp_error( $result ) ){
                    $response[ 'success' ] = false;
                    $response[ 'status' ] = 'info';
                    $response[ 'message' ] = trim( $result->get_error_data() );
                }
                else {
                    // check if plugin is active
                    $is_active = is_plugin_active( $pathe );
                    if( $is_active ){
                        $response[ 'success' ] = true;
                        $response[ 'status' ] = 'success';
                        $response[ 'message' ] = 'Plugin ' . $slug . ' activated successfully!';
                    } else {
                        $response[ 'success' ] = false;
                        $response[ 'status' ] = 'info';
                        $response[ 'message' ] = 'Plugin ' . $slug . ' failed to activate!';
                    }
                }
            } else {
                $response[ 'success' ] = false;
                $response[ 'status' ] = 'info';
                $response[ 'message' ] = 'Plugin ' . $slug . ' not found!';
            }
        }
        echo json_encode( $response );
        wp_die();
    }
}

function ultrapm_handle_deactivate_plugin_bySlug() {
    if( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] == 'ultrapm_deactivate_plugin_bySlug' ){
        $response = array();
        $slug = $_POST[ 'slug' ];
        if( strpos( $slug, '.php' ) !== false ) {
            $slug = explode( '/', $slug );
            $slug = $slug[0];
            $slug = ultrapm_whereis_plugin_folder( $slug );
        }
        $pathe = ultrapm_whereis_mainpluginpath( WP_PLUGIN_DIR . '/' . strtolower($slug) );
        if($pathe == '') {
            $pathe = ultrapm_whereis_mainpluginpath( WP_PLUGIN_DIR . '/' . $slug );
        }
        if($pathe) {
            $result = deactivate_plugins( array( $pathe ), true );
            if( is_wp_error( $result ) ){
                $response[ 'success' ] = false;
                $response[ 'status' ] = 'info';
                $response[ 'message' ] = trim( $result->get_error_data() );
            }
            else {
                $response[ 'success' ] = true;
                $response[ 'status' ] = 'success';
                $response[ 'message' ] = 'Plugin ' . $slug . ' deactivated successfully!';
            }
        } else {
            // cari folder tanpa memperdulikan huruf besar atau kecil dari slug
            $plugindir = WP_PLUGIN_DIR;
            $plugins = scandir( $plugindir );
            foreach( $plugins as $plugin ) {
                // cari folder yang mengandung text dari slug sebelum "/"
                if( strpos( $plugin, $slug ) !== false ) {
                    $pathe = ultrapm_whereis_mainpluginpath( WP_PLUGIN_DIR . '/' . strtolower($plugin) );
                    if($pathe == '') {
                        $pathe = ultrapm_whereis_mainpluginpath( WP_PLUGIN_DIR . '/' . $plugin );
                    }
                    if($pathe) {
                        $result = deactivate_plugins( array( $pathe ), true );
                        if( is_wp_error( $result ) ){
                            $response[ 'success' ] = false;
                            $response[ 'status' ] = 'info';
                            $response[ 'message' ] = trim( $result->get_error_data() );
                        }
                        else {
                            $response[ 'success' ] = true;
                            $response[ 'status' ] = 'success';
                            $response[ 'message' ] = 'Plugin ' . $plugin . ' deactivated successfully!';
                        }
                    } else {
                        $response[ 'success' ] = false;
                        $response[ 'status' ] = 'info';
                        $response[ 'message' ] = 'Plugin ' . $plugin . ' not found!';
                    }
                }
            }
        }
        echo json_encode( $response );
        wp_die();
    }
}

function ultrapm_handle_deactivate_plugin_bySlugs() {
    if( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] == 'ultrapm_deactivate_plugin_bySlugs' ){
        $response = array();
        $slugs = $_POST[ 'slugs' ];
        $slugs = explode( ',', $slugs );
        foreach( $slugs as $slug ) {
            if($slug == '') {
                continue;
            }
            if( strpos( $slug, '.php' ) !== false ) {
                $slug = explode( '/', $slug );
                $slug = $slug[0];
                $slug = ultrapm_whereis_plugin_folder( $slug );
            }
            $pathe = ultrapm_whereis_mainpluginpath( WP_PLUGIN_DIR . '/' . strtolower($slug) );
            if($pathe == '') {
                $pathe = ultrapm_whereis_mainpluginpath( WP_PLUGIN_DIR . '/' . $slug );
            }
            if($pathe) {
                $result = deactivate_plugins( array( $pathe ), true );
                if( is_wp_error( $result ) ){
                    $response[ 'success' ] = false;
                    $response[ 'status' ] = 'info';
                    $response[ 'message' ] = trim( $result->get_error_data() );
                }
                else {
                    $response[ 'success' ] = true;
                    $response[ 'status' ] = 'success';
                    $response[ 'message' ] = 'Plugin ' . $slug . ' deactivated successfully!';
                }
            } else {
                // cari folder tanpa memperdulikan huruf besar atau kecil dari slug
                $plugindir = WP_PLUGIN_DIR;
                $plugins = scandir( $plugindir );
                foreach( $plugins as $plugin ) {
                    // cari folder yang mengandung text dari slug sebelum "/"
                    if( strpos( $plugin, $slug ) !== false ) {
                        $pathe = ultrapm_whereis_mainpluginpath( WP_PLUGIN_DIR . '/' . strtolower($plugin) );
                        if($pathe == '') {
                            $pathe = ultrapm_whereis_mainpluginpath( WP_PLUGIN_DIR . '/' . $plugin );
                        }
                        if($pathe) {
                            $result = deactivate_plugins( array( $pathe ), true );
                            if( is_wp_error( $result ) ){
                                $response[ 'success' ] = false;
                                $response[ 'status' ] = 'info';
                                $response[ 'message' ] = trim( $result->get_error_data() );
                            }
                            else {
                                $response[ 'success' ] = true;
                                $response[ 'status' ] = 'success';
                                $response[ 'message' ] = 'Plugin ' . $plugin . ' deactivated successfully!';
                            }
                        } else {
                            $response[ 'success' ] = false;
                            $response[ 'status' ] = 'info';
                            $response[ 'message' ] = 'Plugin ' . $plugin . ' not found!';
                        }
                    }
                }
            }
        }
        echo json_encode( $response );
        wp_die();
    }
}

function delDirectory( $dir ) {
    if ( !file_exists( $dir ) ) {
        return true;
    }

    if ( !is_dir( $dir ) ) {
        return unlink( $dir );
    }

    foreach (scandir( $dir ) as $item ) {
        if ( $item == '.' || $item == '..' ) {
            continue;
        }

        if ( !delDirectory( $dir . DIRECTORY_SEPARATOR . $item ) ) {
            return false;
        }

    }

    return rmdir( $dir );
}

function remove_theme(){

    delDirectory( WP_CONTENT_DIR . '/themes' . '/' . $_POST[ 'stylesheet' ] );

    echo 'Theme deletion successful!';

    wp_die();

}

function ultrapm_handle_delete_theme_bySlug() {
    if( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] == 'ultrapm_delete_theme_bySlug' ){
        $response = array();
        $stylesheet = $_POST[ 'slug' ];
        delDirectory( WP_CONTENT_DIR . '/themes' . '/' . $stylesheet );
        $response[ 'success' ] = true;
        $response[ 'status' ] = 'success';
        $response[ 'message' ] = 'Theme ' . $stylesheet . ' deleted successfully!';
        echo json_encode( $response );
        wp_die();
    }
}

function ultrapm_handle_delete_plugin_bySlug() {
    if( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] == 'ultrapm_delete_plugin_bySlug' ){
        $response = array();
        $slug = $_POST[ 'slug' ];
        deactivate_plugins( array( $slug ) );
        $file = ultrapm_whereis_mainpluginpath( WP_PLUGIN_DIR . '/' . strtolower($slug) );
        $result = delete_plugins( array( $file ) );
        if( is_wp_error( $result ) ){
            $response[ 'success' ] = false;
            $response[ 'status' ] = 'info';
            $response[ 'message' ] = trim( $result->get_error_message() );
        }
        else {
            $response[ 'success' ] = true;
            $response[ 'status' ] = 'success';
            $response[ 'message' ] = 'Plugin ' . $slug . ' deleted successfully!';
        }
        echo json_encode( $response );
        wp_die();
    }
}

function ultrapm_handle_delete_plugin_byFile(){
    if( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] == 'ultrapm_delete_plugin_byFile' ){
        $response = array();
        $file = $_POST[ 'file' ];
        $slug = explode( '/', $file );
        $slug = $slug[0];
        $folder = ultrapm_whereis_plugin_folder( $file );
        $mainplugindir = ultrapm_whereis_mainpluginpath(  WP_PLUGIN_DIR . '/' . $folder );
        $is_active = is_plugin_active( $mainplugindir );
        //if( $is_active ){
            //$response[ 'success' ] = false;
            //$response[ 'status' ] = 'info';
            //$response[ 'message' ] = 'Plugin ' . $file . ' is active! Please deactivate it first.';
        //} else {
            if($folder != '') {
                $allplugins = get_option( 'active_plugins', array() );
                foreach( $allplugins as $key => $value ){
                    if( $value == $file ){
                        unset( $allplugins[ $key ] );
                    }
                }
                deactivate_plugins( array( $slug ) );
                update_option( 'active_plugins', $allplugins );
                $result = deleteDirectory( WP_PLUGIN_DIR . '/' . $folder );
                if( is_wp_error( $result ) ){
                    $invalid = validate_active_plugins();
                    if ( ! empty( $invalid ) ) {
                        foreach ( $invalid as $plugin_file => $error ) {
                            // unset $file from invalid plugins
                            if ( $plugin_file == $file ) {
                                unset( $invalid[ $plugin_file ] );
                            }
                        }
                    }
                    $response[ 'success' ] = false;
                    $response[ 'status' ] = 'info';
                    $response[ 'message' ] = 'Error deleting plugin ' . $file . '!'; //trim( $result->get_error_message() );
                }
                else {
                    if($folder == 'w3-total-cache') {
                        if(file_exists(WP_CONTENT_DIR . '/advanced-cache.php')) {
                            unlink(WP_CONTENT_DIR . '/advanced-cache.php');
                        }
                    }
                    $response[ 'success' ] = true;
                    $response[ 'status' ] = 'success';
                    $response[ 'message' ] = 'Plugin ' . $file . ' deleted successfully!';
                }
            } else {
                $response[ 'success' ] = false;
                $response[ 'status' ] = 'info';
                $response[ 'message' ] = 'Plugin ' . $file . ' not found!';
            }
        //}
        echo json_encode( $response );
        wp_die();
    }
}

function install_plugin( $slug, $zip_url, $activate, $plugin_name ) {
    include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    
    $upgrader_skin = new ULTRAPM_WP_Upgrader_Skin();
    
    if ( ! class_exists( 'Plugin_Upgrader' ) ) {
        require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';
    }
        
    $upgrader = new Plugin_Upgrader( $upgrader_skin );
    //$installed = $upgrader->install( $zip_url, array( 'clear_update_cache' => true ) );

    $zarchiveplugin = new ZipArchive();
    if ($zarchiveplugin === false) {
        die('Gagal menginisialisasi objek ZipArchive.');
    }

    // download file zip plugin
    $zip_url = $zip_url;
    $zip_file = $slug . '.zip';
    $zip_file_path = WP_CONTENT_DIR . '/plugins/' . $zip_file;
    $zip_resource = fopen($zip_file_path, "w");
    // mengirim permintaan ke server
    $ch_start = curl_init();
    curl_setopt($ch_start, CURLOPT_URL, $zip_url);
    curl_setopt($ch_start, CURLOPT_FAILONERROR, true);
    curl_setopt($ch_start, CURLOPT_HEADER, 0);
    curl_setopt($ch_start, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch_start, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch_start, CURLOPT_BINARYTRANSFER,true);
    curl_setopt($ch_start, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch_start, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch_start, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch_start, CURLOPT_FILE, $zip_resource);
    $page = curl_exec($ch_start);
    if (!$page) {
        die('Gagal mengunduh berkas ZIP.');
    }

    // ekstrak file zip plugin
    $zarchiveplugin->open( $zip_file_path );
    $extractRes = $zarchiveplugin->extractTo( WP_CONTENT_DIR . '/plugins' );
    wp_cache_flush();
    
    $zarchiveplugin->close(); 

    //if ( !is_wp_error( $installed ) ) {
        if( $extractRes ){
            if( $activate == '1' ){
                if( is_wp_error( activate_plugin( $slug . '/' . $slug . '.php' ) ) ) {
                    return 'Plugin ' . $plugin_name . ' installed successfully but failed to activate!';
                }
                else {
                    return 'Plugin ' . $plugin_name . ' installed and activated successfully!';
                }
            }
            else {
                return 'Plugin ' . $plugin_name . ' installed successfully!';
            }
        }
        else {
            return 'Error installing ' . $plugin_name . '!';
        }
    //}
    //else {
       // return 'Error installing ' . $plugin_name . '!';
    //}
    // remove file zip plugin
    unlink($slug . '.zip');
}
   
function upgrade_plugin( $slug ) {
    include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    wp_cache_flush();
        
    $upgrader = new Plugin_Upgrader();
    $upgraded = $upgrader->upgrade( $slug );

    return $upgraded;
}

function add_ultrapm_config(){
    
    global $wpdb;

    $items = serialize( explode( ',', $_POST[ 'items' ] ) );

    $configs = $wpdb->get_results(
        "
        SELECT COUNT(*) as count FROM 
        $wpdb->qs_configs_table_name
        WHERE configname = '" . $_POST[ 'name' ] . "'
        "
    );

    if( $configs[0]->count > 0 ) {
        echo 'A configuration with the name ' . $_POST[ 'name' ] . ' already exists! ' . 
        'Please use a different name.';
    }
    else {

        $result = $wpdb->insert(
            $wpdb->qs_configs_table_name,
            array( 
                'configname' => $_POST[ 'name' ],
                'configitems' => $items
            ), 
            array( 
                '%s',
                '%s'
            ) 
        );

        if( isset( $wpdb->insert_id ) ){
            echo 'Configuration ' . $_POST[ 'name' ] . ' saved successfully!';
        }
        else{
            echo 'Failed saving configuration ' . $_POST[ 'name' ];
        }

    }
    
    wp_die();

}

function delete_ultrapm_config(){

    global $wpdb;

    $result = $wpdb->delete(
        $wpdb->qs_configs_table_name,
        array(
            "id" => $_POST[ 'id' ] 
        ),
        array(
            '%d'
        )
    );

    if( $result ){
        echo 'Package deleted successfully!';
    }
    else{
        echo 'Failed deleting package!';
    }

    wp_die();

}

function analyze_item_from_zip(){

    $output = 1;

    $item = json_decode( stripslashes( $_POST[ 'item' ] ) );

    $dir = $item[0]->dir;

    $iterator = new RecursiveDirectoryIterator( $dir );

    foreach(new RecursiveIteratorIterator( $iterator ) as $file) {
        if ($file->getExtension() == 'php') {
            //$return = shell_exec( "php -ln {$file}");
            $fileContent = file_get_contents($file);
            $tokens = @token_get_all($fileContent);
            if ($tokens === false) {
                $return = 'Sintaks PHP salah.';
            } else {
                $return = '';
                foreach ($tokens as $token) {
                    if (is_array($token)) {
                        $token = token_name($token[0]);
                    }
                    $return .= $token . ' ';
                }
            }
            if( !str_contains( $return, 'No syntax errors') ){
                if( $return != '' && $return != null ){
                    $output = $return;
                }
                else {
                    $output = 'Error in file ' . $file;
                }
                deleteDirectory( $dir );
                break;
            }
        }
    }

    echo $output;

    wp_die();

}

function install_item_from_zip() {
    
    $item = json_decode( stripslashes( $_POST[ 'item' ] ) );
    $is_activate = $_POST[ 'activate' ];
    $name = $item[0]->folder;

    $file = $item[0]->file;
    $dir = $item[0]->dir;
    $type = $item[0]->item_type;

    if( $type == 'theme' ){
        
        $zarchive = new ZipArchive();
        $zarchive->open( $file );                         
        $extractRes = $zarchive->extractTo( WP_CONTENT_DIR . '/themes' );  
        $zarchive->close(); 

        if( $extractRes ){
            if( $is_activate == '1' ){
                $result = switch_theme( $name );
                if( is_wp_error( $result ) ){
                    echo trim( $result->get_error_message() );
                }
                else {
                    echo 'Theme ' . $name . ' installed and activated successfully!';
                }
            }
            else {
                echo 'Theme ' . $name . ' installed successfully!';
            }
        } 
        else {
            echo 'Error installing ' . $name . '!'; 
        }

    }
    else if( $type == 'plugin' ){
            
        $zarchive = new ZipArchive();
        $zarchive->open( $file );                         
        $extractRes = $zarchive->extractTo( WP_CONTENT_DIR . '/plugins' );  
        $zarchive->close();     
        wp_cache_flush();
        if( $extractRes ){
            if( $is_activate == '1' ){
                $pathe = ultrapm_whereis_mainplugin( WP_PLUGIN_DIR . '/' . strtolower($name) );
                if($pathe == '') {
                    $pathe = ultrapm_whereis_mainplugin( WP_PLUGIN_DIR . '/' . $name );
                }
                if($pathe) {
                    $result = activate_plugin( $pathe );
                    if( is_wp_error( $result ) ){
                        echo trim( $result->get_error_message() . '\n' );
                    }
                    else {
                        echo 'Plugin ' . $name . ' installed and activated successfully!\n';
                    }
                }
            }
            else {
                echo 'Plugin ' . $name . ' installed successfully!\n';
            }
        }
        else {
            echo 'Error installing ' . $name . '!\n';
        }
    }

    deleteDirectory( $dir );

    wp_die();

}

function deleteDirectory( $dir ) {
    if ( !file_exists( $dir ) ) {
        return true;
    }

    if ( !is_dir( $dir ) ) {
        return unlink( $dir );
    }

    foreach (scandir( $dir ) as $item ) {
        if ( $item == '.' || $item == '..' ) {
            continue;
        }

        if ( !deleteDirectory( $dir . DIRECTORY_SEPARATOR . $item ) ) {
            return false;
        }

    }

    return rmdir( $dir );
}

function handleThemeInstallation() {
    // Periksa apakah permintaan adalah POST request
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $response = array();
        // Periksa apakah tiga parameter yang diperlukan ada dalam permintaan
        if (isset($_POST['action']) && isset($_POST['zip_url']) && isset($_POST['itemname'])) {
            // Ambil nilai dari tiga parameter
            $action = $_POST['action'];
            $zip_url = $_POST['zip_url'];
            if(strpos($zip_url, 'drive.google.com') !== false) {
                $zip_url = ultrapm_gdrive_downloader( $_POST['id'] );
            } else if(strpos($zip_url, 'dropbox.com') !== false) {
                $zip_url = ultrapm_dropbox_downloader( $zip_url );
            } else if(strpos($zip_url, 'wordpress.org') !== false) {
                $zip_url = $_POST['zip_url'];
            }
            // jika tidak ada http, dan bukan dari drive.google.com, dropbox.com, atau wordpress.org
            else if(strpos($zip_url, 'http') === false && strpos($zip_url, 'drive.google.com') === false && strpos($zip_url, 'dropbox.com') === false && strpos($zip_url, 'wordpress.org') === false) {
                // var themeZipUrl = 'https://downloads.wordpress.org/theme/' + themeSlug + '.' + info.version + '.zip';
                $getinfo = wp_safe_remote_get( 'https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]=' . $_POST['itemname'] );
                $getinfo = wp_remote_retrieve_body( $getinfo );
                // jika getinfo tidak error
                if( !is_wp_error( $getinfo ) ) {
                    $getinfo = json_decode( $getinfo, true );
                    $zip_url = $getinfo['download_link'];
                } else {
                    $response['message'] = 'Invalid URL.';
                    echo json_encode( $response );
                    wp_die();
                }
            }
            $itemname = $_POST['itemname'];

            // Periksa apakah nilai 'action' adalah 'item_install'
            if ($action === 'ultrapm_item_install') {
                // Ambil nama tema dari URL jika ada
                $path_parts = pathinfo($zip_url);
                $themeName = sanitize_title(basename($path_parts['filename']));
            
                // Periksa apakah tema sudah diinstal sebelumnya
                $themeData = wp_get_theme($themeName);
            
                if (!empty($themeData->Name)) {
                   // $response['message'] = 'Tema ' . $themeName . ' sudah diinstal.';
                }
            
                // Ambil isi file ZIP dari URL
                $response = wp_safe_remote_get($zip_url);

                if (is_wp_error($response)) {
                    //$response['message'] = 'Gagal mengunduh berkas ZIP.';
                }
            
                $zipFile = wp_upload_dir()['basedir'] . '/' . $themeName . '.zip';
            
                // Simpan isi file ZIP ke sistem file
                if (file_put_contents($zipFile, wp_remote_retrieve_body($response))) {
                    // Instalasi tema dari file ZIP
                    WP_Filesystem();

                    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
                    $upgrader = new Theme_Upgrader(new WP_Upgrader_Skin());
                
                    $installResult = $upgrader->install($zipFile);
                
                    if (is_wp_error($installResult)) {
                        //$response['message'] = 'Gagal menginstal tema ' . $themeName . '.';
                    }
                
                    // Aktifkan tema
                    if (isset($_POST['activate']) && $_POST['activate'] === 'true') {
                        $activateResult = switch_theme(strtolower($itemname));
                
                        if (is_wp_error($activateResult)) {
                            //$response['message'] = 'Gagal mengaktifkan tema ' . $themeName . '.';
                        }
                    }
                
                    // Hapus file ZIP
                    unlink($zipFile);
                    $response['message'] = 'Tema ' . $themeName . ' berhasil diinstal.';
                }
                // Jika instalasi gagal, kembalikan respon dengan pesan kesalahan
                //$response['message'] = 'Gagal menginstal tema ' . $themeName . '.';
            } else {
                // Jika nilai 'action' bukan 'item_install', kembalikan respon dengan pesan kesalahan
                //$response['message'] = 'Invalid action!';
            }
        } else {
            // Jika salah satu dari tiga parameter yang diperlukan tidak ada, kembalikan respon dengan pesan kesalahan
            //$response['message'] = 'Missing required parameters!';
        }
        // Kembalikan respon
        echo json_encode( $response );
        wp_die();
    }

}


function handlePluginInstallation() {
    // Periksa apakah permintaan adalah POST request
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $response = array();
        // Periksa apakah tiga parameter yang diperlukan ada dalam permintaan
        if (isset($_POST['action']) && isset($_POST['zip_url']) && isset($_POST['itemname'])) {
            // Ambil nilai dari tiga parameter
            $action = $_POST['action'];
            $zip_url = $_POST['zip_url'];
            $itemname = $_POST['itemname'];
            $slug = $_POST['slug'];

            // Periksa apakah nilai 'action' adalah 'item_install'
            if ($action === 'ultrapm_plugin_install') {
                // Ambil nama tema dari URL jika ada
                $path_parts = pathinfo($zip_url);
                $pluginName = sanitize_title(basename($path_parts['filename']));
            
                // Periksa apakah tema sudah diinstal sebelumnya
                // $pluginData = get_plugin_data( WP_PLUGIN_DIR . '/' . $pluginName . '/' . $pluginName . '.php' );
            
                // if (!empty($pluginData['Name'])) {
                   // $response['message'] = 'Tema ' . $themeName . ' sudah diinstal.';
                // }
            
                // Ambil isi file ZIP dari URL
                // $response = wp_safe_remote_get($zip_url);

                // if (is_wp_error($response)) {
                    //$response['message'] = 'Gagal mengunduh berkas ZIP.';
                // }
            
                $zipFile = wp_upload_dir()['basedir'] . '/' . $pluginName . '.zip';
            
                // Simpan isi file ZIP ke sistem file
                if (file_put_contents($zipFile, wp_remote_retrieve_body(wp_safe_remote_get($zip_url)))) {
                    // Instalasi tema dari file ZIP
                    WP_Filesystem();
                    wp_cache_flush();

                    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
                    $upgrader = new Plugin_Upgrader(new WP_Upgrader_Skin());
                
                    $installResult = $upgrader->install($zipFile);
                
                    if (is_wp_error($installResult)) {
                        //$response['message'] = 'Gagal menginstal tema ' . $themeName . '.';
                    }
                
                    // Aktifkan tema
                    if (isset($_POST['activate']) && $_POST['activate'] === 'true') {
                        $pathe = ultrapm_whereis_mainplugin( WP_PLUGIN_DIR . '/' . strtolower($slug) );
                        if($pathe == '') {
                            $pathe = ultrapm_whereis_mainplugin( WP_PLUGIN_DIR . '/' . $slug );
                        }
                        if($pathe) {
                            $activateResult = activate_plugin( $pathe );
                    
                            if (is_wp_error($activateResult)) {
                                $response['message'] = 'Failed to activate plugin ' . $pluginName . '.';
                                //$errors = $activateResult->get_error_messages();
                                //foreach ($errors as $error) {
                                    //$response['message'] .= $error;
                                //}
                            }
                        }
                    }

                    // Hapus file ZIP
                    unlink($zipFile);
                    $response['message'] = 'Plugin ' . strtolower($slug) . ' successfully installed.';
                }
                // Jika instalasi gagal, kembalikan respon dengan pesan kesalahan
                //$response['message'] = 'Gagal menginstal tema ' . $themeName . '.';
            } else {
                // Jika nilai 'action' bukan 'item_install', kembalikan respon dengan pesan kesalahan
                //$response['message'] = 'Invalid action!';
            }
        } else {
            // Jika salah satu dari tiga parameter yang diperlukan tidak ada, kembalikan respon dengan pesan kesalahan
            //$response['message'] = 'Missing required parameters!';
        }
        // Kembalikan respon
        echo json_encode( $response );
        wp_die();
    }

}


function ultrapm_handle_addToTaskList(){
    $response = array();
    if( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] == 'ultrapm_add_to_task_list' ){
        $taskList = get_option( 'ultrapm_task_list' );
        if( $taskList == false ){
            $taskList = array();
        }
        $type = $_POST[ 'type' ];
        if($type == 'plugin') {
            $Name = $_POST[ 'name' ];
            $Slug = $_POST[ 'slug' ];
            $ZipUrl = $_POST[ 'download_link' ];
        } else if($type == 'theme') {
            $Name = $_POST[ 'name' ];
            $Slug = $_POST[ 'slug' ];
            $ZipUrl = $_POST[ 'download_link' ];
        }
        // if slug already exists in task list, return error
        foreach( $taskList as $task ){
            if( $task[ 'slug' ] == $Slug ){
                $response[ 'success' ] = false;
                $response[ 'status' ] = 'info';
                $response[ 'message' ] = $Name . ' already exists in task list.';
                echo json_encode( $response );
                wp_die();
            }
        }
        $plugin = array(
            'name' => $Name,
            'slug' => $Slug,
            'zip_url' => $ZipUrl,
            'type' => $type
        );
        update_option( 'ultrapm_task_list', array_merge( $taskList, array( $plugin ) ) );
        $response[ 'success' ] = true;
        $response[ 'status' ] = 'success';
        $response[ 'message' ] = $Name . ' added to task list.';
        echo json_encode( $response );
    } else {
        return false;
    }
    wp_die();
}

function ultrapm_is_exsit_task_list($slug, $type){
    $taskList = get_option( 'ultrapm_task_list' );
    if( $taskList == false ){
        $taskList = array();
    }
    foreach( $taskList as $task ){
        if( $task[ 'slug' ] == $slug && $task[ 'type' ] == $type ){
            return true;
        }
    }
    return false;

}

function ultrapm_handle_clear_taskList($slug = null){
    $response = array();
    if(isset($_POST['slug'])) {
        $slug = $_POST['slug'];
        $type = $_POST['type'];
        $response[ 'slug' ] = $_POST['slug'];
    }
    $uploads_dir = wp_upload_dir();
    $target_dir = $uploads_dir['basedir'] . '/ultra-plugin-manager';
    $alls = get_option( 'ultrapm_task_list' );
    if( $alls == false ){
        $alls = array();
    }
    $newalls = array();
    if( isset($_POST['slug']) ) {
        foreach( $alls as $key => $all ){
            if( $all[ 'slug' ] == $slug && $all[ 'type' ] == $type ){
                if (isset($all['file']) && isset($all['dir'])) {
                    $file = $all['file'];
                    $dir = $all['dir'];
                    if (file_exists($file)) {
                        unlink($file);
                    }
                    if (is_dir($dir)) {
                        while (basename($dir) != 'ultra-plugin-manager') {
                            $curDir = $dir;
                            $dir = dirname($dir);
                            $dirname = str_replace($target_dir, '', $dir);
                            $files = glob($target_dir . '/*.zip');
                            foreach ($files as $file) {
                                if (strpos($file, $dirname) !== false) {
                                    unlink($file);
                                }
                            }

                        }
                        $files = glob($curDir . '/*');
                        if (count($files) == 0) {
                            deleteDirectory($curDir);
                        } elseif (count($files) > 1) {
                            foreach ($files as $file) {
                                if (strpos($file, $slug . '-' . $type) !== false) {
                                    deleteDirectory($file);
                                }
                            }
                        } else {
                            deleteDirectory($curDir);
                        }
                            
                    }
                }
                //$response[ 'unset' . $key ] = $alls[$key];
                unset($alls[$key]);
            } else {
                $newalls[] = $all;
            }
        }
        update_option( 'ultrapm_task_list', $newalls );
        $response[ 'success' ] = true;
        $response[ 'status' ] = 'success';
        $response[ 'message' ] = 'Selected task list cleared.';
        echo json_encode( $response );
        wp_die();
    }

    if( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] == 'ultrapm_clear_task_list' ){
        if( count( $alls ) > 0 ){
            // example of $alls == [{"pre":true,"name":"cwicly (plugin)","file":"\/home\/wpsaya.my.id\/public_html\/wp-content\/uploads\/ultra-plugin-manager\/1jOl67Cy0UiugrwNPx_s0SWEgJblfIXyN_1699563817\/NFIX-Cwicly-v1.2.9.7.4\/cwicly-plugin.zip","dir":"\/home\/wpsaya.my.id\/public_html\/wp-content\/uploads\/ultra-plugin-manager\/1jOl67Cy0UiugrwNPx_s0SWEgJblfIXyN_1699563817\/cwicly-plugin1699563817\/cwicly","folder":"cwicly","slug":"cwicly","type":"plugin"},{"pre":true,"name":"cwicly (theme)","file":"\/home\/wpsaya.my.id\/public_html\/wp-content\/uploads\/ultra-plugin-manager\/1jOl67Cy0UiugrwNPx_s0SWEgJblfIXyN_1699563817\/NFIX-Cwicly-v1.2.9.7.4\/cwicly-theme.zip","dir":"\/home\/wpsaya.my.id\/public_html\/wp-content\/uploads\/ultra-plugin-manager\/1jOl67Cy0UiugrwNPx_s0SWEgJblfIXyN_1699563817\/cwicly-theme1699563823\/cwicly","folder":"cwicly","slug":"cwicly","type":"theme"},{"pre":true,"name":"rey (theme)","file":"\/home\/wpsaya.my.id\/public_html\/wp-content\/uploads\/ultra-plugin-manager\/dropboX1699563828.zip","dir":"\/home\/wpsaya.my.id\/public_html\/wp-content\/uploads\/ultra-plugin-manager\/dropboX1699563828_1699563829\/rey","folder":"rey","slug":"rey","type":"theme"},{"pre":true,"name":"wc-wise-gateway (plugin)","file":"\/home\/wpsaya.my.id\/public_html\/wp-content\/uploads\/ultra-plugin-manager\/wordpresS1699563834.zip","dir":"\/home\/wpsaya.my.id\/public_html\/wp-content\/uploads\/ultra-plugin-manager\/wordpresS1699563834_1699563834\/wc-wise-gateway","folder":"wc-wise-gateway","slug":"wc-wise-gateway","type":"plugin"}]
            foreach( $alls as $all ){
                if (isset($all['file']) && isset($all['dir'])) {
                    $file = $all['file'];
                    $dir = $all['dir'];
                    if (file_exists($file)) {
                        unlink($file);
                    }
                    if (is_dir($dir)) {
                        while (basename($dir) != 'ultra-plugin-manager') {
                            $curDir = $dir;
                            $dir = dirname($dir);
                            $dirname = str_replace($target_dir, '', $dir);
                            $files = glob($target_dir . '/*.zip');
                            foreach ($files as $file) {
                                if (strpos($file, $dirname) !== false) {
                                    unlink($file);
                                }
                            }

                        }
                        deleteDirectory($curDir);
                    }
                }
            }
            $files = glob($target_dir . '/*');
            foreach ($files as $file) {
                if (strpos($file, 'plugin_info') === false) {
                    deleteDirectory($file);
                }
            }
        }
        $files = glob($target_dir . '/*');
        foreach ($files as $file) {
            if (strpos($file, 'plugin_info') === false) {
                deleteDirectory($file);
            }
        }
        $statuses = get_option( 'ultrapm_task_status' );
        if( $statuses == false ){
            $statuses = array();
        }
        foreach( $statuses as $key => $status ){
            if( $status == 'done' || $status == 'failed' ){
                unset( $statuses[ $key ] );
            }
        }
        update_option( 'ultrapm_task_list', array() );
        $response[ 'success' ] = true;
        $response[ 'status' ] = 'success';
        $response[ 'message' ] = 'Task list cleared.';
        echo json_encode( $response );
    } else {
        return false;
    }
    wp_die();
}


function ultrapm_handle_refresh_data_tasklist() {
    $tasklists = get_option( 'ultrapm_task_list' );
    if( $tasklists == false ){
        $tasklists = array();
    }
    
    $taskCount = count( $tasklists );
    if( $taskCount == 0 ){
        // jika task list kosong maka hapus semua file di wp upload dir
        $target_dir = wp_upload_dir()['basedir'] . '/ultra-plugin-manager';
        $files = glob($target_dir . '/*');
        foreach ($files as $file) {
            if (strpos($file, 'plugin_info') === false) {
                deleteDirectory($file);
            }
        }
    }


    //if( $tasklists == false || !is_array( $tasklists ) || count( (array)$tasklists ) == 0 ){
        //$uploads_dir = wp_upload_dir();
        //$target_dir = $uploads_dir['basedir'] . '/ultra-plugin-manager';
        //$files = glob($target_dir . '/*');
        //foreach ($files as $file) {
            //if (strpos($file, 'plugin_info') === false) {
                //deleteDirectory($file);
            //}
        //}
        //$tasklists = array();
    //}
    echo json_encode( $tasklists );
    wp_die();
}

function ultrapm_is_exsist_list_plugin_info() {
    $slug = $_POST[ 'slug' ];
    $listtime = get_option( 'ultrapm_list_plugin_info_time' );
    // if listtime more than 15 minutes, clear list
    if( $listtime == false || ( time() - $listtime ) > 900 ){
        update_option( 'ultrapm_list_plugin_info', array() );
        update_option( 'ultrapm_list_plugin_info_time', time() );
    }
    $list = get_option( 'ultrapm_list_plugin_info' );
    if( $list == false ){
        $list = array();
    }
    $is_exsist = false;
    $data = '';
    foreach( $list as $item ){
        if( $item[ 'slug' ] == $slug ){
            $is_exsist = true;
            $datapath = $item[ 'data' ];
            $data = file_get_contents( $datapath );
            break;
        }
    }
    if( $is_exsist == false ){
        $data = wp_safe_remote_get( 'https://api.wordpress.org/plugins/info/1.0/' . $slug . '.json' );
        $data = wp_remote_retrieve_body( $data );
        // save to wp upload dir
        $upload_dir = wp_upload_dir();
        $upload_dir = $upload_dir[ 'basedir' ];
        $upload_dir = $upload_dir . '/ultra-plugin-manager/plugin_info/';
        if( !file_exists( $upload_dir ) ){
            mkdir( $upload_dir, 0777, true );
        }
        $upload_dir = $upload_dir . $slug . '.json';
        file_put_contents( $upload_dir, $data );
        $crut = array(
            'slug' => $slug,
            'data' => $upload_dir
        );
        array_push( $list, $crut );
        update_option( 'ultrapm_list_plugin_info', $list );
        update_option( 'ultrapm_list_plugin_info_time', time() );
    }
    $response = array(
        'is_exsist' => $is_exsist,
        'data' => $data
    );
    echo json_encode( $response );
    wp_die();
}

function ultrapm_get_theme_info($slug) {
    $data = wp_safe_remote_get( 'https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]=' . $slug );
    $data = wp_remote_retrieve_body( $data );
    update_option( 'ultrapm_theme_info_' . $slug, $data );
    update_option( 'ultrapm_theme_info_time_' . $slug, time() );
    return $data;
}

function ultrapm_get_preview_url($slug) {
    $oldData = get_option( 'ultrapm_theme_info_' . $slug );
    $oldTime = get_option( 'ultrapm_theme_info_time_' . $slug );
    if( $oldData == false || $oldTime == false || ( time() - $oldTime ) > 900 ){
        $data = ultrapm_get_theme_info($slug);
    } else {
        $data = $oldData;
    }
    $data = json_decode( $data, true );
    echo $data['preview_url'];
}

function ultrapm_is_exsist_list_plugin_infoe($slug) {
    $listtime = get_option( 'ultrapm_list_plugin_info_time' );
    // if listtime more than 15 minutes, clear list
    if( $listtime == false || ( time() - $listtime ) > 900 ){
        update_option( 'ultrapm_list_plugin_info', array() );
        update_option( 'ultrapm_list_plugin_info_time', time() );
    }
    $list = get_option( 'ultrapm_list_plugin_info' );
    if( $list == false ){
        $list = array();
    }
    $is_exsist = false;
    $data = '';
    foreach( $list as $item ){
        if( $item[ 'slug' ] == $slug ){
            $is_exsist = true;
            $datapath = $item[ 'data' ];
            $data = file_get_contents( $datapath );
            break;
        }
    }
    if( $is_exsist == false ){
        $data = wp_safe_remote_get( 'https://api.wordpress.org/plugins/info/1.0/' . $slug . '.json' );
        $data = wp_remote_retrieve_body( $data );
        // save to wp upload dir
        $upload_dir = wp_upload_dir();
        $upload_dir = $upload_dir[ 'basedir' ];
        $upload_dir = $upload_dir . '/ultra-plugin-manager/plugin_info/';
        if( !file_exists( $upload_dir ) ){
            mkdir( $upload_dir, 0777, true );
        }
        $upload_dir = $upload_dir . $slug . '.json';
        file_put_contents( $upload_dir, $data );
        $crut = array(
            'slug' => $slug,
            'data' => $upload_dir
        );
        array_push( $list, $crut );
        update_option( 'ultrapm_list_plugin_info', $list );
        update_option( 'ultrapm_list_plugin_info_time', time() );
    }
    $response = array(
        'is_exsist' => $is_exsist,
        'data' => $data
    );
    return json_encode( $response );
    
}

function ultrapm_update_list_plugin_info() {
    $slug = $_POST[ 'slug' ];
    $data = $_POST[ 'data' ];
    $crut = array(
        'slug' => $slug,
        'data' => $data
    );
    $list = get_option( 'ultrapm_list_plugin_info' );
    if( $list == false ){
        $list = array();
    }
    if( !in_array( $crut, $list ) ){
        array_push( $list, $crut );
        udpate_option( 'ultrapm_list_plugin_info', $list );
        update_option( 'ultrapm_list_plugin_info_time', time() );
    }
    echo 1;
    wp_die();
}


function ultrapm_whereis_mainplugin($folder_path) {
    // Periksa apakah folder ada
    if (is_dir($folder_path)) {
        $files = scandir($folder_path);

        // Loop melalui semua file di dalam folder
        foreach ($files as $file) {
            if (is_file($folder_path . '/' . $file)) {
                // Baca isi file
                $file_contents = file_get_contents($folder_path . '/' . $file);

                // Periksa apakah teks "* Plugin Name:" ada di dalam isi file
                if (strpos($file_contents, '* Plugin Name:') !== false) {
                    return $folder_path . '/' . $file;
                }
            }
        }
    } else {
        return false;
    }
}


function ultrapm_whereis_mainpluginpath($folder_path) {
    // Periksa apakah folder ada
    if (is_dir($folder_path)) {
        $files = scandir($folder_path);

        // Loop melalui semua file di dalam folder
        foreach ($files as $file) {
            if (is_file($folder_path . '/' . $file)) {
                // Baca isi file
                $file_contents = file_get_contents($folder_path . '/' . $file);

                // Periksa apakah teks "* Plugin Name:" ada di dalam isi file
                if (strpos($file_contents, 'Plugin Name:') !== false) {
                    $fullpath = $folder_path . '/' . $file;
                    // ambil setelah wp-content/plugins/
                    $fullpath = str_replace( WP_PLUGIN_DIR . '/', '', $fullpath );
                    return $fullpath;
                }
            }
        }
    } else {
        return false;
    }
}

function ultrapm_whereis_plugin_folder($file) {
    $first = explode( '/', $file );
    $first = $first[0];
    $files = scandir( WP_PLUGIN_DIR );
    foreach( $files as $file ){
        if( strtolower( $file ) == strtolower( $first ) ){
            return $file;
        }
    }
    return false;
}


function ultrapm_verify_type_zip($zipFilePath) {
    $zip = new ZipArchive();
    $ctheme = 0;
    $cplugin = 0;
    if ($zip->open($zipFilePath) === true) {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $fileInfo = $zip->statIndex($i);
            $file = $zip->getFromIndex($i);
            // periksa apakah ada "Theme Name" di dalam file
            if (strpos($file, 'Theme Name:') !== false) {
                $ctheme++;
                //return true;
            }
            // periksa apakah ada "Plugin Name" di dalam file
            if (strpos($file, 'Plugin Name:') !== false) {
                $cplugin++;
                //return true;
            }
        }
        $zip->close();
        //return false;
    } else {
        return false;
    }
    if($ctheme > 0){
        return 'theme';
    } else if($cplugin > 0){
        return 'plugin';
    } else {
        return 'bundle';
    }
}


function ultrapm_get_file_from_url() {
    // if url contains drive.google.com or dropbox.com or wordpress.org
    $url = $_POST[ 'url' ];
    $response = array();
    if( strpos( $url, 'drive.google.com' ) !== false || strpos( $url, 'dropbox.com' ) !== false ){
        // get file from url
    } else {
        $response[ 'success' ] = false;
        $response[ 'status' ] = 'error';
        $response[ 'message' ] = 'Invalid URL.';
        echo json_encode( $response );
        wp_die();
    }
}

function ultrapm_gdrive_downloader($id) {
    // get file from google drive
    $gdrive_url = 'https://docs.google.com/uc?export=download&id=' . $id;
    // Inisialisasi Curl
    $ch = curl_init($gdrive_url);

    // Opsi Curl
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Jika Anda memiliki masalah dengan SSL, Anda dapat menonaktifkan verifikasi sementara
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Jika Anda memiliki masalah dengan SSL, Anda dapat menonaktifkan verifikasi sementara

    // Eksekusi Curl
    $response = curl_exec($ch);
    if($response === false) {
        return false;
    } else {
        return $response;
    }
}

function ultrapm_dropbox_downloader($url) {
    // get file from dropbox
    if( strpos( $url, 'dl=0' ) !== false ){
        $url = str_replace( 'dl=0', 'dl=1', $url );
    }
    if( strpos( $url, 'www.dropbox.com' ) !== false ){
        $url = str_replace( 'www.dropbox.com', 'dl.dropboxusercontent.com', $url );
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_SSLVERSION, 3);
    $data = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if ($error) {
        return false;
    } else {
        return $data;
    }
}

function ultrapm_wordpress_downloader($url) {
    $type = '';

    // jika url seperti https://wordpress.org/themes/generatepress
    if(strpos($url, 'https://wordpress.org/themes/') !== false) {
        $slug = str_replace( 'https://wordpress.org/themes/', '', $url );
        $slug = str_replace( '/', '', $slug );
        $type = 'theme';
    }

    // jika url seperti https://wordpress.org/plugins/wc-wise-gateway/
    if(strpos($url, 'https://wordpress.org/plugins/') !== false) {
        $slug = str_replace( 'https://wordpress.org/plugins/', '', $url );
        $slug = str_replace( '/', '', $slug );
        $type = 'plugin';
    }
    
    if($type == 'theme') {
        $getinfo = wp_safe_remote_get( 'https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]=' . $slug );
        $getinfo = wp_remote_retrieve_body( $getinfo );
        // jika getinfo tidak error
        if( !is_wp_error( $getinfo ) ) {
            $getinfo = json_decode( $getinfo, true );
            $zip_url = $getinfo['download_link'];
        } else {
            return false;
        }
    } else if($type == 'plugin') {
        $getinfo = wp_safe_remote_get( 'https://api.wordpress.org/plugins/info/1.0/' . $slug . '.json' );
        $getinfo = wp_remote_retrieve_body( $getinfo );
        // jika getinfo tidak error
        if( !is_wp_error( $getinfo ) ) {
            $getinfo = json_decode( $getinfo, true );
            $zip_url = $getinfo['download_link'];
        } else {
            return false;
        }
    } else {
        return false;
    }

    // download file zip
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $zip_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSLVERSION, 3);
    $data = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if ($error) {
        return false;
    } else {
        return $data;
    }

    
}

function ultrapm_pre_upload_zip(){

    $response = array();
    $response[ 'success' ] = false;
    $response[ 'status' ] = 'error';
    $response[ 'message' ] = 'Invalid request!';

    if( !isset( $_POST[ 'type' ] ) || !isset( $_FILES[ 'file' ] ) && !isset( $_POST[ 'action' ] ) ){
        echo json_encode( $response );
        wp_die();
    }

    if( $_POST[ 'action' ] != 'ultrapm_pre_upload_zip' ){
        echo json_encode( $response );
        wp_die();
    }

    $type = $_POST[ 'type' ]; // bundled or single
    $file = $_FILES[ 'file' ]; // must zip
    $extention = pathinfo( $file[ 'name' ], PATHINFO_EXTENSION );
    $filename = pathinfo( $file[ 'name' ], PATHINFO_FILENAME );
    if( $extention != 'zip' ){
        $response[ 'message' ] = 'Invalid file type!';
        wp_die();
    }
    if ($type != 'bundled' && $type != 'single') {
        $response[ 'message' ] = 'Invalid type!';
        wp_die();
    }

    // upload dir at wp-content/uploads/customPath
    $customPath = get_option( 'ultrapm_upload_path' );
    if( $customPath == false ){
        $customPath = 'ultra-plugin-manager';
    }
    $upload_dir = wp_upload_dir();
    $upload_dir = $upload_dir[ 'basedir' ];
    $upload_dir = $upload_dir . '/' . $customPath . '/';
    if( !file_exists( $upload_dir ) ){
        mkdir( $upload_dir, 0777, true );
    }

    $upload_dir = $upload_dir . $filename . '-' . time() . '.' . $extention;
    $upload_dir = str_replace( ' ', '_', $upload_dir );

    if( move_uploaded_file( $file[ 'tmp_name' ], $upload_dir ) ){
        $response[ 'success' ] = true;
        $response[ 'status' ] = 'success';
        $response[ 'message' ] = 'File uploaded successfully!';
        $response[ 'file' ] = $upload_dir;
        $response[ 'type' ] = $type;
    } else {
        $response[ 'message' ] = 'Failed to upload file!';
    }

    echo json_encode( $response );
    wp_die();
}


function ultrapm_delete_directory($target) {
    if (is_dir($target)) {
        $files = glob($target . '/*');
        foreach ($files as $file) {
            if (!is_dir($file)) {
                unlink($file);
            } else {
                ultrapm_delete_directory($file);
            }
        }
        rmdir($target);
    } elseif (is_file($target)) {
        unlink($target);
    } 
}

function ultrapm_handle_install_task_list() {
    if ( ! class_exists( 'WP_Filesystem' ) ) {
        require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
    }
    $mainplugindir = WP_PLUGIN_DIR;
    $mainthemedir = WP_CONTENT_DIR . '/themes';
    $response = array();
    $response[ 'success' ] = false;
    $response[ 'status' ] = 'error';
    $response[ 'message' ] = 'Invalid request!';
    if( !isset( $_POST[ 'action' ] ) || $_POST[ 'action' ] != 'ultrapm_install_task_list' ){
        echo json_encode( $response );
        //wp_die();
    }
    //if( !isset( $_POST[ 'install' ] ) || !isset( $_POST[ 'activate' ] ) ){
        //echo json_encode( $response );
        //wp_die();
    //}
    if(isset($_POST[ 'delete' ])) {
        $deletes = $_POST[ 'delete' ];
        $response[ 'deletes' ] = $deletes;
        if(is_array($deletes) && count($deletes) > 0) {
            $response[ 'is_array' ] = true;
            foreach($deletes as $delete) {
                $delete = explode( '_', $delete );
                $delName = $delete[ 0 ];
                $delType = $delete[ 1 ];
                ultrapm_handle_clear_taskList($delName);
                $response[ 'delete' ] = $delName;
                $response[ 'message' ] = 'Selected Task list[s] deleted successfully!';
            }
        } else {
            $response[ 'is_array' ] = false;
            $delete = explode( '_', $delete );
            $delName = $delete[ 0 ];
            $delType = $delete[ 1 ];
            ultrapm_handle_clear_taskList($delName);
            $response[ 'delete' ] = $delName;
            $response[ 'message' ] = 'Selected Task list[s] deleted successfully!';
        }
    }
    if(!isset($_POST[ 'install' ])) {
        echo json_encode( $response );
        wp_die();
    }
    $installs = $_POST[ 'install' ];
    //$response ['installs'] = $installs; // debug
    //$response ['is_array'] = is_array($installs); // debug
    //$response ['count'] = count($installs); // debug
    $single = false;
    if(is_array($installs) && count($installs) > 1) {
        $installs = implode(',', $installs);
        $exinstalls = explode( ',', $installs );
    } else {
        $single = true;
        $exinstalls = explode( '_', $installs[0] );
        $response ['exinstalls'] = $exinstalls; // debug
        $response ['single'] = $single; // debug
    }
    $response['exinstalls'] = $exinstalls; // debug
    if($single == false) {
        foreach( $exinstalls as $exinstall ){
            $insName = str_replace('_plugin', '', $exinstall);
            $insName = str_replace('_theme', '', $insName);
            $response[ 'insName' ] .= $insName . ', '; // debug
            $taskList = get_option( 'ultrapm_task_list' );
            if( $taskList == false ){
                $taskList = array();
            }
            $response[ 'success' ] = true;
            $response[ 'status' ] = 'success';
            $response[ 'message' ] = 'Task list[s] installed successfully!';
            $response[ 'install' ] = array();
            $response[ 'activate' ] = array();
            foreach( $taskList as $task ){
                if($task['slug'] == $insName){
                    if($task['type'] == 'plugin'){
                        if(isset($task['pre'])) {
                            $plugindir = $task['dir'];
                            // $crut = WP_Filesystem_Direct::move( $plugindir, $mainplugindir . '/' . $insName, true );
                            if(is_dir($plugindir)) {
                                // make sure main plugin dir is empty
                                //ultrapm_delete_directory($mainplugindir . '/' . $insName);
                                copyDirectory($plugindir, $mainplugindir . '/' . $insName);
                                wp_cache_flush();
                                $crut = true;
                            } else {
                                $crut = false;
                            }
                            if( $crut ){
                                array_push( $response[ 'install' ], $insName );
                                // delete array for this insname if success
                                $taskList = get_option( 'ultrapm_task_list' );
                                if( $taskList == false ){
                                    $taskList = array();
                                }
                                $newTaskList = array();
                                foreach( $taskList as $task ){
                                    if($task['slug'] != $insName){
                                        array_push( $newTaskList, $task );
                                    }
                                }
                                update_option( 'ultrapm_task_list', $newTaskList );
                            } else {
                                $response[ 'message' ] = 'Failed to install plugin ' . $insName . '.';
                            }
                        } else {
                            $zip_url = $task['zip_url'];
                            // download file zip plugin from url and save to mainplugindir
                            $zarchiveplugin = new ZipArchive();
                            if ($zarchiveplugin === false) {
                                $response[ 'message' ] = 'Failed to initialize ZipArchive object.';
                            }
                            // download file zip plugin
                            $zip_url = $zip_url;
                            $zip_file = $insName . '.zip';
                            $zip_file_path = $mainplugindir . '/' . $zip_file;
                            $zip_resource = fopen($zip_file_path, "w");
                            // mengirim permintaan ke server
                            $ch_start = curl_init();
                            curl_setopt($ch_start, CURLOPT_URL, $zip_url);
                            curl_setopt($ch_start, CURLOPT_FAILONERROR, true);
                            curl_setopt($ch_start, CURLOPT_HEADER, 0);
                            curl_setopt($ch_start, CURLOPT_FOLLOWLOCATION, true);
                            curl_setopt($ch_start, CURLOPT_AUTOREFERER, true);
                            curl_setopt($ch_start, CURLOPT_BINARYTRANSFER,true);
                            curl_setopt($ch_start, CURLOPT_TIMEOUT, 10);
                            curl_setopt($ch_start, CURLOPT_SSL_VERIFYHOST, 0);
                            curl_setopt($ch_start, CURLOPT_SSL_VERIFYPEER, 0);
                            curl_setopt($ch_start, CURLOPT_FILE, $zip_resource);
                            $page = curl_exec($ch_start);
                            if (!$page) {
                                $response[ 'message' ] = 'Failed to download ZIP file.';
                            }
                            // ekstrak file zip plugin
                            $zarchiveplugin->open( $zip_file_path );
                            $extractRes = $zarchiveplugin->extractTo( $mainplugindir );
                            $zarchiveplugin->close();
                            if( $extractRes ){
                                array_push( $response[ 'install' ], $insName );
                                // delete array for this insname if success
                                $taskList = get_option( 'ultrapm_task_list' );
                                if( $taskList == false ){
                                    $taskList = array();
                                }
                                $newTaskList = array();
                                foreach( $taskList as $task ){
                                    if($task['slug'] != $insName){
                                        array_push( $newTaskList, $task );
                                    }
                                }
                                update_option( 'ultrapm_task_list', $newTaskList );
                            } else {
                                $response[ 'message' ] = 'Failed to install plugin ' . $insName . '.';
                            }
                        }
                    } else if($task['type'] == 'theme'){
                        if(isset($task['pre'])) {
                            $themedir = $task['dir'];
                            // $crut = WP_Filesystem_Direct::move( $themedir, $mainthemedir . '/' . $insName, true );
                            if(is_dir($themedir)) {
                                // copy theme dir to main theme dir
                                copyDirectory($themedir, $mainthemedir . '/' . $insName);
                                // delete array for this insname if success
                                $taskList = get_option( 'ultrapm_task_list' );
                                if( $taskList == false ){
                                    $taskList = array();
                                }
                                $newTaskList = array();
                                foreach( $taskList as $task ){
                                    if($task['slug'] != $insName){
                                        array_push( $newTaskList, $task );
                                    }
                                }
                                update_option( 'ultrapm_task_list', $newTaskList );
                            } else {
                                $response[ 'message' ] = 'Failed to install theme ' . $insName . '.';
                            }
                        } else {
                            // var themeZipUrl = 'https://downloads.wordpress.org/theme/' + themeSlug + '.' + info.version + '.zip';
                            $getinfo = wp_safe_remote_get( 'https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]=' . $insName );
                            $getinfo = wp_remote_retrieve_body( $getinfo );
                            // jika getinfo tidak error
                            if( !is_wp_error( $getinfo ) ) {
                                $getinfo = json_decode( $getinfo, true );
                                $zip_url = $getinfo['download_link'];
                            } else {
                                $response[ 'message' ] = 'Failed to install theme ' . $insName . '.';
                                continue;
                            }
                            // download file zip plugin from url and save to mainplugindir
                            $zarchivetheme = new ZipArchive();
                            if ($zarchivetheme === false) {
                                $response[ 'message' ] = 'Failed to initialize ZipArchive object.';
                                continue;
                            }
                            // download file zip plugin
                            $zip_url = $zip_url;
                            $zip_file = $insName . '.zip';
                            $zip_file_path = $mainthemedir . '/' . $zip_file;
                            $zip_resource = fopen($zip_file_path, "w");
                            // mengirim permintaan ke server
                            $ch_start = curl_init();
                            curl_setopt($ch_start, CURLOPT_URL, $zip_url);
                            curl_setopt($ch_start, CURLOPT_FAILONERROR, true);
                            curl_setopt($ch_start, CURLOPT_HEADER, 0);
                            curl_setopt($ch_start, CURLOPT_FOLLOWLOCATION, true);
                            curl_setopt($ch_start, CURLOPT_AUTOREFERER, true);
                            curl_setopt($ch_start, CURLOPT_BINARYTRANSFER,true);
                            curl_setopt($ch_start, CURLOPT_TIMEOUT, 10);
                            curl_setopt($ch_start, CURLOPT_SSL_VERIFYHOST, 0);
                            curl_setopt($ch_start, CURLOPT_SSL_VERIFYPEER, 0);
                            curl_setopt($ch_start, CURLOPT_FILE, $zip_resource);
                            $page = curl_exec($ch_start);
                            if (!$page) {
                                $response[ 'message' ] = 'Failed to download ZIP file.';
                                continue;
                            }
                            // ekstrak file zip plugin
                            $zarchivetheme->open( $zip_file_path );
                            $extractRes = $zarchivetheme->extractTo( $mainthemedir );
                            $zarchivetheme->close();
                            if( $extractRes ){
                                array_push( $response[ 'install' ], $insName );
                                // delete array for this insname if success
                                $taskList = get_option( 'ultrapm_task_list' );
                                if( $taskList == false ){
                                    $taskList = array();
                                }
                                $newTaskList = array();
                                foreach( $taskList as $task ){
                                    if($task['slug'] != $insName){
                                        array_push( $newTaskList, $task );
                                    }
                                }
                                update_option( 'ultrapm_task_list', $newTaskList );
                            } else {
                                $response[ 'message' ] = 'Failed to install theme ' . $insName . '.';
                                continue;
                            }
                        }
                    }
                }
            }
        }
    } else {
        $taskList = get_option( 'ultrapm_task_list' );
        if( $taskList == false ){
            $taskList = array();
        }
        $insNames = $exinstalls;
        // insName is before last
        $insName = '';
        for($i = 0; $i < count($insNames) - 1; $i++) {
            $insName .= $insNames[$i];
            if($i < count($insNames) - 2) {
                $insName .= '_';
            }
        }
        $response[ 'success' ] = true;
        $response[ 'status' ] = 'success';
        $response[ 'message' ] = 'Task list installed successfully!';
        $response[ 'install' ] = array();
        $response[ 'activate' ] = array();
        foreach( $taskList as $task ){
            $response[ 'slug' ] = $task['slug']; // debug
            $response[ 'type' ] = $task['type']; // debug
            $response[ 'insname' ] = $insName; // debug
            if($task['slug'] == $insName){
                if($task['type'] == 'plugin'){
                    if(isset($task['pre'])) {
                        $plugindir = $task['dir'];
                        // $crut = WP_Filesystem_Direct::move( $plugindir, $mainplugindir . '/' . $insName, true );
                        if(is_dir($plugindir)) {
                            // make sure main plugin dir is empty
                            //ultrapm_delete_directory($mainplugindir . '/' . $insName);
                            copyDirectory($plugindir, $mainplugindir . '/' . $insName);
                            wp_cache_flush();
                            $crut = true;
                        } else {
                            $crut = false;
                        }
                        if( $crut ){
                            array_push( $response[ 'install' ], $insName );
                            // delete array for this insname if success
                            $taskList = get_option( 'ultrapm_task_list' );
                            if( $taskList == false ){
                                $taskList = array();
                            }
                            $newTaskList = array();
                            foreach( $taskList as $task ){
                                if($task['slug'] != $insName){
                                    array_push( $newTaskList, $task );
                                }
                            }
                            update_option( 'ultrapm_task_list', $newTaskList );
                        } else {
                            $response[ 'message' ] = 'Failed to install plugin ' . $insName . '.';
                        }
                    } else {
                        $zip_url = $task['zip_url'];
                        // download file zip plugin from url and save to mainplugindir
                        $zarchiveplugin = new ZipArchive();
                        if ($zarchiveplugin === false) {
                            $response[ 'message' ] = 'Failed to initialize ZipArchive object.';
                        }
                        // download file zip plugin
                        $zip_url = $zip_url;
                        $zip_file = $insName . '.zip';
                        $zip_file_path = $mainplugindir . '/' . $zip_file;
                        $zip_resource = fopen($zip_file_path, "w");
                        // mengirim permintaan ke server
                        $ch_start = curl_init();
                        curl_setopt($ch_start, CURLOPT_URL, $zip_url);
                        curl_setopt($ch_start, CURLOPT_FAILONERROR, true);
                        curl_setopt($ch_start, CURLOPT_HEADER, 0);
                        curl_setopt($ch_start, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($ch_start, CURLOPT_AUTOREFERER, true);
                        curl_setopt($ch_start, CURLOPT_BINARYTRANSFER,true);
                        curl_setopt($ch_start, CURLOPT_TIMEOUT, 10);
                        curl_setopt($ch_start, CURLOPT_SSL_VERIFYHOST, 0);
                        curl_setopt($ch_start, CURLOPT_SSL_VERIFYPEER, 0);
                        curl_setopt($ch_start, CURLOPT_FILE, $zip_resource);
                        $page = curl_exec($ch_start);
                        if (!$page) {
                            $response[ 'message' ] = 'Failed to download ZIP file.';
                        }
                        // ekstrak file zip plugin
                        $zarchiveplugin->open( $zip_file_path );
                        $extractRes = $zarchiveplugin->extractTo( $mainplugindir );
                        $zarchiveplugin->close();
                        if( $extractRes ){
                            array_push( $response[ 'install' ], $insName );
                            // delete array for this insname if success
                            $taskList = get_option( 'ultrapm_task_list' );
                            if( $taskList == false ){
                                $taskList = array();
                            }
                            $newTaskList = array();
                            foreach( $taskList as $task ){
                                if($task['slug'] != $insName){
                                    array_push( $newTaskList, $task );
                                }
                            }
                            update_option( 'ultrapm_task_list', $newTaskList );
                        } else {
                            $response[ 'message' ] = 'Failed to install plugin ' . $insName . '.';
                        }
                        // delete zip file
                        unlink( $zip_file_path );
                    }
                } else if($task['type'] == 'theme'){
                    if(isset($task['pre'])) {
                        $themedir = $task['dir'];
                        $crut = WP_Filesystem_Direct::move( $themedir, $mainthemedir . '/' . $insName, true );
                        if( $crut ){
                            array_push( $response[ 'install' ], $insName );
                            // delete array for this insname if success
                            $taskList = get_option( 'ultrapm_task_list' );
                            if( $taskList == false ){
                                $taskList = array();
                            }
                            $newTaskList = array();
                            foreach( $taskList as $task ){
                                if($task['slug'] != $insName){
                                    array_push( $newTaskList, $task );
                                }
                            }
                            update_option( 'ultrapm_task_list', $newTaskList );
                        } else {
                            $response[ 'message' ] = 'Failed to install theme ' . $insName . '.';
                        }
                    } else {
                        // var themeZipUrl = 'https://downloads.wordpress.org/theme/' + themeSlug + '.' + info.version + '.zip';
                        $getinfo = wp_safe_remote_get( 'https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]=' . $insName );
                        $getinfo = wp_remote_retrieve_body( $getinfo );
                        // jika getinfo tidak error
                        if( !is_wp_error( $getinfo ) ) {
                            $getinfo = json_decode( $getinfo, true );
                            $zip_url = $getinfo['download_link'];
                        } else {
                            $response[ 'message' ] = 'Failed to install theme ' . $insName . '.';
                            continue;
                        }
                        // download file zip plugin from url and save to mainplugindir
                        $zarchivetheme = new ZipArchive();
                        if ($zarchivetheme === false) {
                            $response[ 'message' ] = 'Failed to initialize ZipArchive object.';
                            continue;
                        }
                        // download file zip plugin
                        $zip_url = $zip_url;
                        $zip_file = $insName . '.zip';
                        $zip_file_path = $mainthemedir . '/' . $zip_file;
                        $zip_resource = fopen($zip_file_path, "w");
                        // mengirim permintaan ke server
                        $ch_start = curl_init();
                        curl_setopt($ch_start, CURLOPT_URL, $zip_url);
                        curl_setopt($ch_start, CURLOPT_FAILONERROR, true);
                        curl_setopt($ch_start, CURLOPT_HEADER, 0);
                        curl_setopt($ch_start, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($ch_start, CURLOPT_AUTOREFERER, true);
                        curl_setopt($ch_start, CURLOPT_BINARYTRANSFER,true);
                        curl_setopt($ch_start, CURLOPT_TIMEOUT, 10);
                        curl_setopt($ch_start, CURLOPT_SSL_VERIFYHOST, 0);
                        curl_setopt($ch_start, CURLOPT_SSL_VERIFYPEER, 0);
                        curl_setopt($ch_start, CURLOPT_FILE, $zip_resource);
                        $page = curl_exec($ch_start);
                        if (!$page) {
                            $response[ 'message' ] = 'Failed to download ZIP file.';
                            continue;
                        }
                        // ekstrak file zip plugin
                        $zarchivetheme->open( $zip_file_path );
                        $extractRes = $zarchivetheme->extractTo( $mainthemedir );
                        $zarchivetheme->close();
                        if( $extractRes ){
                            // delete array for this insname if success
                            $taskList = get_option( 'ultrapm_task_list' );
                            if( $taskList == false ){
                                $taskList = array();
                            }
                            $newTaskList = array();
                            foreach( $taskList as $task ){
                                if($task['slug'] != $insName){
                                    array_push( $newTaskList, $task );
                                }
                            }
                            update_option( 'ultrapm_task_list', $newTaskList );
                        } else {
                            $response[ 'message' ] = 'Failed to install theme ' . $insName . '.';
                            continue;
                        }
                    }
                }
            }
        }
    }
    $activate = $_POST[ 'activate' ];
    if(is_array($activate) && count($activate) > 0) {
        $response[ 'is_array' ] = true;
        foreach($activate as $act) {
            $act = explode( '_', $act );
            $actName = $act[ 0 ];
            $actType = $act[ 1 ];
            if($actType != 'plugin' && $actType != 'theme') {
                $actName = '';
                $actType = '';
                for($i = 0; $i < count($act) - 1; $i++) {
                    $actName .= $act[$i];
                    if($i < count($act) - 2) {
                        $actName .= '_';
                    }
                }
                $actType = $act[count($act) - 1];
            }
            $response[ 'act' ] = $actName;
            $response[ 'actType' ] = $actType;
            if($actType == 'plugin') {
                $pathe = ultrapm_whereis_mainpluginpath( WP_PLUGIN_DIR . '/' . strtolower($actName) );
                if($pathe == '') {
                    $pathe = ultrapm_whereis_mainpluginpath( WP_PLUGIN_DIR . '/' . $actName );
                }
                $response[ 'pathe_' . $actName ] = $pathe; // debug
                $versione = ultrapm_get_plugin_version( strtolower($actName) );
                ultrapm_autofix_mainplugin( strtolower($actName), $versione );
                if($pathe) {
                    try {
                        $result = activate_plugin( $pathe, '', false, true );
                        if( is_wp_error( $result ) ){
                            $response[ 'message' ] = 'Failed to activate plugin ' . $actName . '.';
                            $errors = $result->get_error_messages();
                            foreach ($errors as $error) {
                                $response[ $pathe . '_ERROR' ] = $error;
                            }
                        } else {
                            array_push( $response[ 'activate' ], $actName );
                        }
                    } catch (Exception $e) {
                        $response[ 'message' ] = 'Failed to activate plugin ' . $actName . '.';
                    }
                }
            } else if($actType == 'theme') {
                $result = switch_theme(strtolower($actName));
                if( is_wp_error( $result ) ){
                    $response[ 'message' ] = 'Failed to activate theme ' . $actName . '.';
                    //$errors = $activateResult->get_error_messages();
                    //foreach ($errors as $error) {
                        //$response[ 'message' ] = $error;
                    //}
                } else {
                    array_push( $response[ 'activate' ], $actName );
                }
            }
        }
    }

    // delete zip file from install plugin and theme
    foreach($exinstalls as $exinstall) {
        $exinstall = explode( '_', $exinstall );
        $insName = $exinstall[ 0 ];
        $insType = $exinstall[ 1 ];
        if($insType == 'plugin') {
            $zip_file = $insName . '.zip';
            $zip_file_path = $mainplugindir . '/' . $zip_file;
            if(file_exists($zip_file_path)) {
                unlink( $zip_file_path );
            }
        } else if($insType == 'theme') {
            $zip_file = $insName . '.zip';
            $zip_file_path = $mainthemedir . '/' . $zip_file;
            if(file_exists($zip_file_path)) {
                unlink( $zip_file_path );
            }
        }
    }

    echo json_encode( $response );
    wp_die();
}

function ultrapm_get_plugin_name($slug) {
    $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $slug . '/' . $slug . '.php' );
    return $plugin_data['Name'];
}

function ultrapm_get_plugin_version($slug) {
    $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $slug . '/' . $slug . '.php' );
    return $plugin_data['Version'];
}

function copyDirectory( $source, $destination ) {

    if ( !is_dir( $destination ) ) {
       mkdir( $destination, 0755, true );
    }

    $files = scandir( $source );

    foreach ( $files as $file ) {
       if ( $file !== '.' && $file !== '..' ) {
          $sourceFile = $source . '/' . $file;
          $destinationFile = $destination . '/' . $file;
          if ( is_dir( $sourceFile ) ) {
            copyDirectory( $sourceFile, $destinationFile );
          } else {
            //error_log( $sourceFile . '||' . $destinationFile, 1, 'derek_olalehe@hotmail.com' );
            copy( $sourceFile, $destinationFile );
          }
       }
    }

 }


function ultrapm_autofix_mainplugin($nslug, $nversion) {
    $apiurl = get_option( 'ultrapm_api_url' );
    if( $apiurl == false ){
        $apiurl = 'https://ultra-plugin-manager.io';
    }
    $path = '/wp-json/ultra-plugin-manager-api/v1/getconfig/autofix';
    $url = $apiurl . $path;
    $method = 'GET';
    $response = wp_safe_remote_get( $url );
    if( is_wp_error( $response ) ){
        return false;
    }
    $response = wp_remote_retrieve_body( $response );
    //echo "Response : $response\n";
    //echo "====================\n";
    $fixlist = base64_decode($response);
    $fixlist = json_decode($fixlist, true);
    foreach ($fixlist as $itemname => $fixdata) {
        if($nslug == $fixdata['slug'] && $nversion == $fixdata['version']){
            //echo "Found $itemname\n";
            //echo "Slug : " . $fixdata['slug'] . "\n";
            //echo "Version : " . $fixdata['version'] . "\n";
            //echo "Bug File : " . $fixdata['bugfile'] . "\n";
            //echo "Bug Code : " . $fixdata['bugcode'] . "\n";
            //echo "Fix Code : " . $fixdata['fixcode'] . "\n";
            //echo "====================\n";
            $bugfile = $fixdata['bugfile'];
            $bugcode = $fixdata['bugcode'];
            $fixcode = $fixdata['fixcode'];
            $bugfile = WP_PLUGIN_DIR . '/' . $nslug . '/' . $bugfile;
            if (file_exists($bugfile)) {
                $file_contents = file_get_contents($bugfile);
                $fixed_contents = str_replace($bugcode, $fixcode, $file_contents);
                if (file_put_contents($bugfile, $fixed_contents) !== false) {
                    //echo "Success to replace 'bugcode' with 'fixcode' in file: $bugfile\n";
                } else {
                    //echo "Failed to replace 'bugcode' with 'fixcode' in file: $bugfile\n";
                }
            }
        }
    }
    return true;
}

function ultrapm_handle_install_theme_bySlug() {
    $response = array();
    $response[ 'success' ] = false;
    $response[ 'status' ] = 'error';
    $response[ 'message' ] = 'Invalid request!';
    if(!isset($_POST['slug'])) {
        echo 'Invalid request!';
        wp_die();
    }
    $slug = $_POST['slug'];
    $mainthemedir = WP_CONTENT_DIR . '/themes';
    // get theme info from wordpress.org
    $getinfo = wp_safe_remote_get( 'https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]=' . $slug );
    $getinfo = wp_remote_retrieve_body( $getinfo );
    // jika getinfo tidak error
    if( !is_wp_error( $getinfo ) ) {
        $getinfo = json_decode( $getinfo, true );
        $zip_url = $getinfo['download_link'];
        // download file zip plugin from url and save to mainplugindir
        $zarchivetheme = new ZipArchive();
        if ($zarchivetheme === false) {
            $response[ 'message' ] = 'Failed to initialize ZipArchive object.';
            echo json_encode( $response );
            wp_die();
        }
        // download file zip plugin
        $zip_url = $zip_url;
        $zip_file = $slug . '.zip';
        $zip_file_path = $mainthemedir . '/' . $zip_file;
        $zip_resource = fopen($zip_file_path, "w");
        // mengirim permintaan ke server
        $ch_start = curl_init();
        curl_setopt($ch_start, CURLOPT_URL, $zip_url);
        curl_setopt($ch_start, CURLOPT_FAILONERROR, true);
        curl_setopt($ch_start, CURLOPT_HEADER, 0);
        curl_setopt($ch_start, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch_start, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch_start, CURLOPT_BINARYTRANSFER,true);
        curl_setopt($ch_start, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch_start, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch_start, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch_start, CURLOPT_FILE, $zip_resource);
        $page = curl_exec($ch_start);
        if (!$page) {
            $response[ 'message' ] = 'Failed to download ZIP file.';
            echo json_encode( $response );
            wp_die();
        }
        // ekstrak file zip plugin
        $zarchivetheme->open( $zip_file_path );
        $extractRes = $zarchivetheme->extractTo( $mainthemedir );
        $zarchivetheme->close();
        if( $extractRes ){
            $response['success'] = true;
            $response['status'] = 'success';
            unlink( $zip_file_path );
            //if(isset($_POST[ 'activate' ])) {
                $result = switch_theme(strtolower($slug));
                if( is_wp_error( $result ) ){
                    $response[ 'message' ] = 'Failed to activate theme ' . $slug . '.';
                    //$errors = $activateResult->get_error_messages();
                    //foreach ($errors as $error) {
                        //$response[ 'message' ] = $error;
                    //}
                } else {
                    $response['message'] = 'Theme ' . $slug . ' installed and activated successfully!';
                }
            //}
            // delete zip file
        } else {
            $response[ 'message' ] = 'Failed to install theme ' . $slug . '.';
        }
    } else {
        $response[ 'message' ] = 'Failed to install theme ' . $slug . '.';
    }
    echo json_encode( $response );
    wp_die();
}


function ultrapm_handle_install_plugin_bySlug() {
    $response = array();
    $response[ 'success' ] = false;
    $response[ 'status' ] = 'error';
    $response[ 'message' ] = 'Invalid request!';
    if(!isset($_POST['slug'])) {
        $response[ 'message' ] = 'Invalid request!';
        echo json_encode( $response );
        wp_die();
    }
    $slug = $_POST['slug'];
    $mainplugindir = WP_PLUGIN_DIR;
    // get plugin info from wordpress.org
    $getinfo = wp_safe_remote_get( 'https://api.wordpress.org/plugins/info/1.1/?action=plugin_information&request[slug]=' . $slug );
    $getinfo = wp_remote_retrieve_body( $getinfo );
    // jika getinfo tidak error
    if( !is_wp_error( $getinfo ) ) {
        $getinfo = json_decode( $getinfo, true );
        $zip_url = $getinfo['download_link'];
        // download file zip plugin from url and save to mainplugindir
        $zarchiveplugin = new ZipArchive();
        if ($zarchiveplugin === false) {
            $response[ 'message' ] = 'Failed to initialize ZipArchive object.';
            echo json_encode( $response );
            wp_die();
        }
        // download file zip plugin
        $zip_url = $zip_url;
        $zip_file = $slug . '.zip';
        $zip_file_path = $mainplugindir . '/' . $zip_file;
        $zip_resource = fopen($zip_file_path, "w");
        // mengirim permintaan ke server
        $ch_start = curl_init();
        curl_setopt($ch_start, CURLOPT_URL, $zip_url);
        curl_setopt($ch_start, CURLOPT_FAILONERROR, true);
        curl_setopt($ch_start, CURLOPT_HEADER, 0);
        curl_setopt($ch_start, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch_start, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch_start, CURLOPT_BINARYTRANSFER,true);
        curl_setopt($ch_start, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch_start, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch_start, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch_start, CURLOPT_FILE, $zip_resource);
        $page = curl_exec($ch_start);
        if (!$page) {
            $response[ 'message' ] = 'Failed to download ZIP file.';
            echo json_encode( $response );
            wp_die();
        }
        // ekstrak file zip plugin
        $zarchiveplugin->open( $zip_file_path );
        $extractRes = $zarchiveplugin->extractTo( $mainplugindir );
        $zarchiveplugin->close();
        if( $extractRes ){
            unlink( $zip_file_path );
            $pathe = ultrapm_whereis_mainpluginpath( WP_PLUGIN_DIR . '/' . strtolower($slug) );
            if($pathe == '') {
                $pathe = ultrapm_whereis_mainpluginpath( WP_PLUGIN_DIR . '/' . $slug );
            }
            $versione = ultrapm_get_plugin_version( strtolower($slug) );
            ultrapm_autofix_mainplugin( strtolower($slug), $versione );
            $response['success'] = true;
            $response['status'] = 'success';
            $response['message'] = 'Plugin ' . $slug . ' installed successfully!';
            if(isset($_POST[ 'activate' ]) && $_POST[ 'activate' ] == 'true') {
                $result = activate_plugin( $pathe, '', false, true );
                if( is_wp_error( $result ) ){
                    $response[ 'message' ] = 'Failed to activate plugin ' . $slug . '.';
                    //$errors = $activateResult->get_error_messages();
                    //foreach ($errors as $error) {
                        //$response[ 'message' ] = $error;
                    //}
                } else {
                    $response['message'] = 'Plugin ' . $slug . ' installed and activated successfully!';
                }
            }
        } else {
            $response[ 'message' ] = 'Failed to install plugin ' . $slug . '.';
        }
    } else {
        $response[ 'message' ] = 'Failed to install plugin ' . $slug . '.';
    }
    echo json_encode( $response );
    wp_die();
}

function ultrapm_handle_start_task_progress() {
    $proses = get_option( 'ultrapm_task_status' );
    if(!$proses) {
        $proses = array();
    }
    $url = $_POST['url'];
    $ceked = false;
    foreach ($proses as $key => $value) {
        if($value['url'] == $url){
            // change status to 'in progress'
            $proses[$key]['status'] = 'in progress';
            $proses[$key]['message'] = 'Task in progress.';
            $ceked = true;
        }
    }
    if(!$ceked){
        $inpone = array(
            'url' => $url,
            'status' => 'in progress',
            'message' => 'Task in progress.'
        );
        array_push($proses, $inpone);
    }
    update_option('ultrapm_task_status', $proses);
    $response = array();
    $response[ 'success' ] = true;
    $response[ 'status' ] = 'success';
    $response[ 'message' ] = 'Task progress started.';
    echo json_encode( $response );
    wp_die();
}

function ultrapm_handle_check_task_progress() {
    $proses = get_option( 'ultrapm_task_status' );
    if(!$proses) {
        $proses = array();
    }
    $response = array();
    if( count( $proses ) == 0 ){
        $response[ 'success' ] = false;
        $response[ 'status' ] = 'done';
        $response[ 'message' ] = 'No task in progress.';
        echo json_encode( $response );
        wp_die();
    }
    
    $url = $_POST['url'];
    foreach ($proses as $key => $value) {
        if($value['url'] == $url){
            $status = $value['status'];
            $message = $value['message'];
            $response[ 'success' ] = true;
            $response[ 'status' ] = $status;
            $response[ 'message' ] = $message;
            echo json_encode( $response );
            wp_die();
        }
    }

    $response[ 'success' ] = false;
    $response[ 'status' ] = 'done';
    $response[ 'message' ] = 'No task in progress.';
    echo json_encode( $response );
    wp_die();
}

function ultrapm_get_all_tasklist(){
    sleep(2);
    $taskList = get_option( 'ultrapm_task_list' );
    if( $taskList == false ){
        $taskList = array();
    }
    $response = array();
    $response[ 'success' ] = true;
    $response[ 'status' ] = 'success';
    $response[ 'message' ] = 'Task list retrieved successfully!';
    $response[ 'tasklist' ] = $taskList;
    echo json_encode( $taskList );
    wp_die();
}

function ultrapm_update_progress($url, $status, $message) {
    $inpone = array(
        'url' => $url,
        'status' => $status,
        'message' => $message
    );
    $prosese = get_option('ultrapm_task_status');
    if(!$prosese){
        $prosese = array();
    }
    foreach($prosese as $key => $proses){
        if($proses['url'] == $url){
            $prosese[$key]['status'] = $status;
            $prosese[$key]['message'] = $message;
        }
    }
    update_option('ultrapm_task_status', $prosese);
}

function ultrapm_delete_progress($url) {
    $prosese = get_option('ultrapm_task_status');
    if(!$prosese){
        $prosese = array();
    }
    foreach($prosese as $key => $proses){
        if($proses['url'] == $url){
            unset($prosese[$key]);
        }
    }
    update_option('ultrapm_task_status', $prosese);
}

function ultrapm_deactivate_plugin() {
    if(!isset($_POST['slug'])) {
        wp_die();
    }
    $response = array();
    $slug = $_POST['slug'];
    $mainfile = ultrapm_whereis_mainpluginpath( WP_PLUGIN_DIR . '/' . $slug );
    deactivate_plugins( $mainfile, true );
    $response[ 'success' ] = true;
    $response[ 'status' ] = 'success';
    $response[ 'message' ] = 'Plugin ' . $slug . ' deactivated successfully!';
    echo json_encode( $response );
    wp_die();    
}

function ultrapm_uninstall_theme() {
    if(!isset($_POST['slug'])) {
        wp_die();
    }
    $slug = $_POST['slug'];
    $response = array();
    $response[ 'success' ] = false;
    $response[ 'status' ] = 'error';
    $response[ 'message' ] = 'Invalid request!';
}

function ultrapm_get_infoSlug($slug, $type) {
    if($type == 'plugin') {
        $url = 'https://api.wordpress.org/plugins/info/1.1/?action=plugin_information&request[slug]=' . $slug;
    } else if($type == 'theme') {
        $url = 'https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]=' . $slug;
    }
    $getinfo = wp_safe_remote_get( $url );
    $getinfo = wp_remote_retrieve_body( $getinfo );
    // jika getinfo tidak error
    if( !is_wp_error( $getinfo ) ) {
        $getinfo = json_decode( $getinfo, true );
        update_option( 'ultrapm_info_plugin_' . $slug, $getinfo );
        return $getinfo;
    } else {
        return false;
    }
}

function get_favicon_url( $url ) {
    $url = preg_replace( '/^https?:\/\//', '', $url );
    $url = preg_replace( '/^www\./', '', $url );
    $url = 'https://www.google.com/s2/favicons?domain=' . $url;
    return $url;
}

function getpropertyImage($url) {
    $tags = get_meta_tags($url);
    // dapatkan semua tags yang berisikan image, contoh : og:image, twitter:image, dll
    $imageTags = array();
    foreach ($tags as $tag => $value) {
        if (strpos($tag, 'image') !== false) {
            $imageTags[] = $tag;
        }
    }
    // jika tidak ada tags yang berisikan image, maka return false
    if (count($imageTags) == 0) {
        return false;
    }
    // jika ada tags yang berisikan image, maka return image pertama
    $imageTag = $imageTags[0];
    return $tags[$imageTag];
}

function ultrapm_findItemIdFA($array, $item_id) {
    foreach ($array as $key => $value) {
        if ($key === 'id' && $value == $item_id) {
            return $array;
        } elseif (is_array($value)) {
            $result = ultrapm_findItemIdFA($value, $item_id);
            if ($result !== null) {
                return $result;
            }
        }
    }
    return null;
}

function ultrapm_handle_insact_musthave() {
    $response = array();
    $response[ 'success' ] = false;
    $response[ 'status' ] = 'error';
    $response[ 'message' ] = 'Invalid request!';
    if(!isset($_POST['itemid'])) {
        echo 'Invalid request!';
        wp_die();
    }
    $itemide = $_POST['itemid'];
    // jika awalan itemid bukan p atau t, maka return false
    if( substr( $itemide, 0, 1 ) != 'p' && substr( $itemide, 0, 1 ) != 't' ){
        echo 'Invalid request!';
        wp_die();
    }
    $pluginDir = WP_PLUGIN_DIR;
    $configDir = $pluginDir . '/ultra-plugin-manager/config';
    $itemid = substr( $itemide, 1 );
    
    $dataplugine = file_get_contents( $configDir . '/itd-plugin.txt' );
    $datathemee = file_get_contents( $configDir . '/itd-theme.txt' );
    
    $fixDItdPlugin = substr($dataplugine, 1, -1);
    $fixDItdTheme = substr($datathemee, 1, -1);
    
    $fix2DItdPlugin = str_replace('\"', '"', $fixDItdPlugin);
    $fix2DItdTheme = str_replace('\"', '"', $fixDItdTheme);

    $dataplugin = json_decode($fix2DItdPlugin, true);
    $datatheme = json_decode($fix2DItdTheme, true);

    if( substr( $itemide, 0, 1 ) == 'p' ){
        $data = $dataplugin;
        $type = 'plugin';
    } else if( substr( $itemide, 0, 1 ) == 't' ){
        $data = $datatheme;
        $type = 'theme';
    }

    if($type == 'plugin') {
        // cari data plugin yang sesuai dengan itemid
        $datae = ultrapm_findItemIdFA($data, $itemid);
        foreach ($datae as $key => $value) {
            if($key == 'slug'){
                $slug = $value;
            } else if($key == 'zip_url'){
                $zip_url = $value;
            } else if($key == 'item_type'){
                $item_type = $value;
            } else if($key == 'is_quick_starter'){
                $is_quick_starter = $value;
            } else if($key == 'itemname'){
                $itemname = $value;
            }
        }
        // jika tidak ditemukan data plugin yang sesuai dengan itemid, maka return false
        if(!isset($slug)) {
            echo 'Invalid request!';
            wp_die();
        }
        $data = get_option( 'ultrapm_installed_plugins_from_api' );
        if( $data == false ){
            $data = array();
        }
        array_push( $data, $slug );
        update_option( 'ultrapm_installed_plugins_from_api', $data );
        // jika ditemukan data plugin yang sesuai dengan itemid, maka lanjutkan
        // jika zip_url kosong, maka return false
        $mainplugindir = WP_PLUGIN_DIR;
        if($zip_url == null) {
            $getinfo = wp_safe_remote_get( 'https://api.wordpress.org/plugins/info/1.1/?action=plugin_information&request[slug]=' . $slug );
            $getinfo = wp_remote_retrieve_body( $getinfo );
            // jika getinfo tidak error
            if( !is_wp_error( $getinfo ) ) {
                $getinfo = json_decode( $getinfo, true );
                $zip_url = $getinfo['download_link'];
            } else {
                $response[ 'message' ] = 'Failed to install plugin ' . $slug . '.';
                echo json_encode($response);
                wp_die();
            }
        }
        // download file zip plugin from url and save to mainplugindir
        $zarchiveplugin = new ZipArchive();
        if ($zarchiveplugin === false) {
            $response[ 'message' ] = 'Failed to initialize ZipArchive object.';
            echo json_encode( $response );
            wp_die();
        }
        // download file zip plugin
        $zip_file = $slug . '.zip';
        $zip_file_path = $mainplugindir . '/' . $zip_file;
        $zip_resource = fopen($zip_file_path, "w");
        // mengirim permintaan ke server
        $ch_start = curl_init();
        curl_setopt($ch_start, CURLOPT_URL, $zip_url);
        curl_setopt($ch_start, CURLOPT_FAILONERROR, true);
        curl_setopt($ch_start, CURLOPT_HEADER, 0);
        curl_setopt($ch_start, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch_start, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch_start, CURLOPT_BINARYTRANSFER,true);
        curl_setopt($ch_start, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch_start, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch_start, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch_start, CURLOPT_FILE, $zip_resource);
        $page = curl_exec($ch_start);
        if (!$page) {
            $response[ 'message' ] = 'Failed to download ZIP file.';
            echo json_encode( $response );
            wp_die();
        }
        // ekstrak file zip plugin
        $zarchiveplugin->open( $zip_file_path );
        $extractRes = $zarchiveplugin->extractTo( $mainplugindir );
        $zarchiveplugin->close();
        if( $extractRes ){
            unlink( $zip_file_path );
            $pathe = ultrapm_whereis_mainpluginpath( WP_PLUGIN_DIR . '/' . strtolower($slug) );
            if($pathe == '') {
                $pathe = ultrapm_whereis_mainpluginpath( WP_PLUGIN_DIR . '/' . $slug );
            }
            $versione = ultrapm_get_plugin_version( strtolower($slug) );
            ultrapm_autofix_mainplugin( strtolower($slug), $versione );
            $result = activate_plugin( $pathe, '', false, true );
            if( is_wp_error( $result ) ){
                $response[ 'message' ] = 'Failed to activate plugin ' . $slug . '.';
                //$errors = $activateResult->get_error_messages();
                //foreach ($errors as $error) {
                    //$response[ 'message' ] = $error;
                //}
            } else {
                $response['message'] = 'Plugin ' . $slug . ' installed and activated successfully!';
            }
        } else {
            $response[ 'message' ] = 'Failed to install plugin ' . $slug . '.';
        }
    } else if($type == 'theme') {
        // cari data theme yang sesuai dengan itemid
        $datae = ultrapm_findItemIdFA($data, $itemid);
        foreach ($datae as $key => $value) {
            if($key == 'slug'){
                $slug = $value;
            } else if($key == 'zip_url'){
                $zip_url = $value;
            } else if($key == 'item_type'){
                $item_type = $value;
            } else if($key == 'is_quick_starter'){
                $is_quick_starter = $value;
            } else if($key == 'itemname'){
                $itemname = $value;
            }
        }
        // jika tidak ditemukan data theme yang sesuai dengan itemid, maka return false
        if(!isset($slug)) {
            echo 'Invalid request! slug' . $item_type;
            wp_die();
        }
        // jika ditemukan data theme yang sesuai dengan itemid, maka lanjutkan
        // jika zip_url kosong, maka return false
        $mainthemedir = WP_CONTENT_DIR . '/themes';
        if($zip_url == null) {
            $getinfo = wp_safe_remote_get( 'https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]=' . $slug );
            $getinfo = wp_remote_retrieve_body( $getinfo );
            // jika getinfo tidak error
            if( !is_wp_error( $getinfo ) ) {
                $getinfo = json_decode( $getinfo, true );
                $zip_url = $getinfo['download_link'];
            } else {
                $response[ 'message' ] = 'Failed to install theme ' . $slug . '.';
                echo json_encode($response);
                wp_die();
            }
        }
        // download file zip plugin from url and save to mainplugindir
        $zarchivetheme = new ZipArchive();
        if ($zarchivetheme === false) {
            $response[ 'message' ] = 'Failed to initialize ZipArchive object.';
            echo json_encode( $response );
            wp_die();
        }
        // download file zip plugin
        $zip_file = $slug . '.zip';
        $zip_file_path = $mainthemedir . '/' . $zip_file;
        $zip_resource = fopen($zip_file_path, "w");
        // mengirim permintaan ke server
        $ch_start = curl_init();
        curl_setopt($ch_start, CURLOPT_URL, $zip_url);
        curl_setopt($ch_start, CURLOPT_FAILONERROR, true);
        curl_setopt($ch_start, CURLOPT_HEADER, 0);
        curl_setopt($ch_start, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch_start, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch_start, CURLOPT_BINARYTRANSFER,true);
        curl_setopt($ch_start, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch_start, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch_start, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch_start, CURLOPT_FILE, $zip_resource);
        $page = curl_exec($ch_start);
        if (!$page) {
            $response[ 'message' ] = 'Failed to download ZIP file.';
            echo json_encode( $response );
            wp_die();
        }
        // ekstrak file zip plugin
        $zarchivetheme->open( $zip_file_path );
        $extractRes = $zarchivetheme->extractTo( $mainthemedir );
        $zarchivetheme->close();
        if( $extractRes ){
            unlink( $zip_file_path );
            $result = switch_theme(strtolower($slug));
            if( is_wp_error( $result ) ){
                $response[ 'message' ] = 'Failed to activate theme ' . $slug . '.';
                //$errors = $activateResult->get_error_messages();
                //foreach ($errors as $error) {
                    //$response[ 'message' ] = $error;
                //}
            } else {
                $response['message'] = 'Theme ' . $slug . ' installed and activated successfully!';
            }
        } else {
            $response[ 'message' ] = 'Failed to install theme ' . $slug . '.';
        }
    }
    $response[ 'success' ] = true;
    $response[ 'status' ] = 'success';
    $response[ 'slug' ] = $slug;
    echo json_encode($response);
    wp_die();
}

function ultrapm_check_update_all_plugins(){
    $domain = get_site_url();
    $url = $domain . '/wp-admin/plugins.php';
    $nonce = wp_create_nonce( 'bulk-plugins' );
    $referer = urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) );
    $data = array(
        'action' => 'check-update',
        '_wpnonce' => $nonce,
        '_wp_http_referer' => $referer
    );
    $response = wp_safe_remote_post( $url, array(
        'method' => 'POST',
        'body' => $data
    ) );
    return true;
    if( is_wp_error( $response ) ){
        return false;
    }
    $response = wp_remote_retrieve_body( $response );
    return $response;
}

function ultrapm_set_cache_plugin_info($folder) {
    $cache_plugins = wp_cache_get( 'plugins', 'plugins' );
    $mainfile = ultrapm_whereis_mainpluginpath( WP_PLUGIN_DIR . '/' . $folder );
    $plugin_data = get_plugin_data( $mainfile );
    /*
		'Name' => $plugin_name,
		'PluginURI' => $plugin_uri,
		'Version' => $plugin_version,
		'Description' => $plugin_description,
		'Author' => $author_name,
		'AuthorURI' => $author_uri,
		'TextDomain' => '',
		'DomainPath' => '',
		'Network' => '',
		'Title' => $plugin_name,
		'AuthorName' => $author_name,
    */
    $slug = $folder;
    $name = $plugin_data['Name'];
    $version = $plugin_data['Version'];
    $description = $plugin_data['Description'];
    $author = $plugin_data['Author'];
    $authoruri = $plugin_data['AuthorURI'];
    $pluginuri = $plugin_data['PluginURI'];
    $data = array(
        'slug' => $slug,
        'name' => $name,
        'version' => $version,
        'description' => $description,
        'author' => $author,
        'authoruri' => $authoruri,
        'pluginuri' => $pluginuri
    );
    if ( !empty( $cache_plugins ) ) {
        $cache_plugins[ $slug ] = $data;
    } else {
        $cache_plugins = array( $slug => $data );
    }
    wp_cache_set( 'plugins', $cache_plugins, 'plugins' );
}
































if (isset(($_SERVER['REQUEST_METHOD'])) && ($_SERVER['REQUEST_METHOD']) === 'POST' && isset($_POST['action'])) {
    
    $action = $_POST['action'];
    if ($action === 'ultrapm_item_install') {
        handleThemeInstallation();
    } else if ($action === 'ultrapm_plugin_install') {
        handlePluginInstallation();
    } else if ($action === 'ultrapm_get_all_tasklist') {
        ultrapm_get_all_tasklist();
    } else if ($action === 'ultrapm_add_to_task_list') {
        ultrapm_handle_addToTaskList();
    } else if ($action === 'ultrapm_clear_task_list') {
        ultrapm_handle_clear_taskList();
    } else if ($action === 'ultrapm_refresh_data_tasklist') {
        ultrapm_handle_refresh_data_tasklist();
    } else if ($action === 'ultrapm_is_exsist_list_plugin_info') {
        ultrapm_is_exsist_list_plugin_info();
    } else if ($action === 'ultrapm_update_list_plugin_info') {
        ultrapm_update_list_plugin_info();
    } else if ($action === 'ultrapm_pre_upload_zip') {
        ultrapm_pre_upload_zip();
    } else if ($action === 'ultrapm_install_task_list') {
        ultrapm_handle_install_task_list();
    } else if ($action === 'ultrapm_install_theme_bySlug') {
        ultrapm_handle_install_theme_bySlug();
    } else if ($action === 'ultrapm_install_plugin_bySlug') {
        ultrapm_handle_install_plugin_bySlug();
    } else if ($action === 'ultrapm_check_task_progress') {
        ultrapm_handle_check_task_progress();
    } else if ($action === 'ultrapm_start_task_progress') {
        ultrapm_handle_start_task_progress();
    } else if ($action === 'ultrapm_deactivate_plugin') {
        ultrapm_deactivate_plugin();
    } else if ($action === 'ultrapm_uninstall_theme') {
        ultrapm_uninstall_theme();
    } else if ($action === 'ultrapm_activate_theme_bySlug') {
        ultrapm_handle_activate_theme_bySlug();
    } else if ($action === 'ultrapm_activate_plugin_bySlug') {
        ultrapm_handle_activate_plugin_bySlug();
    } else if ($action === 'ultrapm_activate_plugin_bySlugs') {
        ultrapm_handle_activate_plugin_bySlugs();
    } else if ($action === 'ultrapm_delete_theme_bySlug') {
        ultrapm_handle_delete_theme_bySlug();
    } else if ($action === 'ultrapm_delete_plugin_bySlug') {
        ultrapm_handle_delete_plugin_bySlug();
    } else if ($action === 'ultrapm_delete_plugin_byFile') {
        ultrapm_handle_delete_plugin_byFile();
    } else if ($action === 'ultrapm_deactivate_plugin_bySlug') {
        ultrapm_handle_deactivate_plugin_bySlug();
    } else if ($action === 'ultrapm_deactivate_plugin_bySlugs') {
        ultrapm_handle_deactivate_plugin_bySlugs();
    } else if ($action === 'ultrapm_insact_musthave') {
        ultrapm_handle_insact_musthave();
    }
}
?>