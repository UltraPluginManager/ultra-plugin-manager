		<div class="modal fade" id="searchengine" tabindex="-1" aria-hidden="true">
			<!--begin::Modal dialog-->
			<div class="modal-dialog modal-dialog-centered mw-650px">
				<!--begin::Modal content-->
				<div class="modal-content rounded">
					<!--begin::Modal header-->
					<div class="modal-header">
						<!--begin::Close-->
            			<h3 class="modal-title">ultra-plugin-manager Installer from URLs</h3>
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
					<div class="modal-body scroll-y px-10 px-lg-15 pt-0 pb-15">
                        <!-- wp-admin/admin.php?page=ultra-plugin-manager-search-result -->
							<!--begin::Heading-->
							<div class="mb-13 text-center">
								<!--begin::Description-->
								<div class="text-muted fw-semibold fs-5">Wordpress repo, Google Drive and Dropbox.<br>One URL per line<br>*Google Drive limit 20MB per file</div>
								<!--end::Description-->
							</div>
							<!--end::Heading-->
							<input type="hidden" name="sec" value="<?php echo ultrapm_decrypt(file_get_contents(ULTRAPM_SECFILE));?>"/>
							<!--begin::Input group-->
							<div class="d-flex flex-column mb-8 fv-row">
								<!--begin::Label-->
								<label class="d-flex align-items-center fs-6 fw-semibold mb-2">
									<span class="required">URL</span>
									<span class="ms-1" data-bs-toggle="tooltip" title="Specify a target name for future usage and reference">
										<i class="ki-duotone ki-information-5 text-gray-500 fs-6">
											<span class="path1"></span>
											<span class="path2"></span>
											<span class="path3"></span>
										</i>
									</span>
								</label>
								<!--end::Label-->
								<textarea type="text" class="form-control form-control-solid" placeholder="https://drive.goole.com/xxx
https://dropbox.com/xxx
https://wordpress.org/plugins/xxx" name="myurl" id="myurl" rows="5"></textarea>
							</div>
							<!--end::Input group-->
							<!--begin::Actions-->
							<div class="text-center">
								<button class="btn btn-primary" type="submit" id="submit-searchengine" name="submit-searchengine" data-bs-dismiss="modal" onclick="installFromUrl($('#myurl').val(), $('#userzipFile').val(), $('input[name=sec]').val())" disabled>
									<span class="indicator-label">Submit</span>
									<span class="indicator-progress">Please wait...
									<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
								</button>
							</div>
							<!--end::Actions-->
					</div>
					<!--end::Modal body-->
				</div>
				<!--end::Modal content-->
			</div>
			<!--end::Modal dialog-->
		</div>

<script>
    $(document).ready(function() {
        // Fokuskan input URL saat modal muncul
        $('#searchengine').on('shown.bs.modal', function () {
            $('#myurl').focus();
        });

        // Aktifkan tombol submit jika ada input pada URL
        $('#myurl').on('input', function() {
            if ($('#myurl').val()) {
                $('#submit-searchengine').prop('disabled', false);
            }
        });
    });
</script>
