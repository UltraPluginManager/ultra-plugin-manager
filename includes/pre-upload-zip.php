<?php

$wp_load_path = __DIR__ . '/../../../../wp-load.php';
if (file_exists($wp_load_path)) {
    require_once($wp_load_path);
} else {
    die('Contact ultra-plugin-manager support');
}


if( isset( $_POST[ 'sec' ]) ){

    $plugins_url = WP_PLUGIN_DIR;
    $home_url = home_url();
    $sec = $_POST[ 'sec' ];
    $ownsec = ultrapm_decrypt(file_get_contents(ULTRAPM_SECFILE));
    if($sec != $ownsec){
        die('Contact ultra-plugin-manager support');
    }

    $listtempzip = get_option('ultrapm_list_tempzip');
    if(!$listtempzip){
        $listtempzip = array();
    }

    $install_log = '';

    $pre_installs = array();
    $prosese = get_option('ultrapm_task_status');
    if(!$prosese){
        $prosese = array();
    }

    if(isset( $_POST[ 'url' ] )) {
        $cUrle = $_POST[ 'url' ];
        // jika url lebih dari satu baris
        if( strpos( $_POST[ 'url' ], "\n" ) !== false ){
            $urls = explode( "\n", $_POST[ 'url' ] );
            $plugins_url = WP_PLUGIN_DIR;
            $home_url = home_url();
            $sec = $_POST[ 'sec' ];
            $ownsec = ultrapm_decrypt(file_get_contents(ULTRAPM_SECFILE));
            if($sec != $ownsec){
                die('Contact ultra-plugin-manager support');
            }
            $install_log = '';
            $pre_installs = array();
            $numfilee = 0;
            foreach( $urls as $url ){
                $url = trim( $url );
                $numfilee++;
                ultrapm_update_progress($cUrle, 'proses', 'Downloading file ' . $numfilee . ' of ' . sizeof( $urls ) . '...');
                // if url contains google drive
                if( strpos( $url, 'drive.google.com' ) !== false ){
                    $parts = explode( '/', $url );
                    $id = $parts[ sizeof( $parts ) - 2 ];
                    $data = ultrapm_gdrive_downloader( $id );
                } elseif( strpos( $url, 'dropbox.com' ) !== false ) {
                    $data = ultrapm_dropbox_downloader( $url );
                    $id = 'dropboX' . time();
                } elseif( strpos( $url, 'wordpress.org' ) !== false ) {
                    $data = ultrapm_wordpress_downloader( $url );
                    $id = 'wordpresS' . time();
                } else {
                    $response = array(
                        'success' => 'false',
                        'message' => 'Invalid URL'
                    );
                    echo json_encode( $response );
                    die();
                }
                $filename = $id . '.zip';
                $filename_without_ext = substr( $filename, 0, strrpos( $filename, "." ) );
                $listtempzip = get_option( 'ultrapm_list_tempzip' );
                if( !$listtempzip ){
                    $listtempzip = array();
                }
                $uploads_dir = wp_upload_dir();
                $target_dir = $uploads_dir[ 'basedir' ] . '/ultra-plugin-manager';
                if( !is_dir( $target_dir ) ){
                    mkdir( $target_dir );
                    chmod( $target_dir, 0777 );
                }
                $target_file = $target_dir . "/" . $filename;
                file_put_contents( $target_file, $data );
                ultrapm_update_progress($cUrle, 'proses', 'Verifying file...');
                $cekUserZip = ultrapm_verify_type_zip( $target_file );
                if(!$cekUserZip){
                    $response = array(
                        'success' => 'false',
                        'message' => 'Invalid zip file',
                        'target_file' => $target_file,
                    );
                    echo json_encode( $response );
                    die();
                }
                if($cekUserZip == 'theme' || $cekUserZip == 'plugin') {
                    $userzipFile = 'single';
                } else {
                    $userzipFile = 'bundled';
                }
                $zarchive = new ZipArchive();
                if ( $zarchive->open( $target_file ) ) {
                    ultrapm_update_progress($cUrle, 'proses', 'Extracting file...');
                    $tname = $filename_without_ext . '_' . time();
                    $main_extract_dir = $target_dir . '/' . $tname;
                    if( !is_dir( $main_extract_dir ) ){
                        mkdir( $main_extract_dir );
                    } else {
                        // if directory already exist, delete it
                        $files = glob( $main_extract_dir . '/*' );
                        foreach( $files as $file ){
                            if( is_file( $file ) ){
                                unlink( $file );
                            }
                        }

                        $dirs = glob( $main_extract_dir . '/*', GLOB_ONLYDIR );
                        foreach( $dirs as $dir ){
                            if( is_dir( $dir ) ){
                                deleteDirectory( $dir );
                            }
                        }

                        deleteDirectory( $main_extract_dir );
                    }
                    $addlisttempzip = array(
                        'filename' => $filename,
                        'extract_dir' => $tname
                    );
                    array_push($listtempzip, $addlisttempzip);
                    if( $zarchive->extractTo( $main_extract_dir ) ) {
                        //if( $_POST[ 'userzipFile' ] == 'bundled' ){
                        if( $userzipFile == 'bundled' ){
                            $zarchive->close();
                            // Get all directories if bundled
                            $allFiles = scandir( $main_extract_dir );
                            ultrapm_update_progress($cUrle, 'proses', 'Classifying file...');
                            foreach( $allFiles as $bundledFile ){
                                if( $bundledFile != '.' && $bundledFile != '..' ){
                                    $targetbfile = $main_extract_dir . '/' . $bundledFile;
                                    if( is_file( $targetbfile ) ){                                        
                                        $extract_result = extractFiles( $targetbfile, $bundledFile, $main_extract_dir, $plugins_url, $home_url );
                                        foreach( $extract_result[ 'pre_installs' ] as $preInstall ){
                                            array_push( $pre_installs, $preInstall );
                                        }
                                        $install_log .= $extract_result[ 'log_text' ];
                                    }
                                    else if( is_dir( $targetbfile ) ) {
                                        $extract_results = parseDirectory( $targetbfile, $main_extract_dir, $plugins_url, $home_url );
                                        foreach( $extract_results as $single_extract_result ){
                                            foreach( $single_extract_result[ 'pre_installs' ] as $preInstall ){
                                                array_push( $pre_installs, $preInstall );
                                            }
                                            $install_log .= $single_extract_result[ 'log_text' ];
                                        }
                                    }
                                }
                            }
                        //} else if( $_POST[ 'userzipFile' ] == 'single' ) {
                        } else if( $userzipFile == 'single' ) {
                            $zarchive->close();
                            $item_folder = scandir( $main_extract_dir, SCANDIR_SORT_DESCENDING )[0];
                            ultrapm_update_progress($cUrle, 'proses', 'Verifying file...');
                            $type = verifyItemType( $target_file, $main_extract_dir . '/' . $item_folder, $item_folder, $plugins_url, $home_url );
                            if( $type[ 'type' ] != 'invalid' ){
                                array_push( $pre_installs, array(
                                    'file' => $target_file,
                                    'dir' => $main_extract_dir . '/' . $item_folder,
                                    'folder' => $item_folder,
                                    'slug' => $type[ 'slug' ],
                                    'type' => $type[ 'type' ]
                                ));
                            }
                        }
                    } else {
                        $zarchive->close();                    
                        $install_log .= 'Failed to extract from .zip file ' . $filename . '.|';
                    }
                } else {
                    $install_log .= 'Failed to open .zip file ' . $filename . '.|';
                }
            }
        } else {
            $url = $_POST[ 'url' ];
            ultrapm_update_progress($cUrle, 'proses', 'Downloading file...');
            // if url contains google drive
            if( strpos( $_POST[ 'url' ], 'drive.google.com' ) !== false ){
                $parts = explode( '/', $url );
                $id = $parts[ sizeof( $parts ) - 2 ];
                $data = ultrapm_gdrive_downloader( $id );
            } elseif( strpos( $_POST[ 'url' ], 'dropbox.com' ) !== false ) {
                $data = ultrapm_dropbox_downloader( $url );
                $id = 'dropboX' . time();
            } elseif( strpos( $_POST[ 'url' ], 'wordpress.org' ) !== false ) {
                $data = ultrapm_wordpress_downloader( $url );
                $id = 'wordpresS' . time();
            } else {
                $response = array(
                    'success' => 'false',
                    'message' => 'Invalid URL'
                );
                echo json_encode( $response );
                die();
            }
            if($data === false) {
                echo json_encode( array(
                    'success' => 'false',
                    'message' => 'Failed to download file'
                ) );
                ultrapm_update_progress($cUrle, 'done', 'Failed to download file');
                die();
            }
            ultrapm_update_progress($cUrle, 'proses', 'Extracting file...');
            $filename = $id . '.zip';
            $filename_without_ext = substr( $filename, 0, strrpos( $filename, "." ) );
            $listtempzip = get_option( 'ultrapm_list_tempzip' );
            if( !$listtempzip ){
                $listtempzip = array();
            }
            $uploads_dir = wp_upload_dir();
            $target_dir = $uploads_dir[ 'basedir' ] . '/ultra-plugin-manager';
            if( !is_dir( $target_dir ) ){
                mkdir( $target_dir );
            }
            $target_file = $target_dir . "/" . $filename;
            file_put_contents( $target_file, $data );
            $cekUserZip = ultrapm_verify_type_zip( $target_file );
            if(!$cekUserZip){
                $response = array(
                    'success' => 'false',
                    'message' => 'Invalid zip file',
                    'target_file' => $target_file,
                );
                echo json_encode( $response );
                die();
            }
            if($cekUserZip == 'theme' || $cekUserZip == 'plugin') {
                $userzipFile = 'single';
            } else {
                $userzipFile = 'bundled';
            }
            if (!file_exists($target_file)) {
                echo json_encode( array(
                    'success' => 'false',
                    'message' => 'Failed to download file'
                ) );
                die();
            }
            $zarchive = new ZipArchive();
            if ( $zarchive->open( $target_file ) === true ) {
                $ttime = time();
                $tname = $filename_without_ext . '_' . $ttime;
                $main_extract_dir = $target_dir . '/' . $tname;
                $addlisttempzip = array(
                    'filename' => $filename,
                    'extract_dir' => $tname
                );
                array_push($listtempzip, $addlisttempzip);
                if( $zarchive->extractTo( $main_extract_dir ) ) {
                    //if( $_POST[ 'userzipFile' ] == 'bundled' ){
                    if( $userzipFile == 'bundled' ){
                        $zarchive->close();
                        // Get all directories if bundled
                        $allFiles = scandir( $main_extract_dir );
                        ultrapm_update_progress($cUrle, 'proses', 'Classifying file...');
                        foreach( $allFiles as $bundledFile ){
                            if( $bundledFile != '.' && $bundledFile != '..' ){
                                $targetbfile = $main_extract_dir . '/' . $bundledFile;
                                if( is_file( $targetbfile ) ){                                        
                                    $extract_result = extractFiles( $targetbfile, $bundledFile, $main_extract_dir, $plugins_url, $home_url );
                                    foreach( $extract_result[ 'pre_installs' ] as $preInstall ){
                                        array_push( $pre_installs, $preInstall );
                                    }
                                    $install_log .= $extract_result[ 'log_text' ];
                                }
                                else if( is_dir( $targetbfile ) ) {
                                    $extract_results = parseDirectory( $targetbfile, $main_extract_dir, $plugins_url, $home_url );
                                    foreach( $extract_results as $single_extract_result ){
                                        foreach( $single_extract_result[ 'pre_installs' ] as $preInstall ){
                                            array_push( $pre_installs, $preInstall );
                                        }
                                        $install_log .= $single_extract_result[ 'log_text' ];
                                    }
                                }
                            }
                        }
                        ultrapm_update_progress($cUrle, 'done', 'Process done.');
                    //} else if( $_POST[ 'userzipFile' ] == 'single' ) {
                    } else if( $userzipFile == 'single' ) {
                        $zarchive->close();
                        $item_folder = scandir( $main_extract_dir, SCANDIR_SORT_DESCENDING )[0];
                        $type = verifyItemType( $target_file, $main_extract_dir . '/' . $item_folder, $item_folder, $plugins_url, $home_url );
                        if( $type[ 'type' ] != 'invalid' ){
                            array_push( $pre_installs, array(
                                'file' => $target_file,
                                'dir' => $main_extract_dir . '/' . $item_folder,
                                'folder' => $item_folder,
                                'slug' => $type[ 'slug' ],
                                'type' => $type[ 'type' ]
                            ));
                        }
                    }
                } else {
                    $zarchive->close();                    
                    $install_log .= 'Failed to extract from .zip file ' . $filename . '.|';
                }
            } else {
                echo json_encode( array(
                    'success' => 'false',
                    'message' => 'Failed to open .zip file'
                ) );
                die();
            }
        }
    } else if ( isset( $_FILES[ 'zipFile' ] ) ) {
        $cUrle = $_POST[ 'zipFileName' ];
        $zips = sizeof( $_FILES[ "zipFile" ][ 'name' ] );
        // Loop through each file
        for( $p=0;$p<$zips;$p++ ) {

            $uploads_dir = wp_upload_dir();

            $target_dir = $uploads_dir[ 'basedir' ] . '/ultra-plugin-manager';

            if( !is_dir( $target_dir ) ){
                mkdir( $target_dir );
            }

            $target_file = $target_dir . "/" . basename( $_FILES[ "zipFile" ][ "name" ][$p] );
            if( file_exists( $target_file ) ){
                sleep(1);
                $response = array(
                    'success' => 'false',
                    'message' => 'File already exist, Please Clear List First',
                );
                ultrapm_update_progress($cUrle, 'done', 'File already exist, Please Clear List First');
                echo json_encode( $response );
                die();
            }
            $filename = $_FILES[ "zipFile" ][ "name" ][$p];
            $filename_without_ext = substr( $filename, 0, strrpos( $filename, "." ) );
            $path_to_main_zip =  $target_dir . "/" . $filename_without_ext;
            $uploadOk = 1;

            $ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
            $size = $_FILES[ "zipFile" ][ "size" ][$p];

            if( $size >= 50000000 ) {
                $uploadOk = 1;
            } else {
                $uploadOk = 0;
            }

            if( $ext == 'zip' ) {
                $uploadOk = 1;
            } else {
                $uploadOk = 0;
            }

            if( $uploadOk == 1 ){
                if( copy( $_FILES[ "zipFile" ][ "tmp_name" ][$p], $target_file ) ){
                    ultrapm_update_progress($cUrle, 'proses', 'Verifying file...');

                    $cekUserZip = ultrapm_verify_type_zip( $target_file );
                    if(!$cekUserZip){
                        $response = array(
                            'success' => 'false',
                            'message' => 'Invalid zip file',
                            'target_file' => $target_file,
                        );
                        echo json_encode( $response );
                        die();
                    }
                    if($cekUserZip == 'theme' || $cekUserZip == 'plugin') {
                        $userzipFile = 'single';
                    } else {
                        $userzipFile = 'bundled';
                    }
                    $zarchive = new ZipArchive();

                    if ( $zarchive->open( $target_file ) ) {
                        ultrapm_update_progress($cUrle, 'proses', 'Extracting file...');
                        $tname = $filename_without_ext . '_' . time();
                        $main_extract_dir = $target_dir . '/' . $tname;
                        $addlisttempzip = array(
                            'filename' => $filename,
                            'extract_dir' => $tname
                        );
                        array_push($listtempzip, $addlisttempzip);

                        if( $zarchive->extractTo( $main_extract_dir ) ) {

                            //if( $_POST[ 'userzipFile' ] == 'bundled' ){
                            if( $userzipFile == 'bundled' ){

                                $zarchive->close();

                                // Get all directories if bundled
                                $allFiles = scandir( $main_extract_dir );

                                foreach( $allFiles as $bundledFile ){

                                    if( $bundledFile != '.' && $bundledFile != '..' ){

                                        ultrapm_update_progress($cUrle, 'proses', 'Classifying ' . $filename_without_ext . '...');
                                        $targetbfile = $main_extract_dir . '/' . $bundledFile;

                                        if( is_file( $targetbfile ) ){                                        

                                            $extract_result = extractFiles( $targetbfile, $bundledFile, $main_extract_dir, $plugins_url, $home_url );

                                            foreach( $extract_result[ 'pre_installs' ] as $preInstall ){
                                                array_push( $pre_installs, $preInstall );
                                            }

                                            $install_log .= $extract_result[ 'log_text' ];

                                        }
                                        else if( is_dir( $targetbfile ) ) {    

                                            $extract_results = parseDirectory( $targetbfile, $main_extract_dir, $plugins_url, $home_url );

                                            foreach( $extract_results as $single_extract_result ){

                                                foreach( $single_extract_result[ 'pre_installs' ] as $preInstall ){
                                                    array_push( $pre_installs, $preInstall );
                                                }

                                                $install_log .= $single_extract_result[ 'log_text' ];

                                            }

                                        }

                                    }

                                }

                            }
                            //else if( $_POST[ 'userzipFile' ] == 'single' ) {
                            else if( $userzipFile == 'single' ) {

                                ultrapm_update_progress($cUrle, 'proses', 'Verifying ' . $filename_without_ext . '...');
                                $zarchive->close();

                                $item_folder = scandir( $main_extract_dir, SCANDIR_SORT_DESCENDING )[0];

                                ultrapm_update_progress($cUrle, 'proses', 'Classifying ' . $filename_without_ext . '...');
                                $type = verifyItemType( $target_file, $main_extract_dir . '/' . $item_folder, $item_folder, $plugins_url, $home_url );
                            
                                if( $type[ 'type' ] != 'invalid' ){
                                    array_push( $pre_installs, array(
                                        'file' => $target_file,
                                        'dir' => $main_extract_dir . '/' . $item_folder,
                                        'folder' => $item_folder,
                                        'slug' => $type[ 'slug' ],
                                        'type' => $type[ 'type' ]
                                    ));
                                }

                            }
                        }
                        else {
                            $zarchive->close();                    
                            $install_log .= 'Failed to extract from .zip file ' . $filename . '.|';
                        }
                    
                    }
                    // If zip file is not open/exist
                    else {
                        $install_log .= 'Failed to open .zip file ' . $filename . '.|';
                    }

                }

            }
            else {
                $install_log .= 'Failed to copy .zip file ' . $filename . '.|';
            }

        }
    }

    $overall = array(
        //'pre_installs' => $pre_installs,
        //'install_log' => $install_log
        'success' => 'true',
        'url' => $cUrle,
        //'task' => $inpone,
    );

    update_option('ultrapm_list_tempzip', $listtempzip);
    $taskList = get_option( 'ultrapm_task_list' );
    if( $taskList == false ){
        $taskList = array();
    }

    // if preinstall more than 1 or userzipFile is bundle
    if( sizeof( $pre_installs ) > 1 || $userzipFile == 'bundled' ){

        foreach( $pre_installs as $pre_install ){
            $file = $pre_install[ 'file' ];
            $dir = $pre_install[ 'dir' ];
            $folder = $pre_install[ 'folder' ];
            $type = $pre_install[ 'type' ];
            $ndata = array(
                'pre' => true,
                'name' => $folder . ' (' . $type . ')',
                'file' => $file,
                'dir' => $dir,
                'folder' => $folder,
                'slug' => $folder,
                'type' => $type
            );
            array_push( $taskList, $ndata );
        }

    }
    else if( sizeof( $pre_installs ) == 1 ){
        $type = $pre_installs[ 0 ][ 'type' ];
        $file = $pre_installs[ 0 ][ 'file' ];
        $dir = $pre_installs[ 0 ][ 'dir' ];
        $folder = $pre_installs[ 0 ][ 'folder' ];
        $ndata = array(
            'pre' => true,
            'name' => $folder . ' (' . $type . ')',
            'file' => $file,
            'dir' => $dir,
            'folder' => $folder,
            'slug' => $folder,
            'type' => $type
        );
        array_push( $taskList, $ndata );
    }


    ultrapm_update_progress($cUrle, 'done', 'Process done.');
    update_option( 'ultrapm_task_list', $taskList );
    if ( isset( $_FILES[ 'zipFile' ] ) ) {
        $taskList = get_option( 'ultrapm_task_list' );
        if( $taskList == false ){
            $taskList = array();
        }
        foreach( $taskList as $task ){
            if(isset($task['pre']) && $task['pre'] == true) {
                $slug = $task[ 'slug' ];
                $type = $task[ 'type' ];
                $dir = $task[ 'dir' ];
                $folder = $task[ 'folder' ];
                $file = $task[ 'file' ];
                if( $type == 'plugin' ){
                    $plugin_dir = WP_PLUGIN_DIR . '/' . $slug;
                    if( is_dir( $plugin_dir ) ){
                        deleteDirectory( $plugin_dir );
                    }
                }
            }
        }
    }

    echo json_encode( $overall );

}

