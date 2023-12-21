jQuery(document).ready(function(){

    //getAllConfigsExternal();

    //getAllPluginsExternal();

    //getAllThemesExternal();

    topTenThemes();

    topTenPlugins();

    jQuery('#item-list-view-btn').click(function(){

        if(jQuery('#item-list-view').css('right') == '-700px'){
            jQuery('#item-list-view').animate({
                right: "20px"
            }, 765);
        }
        else {
            jQuery('#item-list-view').animate({
                right: "-700px"
            }, 425);
        }

    });

    jQuery('#search-item-plugin').click();

    // if( jQuery('#unzip-install-log').val() != '' && jQuery('#unzip-install-log').val() != null ){

    //     var html = '';

    //     var logs = jQuery('#unzip-install-log').val();

    //     var logsArray = logs.split('|');

    //     for(i=0;i<logsArray.length;i++){
    //         html += '<p>' + logsArray[i] + '</p>';
    //     }

    //     swal({
    //         title: '<strong>Zip file installation Log</strong>',
    //         icon: 'info',
    //         html: html,
    //         confirmButtonText:
    //           '<span>OK</span>',
    //         showCloseButton: false,
    //         showCancelButton: false,
    //         focusConfirm: false
    //     }).then((result) => {
    //         if ("value" in result) {
    //             if (result.value == true) {
    //                 window.location.reload()
    //             }
    //         }
    //     });

    // }

    jQuery('#quick-starter-select').change(function(){

        var id = jQuery('#quick-starter-select').val();

        var url =  ultrapm_urls.api_url + '/wp-json/ultra-plugin-manager-api/v1/getconfigitemsdetailsbyid/' + id;

        if( jQuery(this).find('option:selected').attr('data-type') == 'internal' ){
            url =  ultrapm_urls.api_url + '/wp-json/ultra-plugin-manager-api/v1/getitemsdetails/' + 
            jQuery(this).find('option:selected').attr('data-itemsstring');
        }

        jQuery.ajax({

            type: "GET",
            url: url,
            success: function(_data) {  

                var data = JSON.parse(_data)[0];

                jQuery('#quick-starter-checks').empty();

                for( const key in data ){

                    if( data[ key ].length > 0 ){

                        var itemGroup = '';
                        
                        for(i=0;i<data[ key ].length;i++){
                            itemGroup +=    '<div class="form-check d-inline-block">' +
                                                '<input id="qsa-' + data[ key ][i].id + '" class="qs-actionable" type="checkbox" value="' + 
                                                data[ key ][i].id + '" data-slug="' + data[ key ][i].slug + 
                                                '" data-zipurl="' + data[ key ][i].zip_url + '" data-itemtype="' + data[ key ][i].item_type + '">' +
                                                '<label class="form-check-label qsa-label" for="qsa-' + data[ key ][i].id + '">' + data[ key ][i].itemname + '</label>' +
                                            '</div>';
                        }

                        jQuery('#quick-starter-checks').prepend(
                            '<h6><strong>' + key.toUpperCase() + '</strong></h6>' + itemGroup
                        );

                    }
                    
                }
                                
            },
            error: function(){
                
            }
    
        });

    });

    jQuery('#install-activate, #install-only').click(function(){

        jQuery('#item-installing-spinner').css('display', 'block');

        var data = {}

        data.action = 'items_stage_install';
        data.items = [];
        var items = [];
        var items_string = '';
        data.activate = jQuery(this).attr('data-activate');

        var items_to_install = jQuery('.qs-actionable:checked').get();

        for(i=0;i<items_to_install.length;i++){

            var _data = {}

            _data.slug = jQuery(items_to_install[i]).attr('data-slug');
            _data.item_type = jQuery(items_to_install[i]).attr('data-itemtype');
            _data.zip_url = jQuery(items_to_install[i]).attr('data-zipurl');
            _data.itemname = jQuery(items_to_install[i]).next('label').text();            
            
            data.items.push(_data);
            items.push(_data);

            items_string +=  jQuery(items_to_install[i]).val().toString() + ',';

        }

        if( jQuery('#save-as-quick-starter-config').is(':checked') ){
            saveConfig(items_string.slice(0, -1), jQuery('#new-configuration-name').val());
        }
        
        jQuery.ajax({

            type: "POST",
            url: ultrapm_urls.ajaxurl,
            data: {
                action: 'items_stage_install',
                activate: jQuery(this).attr('data-activate'),
                items: JSON.stringify(items)
            },
            success: function(_data) {  
    
                var data = JSON.parse(_data.replace(/<\/?[^>]+(>|$)/g, ""));

                var html = '';

                for(i=0;i<data.length;i++){
                    html += '<p>' + data[i] + '</p>';
                }

                new swal({
                    title: '<strong>Installation Log</strong>',
                    icon: 'info',
                    html: html,
                    confirmButtonText:
                      '<span>OK</span>',
                    showCloseButton: false,
                    showCancelButton: false,
                    focusConfirm: false
                });

                jQuery('#item-installing-spinner').css('display', 'none');
                
            },
            error: function(){
                jQuery('#item-installing-spinner').css('display', 'none');
            }
    
        });

    });

    jQuery('#delete-package').click(function(){

        var id = jQuery('#quick-starter-select').val();

        swal({
            title: '<strong>Delete Package</strong>',
            icon: 'info',
            html:
              'This operation is <strong>irreversible</strong>. ',
            confirmButtonText:
              '<span id="delete-package-confirm" data-id="' + id + '">OK</span>',
            showCloseButton: false,
            showCancelButton: false,
            focusConfirm: false
        }).then((result) => {
            if ("value" in result) {
                if (result.value == true) {
                    window.location.reload()
                }
            }
        });

    });

    jQuery('body').delegate('#delete-package-confirm', 'click', function(){    
        
        var id = jQuery(this).attr('data-id');

        deleteConfig(id);

    });

    jQuery('.tp-search').click(function(){

        // var types = jQuery('[name="ultrapm-search-item"]').get();
        // var type;

        // for(i=0;i<types.length;i++){
        //     if(jQuery(types[i]).is(':checked')){
        //         type = jQuery(types[i]).val()
        //     }
        // }

        doItemSearch(jQuery('#tp-search-keyword').val(), jQuery(this).attr("data-type"));    

    });

    jQuery("#tp-search-keyword").on('keyup', function (e) {

        var types = jQuery('[name="ultrapm-search-item"]').get();
        var type;

        for(i=0;i<types.length;i++){
            if(jQuery(types[i]).is(':checked')){
                type = jQuery(types[i]).val()
            }
        }

        if (e.key === 'Enter' || e.keyCode === 13) {
            doItemSearch(jQuery(this).val(), type);
        }

    });

    jQuery('body').delegate('.activate-item', 'click', function(){

        jQuery.ajax({

            type: "POST",
            url: ultrapm_urls.ajaxurl,
            data: {
                action: 'item_activate',
                slug: jQuery(this).attr('data-slug')
            },
            success: function(data) {  
    
                var html = '<p>Activation successful!</p>';

                swal({
                    title: '<strong>Activation Log</strong>',
                    icon: 'info',
                    html: html,
                    confirmButtonText:
                      '<span class="confirm-with-reload">OK</span>',
                    showConfirmButton: true,
                    showCloseButton: false,
                    showCancelButton: false,
                    focusConfirm: false
                }).then((result) => {
                    if ("value" in result) {
                        if (result.value == true) {
                            window.location.reload()
                        }
                    }
                });
                
            },
            error: function(){
                
            }
    
        });

    });

    jQuery('body').delegate('.deactivate-item', 'click', function(){

        jQuery.ajax({

            type: "POST",
            url: ultrapm_urls.ajaxurl,
            data: {
                action: 'item_deactivate',
                slug: jQuery(this).attr('data-slug')
            },
            success: function(data) {  
    
                var html = '<p>' + data + '</p>';

                swal({
                    title: '<strong>De-Activation Log</strong>',
                    icon: 'info',
                    html: html,
                    confirmButtonText:
                      '<span class="confirm-with-reload">OK</span>',
                    showConfirmButton: true,
                    showCloseButton: false,
                    showCancelButton: false,
                    focusConfirm: false
                }).then((result) => {
                    if ("value" in result) {
                        if (result.value == true) {
                            window.location.reload()
                        }
                    }
                });
                
            },
            error: function(){
                
            }
    
        });

    });

    jQuery('body').delegate('.confirm-with-reload', 'click', function(){

        window.location.reload();

    });

    jQuery('body').delegate('.delete-plugin', 'click', function(){

        jQuery.ajax({

            type: "POST",
            url: ultrapm_urls.ajaxurl,
            data: {
                action: 'remove_plugin',
                slug_prefix: jQuery(this).attr('data-slug')
            },
            success: function(data) {  
    
                var html = '<p>' + data + '</p>';

                swal({
                    title: '<strong>Plugin Deleteion</strong>',
                    icon: 'info',
                    html: html,
                    confirmButtonText:
                      '<span class="confirm-with-reload">OK</span>',
                    showCloseButton: false,
                    showCancelButton: false,
                    focusConfirm: false
                }).then((result) => {
                    if ("value" in result) {
                        if (result.value == true) {
                            window.location.reload()
                        }
                    }
                });
                
            },
            error: function(){
                
            }
    
        });

    });

    jQuery('body').delegate('.activate-theme', 'click', function(){

        jQuery.ajax({

            type: "POST",
            url: ultrapm_urls.ajaxurl,
            data: {
                action: 'activate_theme',
                stylesheet: jQuery(this).attr('data-stylesheet')
            },
            success: function(data) {  
    
                var html = '<p>' + data + '</p>';

                swal({
                    title: '<strong>Theme Activation</strong>',
                    icon: 'info',
                    html: html,
                    confirmButtonText:
                      '<span class="confirm-with-reload">OK</span>',
                    showCloseButton: false,
                    showCancelButton: false,
                    focusConfirm: false
                }).then((result) => {
                    if ("value" in result) {
                        if (result.value == true) {
                            window.location.reload()
                        }
                    }
                });
                
            },
            error: function(){
                
            }
    
        });

    });

    jQuery('body').delegate('.delete-theme', 'click', function(){

        jQuery.ajax({

            type: "POST",
            url: ultrapm_urls.ajaxurl,
            data: {
                action: 'remove_theme',
                stylesheet: jQuery(this).attr('data-stylesheet')
            },
            success: function(data) {  
    
                var html = '<p>' + data + '</p>';

                swal({
                    title: '<strong>Theme Deletion</strong>',
                    icon: 'info',
                    html: html,
                    confirmButtonText:
                      '<span class="confirm-with-reload">OK</span>',
                    showCloseButton: false,
                    showCancelButton: false,
                    focusConfirm: false
                }).then((result) => {
                    if ("value" in result) {
                        if (result.value == true) {
                            window.location.reload()
                        }
                    }
                });
                
            },
            error: function(){
                
            }
    
        });

    });

    jQuery('#installed-plugin-bulk-action-start').click(function(){

        if(jQuery('#installed-plugin-bulk-actions').val() == 'delete'){
            bulkDeletePlugins();
        }
        else if(jQuery('#installed-plugin-bulk-actions').val() == 'de-activate') {
            bulkDeActivatePlugins();
        }

    });

    jQuery('#search-result-pages').change(function(){

        var url = jQuery(this).find('option:selected').attr('data-url');

        var page = jQuery(this).val();

        var type = jQuery(this).attr('data-type');

        jQuery('#search-result-pages').empty();

        if(type == 'plugin'){
            loadSearchResultsPlugins(url, page);
        }
        else if(type == 'theme'){
            loadSearchResultsThemes(url, page);
        }

    });

});

