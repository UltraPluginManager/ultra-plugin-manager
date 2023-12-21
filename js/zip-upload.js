jQuery(document).ready(function(){

    jQuery('#start-zip-upload').click(function(){

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
        var zipFileName = jQuery('#zipFileName').val();
		doAdditionalRequest('ultrapm_start_task_progress', zipFileName);

        jQuery('#form-zip-upload' ).submit(
            
            function( e ) {

                var _data = new FormData( this );
                
    	        document.getElementById('ultrapminsproc').textContent = "Preparing for upload...";
                jQuery.ajax( 
                    {
                        url: ultrapm_urls.plugins_url + '/ultra-plugin-manager/includes/pre-upload-zip.php',
                        type: 'POST',
                        data: _data,
                        processData: false,
                        contentType: false,
                    } 
                );
		        var taske = 'ultrapm_check_task_progress';
		        intervaltask = setInterval(function() {
			        doAdditionalRequest(taske, zipFileName);
		        }, 300);

                e.preventDefault();

            } 
        );

    });

});