function extractFiles( $targetbfile, $bundledFile, $main_extract_dir, $plugins_url, $home_url ) {

    $pre_installs = array();

    // Check file extension
    $file_ext = pathinfo( $bundledFile, PATHINFO_EXTENSION );

    $path_parts = pathinfo( $targetbfile ); 

    $install_log_text = '';

    $pre_install = array();

    if( $file_ext == 'zip' ){        
        
        $bfilename_without_ext = substr( $bundledFile, 0, strrpos( $bundledFile, "." ) );

        $bundle_extract_dir = $main_extract_dir . '/' . $bfilename_without_ext . time();

        $zarchivebfilezip = new ZipArchive();

        $zOpenResult = $zarchivebfilezip->open( $targetbfile );
                
        if( $zOpenResult == "1" ) {

            // if( strpos( $targetbfile, '/revslider' ) != false ){
                // error_log( $zarchivebfilezip->open( $targetbfile ) . ' ** ' . $targetbfile, 1, 'derek_olalehe@hotmail.com' );
            // }

            if( $zarchivebfilezip->extractTo( $bundle_extract_dir ) ){//plugins1234789
            
                $zarchivebfilezip->close(); 

                if( strtolower( $bfilename_without_ext ) == 'plugins' || strtolower( $bfilename_without_ext ) == 'addons' ){

                    // Get all plugin or addon zips
                    $pluginZips = scandir( $bundle_extract_dir . '/' . $bfilename_without_ext );

                    foreach( $pluginZips as $pluginZip ){

                        if( is_file( $bundle_extract_dir . '/' . $bfilename_without_ext . '/' . $pluginZip ) ){

                            $plugin_file_ext = pathinfo( $pluginZip, PATHINFO_EXTENSION );

                            if( $plugin_file_ext == 'zip' ){

                                // extract to new location
                                $zarchive = new ZipArchive();

                                $targetpfile = $bundle_extract_dir . '/' . $bfilename_without_ext . '/' . $pluginZip;
                    
                                $zarchive->open( $targetpfile );

                                $pluginname_without_ext = substr( $pluginZip, 0, strrpos( $pluginZip, "." ) );
                    
                                $unzippedPluginLoc = $bundle_extract_dir . '/' . $bfilename_without_ext . '/' . $pluginname_without_ext . time();
                    
                                $zarchive->extractTo( $unzippedPluginLoc );
                    
                                $zarchive->close();

                                $item_pfolder = scandir( $bundle_extract_dir, SCANDIR_SORT_DESCENDING )[0];

                                $type = verifyItemType( '', $unzippedPluginLoc, '', $plugins_url, $home_url );

                                if( $type[ 'type' ] != 'invalid' ){
                                    $pre_install = array(
                                        'file' => $targetpfile,
                                        'dir' => $unzippedPluginLoc,
                                        'folder' => $pluginname_without_ext,
                                        'slug' => $type[ 'slug' ],
                                        'type' => $type[ 'type' ]
                                    );

                                    array_push( $pre_installs, $pre_install );
                                }

                            }

                        }

                    }
        
                }
                else {

                    $item_bfolder = scandir( $bundle_extract_dir, SCANDIR_SORT_DESCENDING )[0];
                                        
                    if( is_dir( $bundle_extract_dir . '/' . $item_bfolder ) ){

                        $type = verifyItemType( $targetbfile, $bundle_extract_dir . '/' . $item_bfolder, $item_bfolder, $plugins_url, $home_url );
//error_log( $type . ' || ' . $item_bfolder , 1, 'derek_olalehe@hotmail.com' );
                        if( $type[ 'type' ] != 'invalid' ){
                            $pre_install = array(
                                'file' => $targetbfile,
                                'dir' => $bundle_extract_dir . '/' . $item_bfolder,
                                'folder' => $item_bfolder,
                                'slug' => $type[ 'slug' ],
                                'type' => $type[ 'type' ]
                            );

                            array_push( $pre_installs, $pre_install );
                        
                        }

                    }

                }

            }
            else {

                $install_log_text = 'Failed to extract from nested .zip file ' . $bundledFile . '.|';

            }

        }
        else {

            $install_log_text = 'Failed to open nested .zip file ' . $bundledFile . '.|';

        }

    }
    else {

        $install_log_text = 'Nested file ' . $bundledFile . ' is not a .zip file.|';

    }

    return array( 
        'pre_installs' => $pre_installs, 
        'log_text' => $install_log_text 
    );

}


