
<script>
	function openTaskList() {
        // klik button dengan 'data-kt-drawer-toggle="#kt_drawer_example_permanent_toggle"'
        var button = document.getElementById("kt_drawer_example_permanent_toggle");
        button.click();
    }
	<?php
		$tasklists = get_option('ultrapm_task_list');
	?>
    // Ambil elemen tabel
    var table = document.getElementById("table-task-list-data");

    // Data dari PHP
    var tasklists = <?php echo json_encode($tasklists); ?>;

    // Loop melalui data dan tambahkan baris baru ke dalam tabel
    for (var i = 0; i < tasklists.length; i++) {
        var row = table.insertRow(i + 1); // +1 untuk header

        // Nama Plugin
        var cell1 = row.insertCell(0);
        cell1.innerHTML = tasklists[i].name;
		cell1.setAttribute("name", tasklists[i].slug + "_" + tasklists[i].type);

        // Checkbox 1
        var cell2 = row.insertCell(1);
        var checkbox1 = document.createElement("input");
		checkbox1.setAttribute("name", "install");
        checkbox1.setAttribute("type", "checkbox");
        checkbox1.setAttribute("value", "1");
        checkbox1.checked = true; // Sesuaikan sesuai kebutuhan
        cell2.appendChild(checkbox1);

        // Checkbox 2
        var cell3 = row.insertCell(2);
        var checkbox2 = document.createElement("input");
		checkbox2.setAttribute("name", "activate");
        checkbox2.setAttribute("type", "checkbox");
        checkbox2.setAttribute("value", "1");
		if(tasklists[i].type == 'theme') {
        	checkbox2.checked = false;
		} else {
			checkbox2.checked = true;
		}
        cell3.appendChild(checkbox2);

		// delete action
		var cell4 = row.insertCell(3);
		var delbutton = document.createElement("i");
		delbutton.setAttribute("name", tasklists[i].slug);
		delbutton.setAttribute("vtipe", tasklists[i].type);
		delbutton.setAttribute("class", "ki-duotone ki-technology-2 fs-1 btn-strip-start");
		delbutton.setAttribute("style", "cursor: pointer; position: relative; top: 5px;");
		delbutton.setAttribute("onclick", "ultrapm_deleteTaskList(this)");
		delbutton.innerHTML = '<span class="path1"></span><span class="path2"></span></i>';
		cell4.appendChild(delbutton);
    }
</script>
<script>
    // Ambil elemen tombol "Refresh"
    var refreshButton = document.getElementById("refreshButton");

    // Ambil elemen tabel
    var table = document.getElementById("table-task-list-data");

    // Kode untuk merefresh data tabel
    function refreshTable() {
        // Di sini Anda dapat menambahkan kode untuk mengganti atau memuat data baru ke dalam tabel.
        // Contoh: Menghapus semua baris dalam tabel
        while (table.rows.length > 1) {
            table.deleteRow(1);
        }

		var datae = {
			'action': 'ultrapm_refresh_data_tasklist'
		};
		jQuery.ajax({
			type: "POST",
			url: ultrapm_urls.ajaxurl,
			data: datae,
			success: function(data) {
				data = JSON.parse(data);
				tasklists = data;
        		// Di sini tambahkan kode untuk menambahkan data baru ke dalam tabel, sama seperti pada contoh sebelumnya.
				// Contoh: Loop melalui data dan tambahkan baris baru ke dalam tabel
				for (var i = 0; i < tasklists.length; i++) {
					var row = table.insertRow(i + 1); // +1 untuk header

					// Nama Plugin
					var cell1 = row.insertCell(0);
					cell1.innerHTML = tasklists[i].name;
					cell1.setAttribute("name", tasklists[i].slug + "_" + tasklists[i].type);
				
        			// Checkbox 1
        			var cell2 = row.insertCell(1);
        			var checkbox1 = document.createElement("input");
					checkbox1.setAttribute("name", "install");
        			checkbox1.setAttribute("type", "checkbox");
        			checkbox1.setAttribute("value", "1");
        			checkbox1.checked = true; // Sesuaikan sesuai kebutuhan
        			cell2.appendChild(checkbox1);

        			// Checkbox 2
        			var cell3 = row.insertCell(2);
        			var checkbox2 = document.createElement("input");
					checkbox2.setAttribute("name", "activate");
        			checkbox2.setAttribute("type", "checkbox");
        			checkbox2.setAttribute("value", "1");
					if(tasklists[i].type == 'theme') {
        				checkbox2.checked = false;
					} else {
						checkbox2.checked = true;
					}
        			cell3.appendChild(checkbox2);
					
					// delete action
					var cell4 = row.insertCell(3);
					var delbutton = document.createElement("i");
					delbutton.setAttribute("name", tasklists[i].slug);
					delbutton.setAttribute("vtipe", tasklists[i].type);
					delbutton.setAttribute("class", "ki-duotone ki-technology-2 fs-1 btn-strip-start");
					delbutton.setAttribute("style", "cursor: pointer; position: relative; top: 5px;");
					delbutton.setAttribute("onclick", "ultrapm_deleteTaskList(this)");
					delbutton.innerHTML = '<span class="path1"></span><span class="path2"></span></i>';
					cell4.appendChild(delbutton);
				}
				
				if (window.location.href.indexOf("wp-admin/admin.php?page=ultrapm-search-result") > -1) {
					//location.reload(true);
				}
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

				toastr.error("Failed to refresh data", "Error");
			}
		});

    }

    // Tambahkan event listener untuk menangani klik pada tombol "Refresh"
    refreshButton.addEventListener("click", refreshTable);
