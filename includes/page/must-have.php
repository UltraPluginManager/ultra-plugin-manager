<?php
function ultrapm_must_have_page_callback(){
    include( ULTRAPM_INC_PATH . '/part/content-start.php');
    include( ULTRAPM_INC_PATH . '/part/header.php');
    $pluginDir = WP_PLUGIN_DIR;
    $musthaveConfigDir = $pluginDir . '/ultra-plugin-manager/config';
    if(is_file($musthaveConfigDir . '/config.json')){
        $jsonConfig = file_get_contents( $musthaveConfigDir . '/config.json' );
    } else {
        $dataConfig = file_get_contents( $musthaveConfigDir . '/config.txt' );
        $jsonConfig = json_decode($dataConfig, true);
        file_put_contents($musthaveConfigDir . '/config.json', $dataConfig);
    }
    if(is_file($musthaveConfigDir . '/itd-plugin.json')){
        $jsonItdPlugin = file_get_contents( $musthaveConfigDir . '/itd-plugin.json' );
        $jsonItdPlugin = json_decode($jsonItdPlugin, true);
    } else {
        $dataItdPlugin = file_get_contents( $musthaveConfigDir . '/itd-plugin.txt' );
        $jsonItdPlugin = json_decode($dataItdPlugin, true);
        file_put_contents($musthaveConfigDir . '/itd-plugin.json', $dataItdPlugin);
    }
    if(is_file($musthaveConfigDir . '/itd-theme.json')){
        $jsonItdTheme = file_get_contents( $musthaveConfigDir . '/itd-theme.json' );
        $jsonItdTheme = json_decode($jsonItdTheme, true);
    } else {
        $dataItdTheme = file_get_contents( $musthaveConfigDir . '/itd-theme.txt' );
        $jsonItdTheme = json_decode($dataItdTheme, true);
        file_put_contents($musthaveConfigDir . '/itd-theme.json', $dataItdTheme);
    }
    
    $s = 0;
    
    ?>

        <div class="content flex-row-fluid" id="kt_content">
            <div class="card card-flush">
                <div class="row" style="margin-top: 20px;">
                    <div class="col-xl-2 col-xxl-2 col-md-4" style="margin-bottom: 20px;">
                        <h5>Theme</h5>
                    </div>
                            <?php
                                $ti = 0;
                                foreach($jsonItdTheme as $key => $value){
                                    foreach($value as $key2 => $value2){
                                        foreach($value2 as $key3 => $value3){
                                            $totalItem = count($value3);
                                            $ti++;
                                            ?>
                    <div class="col-xl-2 col-xxl-2 col-md-4" style="margin-bottom: 20px;">
                                            <?php
                                                $is_quick_starter = $value3['is_quick_starter'];
                                                if($is_quick_starter == 1) { // checked
                                                    echo '<input type="checkbox" name="itemids[]" value="t' . $value3['id'] . '" checked id="activate-' . $value3['slug'] . '"><a data-bs-target="#'. $value3['slug'] .'-modal" data-bs-toggle="modal" style="cursor: pointer;">' . $value3['itemname'] . '</a></input>';
                                                } else {
                                                    echo '<input type="checkbox" name="itemids[]" value="t' . $value3['id'] . '" id="activate-' . $value3['slug'] . '"> <a data-bs-target="#'. $value3['slug'] .'-modal" data-bs-toggle="modal" style="cursor: pointer;">' . $value3['itemname'] . '</a></input>';
                                                }
                                            ?>
                    </div>
                                                <div class="modal fade" tabindex="-1" aria-hidden="true" id="<?php echo $value3['slug']; ?>-modal">
                                                    <!--begin::Modal dialog-->
                                                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable mw-800px">
                                                        <!--begin::Modal content-->
                                                        <div class="modal-content">
                                                                <!--begin iframe-->
                                                                <iframe style="min-height: 80vh; width: 100%; border: none;" ></iframe>
                                                        </div>
                                                        <!--end::Modal content-->
                                                    </div>
                                                    <!--end::Modal dialog-->
                                                </div>
                                            <?php
                                            if($totalItem > 5 && $ti == 5){
                                                ?>
                    <div class="col-xl-2 col-xxl-2 col-md-4" style="margin-bottom: 20px;">
                        <h5> </h5>
                    </div>
                                                <?php
                                                $ti = 0;
                                            }
                                        }
                                    }
                                }
                            ?>
                </div>
            </div>
                    <?php
                    foreach($jsonItdPlugin as $key => $value){
                        $cats = array_keys($value);
                        $listCat = array();
                        foreach($cats as $key2 => $value2){
                            ?>
            <div class="card card-flush">
                <div class="row" style="margin-top: 20px;">
                    <div class="col-xl-2 col-xxl-2 col-md-4" style="margin-bottom: 20px;">
                        <h5><?php echo $value2; ?></h5>
                    </div>
                            <?php
                                $totalItem = count($value[$value2]);
                                $i = 0;
                                foreach($value[$value2] as $key3 => $value3){
                                    $i++;
                                    $listCat[] = $value3['itemname'];
                                    $pluginSlug = $value3['slug'];
                                    $pluginSlugpure = str_replace('-', '', $pluginSlug);
                                    ?>
                    <div class="col-xl-2 col-xxl-2 col-md-4" style="margin-bottom: 20px;">
                                            <?php
                                                $is_quick_starter = $value3['is_quick_starter'];
                                                if($is_quick_starter == 1) { // checked
                                                    echo '<input type="checkbox" name="itemids[]" value="p' . $value3['id'] . '" checked id="activate-' . $value3['slug'] . '"><a onclick="customIframe'. $pluginSlugpure.'()" style="cursor: pointer;">' . $value3['itemname'] . '</a></input>';
                                                } else {
                                                    echo '<input type="checkbox" name="itemids[]" value="p' . $value3['id'] . '" id="activate-' . $value3['slug'] . '"><a onclick="customIframe'. $pluginSlugpure.'()" style="cursor: pointer;">' . $value3['itemname'] . '</a></input>';
                                                }
                                            ?>
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

                                    <?php
                                    if($totalItem > 5 && $i == 5){
                                        ?>
                    <div class="col-xl-2 col-xxl-2 col-md-4" style="margin-bottom: 20px;">
                        <h5> </h5>
                    </div>
                                        <?php
                                        $i = 0;
                                    }
                                }
                            ?>
                </div>
            </div>
                            <?php
                        }
                    }
                    ?>
            <div class="card card-flush">
                <div class="row text-center" style="margin-top: 20px; margin-bottom: 20px;">
                    <div class="col-12">
                        <button type="button" class="btn" style="background-color: #00b2ff; color: white;" onclick="ultrapm_insact()" id="bulk-installNactivate">Install & Activate</button>
                    </div>
                </div>
            </div>

        </div>

<script>
<?php
    foreach($jsonItdPlugin as $key => $value){
        foreach($cats as $key2 => $value2){
            foreach($value[$value2] as $key3 => $value3){
                $pluginSlug = $value3['slug'];
                $pluginSlugpure = str_replace('-', '', $pluginSlug);
                ?>
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
            }
        }
    }
?>
</script>












    <?php
    include( ULTRAPM_INC_PATH . '/part/search-engine.php');
    include( ULTRAPM_INC_PATH . '/part/content-end.php');
    include( ULTRAPM_INC_PATH . '/part/task-list.php');
    include( ULTRAPM_INC_PATH . '/part/scripts.php');
}
?>