//function deleteDirectory( $dir ) {
    //if ( !file_exists( $dir ) ) {
        //return true;
    //}
//
    //if ( !is_dir( $dir ) ) {
        //return unlink( $dir );
    //}
//
    //foreach (scandir( $dir ) as $item ) {
        //if ( $item == '.' || $item == '..' ) {
            //continue;
        //}
//
        //if ( !deleteDirectory( $dir . DIRECTORY_SEPARATOR . $item ) ) {
            //return false;
        //}
//
    //}
//
    //return deleteDirectory( $dir );
//}

//function copyDirectory( $source, $destination ) {
//
    //if ( !is_dir( $destination ) ) {
       //mkdir( $destination, 0755, true );
    //}
//
    //$files = scandir( $source );
//
    //foreach ( $files as $file ) {
       //if ( $file !== '.' && $file !== '..' ) {
          //$sourceFile = $source . '/' . $file;
          //$destinationFile = $destination . '/' . $file;
          //if ( is_dir( $sourceFile ) ) {
            //copyDirectory( $sourceFile, $destinationFile );
          //} else {
            ////error_log( $sourceFile . '||' . $destinationFile, 1, 'derek_olalehe@hotmail.com' );
            //copy( $sourceFile, $destinationFile );
          //}
       //}
    //}
