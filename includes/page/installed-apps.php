<?php


function ultrapm_installed_apps_page_callback(){
    ultrapm_check_update_all_plugins();
    $allInstalledThemes = wp_get_themes();
    //$allInstalledPlugins = get_plugins();
    $allInstalledPlugins = apply_filters( 'all_plugins', get_plugins() );

    include( ULTRAPM_INC_PATH . '/part/content-start.php');
    include( ULTRAPM_INC_PATH . '/part/header.php');
    ?>

<div class="content flex-row-fluid" id="kt_content">
    <div class="row">
        <div class="col-xl-12" style="margin-top: 5vh;">
            <h1 class="anchor fw-bold mb-5" id="text-colors" data-kt-scroll-offset="50">Installed Themes</h1>
        </div>
        

    <?php
        $activeTheme = wp_get_theme();
        $activeThemeName1 = substr($activeTheme->get( 'Name' ), 0, 8);
        $activeThemeName2 = substr($activeTheme->get( 'Name' ), -8);
        if(strlen($activeTheme->get( 'Name' )) > 18){
            $activeThemeName = $activeThemeName1 . '...' . $activeThemeName2;
        } else {
            $activeThemeName = $activeTheme->get( 'Name' );
        }
        $activeThemeVersion = $activeTheme->get( 'Version' );
        $activeThemeAuthor = substr($activeTheme->get( 'Author' ), 0, 10);
        $activeThemeAuthorURI = $activeTheme->get( 'AuthorURI' );
        $activeThemeDescription = $activeTheme->get( 'Description' );
        $activeThemeTemplate = $activeTheme->get( 'Template' );
        $activeThemeStatus = $activeTheme->get( 'Status' );
        $activeThemeTextDomain = $activeTheme->get( 'TextDomain' );
        $activeThemeDomainPath = $activeTheme->get( 'DomainPath' );
        $activeThemeTags = $activeTheme->get( 'Tags' );
        $activeThemeThemeURI = $activeTheme->get( 'ThemeURI' );
        $activeThemeScreenshot = $activeTheme->get_screenshot();
        $activeThemeStylesheet = $activeTheme->get( 'Stylesheet' );
        $activeThemeTemplateDir = $activeTheme->get( 'TemplateDir' );
        $activeThemeTemplateURI = $activeTheme->get( 'TemplateURI' );
        $activeThemeVersion = $activeTheme->get( 'Version' );
        $activeThemeSlug = $activeTheme->get_stylesheet();

        $activeThemeSlugPure = str_replace( '-', '', $activeThemeSlug );
        $activeThemeUrlCustomizer = admin_url( 'customize.php?theme=' . $activeThemeSlug );
        $activeThemeWidgets = admin_url( 'widgets.php' );
        $activeThemeMenus = admin_url( 'nav-menus.php' );

        // if theme has required plugins
        $activeThemeRequiredPlugins = $activeTheme->get( 'RequiredPlugins' );
        $is_reqPlugin = false;
        if($activeThemeRequiredPlugins){
            $is_reqPlugin = true;
        }

        $is_parent = $activeTheme->get( 'Template' ) !== '';
        

        $activeThemeUrlCustomizer = admin_url( 'site-editor.php?return=' . urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
    ?>
        <div class="col-sm-6 col-xl-4" style="margin-bottom: 20px;">
            <div class="card card-flush h-xl-100">
                <div class="card-body text-center pb-5">
                    <a class="d-block overlay" data-fslightbox="lightbox-hot-sales" href="<?php echo $activeThemeThemeURI; ?>" target="_blank">
                        <div class="overlay-wrapper bgi-no-repeat bgi-position-center bgi-size-cover card-rounded mb-7" style="height: 266px;background-image:url('<?php echo $activeThemeScreenshot; ?>')"></div>
                        <div class="overlay-layer card-rounded bg-dark bg-opacity-25">
                            <i class="ki-duotone ki-eye fs-3x text-white">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                        </div>
                    </a>
                    <div class="d-flex align-items-end flex-stack mb-1">
                        <div class="text-start">
                            <span class="fw-bold text-gray-800 cursor-pointer text-hover-primary fs-4 d-block" data-bs-target="#activeTheme-modal" data-bs-toggle="modal"><?php echo $activeThemeName; ?></span>
                            <span class="text-gray-400 mt-1 fw-bold fs-6">By <?php echo $activeThemeAuthor; ?></span>
                        </div>
                        <span class="text-gray-600 text-end fw-bold fs-6">Version <?php echo $activeThemeVersion; ?></span>
                    </div>
                </div>
                <div class="card-footer d-flex flex-stack pt-0">
                        <a class="btn btn-sm btn-primary flex-shrink-0 me-2" href="<?php echo $activeThemeUrlCustomizer; ?>">Customize</a>
                        <a class="btn btn-sm btn-info flex-shrink-0 me-2" href="<?php echo $activeThemeWidgets; ?>">Widgets</a>
                        <a class="btn btn-sm btn-info flex-shrink-0 me-2" href="<?php echo $activeThemeMenus; ?>">Menus</a>
                        <a class="btn btn-sm btn-light flex-shrink-0" data-bs-target="#activeTheme-modal" data-bs-toggle="modal">Details</a>
                </div>
            </div>
        </div>

        <div class="modal fade" tabindex="-1" aria-hidden="true" id="activeTheme-modal">
            <!--begin::Modal dialog-->
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable mw-800px">
                <!--begin::Modal content-->
                <div class="modal-content">
                    <!--begin::Modal header-->
                    <div class="modal-header pb-0 border-0 justify-content-end">
                        <!--begin::Close-->
                        <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                            <i class="ki-duotone ki-cross fs-1">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </div>
                        <!--end::Close-->
                    </div>
                    <!--begin::Modal header-->
                    <!--begin::Modal body-->
                    <div class="modal-body scroll-y pt-0 pb-15">
                        <div class="d-flex flex-column flex-md-row rounded border p-10">
                            <ul class="nav nav-tabs nav-pills border-0 flex-row flex-md-column me-5 mb-3 mb-md-0 fs-6" role="tablist">
                                <li class="nav-item w-md-200px me-0" role="presentation">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#kt_vtab_pane_1_<?php echo $activeThemeSlug; ?>" aria-selected="true" role="tab">Details</a>
                                </li>
                                <li class="nav-item w-md-200px me-0" role="presentation">
                                    <a class="nav-link" data-bs-toggle="tab" href="#kt_vtab_pane_2_<?php echo $activeThemeSlug; ?>" aria-selected="false" role="tab" tabindex="-1">Description</a>
                                </li>
                                <li class="nav-item w-md-200px me-0" role="presentation">
                                    <a class="nav-link" data-bs-toggle="tab" href="#kt_vtab_pane_3_<?php echo $activeThemeSlug; ?>" aria-selected="false" role="tab" tabindex="-1">Images</a>
                                </li>
                            </ul>
                            <div class="tab-content" id="myTabContent_<?php echo $activeThemeSlug; ?>">
                                <div class="tab-pane fade active show" id="kt_vtab_pane_1_<?php echo $activeThemeSlug; ?>" role="tabpanel">
                                    <div class="d-flex flex-column">
                                        <?php if($is_parent){ ?>
                                            <li class="d-flex align-items-center py-2"><span class="bullet bg-warning"></span><strong>&nbsp;Parent Theme : &nbsp;</strong> <?php echo $activeThemeTemplate; ?></li>
                                        <?php } ?>
                                        <li class="d-flex align-items-center py-2"><span class="bullet bg-primary"></span><strong>&nbsp;Name : &nbsp;</strong> <?php echo $activeThemeName; ?></li>
                                        <li class="d-flex align-items-center py-2"><span class="bullet bg-primary"></span><strong>&nbsp;Author : &nbsp;</strong> <?php echo $activeThemeAuthor; ?></li>
                                        <li class="d-flex align-items-center py-2"><span class="bullet bg-primary"></span><strong>&nbsp;Author URI : &nbsp;</strong> <a href="<?php echo $activeThemeAuthorURI; ?>" target="_blank"><?php echo $activeThemeAuthor; ?></a></li>
                                        <li class="d-flex align-items-center py-2"><span class="bullet bg-primary"></span><strong>&nbsp;Text Domain : &nbsp;</strong> <?php echo $activeThemeTextDomain; ?></li>
                                        <li class="d-flex align-items-center py-2"><span class="bullet bg-primary"></span><strong>&nbsp;Theme URI : &nbsp;</strong> <a href="<?php echo $activeThemeThemeURI; ?>" target="_blank"><?php echo $activeThemeThemeURI; ?></a></li>
                                        <li class="d-flex align-items-center py-2"><span class="bullet bg-primary"></span><strong>&nbsp;Version : &nbsp;</strong> <?php echo $activeThemeVersion; ?></li>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="kt_vtab_pane_2_<?php echo $activeThemeSlug; ?>" role="tabpanel">
                                    <div class="d-flex flex-column">
                                        <p><?php echo $activeThemeDescription; ?></p>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="kt_vtab_pane_3_<?php echo $activeThemeSlug; ?>" role="tabpanel">
                                    <div class="d-flex flex-column">
                                        <img src="<?php echo $activeThemeScreenshot; ?>" alt="<?php echo $activeThemeName; ?>" style="width: 100%;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Modal body-->
                    <!--begin::Modal footer-->
                    <div class="modal-footer flex-center">
                        <a href="<?php echo $activeThemeThemeURI; ?>" target="_blank" class="btn btn-light btn-active-light-primary">Visit Website</a>
                        <a href="<?php echo $activeThemeAuthorURI; ?>" target="_blank" class="btn btn-light btn-active-light-primary">Visit Author</a>
                        <!-- Install Plugins -->
                        <?php if($is_reqPlugin){ ?>
                            <a class="btn btn-primary" href="<?php echo admin_url( 'themes.php?page=tgmpa-install-plugins' ); ?>">Install Plugins</a>
                        <?php } ?>
                    </div>
                </div>
                <!--end::Modal content-->
            </div>
            <!--end::Modal dialog-->
        </div>




    <?php
    $s = 0;
    foreach ($allInstalledThemes as $theme) {
        $themeName1 = substr($theme->get( 'Name' ), 0, 8);
        $themeName2 = substr($theme->get( 'Name' ), -8);
        if(strlen($theme->get( 'Name' )) > 18){
            $themeName = $themeName1 . '...' . $themeName2;
        } else {
            $themeName = $theme->get( 'Name' );
        }
        $themeVersion = $theme->get( 'Version' );
        $themeAuthor = substr($theme->get( 'Author' ), 0, 10);
        $themeAuthorURI = $theme->get( 'AuthorURI' );
        $themeDescription = $theme->get( 'Description' );
        $themeTemplate = $theme->get( 'Template' );
        $themeStatus = $theme->get( 'Status' );
        $themeTextDomain = $theme->get( 'TextDomain' );
        $themeDomainPath = $theme->get( 'DomainPath' );
        $themeTags = $theme->get( 'Tags' );
        $themeThemeURI = $theme->get( 'ThemeURI' );
        $themeScreenshot = $theme->get_screenshot();
        $themeStylesheet = $theme->get( 'Stylesheet' );
        $themeTemplateDir = $theme->get( 'TemplateDir' );
        $themeTemplateURI = $theme->get( 'TemplateURI' );
        $themeVersion = $theme->get( 'Version' );
        $themeSlug = $theme->get_stylesheet();
        $themeSlugPure = str_replace( '-', '', $themeSlug );
        
        $is_active = $theme->get_stylesheet() === get_stylesheet();
        $is_auto_update = $theme->get( 'autoupdate' );
        $is_parent = $theme->get( 'Template' ) !== '';
        $s++;

        if($is_active){
            continue;
        }
    ?>
		<div class="col-sm-6 col-xl-4 col-xxl-4" style="margin-bottom: 20px;">
			<div class="card card-flush h-xl-100">
				<div class="card-body text-center pb-5">
					<a class="d-block overlay" data-fslightbox="lightbox-hot-sales" data-bs-target="#<?php echo $themeSlug; ?>-modal" data-bs-toggle="modal">
						<div class="overlay-wrapper bgi-no-repeat bgi-position-center bgi-size-cover card-rounded mb-7" style="height: 266px;background-image:url('<?php echo $themeScreenshot; ?>')"></div>
						<div class="overlay-layer card-rounded bg-dark bg-opacity-25">
							<i class="ki-duotone ki-eye fs-3x text-white">
								<span class="path1"></span>
								<span class="path2"></span>
								<span class="path3"></span>
							</i>
						</div>
					</a>
					<div class="d-flex align-items-end flex-stack mb-1">
						<div class="text-start">
							<span class="fw-bold text-gray-800 cursor-pointer text-hover-primary fs-4 d-block"><?php echo $themeName; ?></span>
							<span class="text-gray-400 mt-1 fw-bold fs-6">By <?php echo $themeAuthor; ?></span>
						</div>
						<span class="text-gray-600 text-end fw-bold fs-6">Version <?php echo $themeVersion; ?></span>
					</div>
				</div>
				<div class="card-footer d-flex flex-stack pt-0">
					<a class="btn btn-sm btn-primary flex-shrink-0 me-2" onclick="UltrapmActivateTheme<?php echo $s; ?>()" id="activate-<?php echo $themeSlugPure; ?>">Activate</a>
					<a class="btn btn-sm btn-info flex-shrink-0 me-2" href="<?php echo admin_url( 'customize.php?theme=' . $themeSlug ); ?>" target="_blank">Preview</a>
					<a class="btn btn-sm btn-danger flex-shrink-0 me-2" onclick="UltrapmSDeleteTheme<?php echo $s; ?>()" id="delete-<?php echo $themeSlugPure; ?>">Delete</a>
                    <a class="btn btn-sm btn-light flex-shrink-0" data-bs-target="#<?php echo $themeSlug; ?>-modal" data-bs-toggle="modal">Details</a>
				</div>
			</div>
		</div>



        
        <div class="modal fade" tabindex="-1" aria-hidden="true" id="<?php echo $themeSlug; ?>-modal">
            <!--begin::Modal dialog-->
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable mw-800px">
                <!--begin::Modal content-->
                <div class="modal-content">
                    <!--begin::Modal header-->
                    <div class="modal-header pb-0 border-0 justify-content-end">
                        <!--begin::Close-->
                        <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                            <i class="ki-duotone ki-cross fs-1">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </div>
                        <!--end::Close-->
                    </div>
                    <!--begin::Modal header-->
                    <!--begin::Modal body-->
                    <div class="modal-body scroll-y pt-0 pb-15">
                        <div class="d-flex flex-column flex-md-row rounded border p-10">
                            <ul class="nav nav-tabs nav-pills border-0 flex-row flex-md-column me-5 mb-3 mb-md-0 fs-6" role="tablist">
                                <li class="nav-item w-md-200px me-0" role="presentation">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#kt_vtab_pane_1_<?php echo $themeSlug; ?>" aria-selected="true" role="tab">Details</a>
                                </li>
                                <li class="nav-item w-md-200px me-0" role="presentation">
                                    <a class="nav-link" data-bs-toggle="tab" href="#kt_vtab_pane_2_<?php echo $themeSlug; ?>" aria-selected="false" role="tab" tabindex="-1">Description</a>
                                </li>
                                <li class="nav-item w-md-200px me-0" role="presentation">
                                    <a class="nav-link" data-bs-toggle="tab" href="#kt_vtab_pane_3_<?php echo $themeSlug; ?>" aria-selected="false" role="tab" tabindex="-1">Images</a>
                                </li>
                            </ul>
                            <div class="tab-content" id="myTabContent_<?php echo $themeSlug; ?>">
                                <div class="tab-pane fade active show" id="kt_vtab_pane_1_<?php echo $themeSlug; ?>" role="tabpanel">
                                    <div class="d-flex flex-column">
                                        <?php if($is_parent){ ?>
                                            <li class="d-flex align-items-center py-2"><span class="bullet bg-warning"></span><strong>&nbsp;Parent Theme : &nbsp;</strong> <?php echo $themeTemplate; ?></li>
                                        <?php } ?>
                                        <li class="d-flex align-items-center py-2"><span class="bullet bg-primary"></span><strong>&nbsp;Name : &nbsp;</strong> <?php echo $themeName; ?></li>
                                        <li class="d-flex align-items-center py-2"><span class="bullet bg-primary"></span><strong>&nbsp;Author : &nbsp;</strong> <?php echo $themeAuthor; ?></li>
                                        <li class="d-flex align-items-center py-2"><span class="bullet bg-primary"></span><strong>&nbsp;Author URI : &nbsp;</strong> <?php echo $themeAuthorURI; ?></li>
                                        <li class="d-flex align-items-center py-2"><span class="bullet bg-primary"></span><strong>&nbsp;Template : &nbsp;</strong> <?php echo $themeTemplate; ?></li>
                                        <li class="d-flex align-items-center py-2"><span class="bullet bg-primary"></span><strong>&nbsp;Text Domain : &nbsp;</strong> <?php echo $themeTextDomain; ?></li>
                                        <li class="d-flex align-items-center py-2"><span class="bullet bg-primary"></span><strong>&nbsp;Theme URI : &nbsp;</strong> <a href="<?php echo $themeThemeURI; ?>" target="_blank"><?php echo $themeThemeURI; ?></a></li>
                                        <li class="d-flex align-items-center py-2"><span class="bullet bg-primary"></span><strong>&nbsp;Version : &nbsp;</strong> <?php echo $themeVersion; ?></li>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="kt_vtab_pane_2_<?php echo $themeSlug; ?>" role="tabpanel">
                                    <div class="d-flex flex-column">
                                        <p><?php echo $themeDescription; ?></p>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="kt_vtab_pane_3_<?php echo $themeSlug; ?>" role="tabpanel">
                                    <div class="d-flex flex-column">
                                        <img src="<?php echo $themeScreenshot; ?>" alt="<?php echo $themeName; ?>" style="width: 100%;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Modal body-->
                    <!--begin::Modal footer-->
                    <div class="modal-footer flex-center">
                        <a href="<?php echo $themeThemeURI; ?>" target="_blank" class="btn btn-light btn-active-light-primary">Visit Website</a>
                        <a href="<?php echo $themeAuthorURI; ?>" target="_blank" class="btn btn-light btn-active-light-primary">Visit Author</a>
                        <a href="<?php echo admin_url( 'customize.php?theme=' . $themeSlug ); ?>" target="_blank" class="btn btn-info btn-active-light-primary">Live Preview</a>
                        <!-- Activate -->
                        <a class="btn btn-primary" onclick="UltrapmActivateTheme<?php echo $s; ?>()" id="activate-<?php echo $themeSlugPure; ?>">Activate</a>
                        <!-- Delete -->
                        <a class="btn btn-danger" onclick="UltrapmDeleteTheme<?php echo $s; ?>()" id="delete-<?php echo $themeSlugPure; ?>">Delete</a>
                    </div>
                </div>
                <!--end::Modal content-->
            </div>
            <!--end::Modal dialog-->
        </div>
        <script>
            function UltrapmActivateTheme<?php echo $s; ?>(){
                var data = {
                    'action': 'activate',
                    'stylesheet': '<?php echo $themeSlug; ?>',
                    '_wpnonce': '<?php echo wp_create_nonce( 'switch-theme_' . $themeSlug ); ?>'
                };
                jQuery('#activate-<?php echo $themeSlugPure; ?>').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Activating...');
                jQuery.get('<?php echo admin_url( 'themes.php' ); ?>', data, function(response) {
                    if ( response ) {
                        jQuery('#activate-<?php echo $themeSlugPure; ?>').html('Activated');
                        window.location.reload();
                    }
                });
            }
            function UltrapmDeleteTheme<?php echo $s; ?>(){
                var data = {
                    'action': 'delete',
                    'stylesheet': '<?php echo $themeSlug; ?>',
                    '_wpnonce': '<?php echo wp_create_nonce( 'delete-theme_' . $themeSlug ); ?>'
                };
                jQuery('#delete-<?php echo $themeSlugPure; ?>').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...');
                jQuery.get('<?php echo admin_url( 'themes.php' ); ?>', data, function(response) {
                    if ( response ) {
                        jQuery('#delete-<?php echo $themeSlugPure; ?>').html('Deleted');
                        window.location.reload();
                    }
                });
            }
            function UltrapmSDeleteTheme<?php echo $s; ?>(){
                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete it!"
                }).then(function(result) {
                    if (result.value) {
                        var data = {
                            'action': 'delete',
                            'stylesheet': '<?php echo $themeSlug; ?>',
                            '_wpnonce': '<?php echo wp_create_nonce( 'delete-theme_' . $themeSlug ); ?>'
                        };
                        jQuery('#delete-<?php echo $themeSlugPure; ?>').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...');
                        jQuery.get('<?php echo admin_url( 'themes.php' ); ?>', data, function(response) {
                            if ( response ) {
                                jQuery('#delete-<?php echo $themeSlugPure; ?>').html('Deleted');
                                window.location.reload();
                            }
                        });
                    }
                });
            }
        </script>
    <?php
    }
    ?>


        <div class="col-xl-12" style="margin-top: 5vh;">
            <h1 class="anchor fw-bold mb-5" id="text-colors" data-kt-scroll-offset="50">Installed Plugins</h1>
        </div>
        <div class="col-xl-12" style="margin-bottom: 20px;">
                <div class="card-body text-center pb-5" style="background-color: #ffffff; margin-top: 10px;">
                    <table id="kt_datatable_responsive" class="table table-hover table-rounded table-striped gy-5 gs-7" style="width:100%">
                        <thead>
                            <tr>
                                <td colspan="12">
                                    <button type="button" class="btn" onclick="ultrapm_activate_iplugin()" id="bulk-activatee" style="background-color: #f3f3f3;">Activate</button>
                                    <button type="button" class="btn" onclick="ultrapm_deactivate_iplugin()" id="bulk-deactivatee" style="background-color: #f3f3f3;">Deactivate</button>
                                    <button type="button" class="btn" onclick="ultrapm_deactivate_and_delete_iplugin()" id="bulk-deletee" style="background-color: #f3f3f3;">Delete</button>
                                    <button type="button" class="btn" onclick="ultrapm_check_update_iplugin()" id="bulk-check-updatee" style="background-color: #f3f3f3;">Check Update</button>
                                    <!-- <button type="button" class="btn btn-primary" onclick="ultrapm_update_iplugin()" id="bulk-update">Update</button> -->
                                    <button type="button" class="btn" onclick="ultrapm_enable_auto_update_iplugin()" id="bulk-enable-auto-updatee" style="background-color: #f3f3f3;">Enable Auto Update</button>
                                    <button type="button" class="btn" onclick="ultrapm_disable_auto_update_iplugin()" id="bulk-disable-auto-updatee" style="background-color: #f3f3f3;">Disable Auto Update</button>
                                </td>
                            </tr>
		                    <tr class="fw-semibold fs-6 text-gray-800">
                                <th data-sortable="false"><input type="checkbox" class="rounded-checkbox" id="select-all"></th>
			                    <th class="min-w-150px" data-priority="1">Name</th>
			                    <th class="min-w-100px">Author</th>
			                    <th class="min-w-100px text-center">Auto Update</th>
			                    <th class="min-w-100px text-center">Version</th>
                                <th class="min-w-100px text-center">New Version</th>
			                    <th class="min-w-100px" data-priority="2">Description</th>
		                    </tr>
                        </thead>
                        <tbody>
                            <?php
                            $s = 0;
                            // allInstalledPlugins
                            $update_plugins = get_site_transient( 'update_plugins' );
                            foreach ($allInstalledPlugins as $key => $plugin) {
                                $pluginName = $plugin['Name'];
                                $pluginAuthor = $plugin['Author'];
                                $pluginAuthorURI = $plugin['AuthorURI'];
                                $pluginIsAutoUpdate = ultrapm_is_plugin_autoupdate($key);
                                $pluginDescription = $plugin['Description'];
                                $pluginVersion = $plugin['Version'];
                                $anyUpdate = false;
                                if($update_plugins){
                                    foreach ($update_plugins->response as $key2 => $value) {
                                        if($key == $key2){
                                            $anyUpdate = true;
                                        }
                                    }
                                }
                                $pluginSlug = explode( '/', $key );
                                $pluginSlug = $pluginSlug[0];
                                $pluginSlugpure = str_replace( '-', '', $pluginSlug );
                                $is_active = is_plugin_active( $key );
                                $pluginSlugpure = str_replace( '-', '', $pluginSlug );
                                $pluginSlugpure = str_replace( '/', '', $pluginSlugpure );
                                $pluginSlugpure = str_replace( '.', '', $pluginSlugpure );
                                $s++;
                                ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="rounded-checkbox" value="<?php echo strtolower($key); ?>" name="plugins[]" id="plugins-<?php echo strtolower($key); ?>">
                                    </td>
                                        <?php 
                                        if($is_active){ 
                                            echo '<td class="text-start">' . $pluginName;?>
                                            <i id="isa-<?php echo $pluginSlugpure; ?>" hidden></i>
                                            <i class="ki-duotone ki-check-square fs-1x text-success" id="active-<?php echo $pluginSlugpure; ?>">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        <?php } else { 
                                            echo '<td class="text-start text-gray-600">' . $pluginName;?>
                                            <i id="noa-<?php echo $pluginSlugpure; ?>" hidden></i>
                                            <i class="ki-duotone ki-cross-circle fs-1x text-danger" id="active-<?php echo $pluginSlugpure; ?>">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        <?php }
                                    ?><br><?php
                                    if($anyUpdate) {
                                        echo '<a onclick="ultrapm_update_iplugin' . $s . '()" style="cursor: pointer; color: blue;" id="supdate-' . $pluginSlugpure . '">Update</a> ';
                                    }
                                    if($is_active){ ?>
                                    <a onclick="ultrapm_crut_ajax_deactivate_plugin('<?php echo $pluginSlug; ?>')" style="cursor: pointer; color: gray;" id="deactivate-<?php echo $pluginSlug; ?>">Deactivate</a>
                                    <?php } else { ?>
                                    <a onclick="ultrapm_crut_ajax_activate_plugin('<?php echo $pluginSlug; ?>')" style="cursor: pointer; color: blue;" id="activate-<?php echo $pluginSlug; ?>">Activate</a>
                                    <?php } ?>
                                    <a onclick="uninstallPluginSwalAsk<?php echo $pluginSlugpure; ?>()" style="cursor: pointer; color: red;" id="delete-<?php echo $pluginSlugpure; ?>">Delete</a>
                                    </td>
                                    <td class="text-start"><a style="cursor: pointer; color:#0073aa;" onclick="window.open('<?php echo $pluginAuthorURI; ?>', '_blank')"><?php echo $pluginAuthor; ?></a></td>
                                    <td><?php
                                        if($pluginIsAutoUpdate){
                                            echo 'Yes';
                                        } else {
                                            echo 'No';
                                        }
                                    ?></td>
                                    <td><?php echo $pluginVersion; ?></td>
                                    <td><?php
                                        if($anyUpdate){ 
                                            ?>
                                            <span class="badge badge-light-info fw-bold text-start" style="cursor: pointer;"><?php echo $update_plugins->response[$key]->new_version; ?></span><br>
                                            <span class="badge badge-light-info fw-bold text-start" id="idetails-<?php echo $pluginSlugpure; ?>" onclick="customIframe<?php echo $pluginSlugpure; ?>()" style="cursor: pointer;">View Details</span>
                                                <div class="modal fade" tabindex="-1" aria-hidden="true" id="<?php echo $pluginSlugpure; ?>-changelog">
                                                    <!--begin::Modal dialog-->
                                                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable mw-800px">
                                                        <!--begin::Modal content-->
                                                        <div class="modal-content">
                                                                <!--begin iframe like https://wpsaya.my.id/wp-admin/plugin-install.php?tab=plugin-information&plugin=woocommerce&section=changelog&TB_iframe=true&width=772&height=682 -->
                                                                <iframe id="TB_iframeContent<?php echo $pluginSlugpure; ?>" style="display: none; min-height: 75vh; width: 100%; border: none;"></iframe>
                                                        </div>
                                                        <!--end::Modal content-->
                                                    </div>
                                                    <!--end::Modal dialog-->
                                                </div>
                                        <?php }
                                    ?></td>
                                    <td class="text-start"><?php echo $pluginDescription; ?></td>
                                </tr>
                                <script>
                                    // select all
                                    jQuery('#select-all').click(function(event) {   
                                        if(this.checked) {
                                            // Iterate each checkbox
                                            jQuery(':checkbox').each(function() {
                                                this.checked = true;                        
                                            });
                                        } else {
                                            jQuery(':checkbox').each(function() {
                                                this.checked = false;                        
                                            });
                                        }
                                    });
                                    function customIframe<?php echo $pluginSlugpure; ?>(){
                                        var mainUrl = '<?php echo wp_nonce_url( self_admin_url( 'plugin-install.php' ), 'plugin-information_' ); ?>';
                                        var datae = {
                                            'tab': 'plugin-information',
                                            'plugin': '<?php echo $pluginSlug; ?>',
                                            'section': 'changelog',
                                            'TB_iframe': 'true',
                                            'width': '772',
                                            'height': '682',
                                            '_wpnonce': '<?php echo wp_create_nonce( 'plugin-information_' . $pluginSlug ); ?>'
                                        };
                                        jQuery('#idetails-<?php echo $pluginSlugpure; ?>').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');
                                        jQuery.get(mainUrl, datae, function(data) {
                                            // open modal
                                            jQuery('#<?php echo $pluginSlug; ?>-changelog').modal('show');
                                            var data = data.replace(/<div\s+id=["']plugin-information-footer["'][\s\S]*?<\/div>/gi, '<div id="plugin-information-footer" style="display: none;"></div>');
                                            var iframe = document.getElementById('TB_iframeContent<?php echo $pluginSlugpure; ?>');
                                            iframe.contentWindow.document.open();
                                            iframe.contentWindow.document.write(data);
                                            iframe.contentWindow.document.close();
                                            
                                            iframe.style.display = 'block';
                                            jQuery('#idetails-<?php echo $pluginSlugpure; ?>').html('View Details');
                                        });
                                    }
                                    function ultrapm_update_iplugin<?php echo $s; ?>(){
                                        var data = {
                                            'action': 'upgrade-plugin',
                                            'plugin': '<?php echo $key; ?>',
                                            '_wpnonce': '<?php echo wp_create_nonce( 'upgrade-plugin_' . $key ); ?>',
                                            '_wp_http_referer': '<?php echo urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ); ?>'
                                        };
                                        jQuery('#supdate-<?php echo $pluginSlugpure; ?>').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...');
                                        jQuery.get('<?php echo admin_url( 'update.php' ); ?>', data, function(response) {
                                            if ( response ) {
                                                var data2 = {
                                                    'action': 'activate-selected',
                                                    'checked': ['<?php echo $key; ?>'],
                                                    '_wpnonce': '<?php echo wp_create_nonce( 'bulk-plugins' ); ?>',
                                                    '_wp_http_referer': '<?php echo urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ); ?>'
                                                };
                                                jQuery.post('<?php echo admin_url( 'plugins.php' ); ?>', data2, function(response) {
                                                    if ( response ) {
                                                        jQuery('#supdate-<?php echo $pluginSlugpure; ?>').html('Updated');
                                                        window.location.reload();
                                                    }
                                                });
                                            }
                                        });
                                    }
                                    <?php
                                        // jika plugin sudah terinstall
                                        $cek = is_plugin_installed(strtolower($pluginSlug));
                                        if($cek) { ?>
                                        function uninstallPluginSwalAsk<?php echo $pluginSlugpure; ?>() {
                                            Swal.fire({
                                                title: 'Are you sure?',
                                                text: "You will uninstall <?php echo $pluginName; ?>",
                                                icon: 'warning',
                                                showCancelButton: true,
                                                confirmButtonText: 'Yes, uninstall it!',
                                                cancelButtonText: 'No, cancel!',
                                                reverseButtons: false
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    var button = document.getElementById('delete-<?php echo $pluginSlugpure; ?>');
                                                    button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Uninstalling...';
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
                                                                    button.innerHTML = 'Uninstalled';
                                                                    var status = data.success;
                                                                    if (status == true) {
                                                                        Swal.fire({
                                                                            title: 'Success!',
                                                                            text: '<?php echo $pluginName; ?> has been uninstalled.',
                                                                            icon: 'success',
                                                                            showCancelButton: false,
                                                                            confirmButtonText: 'Great!',
                                                                        }).then((result) => {
                                                                            location.reload();
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
                                                                        'Failed to uninstall <?php echo $pluginName; ?>',
                                                                        'error'
                                                                    );
                                                                }
                                                            });
                                                        },
                                                        error: function() {
                                                            Swal.fire(
                                                                'Error!',
                                                                'Failed to uninstall <?php echo $pluginName; ?>',
                                                                'error'
                                                            );
                                                        }
                                                    });
                                                } else if (result.dismiss === Swal.DismissReason.cancel) {
                                                    Swal.fire(
                                                        'Cancelled',
                                                        '<?php echo $pluginName; ?> is safe :)',
                                                        'error'
                                                    );
                                                }
                                            });
                                        }
                                        <?php
                                        }?>
                                </script>
                                <?php
                            }
                            ?>
                        </tbody>
                        <tfoot>
		                    <tr class="fw-semibold fs-6 text-gray-800">
                                <th></th>
			                    <th class="min-w-150px" data-priority="1">Name</th>
                                <th class="min-w-100px">Action</th>
			                    <th class="min-w-100px">Author</th>
			                    <th class="min-w-100px">Auto Update</th>
			                    <th class="min-w-100px">Version</th>
			                    <th class="min-w-100px" data-priority="2">Description</th>
		                    </tr>
                            <tr>
                                <td colspan="12">
                                    <button type="button" class="btn" onclick="ultrapm_activate_iplugin()" id="bulk-activate" style="background-color: #f3f3f3;">Activate</button>
                                    <button type="button" class="btn" onclick="ultrapm_deactivate_iplugin()" id="bulk-deactivate" style="background-color: #f3f3f3;">Deactivate</button>
                                    <button type="button" class="btn" onclick="ultrapm_deactivate_and_delete_iplugin()" id="bulk-delete" style="background-color: #f3f3f3;">Delete</button>
                                    <button type="button" class="btn" onclick="ultrapm_check_update_iplugin()" id="bulk-check-update" style="background-color: #f3f3f3;">Check Update</button>
                                    <!-- <button type="button" class="btn btn-primary" onclick="ultrapm_update_iplugin()" id="bulk-update">Update</button> -->
                                    <button type="button" class="btn" onclick="ultrapm_enable_auto_update_iplugin()" id="bulk-enable-auto-update" style="background-color: #f3f3f3;">Enable Auto Update</button>
                                    <button type="button" class="btn" onclick="ultrapm_disable_auto_update_iplugin()" id="bulk-disable-auto-update" style="background-color: #f3f3f3;">Disable Auto Update</button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
        </div>