function bulkDeActivatePlugins() {

    var tiles = jQuery('.tile-item-bulk-selected:checked').get();

    log = new Array();

    for(i=0;i<tiles.length;i++){

        var slug = jQuery(tiles[i]).closest('.ultrapm-item-tile').find('.deactivate-item').attr('data-slug');

        jQuery.ajax({

            type: "POST",
            url: ultrapm_urls.ajaxurl,
            async: false,
            data: {
                action: 'item_deactivate',
                slug: slug
            },
            success: function(data) {  

                log.push(data);
                
            },
            error: function(){
                
            }

        });

    }

    swal({
        title: '<strong>De-Activation Log</strong>',
        icon: 'info',
        html: '<p>' + log.length.toString() + ' plugins de-activated successfully!</p>',
        confirmButtonText:
        '<span class="confirm-with-reload">OK</span>',
        showConfirmButton: true,
        showCloseButton: false,
        showCancelButton: false,
        focusConfirm: false
    }).then((result) => {
        if ("value" in result) {
            if (result.value == true) {
                window.location.reload()
            }
        }
    });

}

function bulkDeletePlugins() {

    var tiles = jQuery('.tile-item-bulk-selected:checked').get();

    log = new Array();

    for(i=0;i<tiles.length;i++){

        var slug = jQuery(tiles[i]).closest('.ultrapm-item-tile').find('.delete-plugin').attr('data-slug') == undefined ? jQuery(tiles[i]).closest('.ultrapm-item-tile').find('.deactivate-item').attr('data-slug'):jQuery(tiles[i]).closest('.ultrapm-item-tile').find('.delete-plugin').attr('data-slug');

        jQuery.ajax({

            type: "POST",
            url: ultrapm_urls.ajaxurl,
            async: false,
            data: {
                action: 'remove_plugin',
                slug_prefix: slug
            },
            success: function(data) {  

                log.push(data);
                
            },
            error: function(){
                
            }
    
        });

    }

    swal({
        title: '<strong>Plugin Deletion</strong>',
        icon: 'info',
        html: '<p>' + log.length.toString() + ' plugins deleted successfully!</p>',
        confirmButtonText:
            '<span class="confirm-with-reload">OK</span>',
        showCloseButton: false,
        showCancelButton: false,
        focusConfirm: false
    }).then((result) => {
        if ("value" in result) {
            if (result.value == true) {
                window.location.reload();
            }
        }
    });

}

