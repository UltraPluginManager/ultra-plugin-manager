<?php

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

// wp_enqueue_style( 'ultrapm-admin-style', plugins_url( 'style.css?v=' . (string)microtime(), __FILE__ ), false, false ); 

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

// assets/js/widgets.bundle.js
wp_enqueue_script( 'ultrapm-widgets-js', plugins_url( 'assets/js/widgets.bundle.js?v=' . (string)microtime(), __FILE__ ), array(), '1.0.0', true);

// assets/js/custom/widgets.js
wp_enqueue_script( 'ultrapm-custom-widgets-js', plugins_url( 'assets/js/custom/widgets.js?v=' . (string)microtime(), __FILE__ ), array(), '1.0.0', true);

// assets/plugins/custom/fullcalendar/fullcalendar.bundle.css
wp_enqueue_style( 'ultrapm-fullcalendar', plugins_url( 'assets/plugins/custom/fullcalendar/fullcalendar.bundle.css', __FILE__ ), false, false );

// assets/plugins/custom/fslightbox/fslightbox.bundle.js
wp_enqueue_script( 'ultrapm-fslightbox', plugins_url( 'assets/plugins/custom/fslightbox/fslightbox.bundle.js', __FILE__ ),false, false );

// assets/plugins/custom/datatables/datatables.bundle.css
wp_enqueue_style( 'ultrapm-datatables', plugins_url( 'assets/plugins/custom/datatables/datatables.bundle.css', __FILE__ ), false, false );

// assets/plugins/custom/datatables/datatables.bundle.js
wp_enqueue_script( 'ultrapm-datatables-js', plugins_url( 'assets/plugins/custom/datatables/datatables.bundle.js', __FILE__ ), false, false );


// ../custom.css
wp_enqueue_style( 'ultrapm-custom', plugins_url( 'custom.css', __FILE__ ), false, false );

// ../custom.js
wp_enqueue_script( 'ultrapm-custom-js', plugins_url( 'custom.js', __FILE__ ), array(), '1.0.0', true);
