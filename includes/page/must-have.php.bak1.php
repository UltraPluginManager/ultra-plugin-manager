<?php

function ultrapm_must_have_menu() {
    add_submenu_page(
        '69', // Slug menu utama
        'Must Have', // Judul halaman
        'Must Have', // Label di menu
        'manage_options', // Hak akses yang diperlukan untuk melihat halaman
        ULTRAPM_SLUG_MUST_HAVE, // Slug halaman
        'ultrapm_must_have_page_callback' // Fungsi yang dipanggil untuk menampilkan halaman
    );
}

add_action( 'admin_menu', 'ultrapm_must_have_menu' );

function ultrapm_must_have_page_callback(){
    include( ULTRAPM_INC_PATH . '/part/content-start.php');
    include( ULTRAPM_INC_PATH . '/part/header.php');
    $mainDomain = get_option( 'ultrapm_api_url' );

    // url /wp-json/ultra-plugin-manager-api/v1/getconfig
    $configUrl = $mainDomain . '/wp-json/ultra-plugin-manager-api/v1/getconfige'; // "[{\"id\":\"1\",\"configname\":\"Starter 1\",\"configitems\":\"a:2:{i:0;s:1:\\\"1\\\";i:1;s:1:\\\"2\\\";}\"}]"

    // url /wp-json/ultra-plugin-manager-api/v1/getconfigitemsdetailsbytype/plugin
    $itdPlugin = $mainDomain . '/wp-json/ultra-plugin-manager-api/v1/getconfigitemsdetailsbytype/plugin'; // "[{\"Turu\":[{\"id\":\"1\",\"itemname\":\"Elementor\",\"slug\":\"elementor\",\"zip_url\":null,\"item_type\":\"plugin\",\"is_quick_starter\":\"1\"},{\"id\":\"2\",\"itemname\":\"Woocommerce\",\"slug\":\"woocommerce\",\"zip_url\":null,\"item_type\":\"plugin\",\"is_quick_starter\":\"1\"}],\"Dulu\":[{\"id\":\"3\",\"itemname\":\"Classic Editor\",\"slug\":\"classic-editor\",\"zip_url\":\"\",\"item_type\":\"plugin\",\"is_quick_starter\":\"1\"}],\"Dahlah\":[]}]"

    // url /wp-json/ultra-plugin-manager-api/v1/getconfigitemsdetailsbytype/theme
    $itdTheme = $mainDomain . '/wp-json/ultra-plugin-manager-api/v1/getconfigitemsdetailsbytype/theme'; // "[{\"Turu\":[],\"Dulu\":[],\"Dahlah\":[]}]"


    $dataConfig = file_get_contents($configUrl);
    $dataItdPlugin = file_get_contents($itdPlugin);
    $dataItdTheme = file_get_contents($itdTheme);
    
    $fixDConfig = substr($dataConfig, 1, -1);
    $fixDItdPlugin = substr($dataItdPlugin, 1, -1);
    $fixDItdTheme = substr($dataItdTheme, 1, -1);
    
    $fix2DConfig = str_replace('\"', '"', $fixDConfig);
    $fix2DConfig = str_replace('\"', '"', $fixDConfig);
    $fix2DItdPlugin = str_replace('\"', '"', $fixDItdPlugin);
    $fix2DItdTheme = str_replace('\"', '"', $fixDItdTheme);
    update_option( 'ultrapm_must_have_config', $fix2DConfig );
    update_option( 'ultrapm_must_have_itd_plugin', $fix2DItdPlugin );
    update_option( 'ultrapm_must_have_itd_theme', $fix2DItdTheme );
    $jsonConfig = json_decode($fix2DConfig, true);
    $jsonItdPlugin = json_decode($fix2DItdPlugin, true);
    $jsonItdTheme = json_decode($fix2DItdTheme, true);
    
    $s = 0;
    
    ?>

<div class="content flex-row-fluid" id="kt_content">
    <div class="row">
        <div class="col" style="margin-bottom: 20px;">
            <div class="card card-flush h-xl-100">
                    <table class="table table-hover table-rounded border gy-5 gs-7" style="width:100%">
                        <tbody>
                            <?php
                                $ct = 0;
                                echo '<tr>';
                                echo '<td style="width: 150px; max-width: 150px;">Theme</td>';
                                foreach($jsonItdTheme as $key => $value){
                                    foreach($value as $key2 => $value2){
                                        foreach($value2 as $key3 => $value3){
                                                $ct++;
                                                if($ct == 5){
                                                    echo '</tr><tr>';
                                                    echo '<td style="width: 150px; max-width: 150px;"></td>';
                                                    $ct = 2;
                                                }
                                                $is_quick_starter = $value3['is_quick_starter'];
                                                if($is_quick_starter == 1) { // checked
                                                    echo '<td style="width: 250px; max-width: 250px;"><input type="checkbox" name="itemids[]" value="t' . $value3['id'] . '" checked id="activate-' . $value3['slug'] . '">' . $value3['itemname'] . '</td>';
                                                } else {
                                                    echo '<td style="width: 250px; max-width: 250px;"><input type="checkbox" name="itemids[]" value="t' . $value3['id'] . '" id="activate-' . $value3['slug'] . '">' . $value3['itemname'] . '</td>';
                                                }
                                        }
                                        if($ct != 5){
                                            for($i = $ct; $i < 5; $i++){
                                                $ct++;
                                                echo '<td style="width: 250px; max-width: 250px;"></td>';
                                            }
                                        }
                                    }
                                }
                                echo '</tr>';
                            ?>
                        </tbody>
                    </table>
                        <?php
                                $ti = 0;
                                $ct = 0;
                                $ctr = 0;
                                foreach($jsonItdPlugin as $key => $value){ // [{"Turu":[{"id":"1","itemname":"Elementor","slug":"elementor","zip_url":null,"item_type":"plugin","is_quick_starter":"1"},{"id":"2","itemname":"Woocommerce","slug":"woocommerce","zip_url":null,"item_type":"plugin","is_quick_starter":"1"}],"Dulu":[{"id":"3","itemname":"Classic Editor","slug":"classic-editor","zip_url":"","item_type":"plugin","is_quick_starter":"0"}],"Dahlah":[]}]

                                    // isi kategori berdasarkan key
                                    $cats = array_keys($value); // ["Turu","Dulu","Dahlah"]
                                    $listCat = array();
                                    foreach($cats as $key2 => $value2){                                
                    ?>
                    <table class="table table-hover table-rounded border gy-5 gs-7" style="width:100%">
                        <tbody>
                                <?php
                                        $listCat[] = $value2;
                                        $totalItem = count($value[$value2]);
                                        $lastItem = end($value[$value2]);
                                        $lastItemName = $lastItem['itemname'];
                                        echo '<tr>';
                                        echo '<td style="width: 150px; max-width: 150px;">' . $value2 . '</td>';
                                        $ct++;
                                        foreach($value[$value2] as $key3 => $value3){
                                            $ti++;
                                            $ct++;
                                            if($ct == 6){
                                                echo '</tr><tr>';
                                                echo '<td style="width: 150px; max-width: 150px;"></td>';
                                                $ct = 2;
                                            }
                                            $is_quick_starter = $value3['is_quick_starter'];
                                            if($is_quick_starter == 1) { // checked
                                                echo '<td style="width: 250px; max-width: 250px;"><input type="checkbox" name="itemids[]" value="p' . $value3['id'] . '" checked id="activate-' . $value3['slug'] . '">' . $value3['itemname'] . '</td>';
                                            } else {
                                                echo '<td style="width: 250px; max-width: 250px;"><input type="checkbox" name="itemids[]" value="p' . $value3['id'] . ' id="activate-' . $value3['slug'] . '">' . $value3['itemname'] . '</td>';
                                            }
                                            if($ti == $totalItem){
                                                if($ti < 5){
                                                    for($i = $ti; $i < 5; $i++){
                                                        $ti++;
                                                        echo '<td style="width: 250px; max-width: 250px;"></td>';
                                                    }
                                                }
                                                $ti = 0;
                                                $ct = 0;
                                                echo '</tr>';
                                            }
                                        }
                                        echo '</tr>';
                                        ?>
                        </tbody>
                    </table>
                                        <?php
                                    }
                                }
                            ?>
                    <table class="table table-hover table-rounded border gy-5 gs-7" style="width:100%">
                        <tfoot style="align-items: center;">
                            <tr>
                                <td colspan="12">
                                    <button type="button" class="btn btn-success" onclick="ultrapm_insact()" id="bulk-installNactivate">Install & Activate</button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
            </div>        
        </div>
    </div>
</div>














    <?php
    include( ULTRAPM_INC_PATH . '/part/search-engine.php');
    include( ULTRAPM_INC_PATH . '/part/content-end.php');
    include( ULTRAPM_INC_PATH . '/part/task-list.php');
    include( ULTRAPM_INC_PATH . '/part/scripts.php');
}
?>