function getAllConfigsExternal(){

    jQuery('#quick-starter-select').empty();

    jQuery.ajax({

        type: "GET",
        url: ultrapm_urls.api_url + '/wp-json/ultra-plugin-manager-api/v1/getconfig',
        success: function(_data) {  

            var data = JSON.parse(_data);

            var selected = '';

            for(i=0;i<data.length;i++){
                selected = i == 0 ? 'selected':'';
                jQuery('#quick-starter-select').append(
                    '<option data-type="external" ' + selected + ' value="' + data[i].id + '">' + data[i].configname + '</option>'
                );
            }            

            jQuery('#quick-starter-select').change();

            getAllConfigsInternal();
            
        },
        error: function(){
            
        }

    });

}

function getAllConfigsInternal(){

    jQuery.ajax({

        type: "POST",
        url: ultrapm_urls.ajaxurl,
        data: {
            action: 'get_ultrapm_config'
        },
        success: function(_data) {  

            var data = JSON.parse(_data);

            for(i=0;i<data.length;i++){
                jQuery('#quick-starter-select').append(
                    '<option data-itemsstring="' + data[i].configitemsstring + 
                    '" data-type="internal" value="' + data[i].id + '">' + data[i].configname + 
                    '</option>'
                );
            }
            
        },
        error: function(){
            
        }

    });

}

