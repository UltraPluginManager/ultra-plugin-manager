<?php
$current_page = $_GET['page'];
?>
<div class="d-flex flex-column flex-root" style="background-color: #f5f6f8;">
	<div class="page d-flex flex-row flex-column-fluid">
		<div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
                    <div id="kt_header" class="header" data-kt-sticky="true" data-kt-sticky-name="header" data-kt-sticky-offset="{default: '200px', lg: '300px'}">
						
						<div class="separator"></div>
						<div class="header-menu-container d-flex flex-stack h-lg-75px w-100" id="kt_header_nav">
							<div class="d-flex align-items-center me-5">
								<div class="d-lg-none btn btn-icon btn-active-color-primary w-30px h-30px ms-n2 me-3" id="kt_header_menu_toggle">
									<i class="ki-duotone ki-abstract-14 fs-2">
										<span class="path1"></span>
										<span class="path2"></span>
									</i>
								</div>
								<a href="<?php echo admin_url('admin.php?page='. ULTRAPM_SLUG_ADMIN); ?>" class="d-flex align-items-center" style="color: #FF5722; font-size: 22px; font-weight: 500;">
									Ultra Plugin Manager
								</a>
							</div>
							<div class="d-flex align-items-center flex-shrink-0">

								<div class="flex-shrink-0 p-4 p-lg-0 me-lg-2">
									<form data-kt-search-element="form" class="d-none d-lg-block w-100 position-relative mb-2 mb-lg-0 ms-auto" action="<?php echo admin_url('admin.php?page='. ULTRAPM_SLUG_SEARCH_RESULT); ?>" method="post" autocomplete="off">
                        				<input type="hidden" name="action" value="ultrapm_search" />
										<input type="hidden" name="type" value="plugin" />
										<input type="hidden" />
										<i class="ki-duotone ki-magnifier fs-2 text-gray-700 position-absolute top-50 translate-middle-y ms-4">
											<span class="path1"></span>
											<span class="path2"></span>
										</i>
										<input type="text" class="form-control bg-transparent ps-13 fs-7 h-40px" name="keywords" value="" placeholder="Plugin Search" data-kt-search-element="input" />
										<span class="position-absolute top-50 end-0 translate-middle-y lh-0 d-none me-5" data-kt-search-element="spinner">
											<span class="spinner-border h-15px w-15px align-middle text-gray-400"></span>
										</span>
										<span class="btn btn-flush btn-active-color-primary position-absolute top-50 end-0 translate-middle-y lh-0 d-none me-4" data-kt-search-element="clear">
											<i class="ki-duotone ki-cross fs-2 fs-lg-1 me-0">
												<span class="path1"></span>
												<span class="path2"></span>
											</i>
										</span>
									</form>
								</div>
								<div class="flex-shrink-0 p-4 p-lg-0 me-lg-2">
									<form data-kt-search-element="form" class="d-none d-lg-block w-100 position-relative mb-2 mb-lg-0 ms-auto" action="<?php echo admin_url('admin.php?page='. ULTRAPM_SLUG_SEARCH_RESULT); ?>" method="post" autocomplete="off">
                        				<input type="hidden" name="action" value="ultrapm_search" />
										<input type="hidden" name="type" value="theme" />
										<input type="hidden" />
										<i class="ki-duotone ki-magnifier fs-2 text-gray-700 position-absolute top-50 translate-middle-y ms-4">
											<span class="path1"></span>
											<span class="path2"></span>
										</i>
										<input type="text" class="form-control bg-transparent ps-13 fs-7 h-40px" name="keywords" value="" placeholder="Theme Search" data-kt-search-element="input" />
										<span class="position-absolute top-50 end-0 translate-middle-y lh-0 d-none me-5" data-kt-search-element="spinner">
											<span class="spinner-border h-15px w-15px align-middle text-gray-400"></span>
										</span>
										<span class="btn btn-flush btn-active-color-primary position-absolute top-50 end-0 translate-middle-y lh-0 d-none me-4" data-kt-search-element="clear">
											<i class="ki-duotone ki-cross fs-2 fs-lg-1 me-0">
												<span class="path1"></span>
												<span class="path2"></span>
											</i>
										</span>
									</form>
								</div>
								<div class="flex-shrink-0 p-4 p-lg-0 me-lg-2">
									<button type="button" class="btn btn-flex" data-bs-toggle="modal" data-bs-target="#upload-zip-modal" style="background-color: #5376a9; padding: calc(.574em + 1px) calc(1.5rem + 1px); color: white;">
									<i class="ki-duotone ki-folder-up fs-2">
										<span class="path1"></span>
										<span class="path2"></span>
									</i>Upload Zip File(s)</button>
									<button type="button" class="btn btn-flex me-3"  data-bs-toggle="modal" data-bs-target="#searchengine" style="background-color: #5376a9; padding: calc(.574em + 1px) calc(1.5rem + 1px); color: white;">
									<i class="ki-duotone ki-fasten fs-2">
										<span class="path1"></span>
										<span class="path2"></span>
									</i>Enter URL</button>
								</div>

							</div>


							
						</div>
					</div>


						<div class="container-xxl d-flex flex-grow-1 flex-stack">
							<div class="d-flex align-items-left flex-shrink-0">
								<div class="menu menu-rounded menu-column menu-lg-row menu-root-here-bg-desktop menu-active-bg menu-state-primary menu-title-gray-800 menu-arrow-gray-400 align-items-stretch flex-grow-1 my-5 my-lg-0 px-2 px-lg-0 fw-semibold fs-6" id="#kt_header_menu" data-kt-menu="true">
									<?php
									if($current_page == ULTRAPM_SLUG_ADMIN){
										$active = 'here show menu-here-bg';
									}else{
										$active = '';
									}
									?>
									<div class="menu-item menu-lg-down-accordion me-0 me-lg-2 <?php echo $active; ?>">
										<span class="menu-link py-3" onclick="window.location.href='<?php echo admin_url('admin.php?page='. ULTRAPM_SLUG_ADMIN); ?>'">
											<span class="menu-title">Dashboards</span>
											<span class="menu-arrow d-lg-none"></span>
										</span>
									</div>
									<?php
									if($current_page == ULTRAPM_SLUG_INSTALLED_APPS){
										$active = 'here show menu-here-bg';
									}else{
										$active = '';
									}
									?>
									<div class="menu-item menu-lg-down-accordion me-0 me-lg-2 <?php echo $active; ?>">
										<span class="menu-link py-3" onclick="window.location.href='<?php echo admin_url('admin.php?page='. ULTRAPM_SLUG_INSTALLED_APPS); ?>'">
											<span class="menu-title">Installed Ones</span>
											<span class="menu-arrow d-lg-none"></span>
										</span>
									</div>
									<?php
									if($current_page == ULTRAPM_SLUG_MUST_HAVE){
										$active = 'here show menu-here-bg';
									}else{
										$active = '';
									}
									?>
									<div class="menu-item menu-lg-down-accordion me-0 me-lg-2 <?php echo $active; ?>">
										<span class="menu-link py-3" onclick="window.location.href='<?php echo admin_url('admin.php?page='. ULTRAPM_SLUG_MUST_HAVE); ?>'">
											<span class="menu-title">Must Have</span>
											<span class="menu-arrow d-lg-none"></span>
										</span>
									</div>
									<?php
									if($current_page == ULTRAPM_SLUG_SEARCH_RESULT){
										$active = 'here show menu-here-bg';
									}else{
										$active = '';
									}
									?>
									<div class="menu-item menu-lg-down-accordion me-0 me-lg-2 <?php echo $active; ?>">
										<span class="menu-link py-3" onclick="window.location.href='<?php echo admin_url('admin.php?page='. ULTRAPM_SLUG_SEARCH_RESULT); ?>'">
											<span class="menu-title">Search Result</span>
											<span class="menu-arrow d-lg-none"></span>
										</span>
									</div>
								</div>
							</div>
						</div>











		</div>
	</div>
</div>






					