<?php

if( is_admin() ) {

    add_action( 'admin_menu', 'add_custom_menu_pages' );

}

function add_custom_menu_pages(){ 
    
    add_menu_page( 'Ultra Plugin Manager', 'Ultra Plugin Manager', 'manage_options',
    'ultrapm-admin','ultrapm_admin',  plugins_url() . '/ultra-plugin-manager/assets/icons/app_radar_ico_24px.png', 2 );

}

function ultrapm_admin(){

    //require_once( 'upload-zip.php' );

?>

<div class="row mt-3">
    <img class="ultrapm-logo-lg" alt="ultra-plugin-manager" src="<?php echo plugins_url( '../assets/logos/ultra-plugin-manager-logo.png', __FILE__ );?>"/>
</div>

<div id="item-list-view" data-simplebar ondrop="searchResultDrop(event)" ondragover="searchResultAllowDrop(event)">
    <table id="item-list-view-table">
        <tr>
            <th width="35%">App. Name</th>
            <th width="15%">Type</th>
            <th width="25%"><input type="checkbox" id="check-all-listed-install"/>&nbsp;&nbsp;Install</th>
            <th width="25%"><input type="checkbox" id="check-all-listed-activate"/>&nbsp;&nbsp;Activate</th>
        </tr>
    </table>
    <button type="button" id="clear-install-list" class="btn btn-sm btn-dark">Clear</button>
    <button type="button" id="install-from-list" class="btn btn-sm btn-dark">Start</button>
</div>

<div id="item-list-view-btn">
    <img alt="item-list-view" src="<?php echo plugins_url( '../assets/icons/add-to-list.png', __FILE__ );?>"/>
</div>

<!-- <form method="POST" action="" enctype="multipart/form-data"> -->

    <div class="container-fluid mt-4">
        <!-- MAIN HEADER -->
        <div class="row mt-2">
            <div class="col-12">
                <ul class="nav nav-tabs" id="ultrapm-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" data-bs-target="#home-tab">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" data-bs-target="#installed-apps-tab">Installed Apps</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" data-bs-target="#themes-tab">Themes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" data-bs-target="#plugins-tab">Plugins</a>
                    </li>
                </ul>                        
                <div id="ultrapm-search-header" class="row py-3 mx-0">
                    <!-- <div class="col-md-3 offset-md-4 mt-3 d-flex justify-content-end">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="ultrapm-search-item" id="search-item-plugin" value="plugin">
                            <span class="ultrapm-modal-radio">Plugin</span>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="ultrapm-search-item" id="search-item-theme" value="theme">
                            <span class="ultrapm-modal-radio">Theme</span>
                        </div>
                    </div> -->
                    <div class="col-md-3 offset-md-6 my-auto">
                        <input type="text" id="tp-search-keyword" placeholder="Search for Themes or Plugins" class="form-control mr-1"/>
                    </div>
                    <div class="col-md-3 my-auto px-0">
                        <button type="button" data-type="theme" id="tp-search-themes" class="btn btn-sm btn-dark mr-2 tp-search">Search Themes</button>
                        <button type="button" data-type="plugin" id="tp-search-plugins" class="btn btn-sm btn-dark tp-search">Search Plugins</button>
                    </div>
                </div>
                <div class="tab-content" id="ultrapm-tab-content">
                    <div class="tab-pane fade show active" id="home-tab" role="tabpanel">
                        <div class="container mb-5">
                            <div class="row my-4">
                                <div class="col-md-2 d-grid my-auto">
                                    <button type="button" data-bs-toggle="modal" data-bs-target="#upload-zip-modal"
                                    id="upload-zip" class="btn btn-sm btn-primary">Upload a New Zip</button>
                                </div>
                                <div class="col-md-10 my-auto">
                                    <small class="text-muted">Upload Theme or Plugin File(s) or Bundled .zip files (Ultra Plugin Manager analyzes and finds the valid themes, plugins & add-ons)</small>
                                </div>
                            </div>
                            <div class="row my-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <h3>Install Quick Starter Packages</h3>
                                        <select class="form-control" id="quick-starter-select">
                                        </select>
                                    </div>
                                    <button type="button" id="delete-package" class="btn btn-sm btn-danger my-2">
                                        Delete Selected Package</button>
                                </div>  
                                <div class="col-md-6">
                                    <div class="row">
                                        <div id="quick-starter-checks">
                                        </div>
                                    </div>
                                    <div class="row my-1">
                                        <div class="col-12">
                                            <div class="form-check" style="display: inline-block;">
                                                <input class="form-check-input" type="checkbox" value="" id="save-as-quick-starter-config">
                                                <label class="form-check-label" for="save-as-quick-starter-config">
                                                    <strong 
                                                    >Save this configuration as ...
                                                    </strong>
                                                </label>
                                            </div><br>
                                            <input type="text" class="mr-1" id="new-configuration-name" placeholder="Name">
                                        </div>
                                    </div>
                                    <div class="row my-2">
                                        <div class="col-md-6">
                                            <button data-activate="1" type="button" id="install-activate" class="btn btn-sm btn-dark mr-2">Install & Activate</button>
                                            <button data-activate="0" type="button" id="install-only" class="btn btn-sm btn-dark">Install Only</button>
                                            <div id="item-installing-spinner" class="my-4 spinner-border text-primary" role="status">
                                                <span class="sr-only"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>                
                            </div>
                        </div>
                        <div class="container">
                            <div class="row mb-3">
                                <div class="col-md-5 offset-md-1 most-popular-list-container">
                                    <h5>Most Popular Themes</h5>
                                    <div id="most-popular-themes" class="most-popular-list" data-simplebar>                                        
                                    </div>
                                </div>
                                <div class="col-md-5 most-popular-list-container">
                                    <h5>Most Popular Plugins</h5>
                                    <div id="most-popular-plugins" class="most-popular-list" data-simplebar>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal fade mt-5" tabindex="-1" id="upload-zip-modal" 
                        data-bs-backdrop="static" data-bs-keyboard="false">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Upload Zip</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <form id="form-zip-upload" method="POST" action="" enctype="multipart/form-data">
                                            <div class="mb-4">
                                                <label for="zipFile" class="form-label">Add a zip file</label>
                                                <input multiple class="form-control" type="file" id="zipFile" name="zipFile[]">
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="userzipFile" id="singe-zip" value="single">
                                                <span class="ultrapm-modal-radio">Single Theme or Plugin Zip</span>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="userzipFile" id="bundled-zip" value="bundled">
                                                <span class="ultrapm-modal-radio">Bundled Zip</span>
                                            </div>
                                            <input type="hidden" name="content_dir" value="<?php echo WP_CONTENT_DIR;?>"/>
                                            <input type="hidden" name="uploads_dir" value="<?php $udir = wp_upload_dir(); echo $udir[ 'basedir' ]?>"/>
                                            <button type="submit" id="start-zip-upload" name="start-zip-upload"
                                            class="btn btn-sm btn-dark mr-2 mt-3">Start</button>
                                            <span id="zip-process-message"></span>
                                            <input type="hidden" name="plugins-dir-path" value="<?php echo WP_PLUGIN_DIR;?>"/>
                                            <input type="hidden" name="home-url" value="<?php echo home_url();?>"/>
                                        </form>                                          
                                    </div>
                                </div>
                                <!-- <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div> -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade mt-5" id="installed-apps-tab" role="tabpanel">
                        <?php $active_theme = wp_get_theme();?>
                        <h3>Plugins</h3>
                        <div class="container mt-4 mb-5">
                            <div class="row">
                                <div class="col-md-2 offset-md-10 d-flex justify-content-end">
                                    <select class="form-control" id="installed-plugin-bulk-actions">
                                        <option selected disabled value="-1">Bulk Actions</option>
                                        <option value="de-activate">De-Activate</option>
                                        <option value="delete">Delete</option>
                                    </select>&nbsp;
                                    <button type="button" id="installed-plugin-bulk-action-start" 
                                    class="btn btn-sm btn-dark">Go!</button>
                                </div>
                            </div>
                            <div class="row">
                            <?php

                            $plugins = get_plugins();

                            foreach( $plugins as $key => $value ){
                                
                                $name = $plugins[$key][ 'Name' ];
                                $slug = $key;
                                $version = $plugins[$key][ 'Version' ];
                                $author = $plugins[$key][ 'Author' ];
                                $uri = $plugins[$key][ 'PluginURI' ];
                                $description = substr( $plugins[$key][ 'Description' ], 0, 120 );

                                ?>
                                    <div class="col-md-3 my-3">
                                        <div class="ultrapm-item-tile">
                                            <input class="form-control tile-item-bulk-selected" type="checkbox"/>                                        
                                            <h5 class="item-title"><strong><?php echo $name;?></strong></h5>
                                            <p class="item-author">by <?php echo $author;?></p>
                                            <p class="item-description"><?php echo $description;?>
                                            <a href="<?php echo $uri;?>" target="_blank"> ...</a></p>
                                            <p class="item-version">v<?php echo $version;?></p>
                                            <div class="item-actions">
                                                <?php 
                                                    $activity = is_plugin_active( $key ) ? 'item-active.png':'item-inactive.png';
                                                    $action = is_plugin_active( $key ) ? 'deactivate-item':'activate-item';
                                                    $can_delete = is_plugin_active( $key ) ? '':'<img data-slug="' . $slug . '" class="item-action delete-plugin" alt="deletion" src="' . plugins_url( '../assets/icons/delete-item.png', __FILE__ ) . '"/>&nbsp;&nbsp;';
                                                ?>
                                                <img data-slug="<?php echo $slug;?>" class="item-action <?php echo $action;?>" alt="activation" src="<?php echo plugins_url( '../assets/icons/' . $activity, __FILE__ );?>"/>&nbsp;&nbsp;
                                                <?php echo $can_delete;?>
                                            </div>
                                        </div>
                                    </div>
                                <?php

                            }

                            ?>
                            </div>
                        </div>
                        <h3 class="mt-5">Themes</h3>
                        <div class="container mt-4 mb-5">
                            <div class="row">
                            <?php

                            $themes = wp_get_themes();

                            foreach( $themes as $theme ){
                                
                                $name = $theme->get( 'Name' );
                                $version = $theme->get( 'Version' );
                                $author = $theme->get( 'Author' );
                                $uri = $theme->get( 'ThemeURI' );
                                $description = substr( $theme->get( 'Description' ), 0, 120 );
                                $stylesheet = $theme->stylesheet;

                                ?>
                                    <div class="col-md-3 my-3">
                                        <div class="ultrapm-item-tile">
                                            <h5 class="item-title"><strong><?php echo $name;?></strong></h5>
                                            <p class="item-author">by <?php echo $author;?></p>
                                            <p class="item-description"><?php echo $description;?>
                                            <a href="<?php echo $uri;?>" target="_blank"> ...</a></p>
                                            <p class="item-version">v<?php echo $version;?></p>
                                            <div class="item-actions">
                                                <?php 
                                                    if ( $name == $active_theme->name || $name == $active_theme->parent_theme ) {
                                                        $activity = 'item-active.png';
                                                        $action = 'no-action-item';
                                                        $can_delete = '';
                                                    }
                                                    else {
                                                        $activity = 'item-inactive.png';
                                                        $action = 'activate-theme';
                                                        $can_delete = '<img data-stylesheet="' . $stylesheet . '" class="item-action delete-theme" alt="deletion" src="' . plugins_url( '../assets/icons/delete-item.png', __FILE__ ) . '"/>&nbsp;&nbsp;';
                                                    }
                                                ?>
                                                <img data-stylesheet="<?php echo $stylesheet;?>" class="item-action <?php echo $action;?>" alt="activation" src="<?php echo plugins_url( '../assets/icons/' . $activity, __FILE__ );?>"/>&nbsp;&nbsp;
                                                <?php echo $can_delete;?>
                                            </div>
                                            <!-- <div class="theme-item-image" 
                                            style="background-image: url('<?php //echo $theme->get_screenshot( $uri );?>')"></div>                                             -->
                                        </div>
                                    </div>
                                <?php

                            }

                            ?>
                            </div>
                        </div>

                    </div>

                    <div class="tab-pane fade" id="themes-tab" role="tabpanel">
                        <div class="container mt-4 mb-5">
                            <div id="ultrapm-themes" class="row">
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="plugins-tab" role="tabpanel">
                        <div class="container mt-4 mb-5">
                            <div id="ultrapm-plugins" class="row">
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="search-results-tab" role="tabpanel">
                        <h3 class="mt-4">Search Results</h3>
                        <div class="container mt-4 mb-5">
                            <div class="row">
                                <div class="col-md-2 offset-md-10 d-flex justify-content-end">
                                    Go to Page&nbsp;&nbsp;
                                    <select id="search-result-pages"></select>
                                </div>
                            </div>
                            <div id="ultrapm-search-results" class="row">
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

<!-- </form> -->


<?php

}

?>