<script>
    $("#kt_datatable_responsive").DataTable({
        paging: false,
        order: [[1, 'asc']],
        responsive: {
            details: {
                type: 'column'
            }
        }
    });

    function ultrapm_activate_iplugin(){
        var plugins = "";
        jQuery('input[name="plugins[]"]:checked').each(function() {
            plugins += jQuery(this).val() + ",";
        });
        console.log(plugins);
        var data = {
            'action': 'ultrapm_activate_plugin_bySlugs',
            'slugs': plugins,
        };
        jQuery('#bulk-activate').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Activating...');
        jQuery('#bulk-activatee').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Activating...');
        jQuery.ajax({
            type: "POST",
            url: ultrapm_urls.ajaxurl,
            data: data,
            success: function(data) {
                jQuery('#bulk-activate').html('Activated');
                jQuery('#bulk-activatee').html('Activated');
                location.reload();
            },
            error: function() {
                jQuery('#bulk-activate').html('Activated');
                jQuery('#bulk-activatee').html('Activated');
                location.reload();
            }
        });
    }

    function ultrapm_deactivate_iplugin(){
        var plugins = "";
        jQuery('input[name="plugins[]"]:checked').each(function() {
            plugins += jQuery(this).val() + ",";
        });
        //console.log(plugins);
        var data = {
            'action': 'ultrapm_deactivate_plugin_bySlugs',
            'slugs': plugins,
        };
        jQuery('#bulk-deactivate').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deactivating...');
        jQuery('#bulk-deactivatee').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deactivating...');
        jQuery.ajax({
            type: "POST",
            url: ultrapm_urls.ajaxurl,
            data: data,
            success: function(data) {
                jQuery('#bulk-deactivate').html('Deactivated');
                jQuery('#bulk-deactivatee').html('Deactivated');
                location.reload();
            },
            error: function() {
                jQuery('#bulk-deactivate').html('Deactivated');
                jQuery('#bulk-deactivatee').html('Deactivated');
                location.reload();
            }
        });
    }

    function ultrapm_deactivate_and_delete_iplugin() {
        function deactivatePlugins(plugins, callback) {
            var data = {
                'action': 'ultrapm_deactivate_plugin_bySlugs',
                'slugs': plugins.join(','),
            };
        
            jQuery('#bulk-deactivate').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deactivating...');
            jQuery('#bulk-deactivatee').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deactivating...');
        
            jQuery.ajax({
                type: "POST",
                url: ultrapm_urls.ajaxurl,
                data: data,
                success: function (data) {
                    jQuery('#bulk-deactivate').html('Deactivated');
                    jQuery('#bulk-deactivatee').html('Deactivated');
                    callback();
                },
                error: function () {
                    jQuery('#bulk-deactivate').html('Deactivated');
                    jQuery('#bulk-deactivatee').html('Deactivated');
                    callback();
                }
            });
        }
    
        function deletePlugins(plugins) {
            var ajaxRequests = [];
        
            jQuery('#bulk-delete').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...');
            jQuery('#bulk-deletee').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...');
        
            for (var i = 0; i < plugins.length; i++) {
                var data = {
                    'action': 'ultrapm_delete_plugin_byFile',
                    'file': plugins[i],
                };
            
                ajaxRequests.push(
                    jQuery.ajax({
                        type: "POST",
                        url: ultrapm_urls.ajaxurl,
                        data: data,
                        success: function (data) {
                            //
                        },
                        error: function () {
                            //
                        }
                    })
                );
            }
        
            jQuery.when.apply(jQuery, ajaxRequests).then(function () {
            
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
                    "timeOut": "2000",
                    "extendedTimeOut": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };
                toastr.success("Plugin Deleted", "Success");
            
                location.reload(true);
            });
        }
    
        function deactivateAndDeletePlugins() {
            var pluginsToDeactivate = [];
            jQuery('input[name="plugins[]"]:checked').each(function () {
                pluginsToDeactivate.push(jQuery(this).val());
            });
        
            deactivatePlugins(pluginsToDeactivate, function () {
                deletePlugins(pluginsToDeactivate);
            });
        }
    
        deactivateAndDeletePlugins();
    }



    function ultrapm_check_update_iplugin() {
        var data = {
            'action': 'check-update',
            '_wpnonce': '<?php echo wp_create_nonce( 'bulk-plugins' ); ?>',
            '_wp_http_referer': '<?php echo urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ); ?>'
        };
        jQuery('#bulk-check-update').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Checking...');
        jQuery('#bulk-check-updatee').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Checking...');
        jQuery.post('<?php echo admin_url( 'plugins.php' ); ?>', data, function(response) {
            if ( response ) {
                window.location.reload();
            }
        });
    }


    //function ultrapm_update_iplugins(){
        //var plugins = [];
        //jQuery('input[name="plugins[]"]:checked').each(function() {
            //plugins.push(jQuery(this).val());
        //});
        //var data = {
            //'action': 'update-selected',
            //'checked': plugins,
            //'_wpnonce': '<?php echo wp_create_nonce( 'bulk-plugins' ); ?>',
            //'_wp_http_referer': '<?php echo urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ); ?>'
        //};
        //jQuery('#bulk-update').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...');
        //jQuery.get('<?php echo admin_url( 'plugins.php' ); ?>', data, function(response) {
            //if ( response ) {
                ////window.location.reload();
            //}
        //});
    //}

    function ultrapm_enable_auto_update_iplugin(){
        var plugins = [];
        jQuery('input[name="plugins[]"]:checked').each(function() {
            plugins.push(jQuery(this).val());
        });
        var data = {
            'action': 'enable-auto-update-selected',
            'checked': plugins,
            '_wpnonce': '<?php echo wp_create_nonce( 'bulk-plugins' ); ?>',
            '_wp_http_referer': '<?php echo urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ); ?>'
        };
        jQuery('#bulk-enable-auto-update').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enabling...');
        jQuery('#bulk-enable-auto-updatee').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enabling...');
        jQuery.post('<?php echo admin_url( 'plugins.php' ); ?>', data, function(response) {
            if ( response ) {
                window.location.reload();
            }
        });
    }

    function ultrapm_disable_auto_update_iplugin(){
        var plugins = [];
        jQuery('input[name="plugins[]"]:checked').each(function() {
            plugins.push(jQuery(this).val());
        });
        var data = {
            'action': 'disable-auto-update-selected',
            'checked': plugins,
            '_wpnonce': '<?php echo wp_create_nonce( 'bulk-plugins' ); ?>',
            '_wp_http_referer': '<?php echo urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ); ?>'
        };
        jQuery('#bulk-disable-auto-update').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Disabling...');
        jQuery('#bulk-disable-auto-updatee').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Disabling...');
        jQuery.post('<?php echo admin_url( 'plugins.php' ); ?>', data, function(response) {
            if ( response ) {
                window.location.reload();
            }
        });
    }
</script>













    </div>
</div>
<style>
    .rounded-checkbox {
        border-radius: 50%;
        width: 1.5em;
        height: 1.5em;
        vertical-align: middle;
        margin: 0 0.5em 0 0;
    }
    .rounded-checkbox:checked {
        background: #007bff;
        
    }
    .rounded-checkbox:focus {
        outline: none;
    }
    .rounded-checkbox:checked:after {
        content: '';
        display: block;
        width: 0.5em;
        height: 0.5em;
        margin: 0.5em;
        background: white;
        border-radius: 50%;
    }
    .rounded-checkbox:disabled {
        background: #e9ecef;
    }
    .rounded-checkbox:disabled:checked:after {
        background: #adb5bd;
    }
</style>



<?php
include( ULTRAPM_INC_PATH . '/part/modal.php');
include( ULTRAPM_INC_PATH . '/part/search-engine.php');
include( ULTRAPM_INC_PATH . '/part/content-end.php');
include( ULTRAPM_INC_PATH . '/part/task-list.php');
include( ULTRAPM_INC_PATH . '/part/scripts.php');
}
?>