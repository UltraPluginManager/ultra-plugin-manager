<?php
/*
Plugin Name: Ultra Plugin Manager
Description: Alternative WordPress Plugin and Theme Manager
Version: 1.0.0
Author: Ultra Plugin Manager
Author URI: http://ultrapluginmanager.com
License: GPL2
*/

/* Copyright 2023 Ultra Plugin Manager
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

DEFINE('ULTRAPM_PATH', plugin_dir_path( __FILE__ ));
DEFINE('ULTRAPM_URL', plugin_dir_url( __FILE__ ));
DEFINE('ULTRAPM_SLUG', 'ultrapm');
DEFINE('ULTRAPM_SECFILE', ULTRAPM_PATH . '.key');
DEFINE('ULTRAPM_SLUG_ADMIN', ULTRAPM_SLUG. '-dashboard');
DEFINE('ULTRAPM_SLUG_SEARCH_RESULT', ULTRAPM_SLUG. '-search-result');
DEFINE('ULTRAPM_SLUG_MUST_HAVE', ULTRAPM_SLUG. '-must-have');
DEFINE('ULTRAPM_SLUG_INSTALLED_APPS', ULTRAPM_SLUG. '-installed-apps');

DEFINE('ULTRAPM_UPLOADS_DIR', wp_upload_dir()['basedir'] . '/ultra-plugin-manager');
if(!is_dir(ULTRAPM_UPLOADS_DIR)){
    mkdir(ULTRAPM_UPLOADS_DIR);
    chmod(ULTRAPM_UPLOADS_DIR, 0777);
}

require_once( plugin_dir_path( __FILE__ ) . 'class-ultrapm.php' );

ULTRAPM::get_instance();
//ultrapm_handle_force_activate_installed_plugin_from_api();
register_activation_hook( __FILE__, 'ultrapm_activations' );
function ultrapm_activations(){

    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS " .  $wpdb->qs_configs_table_name . " (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        configname varchar(255),
        configitems longtext,
        PRIMARY KEY (id)
    ) " . $charset_collate . ";";

    dbDelta( $sql );

    add_option( 'ultrapm_api_url', 'https://inicara.co' );

    $cek = ultrapm_isMemoryLimitSet();
    if(!$cek){
        ultrapm_setMemoryLimit();
    }

    wp_schedule_single_event(time(), 'ultrapm_async_update_info_plugins');
    ultrapm_schedule_cron();

    $seckey = get_option('noncesec');
    if(!$seckey){
        $seckey = Uuid::uuid4()->toString();
        update_option('noncesec', $seckey);
    }

    $secfile = ULTRAPM_PATH . '.key';
    $sec = is_file($secfile) ? file_get_contents($secfile) : false;
    if(!$sec){
        $sec = ultrapm_encrypt(Uuid::uuid4()->toString());
        file_put_contents($secfile, $sec);
    }


}
// update_option( 'ultrapm_api_url', 'https://inicara.co' );
register_deactivation_hook( __FILE__, 'ultrapm_deactivations' );

function ultrapm_deactivations(){
    global $wpdb;
    $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'ultrapm_info_plugin_%'");
    $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'ultrapm_%'");
    wp_clear_scheduled_hook('ultrapm_cron_hook');
}


function ultrapm_isMemoryLimitSet(){
    // check if memory limit is set at wp-config.php
    $wp_config = file_get_contents(ABSPATH . 'wp-config.php');
    $memory_limit = preg_match('/define\(\s*\'WP_MEMORY_LIMIT\'\s*,\s*\'(.*)M\'\s*\)/', $wp_config, $matches);
    if($memory_limit){
        $memory_limit = $matches[1];
        if($memory_limit >= 512){
            return true;
        }
    }
    // check if memory limit is set at php.ini
    $memory_limit = ini_get('memory_limit');
    if($memory_limit >= 512){
        return true;
    }
    return false;
}


function ultrapm_setMemoryLimit(){
    // add memory limit to wp-config.php
    $wp_config = file_get_contents(ABSPATH . 'wp-config.php');
    // jika tidak ada memory limit di wp-config.php, tambahkan di bawah <?php
    if(!preg_match('/define\(\s*\'WP_MEMORY_LIMIT\'\s*,\s*\'(.*)M\'\s*\)/', $wp_config)){
        $wp_config = preg_replace('/<\?php/', "<?php\ndefine('WP_MEMORY_LIMIT', '512M');", $wp_config, 1);
        file_put_contents(ABSPATH . 'wp-config.php', $wp_config);
    }
}


function ultrapm_schedule_cron() {
    if (!wp_next_scheduled('ultrapm_cron_hook')) {
        wp_schedule_event(time(), 'hourly', 'ultrapm_cron_hook');
        wp_schedule_event(time(), 'ultrapm_c30s_cron_interval', 'ultrapm_del_temp_folder_hook');
    }
}

add_action('wp', 'ultrapm_schedule_cron');
add_action('ultrapm_cron_hook', 'ultrapm_cron_listpopular_function');

function ultrapm_cron_listpopular_function() {
    $popularPluginsURL = 'https://api.wordpress.org/plugins/info/1.2/?action=query_plugins&request[browse]=popular&request[per_page]=10&request[page]=1';
    $dataPopularPlugins = file_get_contents($popularPluginsURL);
    $dataPopularPlugins = json_decode($dataPopularPlugins, true);
    update_option('ultrapm_popular_plugins', $dataPopularPlugins);
    update_option('ultrapm_popular_plugins_last_update', time());
    foreach ($dataPopularPlugins['plugins'] as $plugin) {
        $pluginSlug = $plugin['slug'];
        $pluginInfoURL = 'https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&request[slug]=' . $pluginSlug;
        $pluginInfo = file_get_contents($pluginInfoURL);
        $pluginInfo = json_decode($pluginInfo, true);
        update_option('ultrapm_info_plugin_' . $pluginSlug, $pluginInfo);
    }

    $recommendedPluginsURL = 'https://api.wordpress.org/plugins/info/1.2/?action=query_plugins&request[browse]=recommended&request[per_page]=10&request[page]=1';
    $dataRecommendedPlugins = file_get_contents($recommendedPluginsURL);
    $dataRecommendedPlugins = json_decode($dataRecommendedPlugins, true);
    update_option('ultrapm_recommended_plugins', $dataRecommendedPlugins);
    update_option('ultrapm_recommended_plugins_last_update', time());
    foreach ($dataRecommendedPlugins['plugins'] as $plugin) {
        $pluginSlug = $plugin['slug'];
        $pluginInfoURL = 'https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&request[slug]=' . $pluginSlug;
        $pluginInfo = file_get_contents($pluginInfoURL);
        $pluginInfo = json_decode($pluginInfo, true);
        update_option('ultrapm_info_plugin_' . $pluginSlug, $pluginInfo);
    }
}

add_action('ultrapm_async_update_info_plugins', 'ultrapm_update_info_plugins');
function ultrapm_update_info_plugins() {
    $popularPluginsURL = 'https://api.wordpress.org/plugins/info/1.2/?action=query_plugins&request[browse]=popular&request[per_page]=10&request[page]=1';
    $dataPopularPlugins = file_get_contents($popularPluginsURL);
    $dataPopularPlugins = json_decode($dataPopularPlugins, true);
    update_option('ultrapm_popular_plugins', $dataPopularPlugins);
    update_option('ultrapm_popular_plugins_last_update', time());
    foreach ($dataPopularPlugins['plugins'] as $plugin) {
        $pluginSlug = $plugin['slug'];
        $pluginInfoURL = 'https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&request[slug]=' . $pluginSlug;
        $pluginInfo = file_get_contents($pluginInfoURL);
        $pluginInfo = json_decode($pluginInfo, true);
        update_option('ultrapm_info_plugin_' . $pluginSlug, $pluginInfo);
    }

    $recommendedPluginsURL = 'https://api.wordpress.org/plugins/info/1.2/?action=query_plugins&request[browse]=recommended&request[per_page]=10&request[page]=1';
    $dataRecommendedPlugins = file_get_contents($recommendedPluginsURL);
    $dataRecommendedPlugins = json_decode($dataRecommendedPlugins, true);
    update_option('ultrapm_recommended_plugins', $dataRecommendedPlugins);
    update_option('ultrapm_recommended_plugins_last_update', time());
    foreach ($dataRecommendedPlugins['plugins'] as $plugin) {
        $pluginSlug = $plugin['slug'];
        $pluginInfoURL = 'https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&request[slug]=' . $pluginSlug;
        $pluginInfo = file_get_contents($pluginInfoURL);
        $pluginInfo = json_decode($pluginInfo, true);
        update_option('ultrapm_info_plugin_' . $pluginSlug, $pluginInfo);
    }
}

function ultrapm_capture_all_errors($code, $message, $data) {
    $all_errors = get_option('ultrapm_all_errors', array());
    $all_errors[] = array(
        'code'    => $code,
        'message' => $message,
        'data'    => $data,
        'time'    => current_time('timestamp'),
    );
    update_option('ultrapm_capture_all_errors', $all_errors);
}
add_action('wp_error', 'capture_all_errors', 10, 3);

function ultrapm_del_temp_folder() {
    $uploads_dir = wp_upload_dir();
    $target_dir = $uploads_dir['basedir'] . '/ultra-plugin-manager';
    $datalist = get_option('ultrapm_list_tempzip');
    if($datalist){
        foreach ($datalist as $data) {
            $extractDir = $data['extract_dir'];
            $parts = explode('.', $extractDir);
            if (count($parts) > 1) {
                $lastPart = end($parts);
                $extractTime = $lastPart;
                $now = time();
                $oneHourAgo = $now - 60;
                if ($extractTime < $oneHourAgo) {
                    $target_file = $target_dir . '/' . $data['filename'];
                    if (file_exists($target_file)) {
                        unlink($target_file);
                    }
                    $target_folder = $target_dir . '/' . $data['extract_dir'];
                    if (file_exists($target_folder)) {
                        deleteDirectory($target_folder);
                    }
                }
            }
        }
    }
}

add_filter( 'cron_schedules', function ( $schedules ) {
    $schedules['ultrapm_c30s_cron_interval'] = array(
        'interval' => 30,
        'display' => __( 'Setengah Menit' )
    );
    return $schedules;
 } );



function ultrapm_encrypt($text) {
    $seckey = get_option('noncesec');
	$iv = substr(hash('sha256', $seckey), 0, 16);
	$encrypted = openssl_encrypt($text, 'aes-256-cbc', $seckey, 0, $iv);
	return $encrypted;
}

function ultrapm_decrypt($text) {
    $seckey = get_option('noncesec');
	$iv = substr(hash('sha256', $seckey), 0, 16);
	$decrypted = openssl_decrypt($text, 'aes-256-cbc', $seckey, 0, $iv);
	return $decrypted;
}






























?>