</script>
<script>
	$("#kt_datatable_zero_configuration").DataTable();
	function ultrapm_addToTaskList(Name, Slug, DownloadLink, type) {
		var datae = {
			'action': 'ultrapm_add_to_task_list',
			'name': Name,
			'slug': Slug,
			'download_link': DownloadLink,
			'type': type
		};
		jQuery.ajax({
            type: "POST",
            url: ultrapm_urls.ajaxurl,
            data: datae,
            success: function(data) {
				data = JSON.parse(data);
				msg = data.message;
				status = data.status;
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
  					"timeOut": "3000",
  					"extendedTimeOut": "1000",
  					"showEasing": "swing",
  					"hideEasing": "linear",
  					"showMethod": "fadeIn",
  					"hideMethod": "fadeOut"
				};

				if(status == 'info') {
					toastr.info(msg, "Info");
					return;
				}
				toastr.success(msg, "Success");
				refreshTable();
				if (window.location.href.indexOf("wp-admin/admin.php?page=ultrapm-search-result") > -1) {
					var button = document.getElementById('addtotask-' + Slug);
					button.innerHTML = 'Added to List'
					button.classList.add("disabled");
					var button = document.getElementById('install-' + Slug);
					button.remove();
					var button = document.getElementById('installa-' + Slug);
					button.remove();
				}
				openTaskList();
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

				toastr.error("Failed to add to task list", "Error");
            }
        });
	}
	function ultrapm_clearTaskList() {
		var datae = {
			'action': 'ultrapm_clear_task_list'
		};
		jQuery.ajax({
			type: "POST",
			url: ultrapm_urls.ajaxurl,
			data: datae,
			success: function(data) {
				data = JSON.parse(data);
				msg = data.message;
				status = data.status;
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
				toastr.success(msg, "Success");
				refreshTable();
				var button = document.getElementById("kt_drawer_example_permanent_close");
				button.click();
				if (window.location.href.indexOf("wp-admin/admin.php?page=ultrapm-search-result") > -1) {
					location.reload(true);
				}
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

				toastr.error("Failed to clear task list", "Error");
			}
		});
	}
</script>

<script>
	function ultrapm_installTaskList() {
		var datae = {
			'action': 'ultrapm_install_task_list',
			'install': $('input[name="install"]:checked').map(function() {return $(this).parent().parent().find('td[name]').attr('name');}).get(),
			'activate': $('input[name="activate"]:checked').map(function() {return $(this).parent().parent().find('td[name]').attr('name');}).get(),
			'delete': $('input[name="delete"]:checked').map(function() {return $(this).parent().parent().find('td[name]').attr('name');}).get()
		};
    	const loadingEl = document.createElement("div");
    	document.body.prepend(loadingEl);
    	loadingEl.classList.add("page-loader");
    	loadingEl.classList.add("flex-column");
    	loadingEl.classList.add("bg-dark");
    	loadingEl.classList.add("bg-opacity-25");
		loadingEl.innerHTML = `
			<span class="spinner-border text-primary" role="status"></span>
			<span class="text-gray-800 fs-6 fw-semibold mt-5" id="ultrapminsproc">Please wait...</span>
		`;
		KTApp.showPageLoading();
    	document.getElementById('ultrapminsproc').textContent = "Running task...";
		jQuery.ajax({
			type: "POST",
			url: ultrapm_urls.ajaxurl,
			data: datae,
			success: function(data) {
				data = JSON.parse(data);
				msg = data.message;
				status = data.status;
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
				KTApp.hidePageLoading();
				loadingEl.remove();
				toastr.success(msg, "Success");
				refreshTable();
				location.reload(true);
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

				KTApp.hidePageLoading();
				loadingEl.remove();
				toastr.error("Failed to install task list", "Error");
			}
		});
	}
