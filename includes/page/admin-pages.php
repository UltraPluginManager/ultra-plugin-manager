<?php

if( is_admin() ) {

    function remove_admin_footer_text() {
        $current_screen = get_current_screen();
        if ($current_screen && $current_screen->base === 'toplevel_page_ultrapm-admin') {
            echo '';
        }
    }
    add_filter('admin_footer_text', 'remove_admin_footer_text');
	require_once( ULTRAPM_PATH . 'includes/methods.php' );

}

function ultrapm_admin(){

    //require_once( 'upload-zip.php' );
	$logo = plugins_url() . '/ultra-plugin-manager/assets/icons/ultra-plugin-manager-logo-21px.png';

    $installedPlugins = get_plugins();
    $installedThemes = wp_get_themes();
    $trecplug = 0;
    $tpoptheme = 0;
    $tpoplug = 0;

    $lastUpdate = get_option('ultrapm_popular_themes_last_update');
    $lastPopularPluginUpdate = get_option('ultrapm_popular_plugins_last_update');
    $lastRecommendedPluginUpdate = get_option('ultrapm_recommended_plugins_last_update');

    if ( ! $lastUpdate || ( time() - $lastUpdate ) > 900 ) {
        $popularThemesURL = 'https://api.wordpress.org/themes/info/1.2/?action=query_themes&request[browse]=popular&request[per_page]=10&request[page]=1';
        $dataPopularThemes = file_get_contents($popularThemesURL);
        $dataPopularThemes = json_decode($dataPopularThemes, true);
        update_option('ultrapm_popular_themes', $dataPopularThemes);
        update_option('ultrapm_popular_themes_last_update', time());
    } else {
        $dataPopularThemes = get_option('ultrapm_popular_themes');
    }
    if ( ! $lastPopularPluginUpdate || ( time() - $lastPopularPluginUpdate ) > 900 ) {
        $popularPluginsURL = 'https://api.wordpress.org/plugins/info/1.2/?action=query_plugins&request[browse]=popular&request[per_page]=10&request[page]=1';
        $dataPopularPlugins = file_get_contents($popularPluginsURL);
        $dataPopularPlugins = json_decode($dataPopularPlugins, true);
        update_option('ultrapm_popular_plugins', $dataPopularPlugins);
        update_option('ultrapm_popular_plugins_last_update', time());
    } else {
        $dataPopularPlugins = get_option('ultrapm_popular_plugins');
    }
    if ( ! $lastRecommendedPluginUpdate || ( time() - $lastRecommendedPluginUpdate ) > 900 ) {
        $recommendedPluginsURL = 'https://api.wordpress.org/plugins/info/1.2/?action=query_plugins&request[browse]=recommended&request[per_page]=10&request[page]=1';
        $dataRecommendedPlugins = file_get_contents($recommendedPluginsURL);
        $dataRecommendedPlugins = json_decode($dataRecommendedPlugins, true);
        update_option('ultrapm_recommended_plugins', $dataRecommendedPlugins);
        update_option('ultrapm_recommended_plugins_last_update', time());
    } else {
        $dataRecommendedPlugins = get_option('ultrapm_recommended_plugins');
    }
/*
    foreach ($dataPopularPlugins['plugins'] as $key => $plugin) {
        $pluginSlug = $plugin['slug'];
        $cek = is_plugin_installed(strtolower($pluginSlug));
        if($cek) {
            unset($dataPopularPlugins['plugins'][$key]);
        } else {
            $tpoplug++;
        }
        if($tpoplug >10) {
            unset($dataPopularPlugins['plugins'][$key]);
        }
    }
    foreach ($dataRecommendedPlugins['plugins'] as $key => $plugin) {
        $pluginSlug = $plugin['slug'];
        $cek = is_plugin_installed(strtolower($pluginSlug));
        if($cek) {
            unset($dataRecommendedPlugins['plugins'][$key]);
        } else {
            $trecplug++;
        }
        if($trecplug >10) {
            unset($dataRecommendedPlugins['plugins'][$key]);
        }
    }
    foreach ($dataPopularThemes['themes'] as $key => $theme) {
        $themeSlug = $theme['slug'];
        $cek = is_theme_installed($themeSlug);
        if($cek) {
            unset($dataPopularThemes['themes'][$key]);
        } else {
            $tpoptheme++;
        }
        if($tpoptheme >10) {
            unset($dataPopularThemes['themes'][$key]);
        }
    }
*/
    usort($dataPopularThemes['themes'], function ($a, $b) {
        return $b['rating'] - $a['rating'];
    });
    usort($dataPopularPlugins['plugins'], function ($a, $b) {
        return $b['rating'] - $a['rating'];
    });
    usort($dataRecommendedPlugins['plugins'], function ($a, $b) {
        return $b['rating'] - $a['rating'];
    });



?>

				<!--begin::Container-->
				<?php 
					include( ULTRAPM_INC_PATH . '/part/content-start.php');
					include( ULTRAPM_INC_PATH . '/part/header.php'); 
				?>				

					<!--begin::Post-->
					<div class="content flex-row-fluid" id="kt_content">
						<!--begin::Row-->
						<div class="row g-5 g-xl-8">
							<!--begin::Col-->
							<div class="col-xl-6">
								<!--begin::Tables Widget 4-->
								<div class="card">
									<!--begin::Header-->
									<div class="card-header border-0 pt-5">
										<h3 class="card-title align-items-start flex-column">
											<span class="card-label fw-bold fs-3 mb-1">Most Popular Themes</span>
										</h3>
									</div>
									<!--end::Header-->
									<!--begin::Body-->
									<div class="card-body py-3">
										<div class="tab-content">
											<!--begin::Tap pane-->
											<div class="tab-pane fade show active" id="kt_table_widget_4_tab_1">
												<!--begin::Table container scrollable-->
												<div class="table-responsive">
													<!--begin::Table-->
													<table class="table align-middle gs-0 gy-3">
														<!--begin::Table head-->
														<thead>
															<tr>
																<th class="p-0 w-50px"></th>
																<th class="p-0 min-w-150px"></th>
																<th class="p-0 min-w-140px"></th>
																<th class="p-0 min-w-120px"></th>
															</tr>
														</thead>
														<!--end::Table head-->
														<!--begin::Table body-->
														<tbody>
                                                        <?php
                                                            foreach ($dataPopularThemes['themes'] as $theme) {
                                                                $themeName = $theme['name'];
                                                                if(strlen($themeName) > 18) {
                                                                    $themeName = substr($theme['name'], 0, 15) . '...';
                                                                }
                                                                $themeSlug = $theme['slug'];
                                                                if(isset($theme['download_link'])) {
                                                                    $themeDownloadLink = $theme['download_link'];
                                                                } else {
                                                                    $themeDownloadLink = $theme['slug'];
                                                                }
                                                                $themeVersion = $theme['version'];
                                                                $themeAuthor = $theme['author']['display_name'];
                                                                if(strlen($themeAuthor) > 18) {
                                                                    $themeAuthor = substr($theme['author']['display_name'], 0, 15) . '...';
                                                                }
                                                                $themeRating = $theme['rating'] / 20;
                                                                $themeDescription = $theme['description'];
                                                                $themeIcon = $theme['screenshot_url'];
                                                                $type = 'theme';
                                                        ?>
															<tr>
																<td>
																	<div class="symbol symbol-50px" data-bs-target="#<?php echo $themeSlug; ?>-modal" data-bs-toggle="modal" style="cursor: pointer;">
																		<img src="<?php echo $themeIcon; ?>" alt="image" />
																	</div>
																</td>
																<td>
																	<a href="#" class="text-dark fw-bold text-hover-primary mb-1 fs-6" data-bs-target="#<?php echo $themeSlug; ?>-modal" data-bs-toggle="modal" style="cursor: pointer;"><?php echo $themeName; ?></a>
																	<span class="text-muted fw-semibold d-block fs-7"><?php echo $themeAuthor; ?></span>
																</td>
																<td>
																	<span class="text-muted fw-semibold d-block fs-7">Rating</span>
																	<div class="rating">
                                                                        <?php
                                                                            for ($i = 1; $i <= 5; $i++) {
                                                                                if ($i <= $themeRating) {
                                                                                    echo '<div class="rating-label checked"><i class="ki-duotone ki-star fs-6"></i></div>';
                                                                                } else {
                                                                                    echo '<div class="rating-label"><i class="ki-duotone ki-star fs-6"></i></div>';
                                                                                }
                                                                            }
                                                                        ?>
																	</div>
																</td>
																<td class="text-center">
                                                                    <?php
                                                                        $cek = is_theme_installed($themeSlug);
                                                                        if($cek) {
																			echo '<a style="cursor: pointer; color: #0073aa;" onclick="showInstalleThemeSwal()">Installed</a>';
                                                                        } else {
                                                                            echo '<a style="margin-bottom: 5px; cursor: pointer; color: #0073aa" onclick="ultrapm_addToTaskList(\'' . $theme['name'] . '\', \'' . $themeSlug . '\', \'' . $themeDownloadLink . '\', \'' . $type . '\')" style="cursor: pointer;">Add To List &nbsp;</a>';
                                                                            echo '<a style="cursor: pointer; color: #0073aa" onclick="ultrapm_crut_ajax_install_theme(\'' . $themeSlug . '\')"> Install</a>';
                                                                        }
                                                                    ?>
																</td>
															</tr>
                                                        <?php
                                                            }
                                                        ?>
														</tbody>
														<!--end::Table body-->
													</table>
												</div>
												<!--end::Table-->
											</div>
											<!--end::Tap pane-->
										</div>
									</div>
									<!--end::Body-->
								</div>
								<!--end::Tables Widget 4-->
							</div>
							<!--end::Col-->
							<!--begin::Col-->
							<div class="col-xl-6">
								<!--begin::Tables Widget 4-->
								<div class="card">
									<!--begin::Header-->
									<div class="card-header border-0 pt-5">
										<h3 class="card-title align-items-start flex-column">
											<span class="card-label fw-bold fs-3 mb-1">Plugin Library</span>
										</h3>
										<div class="card-toolbar">
											<ul class="nav">
												<li class="nav-item">
													<a class="nav-link btn btn-sm btn-color-muted btn-active btn-active-light-primary active fw-bold px-4 me-1" data-bs-toggle="tab" href="#PopularPlugins">Popular</a>
												</li>
												<li class="nav-item">
													<a class="nav-link btn btn-sm btn-color-muted btn-active btn-active-light-primary fw-bold px-4 me-1" data-bs-toggle="tab" href="#RecommendedPlugins">Recommended</a>
												</li>
											</ul>
										</div>
									</div>
									<!--end::Header-->
									<!--begin::Body-->
									<div class="card-body py-3">
										<div class="tab-content">
											<!--begin::Tap pane-->
											<div class="tab-pane fade show active" id="PopularPlugins">
												<!--begin::Table container scrollable-->
												<div class="table-responsive">
													<!--begin::Table-->
													<table class="table align-middle gs-0 gy-3">
														<!--begin::Table head-->
														<thead>
															<tr>
																<th class="p-0 w-50px"></th>
																<th class="p-0 min-w-150px"></th>
																<th class="p-0 min-w-140px"></th>
																<th class="p-0 min-w-120px"></th>
															</tr>
														</thead>
														<!--end::Table head-->
														<!--begin::Table body-->
														<tbody>
                                                        <?php
                                                            foreach ($dataPopularPlugins['plugins'] as $plugin) {
                                                                $pluginName = $plugin['name'];
                                                                if(strlen($pluginName) > 18) {
                                                                    $pluginName = substr($plugin['name'], 0, 15) . '...';
                                                                }
                                                                $pluginSlug = $plugin['slug'];
                                                                $pluginSlugpure = str_replace('-', '', $pluginSlug);
                                                                $pluginVersion = $plugin['version'];
                                                                $pluginAuthor = $plugin['author'];
                                                                $authorWithoutTags = strip_tags($pluginAuthor); // Menghapus tag HTML
                                                                $shortenedAuthor = substr($authorWithoutTags, 0, 13); // Mengambil 13 karakter pertama
                                                                $pluginRating = $plugin['rating'] / 20;
                                                                $pluginDescription = $plugin['description'];
                                                                $pluginIcon = $plugin['icons']['1x'];
                                                                $pluginDownloadLink = $plugin['download_link'];
                                                                $pluginLastUpdated = $plugin['last_updated'];
                                                                $type = 'plugin';
                                                        ?>
															<tr>
																<td>
																	<div class="symbol symbol-50px" onclick="customIframe<?php echo $pluginSlugpure; ?>()" style="cursor: pointer;">
																		<img src="<?php echo $pluginIcon; ?>" alt="image" />
																	</div>
																</td>
																<td>
																	<a href="#" class="text-dark fw-bold text-hover-primary mb-1 fs-6" onclick="customIframe<?php echo $pluginSlugpure; ?>()" style="cursor: pointer;"><?php echo $pluginName; ?></a>
																	<span class="text-muted fw-semibold d-block fs-7" data-bs-toggle="modal" data-bs-target="#<?php echo $pluginSlug; ?>-modal"><?php echo $shortenedAuthor; ?></span>
																</td>
																<td>
																	<span class="text-muted fw-semibold d-block fs-7">Rating</span>
																	<div class="rating">
                                                                        <?php
                                                                            for ($i = 1; $i <= 5; $i++) {
                                                                                if ($i <= $pluginRating) {
                                                                                    echo '<div class="rating-label checked"><i class="ki-duotone ki-star fs-6"></i></div>';
                                                                                } else {
                                                                                    echo '<div class="rating-label"><i class="ki-duotone ki-star fs-6"></i></div>';
                                                                                }
                                                                            }
                                                                        ?>
																	</div>
																</td>
																<td class="text-center">
																	<?php
																		$cek = is_plugin_installed(strtolower($pluginSlug));
                                                                        if($cek) {
                                                                            echo '<a style="cursor: pointer; color: #0073aa;" onclick="uninstallPluginSwalAsk' . $pluginSlugpure . '()" style="margin-bottom: 5px;">Uninstall</a>';
                                                                        } else {
                                                                            echo '<a style="cursor: pointer; color: #0073aa;" onclick="ultrapm_addToTaskList(\'' . $plugin['name'] . '\', \'' . $pluginSlug . '\', \'' . $pluginDownloadLink . '\', \'' . $type . '\')">Add To List &nbsp;</a>';
                                                                            echo '<a style="cursor: pointer; color: #0073aa;" onclick="ultrapm_crut_ajax_install_plugin(\'' . $pluginSlug . '\', \'false\')"> Install</a>';
                                                                        }
                                                                    ?>
																</td>
															</tr>
                                                        <?php
                                                            }
                                                        ?>
														</tbody>
														<!--end::Table body-->
													</table>
												</div>
												<!--end::Table-->
											</div>
											<!--end::Tap pane-->
											<!--begin::Tap pane-->
											<div class="tab-pane fade" id="RecommendedPlugins">
												<!--begin::Table container scrollable-->
												<div class="table-responsive">
													<!--begin::Table-->
													<table class="table align-middle gs-0 gy-3">
														<!--begin::Table head-->
														<thead>
															<tr>
																<th class="p-0 w-50px"></th>
																<th class="p-0 min-w-150px"></th>
																<th class="p-0 min-w-140px"></th>
																<th class="p-0 min-w-120px"></th>
															</tr>
														</thead>
														<!--end::Table head-->
														<!--begin::Table body-->
														<tbody>
                                                        <?php
                                                            foreach ($dataRecommendedPlugins['plugins'] as $plugin) {
                                                                $pluginName = $plugin['name'];
                                                                if(strlen($pluginName) > 18) {
                                                                    $pluginName = substr($plugin['name'], 0, 15) . '...';
                                                                }
                                                                $pluginSlug = $plugin['slug'];
                                                                $pluginSlugpure = str_replace('-', '', $pluginSlug);
                                                                $pluginVersion = $plugin['version'];
                                                                $pluginAuthor = $plugin['author'];
                                                                $authorWithoutTags = strip_tags($pluginAuthor);
                                                                $shortenedAuthor = substr($authorWithoutTags, 0, 13);
                                                                $pluginRating = $plugin['rating'] / 20;
                                                                $pluginDescription = $plugin['description'];
                                                                if(isset($plugin['icons']['default'])) {
                                                                    $pluginIcon = $plugin['icons']['default'];
                                                                }
                                                                if(isset($plugin['icons']['1x'])) {
                                                                    $pluginIcon = $plugin['icons']['1x'];
                                                                }
                                                                if(isset($plugin['icons']['svg'])) {
                                                                    $pluginIcon = $plugin['icons']['svg'];
                                                                }
                                                                $pluginDownloadLink = $plugin['download_link'];
                                                                $pluginLastUpdated = $plugin['last_updated'];
                                                                $type = 'plugin';
                                                        ?>
															<tr>
																<td>
																	<div class="symbol symbol-50px" onclick="customIframe<?php echo $pluginSlugpure; ?>()" style="cursor: pointer;">
																		<img src="<?php echo $pluginIcon; ?>" alt="image" />
																	</div>
																</td>
																<td>
																	<a onclick="customIframe<?php echo $pluginSlugpure; ?>()" class="text-dark fw-bold text-hover-primary mb-1 fs-6" style="cursor: pointer;"><?php echo $pluginName; ?></a>
																	<span class="text-muted fw-semibold d-block fs-7"><?php echo $shortenedAuthor; ?></span>
																</td>
																<td>
																	<span class="text-muted fw-semibold d-block fs-7">Rating</span>
																	<div class="rating">
                                                                        <?php
                                                                            for ($i = 1; $i <= 5; $i++) {
                                                                                if ($i <= $pluginRating) {
                                                                                    echo '<div class="rating-label checked"><i class="ki-duotone ki-star fs-6"></i></div>';
                                                                                } else {
                                                                                    echo '<div class="rating-label"><i class="ki-duotone ki-star fs-6"></i></div>';
                                                                                }
                                                                            }
                                                                        ?>
																	</div>
																</td>
																<td class="text-center">
																	<?php
																		$cek = is_plugin_installed(strtolower($pluginSlug));
                                                                        if($cek) {
                                                                            echo '<a style="cursor: pointer; color: #0073aa;" onclick="uninstallPluginSwalAsk' . $pluginSlugpure . '()" style="margin-bottom: 5px;">Uninstall</a>';
                                                                        } else {
                                                                            echo '<a style="cursor: pointer; color: #0073aa;" onclick="ultrapm_addToTaskList(\'' . $plugin['name'] . '\', \'' . $pluginSlug . '\', \'' . $pluginDownloadLink . '\', \'' . $type . '\')">Add To List &nbsp;</a>';
                                                                            echo '<a style="cursor: pointer; color: #0073aa;" onclick="ultrapm_crut_ajax_install_plugin(\'' . $pluginSlug . '\', \'false\')"> Install</a>';
                                                                        }
                                                                    ?>
																</td>
															</tr>
                                                        <?php
                                                            }
                                                        ?>
														</tbody>
														<!--end::Table body-->
													</table>
												</div>
												<!--end::Table-->
											</div>
											<!--end::Tap pane-->
										</div>
									</div>
									<!--end::Body-->
								</div>
								<!--end::Tables Widget 4-->
							</div>
							<!--end::Col-->
						</div>
						<!--end::Row-->
                    </div>
                    <!--end::Post-->

                    <?php
                    foreach($dataPopularThemes['themes'] as $dtheme) {
                        $themeName = $dtheme['name'];
                        $themeSlug = $dtheme['slug'];
                        $themeVersion = $dtheme['version'];
                        $themeAuthor = $dtheme['author']['display_name'];
                        $themeAuthorURI = $dtheme['author']['profile'];
                        $themeDescription = $dtheme['description'];
                        $themeScreenshot = $dtheme['screenshot_url'];
                        $themeThemeURI = $dtheme['preview_url'];
                        if(isset($dtheme['template'])) {
                            $is_parent = true;
                            $themeTemplate = $dtheme['template'];
                        } else {
                            $is_parent = false;
                            $themeTemplate = '';
                        }
                        $type = 'theme';
                        $is_installed = is_theme_installed($themeSlug);
                    ?>
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
                                    <?php if($is_installed){ ?>
                                        <a href="<?php echo admin_url( 'customize.php?theme=' . $themeSlug ); ?>" target="_blank" class="btn btn-info btn-active-light-primary">Live Preview</a>
                                    <?php } ?>
                                    <!-- Add To List -->
                                </div>
                            </div>
                            <!--end::Modal content-->
                        </div>
                        <!--end::Modal dialog-->
                    </div>
                    <?php
                    }
                    ?>

                    <?php
                    foreach($dataPopularPlugins['plugins'] as $dpoplug) {
                        $pluginName = $dpoplug['name'];
                        $pluginSlug = $dpoplug['slug'];
                        $pluginSlugpure = str_replace('-', '', $pluginSlug);
                        $infoPlugin = get_option('ultrapm_info_plugin_' . $pluginSlug);
                        if(!is_array($infoPlugin)) {
                            $infoPlugin = ultrapm_get_infoSlug($pluginSlug, 'plugin');
                        }
                        ?>
                        <div class="modal fade" tabindex="-1" aria-hidden="true" id="<?php echo $pluginSlug; ?>-modal">
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
                                                    <a class="nav-link active" data-bs-toggle="tab" href="#kt_vtab_pane_1_<?php echo $pluginSlug; ?>" aria-selected="true" role="tab">Details</a>
                                                </li>
                                                <li class="nav-item w-md-200px me-0" role="presentation">
                                                    <a class="nav-link" data-bs-toggle="tab" href="#kt_vtab_pane_2_<?php echo $pluginSlug; ?>" aria-selected="false" role="tab" tabindex="-1">Changelogs</a>
                                                </li>
                                            </ul>

                                            <div class="tab-content" id="myTabContent_<?php echo $pluginSlug; ?>">
                                                <div class="tab-pane fade active show" id="kt_vtab_pane_1_<?php echo $pluginSlug; ?>" role="tabpanel">
                                                    <div class="d-flex flex-column">
                                                        <li class="d-flex align-items-center py-2"><span class="bullet bg-primary"></span><strong>&nbsp;Author:</strong> <?php echo $infoPlugin['author']; ?></li>
                                                        <li class="d-flex align-items-center py-2"><span class="bullet bg-primary"></span><strong>&nbsp;Version:</strong> <?php echo $infoPlugin['version']; ?></li>
                                                        <li class="d-flex align-items-center py-2"><span class="bullet bg-primary"></span><strong>&nbsp;Last Updated:</strong> <?php echo $infoPlugin['last_updated']; ?></li>
                                                        <li class="d-flex align-items-center py-2"><span class="bullet bg-primary"></span><strong>&nbsp;Requires WordPress Version:</strong> <?php echo $infoPlugin['requires']; ?></li>
                                                        <li class="d-flex align-items-center py-2"><span class="bullet bg-primary"></span><strong>&nbsp;Tested WordPress Version:</strong> <?php echo $infoPlugin['tested']; ?></li>
                                                        <li class="d-flex align-items-center py-2"><span class="bullet bg-primary"></span><strong>&nbsp;Requires PHP Version:</strong> <?php echo $infoPlugin['requires_php']; ?></li>
                                                        <li class="d-flex align-items-center py-2"><span class="bullet bg-primary"></span><strong>&nbsp;Active Installs:</strong> <?php echo $infoPlugin['active_installs']; ?></li>
                                                        <li class="d-flex align-items-center py-2"><span class="bullet bg-primary"></span><strong>&nbsp;Homepage:</strong> <a href="<?php echo $infoPlugin['homepage']; ?>" target="_blank"><?php echo $infoPlugin['homepage']; ?></a></li>
                                                    </div>
                                                </div>

                                                <div class="tab-pane fade" id="kt_vtab_pane_2_<?php echo $pluginSlug; ?>" role="tabpanel">
                                                    <?php 
                                                    if(isset($infoPlugin['sections']['changelog'])) {
                                                        $html = $infoPlugin['sections']['changelog'];
                                                        $dom = new DOMDocument;
                                                        libxml_use_internal_errors(true);
                                                        $dom->loadHTML($html);
                                                        $pElements = $dom->getElementsByTagName('p');
                                                        $pList = array();
                                                        foreach ($pElements as $p) {
                                                            $pList[] = $p->nodeValue;
                                                        }
                                                        foreach ($pList as $p) {
                                                            echo '<p>' . $p . '</p>';
                                                        }
                                                    }
                                                    ?>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Modal body-->
                                </div>
                                <!--end::Modal content-->
                            </div>
                            <!--end::Modal dialog-->
                        </div>
                        <div class="modal fade" tabindex="-1" aria-hidden="true" id="<?php echo $pluginSlug; ?>-changelog">
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
                        <script>
                            function customIframe<?php echo $pluginSlugpure; ?>(){
                                url = '<?php echo admin_url('plugin-install.php?tab=plugin-information&plugin=' . $pluginSlug . '&TB_iframe=true&width=772&height=679'); ?>';
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
                                    // open modal
                                    jQuery('#<?php echo $pluginSlug; ?>-changelog').modal('show');
                                    var data = data.replace(/<div\s+id=["']plugin-information-footer["'][\s\S]*?<\/div>/gi, '<div id="plugin-information-footer" style="display: none;"></div>');
                                    var iframe = document.getElementById('TB_iframeContent<?php echo $pluginSlugpure; ?>');
                                    iframe.contentWindow.document.open();
                                    iframe.contentWindow.document.write(data);
                                    iframe.contentWindow.document.close();
                                    iframe.style.display = 'block';
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
                                    reverseButtons: true
                                }).then((result) => {
                                    if (result.isConfirmed) {
    	                                const loadingEl = document.createElement("div");
    	                                document.body.prepend(loadingEl);
    	                                loadingEl.classList.add("page-loader");
    	                                loadingEl.classList.add("flex-column");
    	                                loadingEl.classList.add("bg-dark");
    	                                loadingEl.classList.add("bg-opacity-25");
		                                loadingEl.setAttribute("id", "loadingEl");
    	                                loadingEl.innerHTML = `
        	                                <span class="spinner-border text-primary" role="status"></span>
        	                                <span class="text-gray-800 fs-6 fw-semibold mt-5" id="ultrapminsproc">Uninstalling <?php echo $pluginName; ?>...</span>
    	                                `;
    	                                KTApp.showPageLoading();
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
				                                        KTApp.hidePageLoading();
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
				                                        KTApp.hidePageLoading();
                                                        Swal.fire(
                                                            'Error!',
                                                            'Failed to uninstall <?php echo $pluginName; ?>',
                                                            'error'
                                                        );
                                                    }
                                                });
                                            },
                                            error: function() {
				                                KTApp.hidePageLoading();
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

				<?php
                include( ULTRAPM_INC_PATH . '/part/modal.php');
				include( ULTRAPM_INC_PATH . '/part/search-engine.php');
				include( ULTRAPM_INC_PATH . '/part/content-end.php');
				include( ULTRAPM_INC_PATH . '/part/task-list.php');
                include( ULTRAPM_INC_PATH . '/part/scripts.php');
				?>

<?php
}
?>