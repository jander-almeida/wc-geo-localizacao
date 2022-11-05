<?php
/**
 * Plugin Name:       GEO Directory integrated with WCFM, to Subsidiary
 * Description:       Geo Directory for WCFM - Shortcode to use <code>[filtrar_lojas]</code>
 * Version:           3.0.2
 * Requires at least: 5.2
 * Requires PHP:      7.0
 * Domain Path:       /languages
 */
 
defined( 'ABSPATH') || die();

//CONSTANTS CURRENT PLUGIN
define( 'DSP_GEODIR_PATH',  plugin_dir_path( __FILE__ ) );
define( 'DSP_GEODIR_URL',   plugin_dir_url( __DIR__ ) );

//Load dependencies
add_action('init', function(){
    $allLoad = array(
        "inc",
        "cpt",
        "3rd",
        "REST"
    );
    
    //Carregar todos os componentes
    foreach($allLoad as $dir){
        if( file_exists(DSP_GEODIR_PATH.$dir) ){
            foreach ( glob( DSP_GEODIR_PATH . "$dir/*.php" ) as $file ) {
                include_once $file;
            }
        }
    }
    
    //Load 3rd parts
    require DSP_GEODIR_PATH.'3rd/cmb2/init.php'; //CMB2 CORE
});