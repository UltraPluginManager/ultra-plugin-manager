jQuery(document).ready(function(){

    jQuery('#clear-install-list').click(function(){
        jQuery('.list-item-row').remove();
        jQuery('#check-all-listed-install').prop('checked', false);
        jQuery('#check-all-listed-activate').prop('checked', false);
    });

    jQuery('#check-all-listed-install').click(function(){
        if(jQuery(this).is(':checked')){
            jQuery('.install-list-item').prop('checked', true);
        }
        else {
            jQuery('.install-list-item').removeAttr('checked');
        }
    });

    jQuery('#check-all-listed-activate').click(function(){
        if(jQuery(this).is(':checked')){
            jQuery('.activate-list-item').prop('checked', true);
        }
        else {
            jQuery('.activate-list-item').removeAttr('checked');
        }
    });

    jQuery('body').delegate('.activate-list-item', 'click', function(){
        if(jQuery(this).is(':checked')){
            var all_items_count = jQuery('.activate-list-item').get().length;
            var all_items_checked_count = jQuery('.activate-list-item:checked').get().length;
            if(all_items_count == all_items_checked_count){
                jQuery('#check-all-listed-activate').prop('checked', true);
            }
        }
        else {
            jQuery('#check-all-listed-activate').removeAttr('checked');
        }
    });

    jQuery('body').delegate('.install-list-item', 'click', function(){
        if(jQuery(this).is(':checked')){
            var all_items_count = jQuery('.install-list-item').get().length;
            var all_items_checked_count = jQuery('.install-list-item:checked').get().length;
            if(all_items_count == all_items_checked_count){
                jQuery('#check-all-listed-install').prop('checked', true);
            }
        }
        else {
            jQuery('#check-all-listed-install').removeAttr('checked');
        }
    });

    jQuery('body').delegate('.add-to-list', 'click', function(e){

        e.preventDefault();

        addToList(jQuery(this));

    });

    jQuery('#install-from-list').click(function(){

        var list_items = jQuery('.list-item-row').get();

        var log = new Array();

        for(i=0;i<list_items.length;i++){

            if(jQuery(list_items[i]).find('td.list-item.check-to-install > input').is(':checked')){

                if(jQuery(list_items[i]).find('td:first-child').hasClass('from-search')){

                    var data = new Array();

                    var _data = {};

                    var _activate = jQuery(list_items[i]).find('td.check-to-activate > input').is(':checked') ? '1':'0';

                    _data.slug = jQuery(list_items[i]).find('td.install-list-item-name').attr('data-slug');
                    _data.item_type = jQuery(list_items[i]).find('td.install-list-item-type').text();
                    _data.zip_url = jQuery(list_items[i]).find('td.install-list-item-name').attr('data-link');
                    _data.itemname = jQuery(list_items[i]).find('td.install-list-item-name > strong').text(); 

                    data.push(_data);

                    var msg = installFromSearchedList(jQuery(list_items[i]).find('td:first-child'), data, _activate);

                    log.push(msg);

                }
                else if(jQuery(list_items[i]).find('td:first-child').hasClass('from-zip')){

                    var data = new Array();

                    var _data = {};

                    var _activate = jQuery(list_items[i]).find('td.check-to-activate > input').is(':checked') ? '1':'0';

                    _data.file = jQuery(list_items[i]).find('td:first-child').attr('data-file');
                    _data.dir = jQuery(list_items[i]).find('td:first-child').attr('data-dir');
                    _data.folder = jQuery(list_items[i]).find('td:first-child').attr('data-folder');
                    _data.item_type = jQuery(list_items[i]).find('td:nth-child(2)').text();

                    data.push(_data);
                    
                    jQuery(list_items[i]).find('td:first-child').find('.install-list-msg').text('Analyzing...');    

                    var msg = analyzeFromZippedList(jQuery(list_items[i]).find('td:first-child'), data, _activate);

                    log.push(msg);

                }

                jQuery(list_items[i]).find('td:first-child').removeClass('from-zip');
                jQuery(list_items[i]).find('td:first-child').removeClass('from-search');

            }
                    
        }        

        // var html = '';

        // for(i=0;i<log.length;i++){
        //     html += '<p>' + log[i] + '</p>';
        // }

        // swal({
        //     title: '<strong>Installation Log</strong>',
        //     icon: 'info',
        //     html: html,
        //     confirmButtonText:
        //       '<span>OK</span>',
        //     showCloseButton: false,
        //     showCancelButton: false,
        //     focusConfirm: false
        // }).then((result) => {
        //     if ("value" in result) {
        //         if (result.value == true) {
        //             jQuery('.list-item').remove();
        //             jQuery('#check-all-listed-install').removeAttr('checked');
        //             jQuery('#check-all-listed-activate').removeAttr('checked');
        //         }
        //     }
        // });

    });

});