function saveConfig(items, name){

    jQuery.ajax({

        type: "POST",
        url: ultrapm_urls.ajaxurl,
        data: {
            action: 'add_ultrapm_config',
            items: items,
            name: name
        },
        dataType: 'text',
        success: function(data) {  

            swal({
                type: 'info',
                title: 'Quick Starter',
                text: data
            });
            
        },
        error: function(){
            
        }

    });

}

function deleteConfig(id){

    jQuery.ajax({

        type: "POST",
        url: ultrapm_urls.ajaxurl,
        data: {
            action: 'delete_ultrapm_config',
            id: id
        },
        dataType: 'text',
        success: function(data) {  

            swal({
                type: 'info',
                title: 'Quick Starter',
                text: data
            });

            getAllConfigsExternal();
            getAllConfigsInternal();
            
        },
        error: function(){
            
        }

    });

}

function doItemSearch(keyword, type){
    
    jQuery('div.tab-pane').removeClass('active').removeClass('show');

    jQuery('div.tab-pane#search-results-tab').addClass('active').addClass('show');

    if(type == "theme"){
        searchThemes(keyword);
    }
    else if (type == "plugin"){
        searchPlugins(keyword);
    }
    
}

function getAllPluginsExternal() {

    jQuery.ajax({

        type: "GET",
        url: ultrapm_urls.api_url + '/wp-json/ultra-plugin-manager-api/v1/getconfigitemsdetailsbytype/plugin',
        success: function(_data) {  

            var data = JSON.parse(_data)[0];

            for( const key in data ){

                if( data[ key ].length > 0 ){
                    
                    for(i=0;i<data[ key ].length;i++){

                        var slug = data[ key ][i].slug;

                        var url = ultrapm_urls.ajaxurl;

                        var cekData = {
                            action: 'ultrapm_is_exsist_list_plugin_info',
                            slug: slug
                        };

                        jQuery.ajax({
                            type: "POST",
                            url: ultrapm_urls.ajaxurl,
                            data: cekData,
                        success: function(data) {
				            data = JSON.parse(data);
                            if (data.status == 'success') {
                                var info = data.info;
                                var description = info.sections.description.slice(0,120).replace( /(<([^>]+)>)/ig, '');
                                jQuery('#ultrapm-plugins').append(
                                    '<div class="col-md-3 my-3">' +
                                        '<div class="ultrapm-item-tile">' +
                                            '<h5 class="item-title"><strong>' + info.name + '</strong></h5>' +
                                            '<p class="item-author">by ' + info.author.replace( /(<([^>]+)>)/ig, '') + '</p>' +
                                            '<p class="item-description">' + description + 
                                            '<a href="' + info.homepage + '" target="_blank"> ...</a></p>' +
                                            '<p class="item-version">v' + info.version + '</p>' +
                                            '<div class="item-actions">' +
                                                // '<a style="text-decoration: none;" href="' + info.download_link + '">' +
                                                // '<img class="item-action install-item"' + 
                                                // 'alt="installation"' + 
                                                // 'src="' + ultrapm_urls.assets_url + '/icons/install.png">&nbsp;&nbsp;' +
                                                // '<a/>' +
                                                '<a style="text-decoration: none;" href="#">' +
                                                '<img class="item-action add-to-list" data-slug="' + info.slug + '"' + 
                                                'alt="add to list" data-type="plugin"' + 
                                                'src="' + ultrapm_urls.assets_url + 
                                                '/icons/add-to-list.png">&nbsp;&nbsp;' +
                                                '</a>' +
                                            '</div>' +
                                        '</div>' +
                                    '</div>'
                                );
                            }
                        },
                        error: function(){
                            
                        }
                        });
                    }
                }
            }
        },
        error: function(){
            
        }
    });


}