//
 //}

function verifyItemType( $target_file, $extracted_dir, $item_folder, $plugins_url, $home_url ){
        
    $required = array( 'functions.php', 'style.css' );

    $files = scandir( $extracted_dir );

    $theme_required_found = 0;

    foreach( $files as $file ){
        if( in_array( $file, $required ) ){
            $theme_required_found++;
        }
    }

    if( $theme_required_found == 2 ){
       return array( "type"=>"theme", "slug"=>"" );
    }
    else {     
        //error_log( $extracted_dir . '||' . $plugins_url . '/' . $item_folder, 1, 'derek_olalehe@hotmail.com' );
        copyDirectory( $extracted_dir, $plugins_url . '/' . $item_folder );

        $purl = $home_url . "/wp-json/ultra-plugin-manager/v1/validateplugin/" . $item_folder;
       
        $curl = curl_init();
        
        curl_setopt( $curl, CURLOPT_URL, $purl );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );

        $raw_output = curl_exec( $curl );
        $output = str_replace( '"', '', $raw_output );
        //error_log( 'raw output is' . $raw_output, 1, 'derek_olalehe@hotmail.com' );        

        curl_close( $curl );

        if( $item_folder != '' && $item_folder != null ){
            //deleteDirectory( $plugins_url . '/' . $item_folder ); // penghapusan folder pada wp-content/plugins/{nama-plugin}
        }
        
        if( $output != "false" ){
            return array( "type"=>"plugin", "slug"=>$output );
        }
        else {
            return array( "type"=>"invalid", "slug"=>"" );
        }
    }

}