</script>
<script>
	function ultrapm_crut_ajax_install_theme(slug) {
		var datae = {
			'action': 'ultrapm_install_theme_bySlug',
			'slug': slug,
		};
		const loadingEl = document.createElement("div");
		document.body.prepend(loadingEl);
		loadingEl.classList.add("page-loader");
		loadingEl.classList.add("flex-column");
		loadingEl.classList.add("bg-dark");
		loadingEl.classList.add("bg-opacity-25");
		loadingEl.innerHTML = `
			<span class="spinner-border text-primary" role="status"></span>
			<span class="text-gray-800 fs-6 fw-semibold mt-5" id="ultrapminsproc">Please wait...</span>
		`;
		KTApp.showPageLoading();
		document.getElementById('ultrapminsproc').textContent = "installing...";
		jQuery.ajax({
			type: "POST",
			url: ultrapm_urls.ajaxurl,
			data: datae,
			success: function(data) {
				data = JSON.parse(data);
				msg = data.message;
				status = data.success;
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
				KTApp.hidePageLoading();
				loadingEl.remove();
				toastr.success(msg, "Success");
				location.reload(true);
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
				KTApp.hidePageLoading();
				loadingEl.remove();
				toastr.info("Something went wrong", "Info");
				location.reload(true);
			}
		});
	}

	function ultrapm_crut_ajax_activate_theme(slug) {
		var datae = {
			'action': 'ultrapm_activate_theme_bySlug',
			'slug': slug,
		};
		// change to spin, button with id activate- + slug
		var button = document.getElementById('activate-' + slug);
		button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Activating...';
		button.disabled = true;
		jQuery.ajax({
			type: "POST",
			url: ultrapm_urls.ajaxurl,
			data: datae,
			success: function(data) {
				data = JSON.parse(data);
				msg = data.message;
				status = data.success;
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
				toastr.success(msg, "Success");
				location.reload(true);
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
				location.reload(true);
			}
		});

	}

	function ultrapm_crut_ajax_delete_theme(slug) {
		var datae = {
			'action': 'ultrapm_delete_theme_bySlug',
			'slug': slug,
		};
		// change to spin, button with id delete- + slug
		var button = document.getElementById('delete-' + slug);
		button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...';
		button.disabled = true;
		jQuery.ajax({
			type: "POST",
			url: ultrapm_urls.ajaxurl,
			data: datae,
			success: function(data) {
				data = JSON.parse(data);
				msg = data.message;
				status = data.success;
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
				toastr.success(msg, "Success");
				location.reload(true);
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
				location.reload(true);
			}
		});
	}





	function ultrapm_crut_ajax_install_plugin(slug, activate) {
		var datae = {
			'action': 'ultrapm_install_plugin_bySlug',
			'slug': slug,
			'activate': activate,
		};
		const loadingEl = document.createElement("div");
		document.body.prepend(loadingEl);
		loadingEl.classList.add("page-loader");
		loadingEl.classList.add("flex-column");
		loadingEl.classList.add("bg-dark");
		loadingEl.classList.add("bg-opacity-25");
		loadingEl.innerHTML = `
			<span class="spinner-border text-primary" role="status"></span>
			<span class="text-gray-800 fs-6 fw-semibold mt-5" id="ultrapminsproc">Please wait...</span>
		`;
		KTApp.showPageLoading();
    	document.getElementById('ultrapminsproc').textContent = "installing..." + slug;
		jQuery.ajax({
			type: "POST",
			url: ultrapm_urls.ajaxurl,
			data: datae,
			success: function(data) {
				data = JSON.parse(data);
				msg = data.message;
				status = data.success;
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
				KTApp.hidePageLoading();
				loadingEl.remove();
				toastr.success(msg, "Success");
				location.reload(true);
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

				KTApp.hidePageLoading();
				loadingEl.remove();
				toastr.info("Something went wrong", "Info");
				location.reload(true);
			}
		});
	}

	function ultrapm_crut_ajax_activate_plugin(slug) {
		var datae = {
			'action': 'ultrapm_activate_plugin_bySlug',
			'slug': slug,
		};
		// change to spin, button with id activate- + slug
		var button = document.getElementById('activate-' + slug);
		button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Activating...';
		button.disabled = true;
		jQuery.ajax({
			type: "POST",
			url: ultrapm_urls.ajaxurl,
			data: datae,
			success: function(data) {
				data = JSON.parse(data);
				msg = data.message;
				status = data.success;
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
				toastr.success(msg, "Success");
				location.reload(true);
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
				location.reload(true);
			}
		});

	}

	function ultrapm_crut_ajax_deactivate_plugin(slug) {
		var datae = {
			'action': 'ultrapm_deactivate_plugin_bySlug',
			'slug': slug,
		};
		// change to spin, button with id deactivate- + slug
		var button = document.getElementById('deactivate-' + slug);
		button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deactivating...';
		button.disabled = true;
		jQuery.ajax({
			type: "POST",
			url: ultrapm_urls.ajaxurl,
			data: datae,
			success: function(data) {
				data = JSON.parse(data);
				msg = data.message;
				status = data.success;
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
				toastr.success(msg, "Success");
				location.reload(true);
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
				location.reload(true);
			}
		});

	}

	function ultrapm_crut_ajax_delete_plugin(slug) {
		var datae = {
			'action': 'ultrapm_delete_plugin_bySlug',
			'slug': slug,
		};
		// change to spin, button with id delete- + slug
		var button = document.getElementById('delete-' + slug);
		button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...';
		button.disabled = true;
		jQuery.ajax({
			type: "POST",
			url: ultrapm_urls.ajaxurl,
			data: datae,
			success: function(data) {
				data = JSON.parse(data);
				msg = data.message;
				status = data.success;
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
				toastr.success(msg, "Success");
				location.reload(true);
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
				location.reload(true);
			}
		});
	}



	let intervaltask;
	
	function installFromUrl(url, type, sec) {
		console.log(url);
		console.log(type);
		console.log(sec);
    	// plugin url + /ultra-plugin-manager/includes/pre-upload-zip.php
    	destUrl = '<?php echo plugins_url('ultra-plugin-manager/includes/pre-upload-zip.php'); ?>';
    	const loadingEl = document.createElement("div");
    	document.body.prepend(loadingEl);
    	loadingEl.classList.add("page-loader");
    	loadingEl.classList.add("flex-column");
    	loadingEl.classList.add("bg-dark");
    	loadingEl.classList.add("bg-opacity-25");
		loadingEl.setAttribute("id", "loadingEl");
    	loadingEl.innerHTML = `
        	<span class="spinner-border text-primary" role="status"></span>
        	<span class="text-gray-800 fs-6 fw-semibold mt-5" id="ultrapminsproc">Please wait...</span>
    	`;
    	KTApp.showPageLoading();
		doAdditionalRequest('ultrapm_start_task_progress', url);
    	// Ganti pesan sebelum AJAX
    	document.getElementById('ultrapminsproc').textContent = "Preparing for upload..."; // Pesan pertama
    	$.ajax({
        	url: destUrl,
        	type: 'POST',
        	data: {
            	sec: sec,
            	url: url,
            	userzipFile: type
        	},
    	});
		var taske = 'ultrapm_check_task_progress';
		var urle = url;
		intervaltask = setInterval(function() {
			doAdditionalRequest(taske, urle);
		}, 1000);
	}

	// Lakukan proses tambahan secara rekursif hingga syarat terpenuhi
	function doAdditionalRequest(taske, urle) {
		var anotherUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
        $.ajax({
            url: anotherUrl, // Ganti dengan URL yang sesuai
            type: 'POST',
            data: {
                'action': taske,
				'url': urle,
            }
        })
        .done(function(data) {
			data = JSON.parse(data);
            // Ganti pesan lagi setelah proses tambahan selesai
            document.getElementById('ultrapminsproc').textContent = data.message; // Pesan ketiga
			if (data.status == 'done' && data.message == 'File already exist, Please Clear List First') {
				// sembunyikan loading
				KTApp.hidePageLoading();
				clearInterval(intervaltask);
				idloadingEl = document.getElementById('loadingEl');
				idloadingEl.remove();
				// tampilkan pesan
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
				toastr.success(data.message, "Success");
				document.getElementById('zipFile').value = '';
				//location.reload(true);
				return;
			} else if (data.status == 'done' && data.message != 'Task progress started.') {
				// sembunyikan loading
				KTApp.hidePageLoading();
				clearInterval(intervaltask);
				idloadingEl = document.getElementById('loadingEl');
				idloadingEl.remove();
				// tampilkan pesan
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
				toastr.success(data.message, "Success");
				document.getElementById('zipFile').value = '';
				// refresh table
				refreshTable();
				openTaskList();
				// reload page
				//location.reload(true);
				return;
			} else if (data.status == false) {
				doAdditionalRequest(taske, urle);
            }
		})
        .fail(function(data) {
            // Ganti pesan jika terjadi kesalahan pada permintaan tambahan
			data = JSON.parse(data);
			document.getElementById('ultrapminsproc').textContent = data.message; // Pesan keempat
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
			toastr.error(data.message, "Error");
			document.getElementById('zipFile').value = '';
			//location.reload(true);
			return;
        });
    }


	function ultrapm_deleteTaskList(e) {
		var datae = {
			'action': 'ultrapm_clear_task_list',
			'slug': e.getAttribute("name"),
			'type': e.getAttribute("vtipe")
		};
		jQuery.ajax({
			type: "POST",
			url: ultrapm_urls.ajaxurl,
			data: datae,
			success: function(data) {
				console.log(data);
				data = JSON.parse(data);
				msg = data.message;
				status = data.status;
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
				toastr.success(msg, "Success");
				refreshTable();
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

				toastr.error("Failed to delete task list", "Error");
			}
		});
	}
	
	function ultrapm_insact() {
    	var itemids = [];
    	$.each($("input[name='itemids[]']:checked"), function () {
        	itemids.push($(this).val());
    	});
	
    	// change to spinner icon id="bulk-installNactivate"
    	var button = document.getElementById('bulk-installNactivate');
    	button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Installing...';
    	button.disabled = true;
	
    	// Promises array to track each request
    	var promises = [];
		var totalids = itemids.length;
	
    	// do request each itemids
    	for (var i = 0; i < itemids.length; i++) {
        	var itemid = itemids[i];
        	var data = {
            	'action': 'ultrapm_insact_musthave',
            	'itemid': itemid
        	};
		
			var Pslugs = [];
			var Tslugs = [];
		
        	// Create a promise for each request
        	var requestPromise = new Promise(function (resolve, reject) {
            	$.post(ajaxurl, data, function (response) {
                	console.log(response);
                	var res = JSON.parse(response);
                	if (res.success == true) {
						var type = itemid.charAt(0).toLowerCase();
						var slug = res.slug;
						if (type == 'p') {
							Pslugs.push(slug);
						} else if (type == 't') {
							Tslugs.push(slug);
						}
                    	resolve(); // Resolve the promise if successful
                	} else {
                    	reject(); // Reject the promise if there's an error
                	}
            	});
        	});
		
        	// Add the promise to the array
        	promises.push(requestPromise);
    	}
	
    	// Execute when all promises are resolved
    	Promise.all(promises).then(function () {
    		var plugins = '';
    		jQuery('input[name="itemids[]"]:checked').each(function() {
        		var id = jQuery(this).attr('id');
        		var slug = id.replace('activate-', '');
        		plugins += slug + ',';
    		});
			//console.log(plugins);
        	var data = {
            	'action': 'ultrapm_activate_plugin_bySlugs',
            	'slugs': plugins,
        	};
			jQuery.ajax({
				type: "POST",
				url: ultrapm_urls.ajaxurl,
				data: data,
				success: function (data) {
					Swal.fire({
						title: 'Success',
						text: 'Plugin[s] and Theme[s] has been installed and activated',
						icon: 'success',
						confirmButtonText: 'OK'
					});
					location.reload(true);
				},
				error: function () {
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
					location.reload(true);
				}
			});
		}).catch(function () {
			// Execute if one or more promises are rejected
			Swal.fire({
				title: 'Error',
				text: 'Failed to install and activate plugin[s] and theme[s]',
				icon: 'error',
				confirmButtonText: 'OK'
			});
			location.reload(true);
		});
	}


function showInstallePluginSwal() {
    Swal.fire({
        title: 'Installed',
        text: 'Plugin has been installed',
        icon: 'info',
        confirmButtonText: 'OK'
    });
}

function showInstalleThemeSwal() {
    Swal.fire({
        title: 'Installed',
        text: 'Theme has been installed',
        icon: 'info',
        confirmButtonText: 'OK'
    });
}

</script>