function getAllThemesExternal() {

    // if page not contains "page=ultrapm-must-have" then return
    if (window.location.href.indexOf("page=ultrapm-must-have") == -1) {
        return;
    }

    jQuery.ajax({

        type: "GET",
        url: ultrapm_urls.api_url + '/wp-json/ultra-plugin-manager-api/v1/getconfigitemsdetailsbytype/theme',
        success: function(_data) {  

            var data = JSON.parse(_data)[0];

            for( const key in data ){
                
                if( data[ key ].length > 0 ){
                    
                    for(j=0;j<data[ key ].length;j++){

                        var slug = data[ key ][j].slug;
                        
                        var url = "https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]=" + slug;

                        var xhttp = new XMLHttpRequest();
                        
                        xhttp.onreadystatechange = function() {
                            if (this.readyState == 4 && this.status == 200) {
                                var info = JSON.parse(xhttp.response);
                                var description = info.sections.description.slice(0,120).replace( /(<([^>]+)>)/ig, '');
                                jQuery('#ultrapm-themes').append(
                                    '<div class="col-md-3 my-3">' +
                                        '<div class="ultrapm-item-tile">' +
                                            '<h5 class="item-title"><strong>' + info.name + '</strong></h5>' +
                                            '<p class="item-author">by ' + info.author.replace( /(<([^>]+)>)/ig, '') + '</p>' +
                                            '<p class="item-description">' + description + 
                                            '<a href="' + info.homepage + '" target="_blank"> ...</a></p>' +
                                            '<p class="item-version">v' + info.version + '</p>' +
                                            '<div class="item-actions">' +
                                                // '<a style="text-decoration: none;" href="' + info.download_link + '">' +
                                                // '<img class="item-action install-item"' + 
                                                // 'alt="installation"' + 
                                                // 'src="' + ultrapm_urls.assets_url + '/icons/install.png">&nbsp;&nbsp;' +
                                                // '<a/>' +
                                                '<a style="text-decoration: none;" href="#">' +
                                                '<img class="item-action add-to-list" data-slug="' + info.slug + '"' + 
                                                'alt="add to list" data-type="theme"' + 
                                                'src="' + ultrapm_urls.assets_url + 
                                                '/icons/add-to-list.png">&nbsp;&nbsp;' +
                                                '</a>' +
                                            '</div>' +
                                        '</div>' +
                                    '</div>'
                                );
                            }
                        };

                        xhttp.open("GET", url, false);
                        xhttp.send();
                        
                    }

                }
                
            }
                            
        },
        error: function(){
            
        }

    });

}

function searchThemes(keyword){

    jQuery('#search-result-pages').attr('data-keyword', keyword);

    var url = "http://api.wordpress.org/themes/info/1.1/?action=query_themes&&request[per_page]=100&request[search]=" + keyword;
           
    loadSearchResultsThemes(url, 1);

}

function searchPlugins(keyword){

    jQuery('#search-result-pages').attr('data-keyword', keyword);

    var url = "https://api.wordpress.org/plugins/info/1.2/?action=query_plugins&request[per_page]=100&request[search]=" + keyword;

    loadSearchResultsPlugins(url, 1);

}

function topTenPlugins() {
    // Mengecek apakah hasil sebelumnya tersimpan dan belum kadaluarsa
    var storedPlugins = localStorage.getItem("ultra-plugin-manager_topTenPlugins");
    var storedTimestamp = localStorage.getItem("ultra-plugin-manager_topTenPlugins_timestamp");
    if (storedPlugins && Date.now() - parseInt(storedTimestamp) < 1 * 60 * 60 * 1000) {
        // Menggunakan hasil yang tersimpan
        var plugins = JSON.parse(storedPlugins);
        displayPlugins(plugins);
    } else {
        // Melakukan permintaan ke API WordPress Plugins
        var url = "https://api.wordpress.org/plugins/info/1.2/?action=query_plugins&request[browse]=popular" +
            "&request[per_page]=10&request[page]=1";

        var xhttp = new XMLHttpRequest();

        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                var result = JSON.parse(xhttp.response);
                var plugins = result.plugins;

                // Menyimpan hasil ke localStorage
                localStorage.setItem("ultra-plugin-manager_topTenPlugins", JSON.stringify(plugins));
                localStorage.setItem("ultra-plugin-manager_topTenPlugins_timestamp", Date.now());

                displayPlugins(plugins);
            }
        };

        xhttp.open("GET", url, false);
        xhttp.send();
    }
}

function displayPlugins(plugins) {
    //console.log(plugins.length);
    for (var i = 0; i < plugins.length; i++) {
        var info = plugins[i];
        var pluginSlug = info.slug;
        var pluginName = info.name.length > 60 ? info.name.slice(0, 60) + '...' : info.name;
        var pluginZipUrl = info.download_link;
        
        jQuery('#most-popular-plugins  .simplebar-content').append(
            '<a class="most-poular-item" href="#" onclick="showInstallPluginPrompt(\'' + pluginSlug + '\', \'' + pluginZipUrl + '\', \'' + pluginName + '\')"><span class="top-10-marker">' +
            (i + 1).toString() + '</span>&nbsp;&nbsp;' + pluginName + '</a>'
        );
    }
}