function analyzeFromZippedList(elem, item, activate){

    var msg = '';

    jQuery.ajax({

        type: "POST",
        url: ultrapm_urls.ajaxurl,
        async: false,
        data: {
            action: 'analyze_item_from_zip',
            item: JSON.stringify(item)
        },
        success: function(data) {  

            if(data == 1){
                jQuery(elem).find('.install-list-msg').text('Installing...');
                msg = installFromZippedList(elem, item, activate);
            }
            else {
                jQuery(elem).find('.install-list-msg').text('Install Failed');
                var info_button_id = jQuery(elem).find('button[id*="info-button-"]').attr('id');
                jQuery('#' + info_button_id).css('display', 'inline');
                tippy('#' + info_button_id, {
                    content: data
                });
                //jQuery(elem).append('<p class="install-list-msg">' + data + '</p>');
            }  
            
        },
        error: function(xhr, ajaxOptions, thrownError){
            console.log(xhr.responseText);
        }

    });    

    return msg;          

}

function installFromZippedList(elem, item, activate){

    var msg = '';

    jQuery.ajax({

        type: "POST",
        url: ultrapm_urls.ajaxurl,
        async: false,
        data: {
            action: 'install_item_from_zip',
            activate: activate,
            slug: jQuery(elem).attr('data-slug').replace(/\\/g, ''),
            stylesheet: jQuery(elem).attr('data-folder'),
            item: JSON.stringify(item)
        },
        success: function(_data) {  

            jQuery(elem).find('.install-list-msg').text('Installed!');
            
        },
        error: function(){
            
        }

    });    

    return msg;

}

function installFromSearchedList(elem, item, activate){

    jQuery(elem).find('.install-list-msg').text('Installing...');

    var msg = '';

    jQuery.ajax({

        type: "POST",
        url: ultrapm_urls.ajaxurl,
        async: false,
        data: {
            action: 'items_stage_install',
            activate: activate,
            items: JSON.stringify(item)
        },
        success: function(_data) {  

            var data = JSON.parse(_data.replace(/<\/?[^>]+(>|$)/g, ""));

            msg = data[0];  
            
        },
        error: function(){
            
        }

    });    

    return msg;          

}

function addToList(elem) {

    var slug = jQuery(elem).attr('data-slug');

    var type = jQuery(elem).attr('data-type');
                    
    var url = type == 'theme' ? "https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]=" + slug:"https://api.wordpress.org/plugins/info/1.0/" + slug.split('/')[0] + '.json';

    var xhttp = new XMLHttpRequest();
    
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var info = JSON.parse(xhttp.response);
            var download_link = info.download_link;
            jQuery('#item-list-view-table').append(
                '<tr id="' + info.slug + '" class="list-item-row" ondragstart="listItemRowDrag(event)" draggable="true" ondrop="listItemRowDrop(event)" ondragover="listItemRowAllowDrop(event)">' +
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

function listItemRowDrag(ev) {

    ev.dataTransfer.setData("text", jQuery(ev.target).closest('tr').attr("id"));
  
}

function listItemRowAllowDrop(ev) {
    ev.preventDefault();
}

function listItemRowDrop(ev) {

    ev.preventDefault();
    var draggedId = ev.dataTransfer.getData("text");
    var clone = jQuery('tr#' + draggedId).clone();
    console.log(clone);

    if(jQuery(clone).attr("id") !== jQuery(ev.target).closest('tr').attr("id")) {
        jQuery('tr#' + draggedId).remove();
        jQuery(clone).insertBefore('tr#' + jQuery(ev.target).closest('tr').attr("id"));
    }
  
}