function parseDirectory( $targetbfile, $main_extract_dir, $plugins_url, $home_url ){

    $data = array();

    $extract_results = array();

    // Get all directories if bundled tier 2
    $allFilesTierTwo = scandir( $targetbfile );

    foreach( $allFilesTierTwo as $bundledFileTierTwo ){

        if( $bundledFileTierTwo != '.' && $bundledFileTierTwo != '..' ){

            $targetbfiletiertwo = $targetbfile . '/' . $bundledFileTierTwo;

            if( is_file( $targetbfiletiertwo ) ) {

                $extract_result = extractFiles( $targetbfiletiertwo, $bundledFileTierTwo, $main_extract_dir, $plugins_url, $home_url );
               
                array_push( $extract_results, $extract_result );

            }
            else if( is_dir( $targetbfiletiertwo ) ) {

                // Get all directories if bundled tier 3
                $allFilesTierThree = scandir( $targetbfiletiertwo );

                foreach( $allFilesTierThree as $bundledFileTierThree ){

                    if( $bundledFileTierThree != '.' && $bundledFileTierThree != '..' ){

                        $targetbfiletierthree = $targetbfiletiertwo . '/' . $bundledFileTierThree;

                        if( is_file( $targetbfiletierthree ) ) {

                            $extract_result = extractFiles( $targetbfiletierthree, $bundledFileTierThree, $main_extract_dir, $plugins_url, $home_url );

                            array_push( $extract_results, $extract_result );

                        }
                        else if( is_dir( $targetbfiletierthree ) ) {

                            // Get all directories if bundled tier 3
                            $allFilesTierFour = scandir( $targetbfiletierthree );
            
                            foreach( $allFilesTierFour as $bundledFileTierFour ){
            
                                if( $bundledFileTierFour != '.' && $bundledFileTierFour != '..' ){
            
                                    $targetbfiletierfour = $targetbfiletierthree . '/' . $bundledFileTierFour;
            
                                    if( is_file( $targetbfiletierfour ) ) {
            
                                        $extract_result = extractFiles( $targetbfiletierthree, $bundledFileTierFour, $main_extract_dir, $plugins_url, $home_url );
                        
                                        array_push( $extract_results, $extract_result );
            
                                    }
            
                                }
            
                            }
            
                        }

                    }

                }

            }

        }

    }

    return $extract_results;

}

?>