function showInstallPluginPrompt(pluginSlug, pluginZipUrl, pluginName) {
    new Swal({
        title: 'Install ' + pluginName + ' Plugin?',
        input: 'checkbox',
        inputPlaceholder: 'Activate after installation',
        showCancelButton: true,
        confirmButtonText: 'Yes',
    }).then((result) => {
        if (result.isConfirmed) {

            // sembunyikan SweetAlert sebelumnya
            new Swal.close();

            // perbaiki jika pluginName mengandung unsur HTML (misalnya: &amp;)
            var pluginName = jQuery('<div/>').html(pluginName).text();

            // Jika pengguna mengonfirmasi aktivasi
            var activate = result.value ? 'true' : 'false';

            // Jika pengguna mengonfirmasi instalasi, maka lakukan permintaan ke API WordPress Plugins
            var url = "https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&request[slug]=" + pluginSlug;

            var xhttp = new XMLHttpRequest();

            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    var info = JSON.parse(xhttp.response);
                    var pluginName = jQuery('<div/>').html(info.name).text();
                    var pluginZipUrl = info.download_link;
                    var pluginSlug = info.slug;

                    // Tampilkan SweetAlert untuk menampilkan status instalasi
                    new swal({
                        title: 'Installing Plugin',
                        text: 'Installing plugin ' + pluginName + '...',
                        icon: 'info',
                        buttons: false,
                        closeOnClickOutside: false,
                        closeOnEsc: false,
                    });

                    // Lakukan permintaan ke API WordPress untuk mengunduh plugin
                    var xhttp2 = new XMLHttpRequest();

                    xhttp2.onreadystatechange = function() {
                        if (this.readyState == 4 && this.status == 200) {

                            // Lakukan permintaan ke API Ultra Plugin Manager untuk menginstal plugin
                            jQuery.ajax({
                                type: "POST",
                                url: ultrapm_urls.ajaxurl,
                                data: {
                                    action: 'ultrapm_plugin_install',
                                    zip_url: pluginZipUrl,
                                    itemname: pluginName,
                                    itemtype: 'plugin',
                                    slug: pluginSlug,
                                    activate: activate
                                },
                                success: function(data) {
                                    // Tampilkan SweetAlert untuk menampilkan status instalasi
                                    new swal({
                                        title: 'Plugin Installed',
                                        text: 'Plugin ' + pluginName + ' installed successfully!',
                                        icon: 'success',
                                        buttons: {
                                            confirm: 'OK'
                                        },
                                    }).then((result) => {
                                        if ("value" in result) {
                                            if (result.value == true) {
                                                window.location.reload();
                                            }
                                        }
                                    });
                                },
                                error: function() {
                                    // Tampilkan SweetAlert untuk menampilkan status instalasi
                                    new swal({
                                        title: 'Plugin Installation Failed',
                                        text: 'Plugin ' + pluginName + ' installation failed!',
                                        icon: 'error',
                                        buttons: {
                                            confirm: 'OK'
                                        },
                                    }).then((result) => {
                                        if ("value" in result) {
                                            if (result.value == true) {
                                                window.location.reload();
                                            }
                                        }
                                    });
                                }
                            });
                        }
                    };

                    xhttp2.open("GET", pluginZipUrl, false);
                    xhttp2.send();
                }
            };

            xhttp.open("GET", url, false);
            xhttp.send();
        }
    });
}


function topTenThemes() {
    // Mengecek apakah hasil sebelumnya tersimpan dan belum kadaluarsa
    var storedThemes = localStorage.getItem("ultra-plugin-manager_topTenThemes");
    var storedTimestamp = localStorage.getItem("ultra-plugin-manager_topTenThemes_timestamp");
    if (storedThemes && Date.now() - parseInt(storedTimestamp) < 1 * 60 * 60 * 1000) {
        // Menggunakan hasil yang tersimpan
        var themes = JSON.parse(storedThemes);
        displayThemes(themes);
    } else {
        // Melakukan permintaan ke API WordPress Themes
        var url = "https://api.wordpress.org/themes/info/1.1/?action=query_themes&request[browse]=popular" +
            "&request[per_page]=10&request[page]=1";

        var xhttp = new XMLHttpRequest();

        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                var result = JSON.parse(xhttp.response);
                var themes = result.themes;

                // Menyimpan hasil ke localStorage
                localStorage.setItem("ultra-plugin-manager_topTenThemes", JSON.stringify(themes));
                localStorage.setItem("ultra-plugin-manager_topTenThemes_timestamp", Date.now());

                displayThemes(themes);
            }
        };

        xhttp.open("GET", url, false);
        xhttp.send();
    }
}

function displayThemes(themes) {
    //console.log(themes.length);
    for (var i = 0; i < themes.length; i++) {
        var info = themes[i];
        var themeSlug = info.slug;
        var themeName = info.name.length > 60 ? info.name.substring(0, 60) + '...' : info.name;
        var themeZipUrl = 'https://downloads.wordpress.org/theme/' + themeSlug + '.' + info.version + '.zip';

        // Tambahkan event handler untuk mengonfirmasi instalasi tema
        jQuery('#most-popular-themes  .simplebar-content').append(
            '<a class="most-poular-item" href="javascript:void(0);" onclick="showInstallThemePrompt(\'' + themeSlug + '\', \'' + themeZipUrl + '\', \'' + themeName + '\')"><span class="top-10-marker">' +
            (i + 1).toString() + '</span>&nbsp;&nbsp;' + themeName + '</a>'
        );
    }
}

