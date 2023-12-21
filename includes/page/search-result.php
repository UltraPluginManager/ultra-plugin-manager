<?php

function ultrapm_search_result_page_callback(){
    if(!isset($_POST['action']) || $_POST['action'] != 'ultrapm_search'){
        // wp_Safe_redirect( admin_url( 'admin.php?page=' . ULTRAPM_SLUG_ADMIN ) );
        $keywords = get_option('ultrapm_last_search');
        $type = get_option('ultrapm_last_search_type');
        if(!$keywords || !$type){
            wp_Safe_redirect( admin_url( 'admin.php?page=' . ULTRAPM_SLUG_ADMIN ) );
        }
        if(!isset($_GET['pages'])){
            $page = 1;
        } else {
            $page = $_GET['pages'];
            if(!is_numeric($page)){
                $page = 1;
            }
        }
    } else {
        $keywords = urlencode($_POST['keywords']);
        update_option('ultrapm_last_search', $keywords);
        $type = $_POST['type'];
        update_option('ultrapm_last_search_type', $type);
        $page = 1;
    }

    $themeurl = 'http://api.wordpress.org/themes/info/1.2/?action=query_themes&request[per_page]=52&request[page]='.$page.'&request[search]=' . $keywords;
    $pluginurl = 'http://api.wordpress.org/plugins/info/1.2/?action=query_plugins&request[per_page]=52&request[page]='.$page.'&request[search]=' . $keywords;
    
    $datathemeenc = get_option('ultrapm_result_datatheme_' . $keywords);
    $datapluginenc = get_option('ultrapm_result_dataplugin_' . $keywords);
    
        $datathemeenc = file_get_contents($themeurl);
        update_option('ultrapm_result_datatheme_' . $keywords, $datathemeenc);
        $datapluginenc = file_get_contents($pluginurl);
        update_option('ultrapm_result_dataplugin_' . $keywords, $datapluginenc);
        update_option('ultrapm_result_datatheme_time_' . $keywords, time());
        update_option('ultrapm_result_dataplugin_time_' . $keywords, time());
        $datatheme = json_decode($datathemeenc, true);
        $dataplugin = json_decode($datapluginenc, true);

    function compareByRating($a, $b) {
        return $b['rating'] - $a['rating'];
    }

    function compareByNumRating($a, $b) {
        return $b['num_ratings'] - $a['num_ratings'];
    }
    
    if($type === 'plugin'){
        $totalPage = $dataplugin['info']['pages'];
        usort($dataplugin['plugins'], 'compareByRating');
        usort($dataplugin['plugins'], 'compareByNumRating');
    } else if($type === 'theme'){
        $totalPage = $datatheme['info']['pages'];
        usort($datatheme['themes'], 'compareByRating');
        usort($datatheme['themes'], 'compareByNumRating');
    }
    include( ULTRAPM_INC_PATH . '/part/content-start.php');
    include( ULTRAPM_INC_PATH . '/part/header.php');




    ?>
    <!--begin::Post-->
    <div class="content flex-row-fluid" id="kt_content">
    <?php
    if($type === 'theme'){
        $count = 0; // Inisialisasi variabel hitungan
    ?>
    <div class="row">
    <?php
        $allInstalledThemes = wp_get_themes();
        $activeTheme = wp_get_theme();
        foreach ($datatheme['themes'] as $theme) {
            $favicon = 'https://s1.wp.com/i/favicon.ico';

            // if $theme['name'] contains woocommerce
            if (strpos(strtolower($theme['name']), 'woocommerce') !== false) {
                $tname = str_replace('WooCommerce', 'Woo', $theme['name']);
                $tname = str_replace('Woocommerce', 'Woo', $tname);
                $tname = str_replace('woocommerce', 'Woo', $tname);
            } else {
                $tname = $theme['name'];
            }

            if (strlen($tname) > 17) {
                $tname = substr($tname, 0, 17) . '...';
            } else {
                $tname = $tname;
            }

            $is_installed = false;
            foreach ($allInstalledThemes as $installedTheme) {
                if ($installedTheme->get('Name') == $theme['name']) {
                    $is_installed = true;
                    break;
                }
            }

            $is_active = false;
            if ($activeTheme->get('Name') == $theme['name']) {
                $is_active = true;
            }

            ?>
        <!--begin::Col-->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <!--begin::Mixed Widget 4-->
            <div class="card card-xl-stretch mb-xl-8">
                <!--begin::Beader-->
                <div class="card-header border-0 py-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-3 mb-1"><?php echo $tname; ?></span>
                        <span class="text-muted fw-semibold fs-7"><?php echo $theme['author']; ?></span>
                    </h3>
                </div>
                <!--end::Header-->
                <!--begin::Body-->
                <div class="card-body d-flex flex-column">
                    <div class="flex-grow-1">
                        <img src="<?php echo $theme['screenshot_url']; ?>" alt="" class="mw-100 w-200px h-200px opacity-75" />
                    </div>
                    <div class="pt-5">
                        <p class="text-center fs-6 pb-5">
                        <span class="badge fs-8 py-2">Preview:</span>&nbsp; <a href="<?php echo $theme['preview_url']; ?>" target="_blank"><span class="badge badge-light-primary fs-8">Click Here</span></a>
                        <?php if(!$is_installed){ ?>
                        <a class="btn btn-primary w-100 py-3" id="install-<?php echo $theme['slug']; ?>"
                            onclick="ultrapm_crut_ajax_install_theme('<?php echo $theme['slug']; ?>')"
                        >Install & Activate</a>
                        <?php } elseif($is_installed && !$is_active) { ?>
                        <a class="btn btn-warning w-100 py-3" id="activate-<?php echo $theme['slug']; ?>"
                            onclick="ultrapm_crut_ajax_activate_theme('<?php echo $theme['slug']; ?>')"
                            style="margin-bottom: 5px;"
                        >Activate</a>
                        <a class="btn btn-danger w-100 py-3" id="delete-<?php echo $theme['slug']; ?>"
                            onclick="ultrapm_crut_ajax_delete_theme('<?php echo $theme['slug']; ?>')"
                        >Delete</a>
                        <?php } elseif($is_installed && $is_active) { ?>
                        <a class="btn w-100 py-3" href="#"
                            style="background-color: #5376a9; color: white;"
                            disabled
                        >Active</a>
                        <?php } ?>
                    </div>
                </div>
                <!--end::Body-->
            </div>
        </div>
            <?php
            $count++;
            if($count == 52){
                break;
            }
        }
    ?>
    </div>
    </div>
    <?php
    } elseif ($type == 'plugin') {
    $countPlugins = 0;
    ?>
    <div class="wp-list-table widefat plugin-install">
        <div id="the-list">
    <?php
        foreach ($dataplugin['plugins'] as $plugin) {

            $name = $plugin['name'];
            $pluginSlug = $plugin['slug'];
            $pluginSlugpure = str_replace('-', '', $pluginSlug);
            $version = $plugin['version'];
			if ( ! empty( $plugin['icons']['svg'] ) ) {
				$screenshot = $plugin['icons']['svg'];
			} elseif ( ! empty( $plugin['icons']['2x'] ) ) {
				$screenshot = $plugin['icons']['2x'];
			} elseif ( ! empty( $plugin['icons']['1x'] ) ) {
				$screenshot = $plugin['icons']['1x'];
			} else {
				$screenshot = $plugin['icons']['default'];
			}
            $homepage = $plugin['homepage'];
            
            //if (strlen($name) > 15) {
                //$name = substr($name, 0, 15) . '...';
            //} else {
                //$name = $name;
            //}

            $rating = $plugin['rating'];

            $is_installed = false;
            $is_active = false;
            $main_file = ultrapm_whereis_mainpluginpath( WP_PLUGIN_DIR . '/' . $plugin['slug'] );
            if($main_file){
                $is_installed = true;
                $is_active = is_plugin_active($main_file);
            }

            ?>
        <div class="plugin-cardz plugin-cardz-<?php echo $pluginSlug; ?> plugin-cardz-<?php echo $pluginSlug; ?>-install">
            <div class="plugin-cardz-top">
                <div class="name column-name">
                    <h3>
                        <a alt="" onclick="customIframe<?php echo $pluginSlugpure; ?>()"><?php echo $name; ?></a>
                    </h3>
                        <img class="plugin-icon" alt="" src="<?php echo $screenshot; ?>" style="width: 100px; height: 100px;" onclick="customIframe<?php echo $pluginSlugpure; ?>()">
                </div>
                <div class="action-links">
                    <ul class="plugin-action-buttons">
                    <?php if(!$is_installed){ 
                        if(!ultrapm_is_exsit_task_list($plugin['slug'], 'plugin')){ ?>
                        <li>
                            <a class="install-now button" id="addtotask-<?php echo $pluginSlug; ?>" onclick="ultrapm_addToTaskList('<?php echo $name; ?>', '<?php echo $pluginSlug; ?>', '<?php echo $plugin['download_link']; ?>', 'plugin')" aria-label="Add <?php echo $name; ?> to your list" data-name="<?php echo $name; ?>">Add To List</a>
                        </li>
                        <li>
                            <a class="install-now button" id="install-<?php echo $pluginSlug; ?>" onclick="ultrapm_crut_ajax_install_plugin('<?php echo $pluginSlug; ?>', 'false')" aria-label="Install <?php echo $name; ?> now" data-name="<?php echo $name; ?>" href="#">Install Now</a>
                        </li>
                        <li>
                            <a class="install-now button" id="installa-<?php echo $pluginSlug; ?>" onclick="ultrapm_crut_ajax_install_plugin('<?php echo $pluginSlug; ?>', 'true')" aria-label="Install and Activate <?php echo $name; ?> now" data-name="<?php echo $name; ?>" href="#">Install & Activate</a>
                        </li>
                        <?php } else { ?>
                        <li>
                            <a class="install-now button" disabled>Added To List</a>
                        </li>
                        <?php } ?>
                    <?php } elseif($is_installed && !$is_active) { ?>
                        <li>
                            <a class="install-now button" id="activate-<?php echo $pluginSlug; ?>" onclick="ultrapm_crut_ajax_activate_plugin<?php echo $pluginSlugpure; ?>()" aria-label="Activate <?php echo $name; ?> now" data-name="<?php echo $name; ?>" href="#">Activate</a>
                        </li>
                        <li>
                            <a class="install-now button" id="delete-<?php echo $pluginSlug; ?>" onclick="uninstallPluginSwalAsk<?php echo $pluginSlugpure; ?>()" aria-label="Delete <?php echo $name; ?> now" data-name="<?php echo $name; ?>" href="#">Delete</a>
                        </li>
                    <?php } elseif($is_installed && $is_active) { ?>
                        <li>
                            <a class="install-now button" id="deactivate-<?php echo $pluginSlug; ?>" onclick="ultrapm_crut_ajax_deactivate_plugin<?php echo $pluginSlugpure; ?>()" aria-label="Deactivate <?php echo $name; ?> now" data-name="<?php echo $name; ?>" href="#">Deactivate</a>
                        </li>
                    <?php } ?>
                    </ul>
                </div>
                <div class="desc column-description">
                    <p><?php echo $plugin['short_description']; ?></p>
                </div>
            </div>
            <div class="plugin-cardz-bottom">
                <div class="vers column-rating">
                    <div class="star-rating">
                        <span class="screen-reader-text">Rated <?php echo $rating; ?> out of 5 stars</span>
                        <?php
                        $rating = $plugin['rating'] / 20;
                        $numrating = $plugin['num_ratings'];
                        $activeInstall = $plugin['active_installs'];
                        $testedWP = $plugin['tested'];
                        if($activeInstall >= 1000000){
                            $activeInstall = $activeInstall / 1000000;
                            $activeInstall = number_format($activeInstall, 0, '', '');
                            $activeInstall = $activeInstall . '+ Million';
                        } else {
                            $activeInstall = number_format($activeInstall, 0, '', '');
                            $activeInstall = $activeInstall . '+ ';
                        }
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $rating) {
                                echo '<div class="star star-full" aria-hidden="true"></div>';
                            } else {
                                echo '<div class="star star-empty" aria-hidden="true"></div>';
                            }
                        }
                        $lastUpdate = date('F j, Y', strtotime($plugin['last_updated']));
                        ?>
                    </div>					
                    <span class="num-ratings" aria-hidden="true">(<?php echo $numrating; ?>)</span>
                </div>
                <div class="column-updated">
                    <strong>Last Updated:</strong> <?php echo $lastUpdate; ?> (<?php echo ultrapm_time_elapsed_stringe($plugin['last_updated']); ?>)
                </div>
                <div class="column-downloaded">
                    <strong>Installations:</strong> <?php echo $activeInstall; ?>
                </div>
                <div class="column-compatibility">
                    <span class="compatibility-compatible">
                        <strong>Compatible up to:</strong> <?php echo $testedWP; ?>
                    </span>
                </div>
            </div>
        </div>
                                                <div class="modal fade" tabindex="-1" aria-hidden="true" id="<?php echo $pluginSlug; ?>-changelog">
                                                    <!--begin::Modal dialog-->
                                                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable mw-800px">
                                                        <!--begin::Modal content-->
                                                        <div class="modal-content">
                                                                <!--begin iframe-->
                                                                <iframe id="TB_iframeContent<?php echo $pluginSlugpure; ?>" style="display: none; min-height: 75vh; width: 100%; border: none;"></iframe>
                                                        </div>
                                                        <!--end::Modal content-->
                                                    </div>
                                                    <!--end::Modal dialog-->
                                                </div>

        <script>
            function customIframe<?php echo $pluginSlugpure; ?>(){
                url = '<?php echo admin_url('plugin-install.php?tab=plugin-information&plugin=' . $plugin['slug'] . '&TB_iframe=true&width=772&height=679'); ?>';
                var mainUrl = '<?php echo wp_nonce_url( self_admin_url( 'plugin-install.php' ), 'plugin-information_' ); ?>';
                var datae = {
                    'tab': 'plugin-information',
                    'plugin': '<?php echo $pluginSlug; ?>',
                    //'section': 'changelog',
                    'TB_iframe': 'true',
                    'width': '772',
                    'height': '682',
                    '_wpnonce': '<?php echo wp_create_nonce( 'plugin-information_' . $pluginSlug ); ?>'
                };
    	        const loadingEl = document.createElement("div");
    	        document.body.prepend(loadingEl);
    	        loadingEl.classList.add("page-loader");
    	        loadingEl.classList.add("flex-column");
    	        loadingEl.classList.add("bg-dark");
    	        loadingEl.classList.add("bg-opacity-25");
		        loadingEl.setAttribute("id", "loadingEl");
    	        loadingEl.innerHTML = `
        	        <span class="spinner-border text-primary" role="status"></span>
        	        <span class="text-gray-800 fs-6 fw-semibold mt-5" id="ultrapminsproc">Getting Data...</span>
    	        `;
    	        KTApp.showPageLoading();
                jQuery.get(mainUrl, datae, function(data) {
				    KTApp.hidePageLoading();
				    loadingEl.remove();
                    jQuery('#<?php echo $pluginSlug; ?>-changelog').modal('show');
                    var data = data.replace(/<div\s+id=["']plugin-information-footer["'][\s\S]*?<\/div>/gi, '<div id="plugin-information-footer" style="display: none;"></div>');
                    var iframe = document.getElementById('TB_iframeContent<?php echo $pluginSlugpure; ?>');
                    iframe.contentWindow.document.open();
                    iframe.contentWindow.document.write(data);
                    iframe.contentWindow.document.close();
                    iframe.style.display = 'block';
                });
            }

            function uninstallPluginSwalAsk<?php echo $pluginSlugpure; ?>() {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You will uninstall <?php echo $name; ?>",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, uninstall it!',
                    cancelButtonText: 'No, cancel!',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
		                var button = document.getElementById('delete-<?php echo $pluginSlug; ?>');
                        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Uninstalling...';
                        button.disabled = true;
                        jQuery.ajax({
                            type: "POST",
                            url: ultrapm_urls.ajaxurl,
                            data: {
                                'action': 'ultrapm_deactivate_plugin',
                                'slug': '<?php echo $pluginSlug; ?>',
                            },
                            success: function(data) {
                                jQuery.ajax({
                                    type: "POST",
                                    url: ultrapm_urls.ajaxurl,
                                    data: {
                                        'action': 'delete-plugin',
                                        'slug': '<?php echo $pluginSlug; ?>',
                                        '_ajax_nonce': '<?php echo wp_create_nonce("updates"); ?>',
                                        'plugin': '<?php echo ultrapm_whereis_mainpluginpath( WP_PLUGIN_DIR . '/' . $pluginSlug ); ?>'
                                    },
                                    success: function(data) {
                                        var status = data.success;
                                        if (status == true) {
                                            Swal.fire({
                                                title: 'Success!',
                                                text: '<?php echo $name; ?> has been uninstalled.',
                                                icon: 'success',
                                                showCancelButton: false,
                                                confirmButtonText: 'Great!',
                                            }).then((result) => {
                                                var button = document.getElementById('delete-<?php echo $pluginSlug; ?>');
                                                button.innerHTML = 'Install & Activate';
                                                button.disabled = false;
                                                button.id = 'install-<?php echo $pluginSlug; ?>';
                                                button.setAttribute('onclick', 'ultrapm_crut_ajax_install_plugin("<?php echo $pluginSlug; ?>")');
                                                button.classList.remove('btn-danger');
                                                button.classList.add('btn-primary');
                                                var activateButton = document.getElementById('activate-<?php echo $pluginSlug; ?>');
                                                activateButton.remove();
                                                var addToTaskListButton = document.createElement('a');
                                                addToTaskListButton.classList.add('btn');
                                                addToTaskListButton.classList.add('btn-primary');
                                                addToTaskListButton.classList.add('w-100');
                                                addToTaskListButton.classList.add('py-3');
                                                addToTaskListButton.style.marginBottom = '5px';
                                                addToTaskListButton.id = 'install-<?php echo $pluginSlug; ?>';
                                                addToTaskListButton.setAttribute('onclick', 'ultrapm_addToTaskList("<?php echo $plugin['name']; ?>", "<?php echo $plugin['slug']; ?>", "<?php echo $plugin['download_link']; ?>", "plugin")');
                                                addToTaskListButton.innerHTML = 'Add To List';
                                                button.before(addToTaskListButton);
                                                var installOnlyButton = document.createElement('a');
                                                installOnlyButton.classList.add('btn');
                                                installOnlyButton.classList.add('btn-primary');
                                                installOnlyButton.classList.add('w-100');
                                                installOnlyButton.classList.add('py-3');
                                                installOnlyButton.style.marginBottom = '5px';
                                                installOnlyButton.id = 'install-<?php echo $pluginSlug; ?>';
                                                installOnlyButton.setAttribute('onclick', 'ultrapm_crut_ajax_install_plugin("<?php echo $pluginSlug; ?>", "false")');
                                                installOnlyButton.innerHTML = 'Install';
                                                addToTaskListButton.after(installOnlyButton);
                                            });
                                        } else {
                                            var datae = data.data;
                                            var errorMessage = datae.errorMessage;
                                            Swal.fire(
                                                'Error!',
                                                errorMessage,
                                                'error'
                                            );
                                        }
                                    },
                                    error: function() {
                                        Swal.fire(
                                            'Error!',
                                            'Failed to uninstall <?php echo $name; ?>',
                                            'error'
                                        );
                                    }
                                });
                            },
                            error: function() {
                                Swal.fire(
                                    'Error!',
                                    'Failed to uninstall <?php echo $name; ?>',
                                    'error'
                                );
                            }
                        });
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        Swal.fire(
                            'Cancelled',
                            '<?php echo $name; ?> is safe :)',
                            'error'
                        );
                    }
                });
            }
                            
	        function ultrapm_crut_ajax_activate_plugin<?php echo $pluginSlugpure; ?>() {
                var url = '<?php echo admin_url('plugins.php'); ?>';
                var mainpluginpath = '<?php echo ultrapm_whereis_mainpluginpath( WP_PLUGIN_DIR . '/' . $pluginSlug ); ?>';
                var nonce = '<?php echo wp_create_nonce("activate-plugin_" . ultrapm_whereis_mainpluginpath( WP_PLUGIN_DIR . '/' . $pluginSlug )); ?>';
		        var datae = {
			        'action': 'activate',
			        'plugin': mainpluginpath,
                    '_wpnonce': nonce
		        };
		        var button = document.getElementById('activate-' + '<?php echo $pluginSlug; ?>');
		        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Activating...';
		        button.disabled = true;
		        jQuery.ajax({
			        type: "GET",
			        url: url + '?' + jQuery.param(datae),
			        success: function(data) {
                        Swal.fire({
                            title: 'Success!',
                            text: '<?php echo $name; ?> has been activated.',
                            icon: 'success',
                            showCancelButton: false,
                            confirmButtonText: 'Great!',
                        }).then((result) => {
                            //var button = document.getElementById('activate-' + '<?php echo $pluginSlug; ?>');
                            //button.innerHTML = 'Deactivate';
                            //button.disabled = false;
                            //button.id = 'deactivate-' + '<?php echo $pluginSlug; ?>';
                            //button.setAttribute('onclick', 'ultrapm_crut_ajax_deactivate_plugin<?php echo $pluginSlugpure; ?>()');
                            //button.classList.remove('btn-warning');
                            //button.classList.add('btn-danger');
                            //var deleteButton = document.getElementById('delete-' + '<?php echo $pluginSlug; ?>');
                            //deleteButton.remove();
                            location.reload();
                        });
			        },
			        error: function() {
				        toastr.options = {
  					        "closeButton": false,
  					        "debug": false,
  					        "newestOnTop": true,
  					        "progressBar": true,
					        "positionClass": "toastr-top-center",
					        "preventDuplicates": false,
  					        "onclick": null,
  					        "showDuration": "300",
  					        "hideDuration": "1000",
					        "timeOut": "5000",
					        "extendedTimeOut": "1000",
					        "showEasing": "swing",
					        "hideEasing": "linear",
					        "showMethod": "fadeIn",
  					        "hideMethod": "fadeOut"
				        };
                    
				        toastr.info("Something went wrong", "Info");
				        //location.reload();
			        }
		        });
            
	        }

            function ultrapm_crut_ajax_deactivate_plugin<?php echo $pluginSlugpure; ?>() {
                var url = '<?php echo admin_url('plugins.php'); ?>';
                var mainpluginpath = '<?php echo ultrapm_whereis_mainpluginpath( WP_PLUGIN_DIR . '/' . $pluginSlug ); ?>';
                var nonce = '<?php echo wp_create_nonce("deactivate-plugin_" . ultrapm_whereis_mainpluginpath( WP_PLUGIN_DIR . '/' . $pluginSlug )); ?>';
                var datae = {
                    'action': 'deactivate',
                    'plugin': mainpluginpath,
                    '_wpnonce': nonce
                };
                var button = document.getElementById('deactivate-' + '<?php echo $pluginSlug; ?>');
                button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deactivating...';
                button.disabled = true;
                jQuery.ajax({
                    type: "GET",
                    url: url + '?' + jQuery.param(datae),
                    success: function(data) {
                        Swal.fire({
                            title: 'Success!',
                            text: '<?php echo $name; ?> has been deactivated.',
                            icon: 'success',
                            showCancelButton: false,
                            confirmButtonText: 'Great!',
                        }).then((result) => {
                            location.reload();
                            var button = document.getElementById('deactivate-' + '<?php echo $pluginSlug; ?>');
                            button.innerHTML = 'Activate';
                            button.disabled = false;
                            button.id = 'activate-' + '<?php echo $pluginSlug; ?>';
                            button.setAttribute('onclick', 'ultrapm_crut_ajax_activate_plugin<?php echo $pluginSlugpure; ?>()');
                            button.classList.remove('btn-danger');
                            button.classList.add('btn-warning');
                            var deleteButton = document.createElement('a');
                            deleteButton.classList.add('btn');
                            deleteButton.classList.add('btn-danger');
                            deleteButton.classList.add('w-100');
                            deleteButton.classList.add('py-3');
                            deleteButton.id = 'delete-' + '<?php echo $pluginSlug; ?>';
                            deleteButton.setAttribute('onclick', 'uninstallPluginSwalAsk<?php echo $pluginSlugpure; ?>()');
                            deleteButton.innerHTML = 'Delete';
                            button.after(deleteButton);

                        });
			        },
			        error: function() {
				        toastr.options = {
  					        "closeButton": false,
  					        "debug": false,
  					        "newestOnTop": true,
  					        "progressBar": true,
					        "positionClass": "toastr-top-center",
					        "preventDuplicates": false,
  					        "onclick": null,
  					        "showDuration": "300",
  					        "hideDuration": "1000",
					        "timeOut": "5000",
					        "extendedTimeOut": "1000",
					        "showEasing": "swing",
					        "hideEasing": "linear",
					        "showMethod": "fadeIn",
  					        "hideMethod": "fadeOut"
				        };
                    
				        toastr.info("Something went wrong", "Info");
				        //location.reload();
			        }
                });
            }



                        </script>
        
            <?php
            $countPlugins++;
            if($countPlugins == 52){
                break;
            }
        }
    }
        ?>
        </div>
    </div>
        <script>
            
            function TakeActionSR(slug, type, name, download_url){
                Swal.fire({
                    title: 'Take Action',
                    text: "What do you want to do with " + name + "?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: '<i class="bi bi-plus-circle"></i> Add to List',
                    cancelButtonText: '<i class="bi bi-download"></i> Install and Activate',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        ultrapm_addToTaskList(name, slug, download_url, type);
                    } else if (
                        result.dismiss === Swal.DismissReason.cancel
                    ) {
                        ultrapm_crut_ajax_install_theme(slug);
                    }
                })
            }
        
            function TakeActionA(slug) {
                Swal.fire({
                    title: 'Take Action',
                    text: "What do you want to do with " + slug + "?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: '<i class="bi bi-check-circle"></i> Activate',
                    cancelButtonText: '<i class="bi bi-trash"></i> Delete',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        ultrapm_crut_ajax_activate_theme(slug);
                    } else if (
                        result.dismiss === Swal.DismissReason.cancel
                    ) {
                        ultrapm_crut_ajax_delete_theme(slug);
                    }
                })
            }
        
        
        </script>







    		
            


    <?php
                include( ULTRAPM_INC_PATH . '/part/modal.php');
				include( ULTRAPM_INC_PATH . '/part/search-engine.php');
				include( ULTRAPM_INC_PATH . '/part/content-end.php');
				include( ULTRAPM_INC_PATH . '/part/task-list.php');
                include( ULTRAPM_INC_PATH . '/part/scripts.php');
}