// Fungsi untuk menampilkan SweetAlert dan mengonfirmasi instalasi tema
function showInstallThemePrompt(themeSlug, themeZipUrl, themeName) {
    new Swal({
        title: 'Install ' + themeName + ' Theme?',
        input: 'checkbox',
        inputPlaceholder: 'Activate after installation',
        showCancelButton: true,
        confirmButtonText: 'Yes',
    }).then((result) => {
        if (result.isConfirmed) {

            // Jika pengguna mengonfirmasi aktivasi
            var activate = result.value ? 'true' : 'false';

            // Jika pengguna mengonfirmasi instalasi, maka lakukan permintaan ke API WordPress Themes
            var url = "https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]=" + themeSlug;

            var xhttp = new XMLHttpRequest();

            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    var info = JSON.parse(xhttp.response);
                    var themeName = info.name;
                    var themeZipUrl = 'https://downloads.wordpress.org/theme/' + themeSlug + '.' + info.version + '.zip';

                    // Tampilkan SweetAlert untuk menampilkan status instalasi
                    new swal({
                        title: 'Installing Theme',
                        text: 'Installing theme ' + themeName + '...',
                        icon: 'info',
                        buttons: false,
                        closeOnClickOutside: false,
                        closeOnEsc: false,
                    });

                    // Lakukan permintaan ke API WordPress untuk mengunduh tema
                    var xhttp2 = new XMLHttpRequest();

                    xhttp2.onreadystatechange = function() {
                        if (this.readyState == 4 && this.status == 200) {
                            var zipUrl = JSON.parse(xhttp2.response).download_link;

                            // Lakukan permintaan ke API Ultra Plugin Manager untuk menginstal tema
                            jQuery.ajax({
                                type: "POST",
                                url: ultrapm_urls.ajaxurl,
                                data: {
                                    action: 'ultrapm_item_install',
                                    zip_url: zipUrl,
                                    itemname: themeName,
                                    itemtype: 'theme',
                                    activate: activate
                                },
                                success: function(data) {
                                    // Tampilkan SweetAlert untuk menampilkan status instalasi
                                    new swal({
                                        title: 'Theme Installed',
                                        text: 'Theme ' + themeName + ' installed successfully!',
                                        icon: 'success',
                                        buttons: {
                                            confirm: 'OK'
                                        },
                                    }).then((value) => {
                                        if (value) {
                                            window.location.reload();
                                        }
                                    });
                                },
                                error: function() {
                                    // Tampilkan SweetAlert untuk menampilkan status instalasi
                                    new swal({
                                        title: 'Theme Installation Failed',
                                        text: 'Theme ' + themeName + ' installation failed!',
                                        icon: 'error',
                                        buttons: {
                                            confirm: 'OK'
                                        },
                                    });
                                }
                            });
                        }
                    };

                    xhttp2.open("GET", url, false);
                    xhttp2.send();
                }
            };

            xhttp.open("GET", url, false);
            xhttp.send();
        }
    });
}



function loadSearchResultsThemes(url, page){

    jQuery('#ultrapm-search-results').empty();
    jQuery('#search-result-pages').empty();

    var xhttp = new XMLHttpRequest();

    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var data = JSON.parse(xhttp.response);
            var themes_data = data.themes;
            var search_meta = data.info;
            var pages = parseInt(search_meta.pages);
            for(i=0;i<pages;i++){
                jQuery('#search-result-pages').append(
                    '<option data-url="http://api.wordpress.org/themes/info/1.1/?action=query_themes&&request[per_page]=100&request[page]=' + (i+1).toString() + '&request[search]=' + jQuery('#search-result-pages').attr('data-keyword') + '">' + (i+1).toString() + '</option>'
                );
                jQuery('#search-result-pages').val(page);
                jQuery('#search-result-pages').attr('data-type', 'theme');
            }
            for(i=0;i<themes_data.length;i++){
                var info = themes_data[i];
                var description = info.description.slice(0,120).replace( /(<([^>]+)>)/ig, '');
                var title = info.name.slice(0,65).replace( /(<([^>]+)>)/ig, '');
                jQuery('#ultrapm-search-results').append(
                    '<div class="col-md-3 my-3">' +
                        '<div class="ultrapm-item-tile" draggable="true" ondragstart="searchResultDrag(event)">' +
                            '<h5 class="item-title"><strong>' + title + '</strong></h5>' +
                            '<p class="ultrapm-item-type-icon"><img alt="theme" src="' + 
                            ultrapm_urls.assets_url + '/icons/theme.png"/><span>&nbsp;Theme</span></p>' +
                            '<p class="item-author">by ' + info.author.replace( /(<([^>]+)>)/ig, '') + '</p>' +
                            '<p class="item-description">' + description + 
                            '<a href="' + info.homepage + '" target="_blank"> ...</a></p>' +
                            '<p class="item-version">v' + info.version + '</p>' +
                            '<div class="item-actions">' +
                                // '<a style="text-decoration: none;" href="https://downloads.wordpress.org/theme/' + info.slug + '.' + info.version + '.zip">' +
                                // '<img class="item-action install-item" data-slug="' + info.slug + '"' + 
                                // 'alt="installation"' + 
                                // 'src="' + ultrapm_urls.assets_url + '/icons/install.png">&nbsp;&nbsp;' +
                                // '</a>' +
                                '<a style="text-decoration: none;" href="#">' +
                                '<img class="item-action add-to-list" data-slug="' + info.slug + '"' + 
                                'alt="add to list" data-type="theme" ' + 
                                'src="' + ultrapm_urls.assets_url + '/icons/add-to-list.png">&nbsp;&nbsp;' +
                                '</a>' +
                            '</div>' +
                        '</div>' +
                    '</div>'
                );
            }
        }
    };

    xhttp.open("GET", url, false);
    xhttp.send();

}

function loadSearchResultsPlugins(url, page) {

    jQuery('#ultrapm-search-results').empty();
    jQuery('#search-result-pages').empty();

    var xhttp = new XMLHttpRequest();
   
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var info = JSON.parse(xhttp.response);
            var plugins_data = info.plugins;
            var search_meta = info.info;
            var pages = parseInt(search_meta.pages);
            for(i=0;i<pages;i++){
                jQuery('#search-result-pages').append(
                    '<option data-url="https://api.wordpress.org/plugins/info/1.2/?action=query_plugins&request[per_page]=100&request[page]=' + (i+1).toString() + '&request[search]=' + jQuery('#search-result-pages').attr('data-keyword') + '">' + (i+1).toString() + '</option>'
                );
                jQuery('#search-result-pages').val(page);                
                jQuery('#search-result-pages').attr('data-type', 'plugin');
            }
            for(i=0;i<plugins_data.length;i++){
                var info = plugins_data[i];
                var description = info.short_description.slice(0,120).replace( /(<([^>]+)>)/ig, '');
                var title = info.name.slice(0,65).replace( /(<([^>]+)>)/ig, '');
                jQuery('#ultrapm-search-results').append(
                    '<div class="col-md-3 my-3">' +
                        '<div class="ultrapm-item-tile" draggable="true" ondragstart="searchResultDrag(event)">' +
                            '<h5 class="item-title"><strong>' + title + '</strong></h5>' +
                            '<p class="ultrapm-item-type-icon"><img alt="plugin" src="' + 
                            ultrapm_urls.assets_url + '/icons/plugin.png"/><span>&nbsp;Plugin</span></p>' +
                            '<p class="item-author">by ' + info.author.replace( /(<([^>]+)>)/ig, '') + '</p>' +
                            '<p class="item-description">' + description + 
                            '<a href="' + info.homepage + '" target="_blank"> ...</a></p>' +
                            '<p class="item-version">v' + info.version + '</p>' +
                            '<div class="item-actions">' +
                                // '<a style="text-decoration: none;" href="https://downloads.wordpress.org/plugin/' + info.slug + '.' + info.version + '.zip">' +
                                // '<img class="item-action install-item" data-slug="' + info.slug + '"' + 
                                // 'alt="installation"' + 
                                // 'src="' + ultrapm_urls.assets_url + '/icons/install.png">&nbsp;&nbsp;' +
                                // '</a>' +
                                '<a style="text-decoration: none;" href="#">' +
                                '<img class="item-action add-to-list" data-slug="' + info.slug + '"' + 
                                'alt="add to list" data-type="plugin" ' + 
                                'src="' + ultrapm_urls.assets_url + '/icons/add-to-list.png">&nbsp;&nbsp;' +
                                '</a>' +
                            '</div>' +
                        '</div>' +
                    '</div>'
                );
            }
        }
    };

    xhttp.open("GET", url, false);
    xhttp.send();

}

function searchResultAllowDrop(ev) {
    ev.preventDefault();
}

function searchResultDrag(ev) {

    var slug = jQuery(ev.target).find('div.item-actions > a > img').attr('data-slug');

    var type = jQuery(ev.target).find('div.item-actions > a > img').attr('data-type');
                    
    var url = type == 'theme' ? "https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]=" + slug:"https://api.wordpress.org/plugins/info/1.0/" + slug.split('/')[0] + '.json';

    ev.dataTransfer.setData("url", url);
    ev.dataTransfer.setData("type", type);
  
}

function searchResultDrop(ev) {

    ev.preventDefault();
    var type = ev.dataTransfer.getData("type");
    var url = ev.dataTransfer.getData("url");

    var xhttp = new XMLHttpRequest();
    
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var info = JSON.parse(xhttp.response);
            var download_link = info.download_link;
            jQuery('#item-list-view-table').append(
                '<tr id="' + info.slug + '" class="list-item-row" draggable="true" ondragstart="listItemRowDrag(event)" ondrop="listItemRowDrop(event)" ondragover="listItemRowAllowDrop(event)">' +
                    '<td class="list-item install-list-item-name from-search" data-slug="' + info.slug + '" data-link="' + 
                    download_link + '"><strong>' + info.name + '</strong><button id="info-button-' + info.slug + '"><img class="ultrapm-info-icon" alt="info" src="' + ultrapm_urls.assets_url + '/icons/info-icon.jpg"/></button><p class="install-list-msg"></td>' +
                    '<td class="list-item install-list-item-type">' + type + '</td>' +
                    '<td class="list-item check-to-install"><input type="checkbox" class="install-list-item"/></td>' +
                    '<td class="list-item check-to-activate"><input type="checkbox" class="activate-list-item"/></td>' +
                '</tr>'
            );
        }
    };

    xhttp.open("GET", url, false);
    xhttp.send();
  
}