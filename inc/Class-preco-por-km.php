<?php

/** 
 * Calcular preço por KM
 */

defined( 'ABSPATH' ) || exit;

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
 
    function dsp_price_by_km_shipping_method() {
        if ( ! class_exists( 'dsp_price_by_km_Shipping_Method' ) ) {
            class dsp_price_by_km_Shipping_Method extends WC_Shipping_Method {
                /**
                 * Constructor for your shipping class
                 *
                 * @access public
                 * @return void
                 */
                public function __construct() {
                    $settings_price_by_km = get_option('settings_price_by_km'); //Get all settings from dashboard manager this plugin
                    
                    $this->id                   = 'dsp_price_by_km'; //ID from shipping
                    $this->method_title         = isset($settings_price_by_km['dsp_shipping_name']) ? $settings_price_by_km['dsp_shipping_name'] : 'Frete por KM';
                    $this->method_description   = isset($settings_price_by_km['dsp_shipping_description']) ? $settings_price_by_km['dsp_shipping_description'] : 'Frete por KM para calcular por distâncias';
                    $this->settings_km          = $settings_price_by_km;
                    
                    // Availability & Countries
                    $this->availability = 'including';
                    $this->countries = array(
                        'BR'
                    );
                    
                    $this->init();
                    
                    $this->enabled  = isset( $this->settings['enabled'] ) ? $this->settings['enabled']  : 'yes';
                    $this->title    = isset( $this->settings['title']   ) ? $this->settings['title']    : $this->get_field("dsp_shipping_name");
                    
                }
                
                public function get_field( $chave = '' ){
                    $field = $this->settings_km;
                    return isset( $field[$chave] ) || array_key_exists($chave, $field) ? $field[$chave] : '';
                }
                /**
                 * Init your settings
                 *
                 * @access public
                 * @return void
                 */
                function init() {
                    // Load the settings API
                    $this->init_form_fields(); 
                    $this->init_settings(); 
 
                    // Save settings in admin if you have any defined
                    add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
                }
 
                /**
                 * Define settings field for this shipping
                 * @return void 
                 */
                function init_form_fields() { 
 
                    // We will add our settings here
                    $this->form_fields = array(
 
                       'enabled' => array(
                            'title' => __( 'Habilitar' ),
                            'type' => 'checkbox',
                            'description' => __( 'Habilitar entregas baseada em Km' ),
                            'default' => 'yes'
                            ),
               
                       'title' => array(
                          'title' => __( 'Título'),
                            'type' => 'text',
                            'description' => __( 'Título desse método de entrega no site' ) ,
                            'default' => $this->settings_km['dsp_shipping_name']
                            ),

                        'cep' => array(
                          'title' => __( 'CEP Origem' ),
                            'type' => 'text',
                            'description' => __( 'O CEP de origem para cálculo da entrada expressa (exemplo 9999-999)' ),
                            'default' => $this->settings_km["dsp_shipping_zipcode"]
                            ),

               );
 
                }
 
                /**
                 * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
                 *
                 * @access public
                 * @param mixed $package
                 * @return void
                 */
                public function calculate_shipping( $package = [] ) {
                    // We will add the cost, rate and logics in here
                    $cost = 20;
                    
                    //$cep = $package["destination"]["postcode"] || ($_COOKIE['cep_cliente']|| 09000000);
                    $cep = $package["destination"]["postcode"];
                    
                    $cep = str_replace("-", "", str_replace(".", "", $cep), $cep); //Remove dots and separators from zipcode
                    
                    $dsp_shipping_tax_is_fixed                  = array_key_exists('dsp_shipping_tax_is_fixed', $this->settings_km ) == true ? $this->settings_km['dsp_shipping_tax_is_fixed'] : "";
                    $dsp_shipping_tax_fix_per_km                = array_key_exists('dsp_shipping_tax_fix_per_km', $this->settings_km ) == true ? $this->settings_km['dsp_shipping_tax_fix_per_km'] : "";
                    
                    $dsp_shipping_tax_min_fixed                 = array_key_exists('dsp_shipping_tax_min_fixed', $this->settings_km ) == true ? $this->settings_km['dsp_shipping_tax_min_fixed'] : "";
                    $dsp_shipping_tax_fee_fixed                 = array_key_exists('dsp_shipping_tax_fee_fixed', $this->settings_km ) == true ? $this->settings_km['dsp_shipping_tax_fee_fixed'] : "";
                    
                    $dsp_shipping_tax_if_zipcode_no_found       = array_key_exists('dsp_shipping_tax_if_zipcode_no_found', $this->settings_km ) == true ? $this->settings_km['dsp_shipping_tax_if_zipcode_no_found'] : "";
                    $dsp_shipping_tax_if_zipcode_no_found       = str_replace(",",".",$dsp_shipping_tax_if_zipcode_no_found);
                    
                    $dsp_shipping_tax_is_free                   = array_key_exists('dsp_shipping_tax_is_free', $this->settings_km ) == true ? $this->settings_km['dsp_shipping_tax_is_free'] : "";
                    
                    $dsp_shipping_max_distance_free_in_km       = array_key_exists('dsp_shipping_max_distance_free_in_km', $this->settings_km ) == true ? $this->settings_km['dsp_shipping_max_distance_free_in_km'] : "";
                    $dsp_shipping_max_distance_free_in_km       = str_replace(",",".", $dsp_shipping_max_distance_free_in_km);
                    
                    $dsp_shipping_zipcode                       = array_key_exists('dsp_shipping_zipcode', $this->settings_km ) == true ? $this->settings_km['dsp_shipping_zipcode'] : "";
                    $dsp_shipping_zipcode                       = str_replace("-", "", str_replace(".", "", $dsp_shipping_zipcode), $dsp_shipping_zipcode);
                    $dsp_google_apikey                          = array_key_exists('dsp_google_apikey', $this->settings_km ) == true ? $this->settings_km['dsp_google_apikey'] : "";
                    
                    $dsp_metabox_preco_por_km                   = array_key_exists('dsp_metabox_preco_por_km', $this->settings_km ) == true ? $this->settings_km['dsp_metabox_preco_por_km'] : [];
                    
                     // LOAD THE WC LOGGER
                    $logger = wc_get_logger();
                      
                    // LOG THE FAILED ORDER TO CUSTOM "failed-orders" LOG
                    //$logger->info( "aqui a noticia", array( 'source' => 'entrega-por-km' ) );
                        $map_call = "https://maps.googleapis.com/maps/api/distancematrix/xml?origins=$dsp_shipping_zipcode|&destinations=$cep&key=$dsp_google_apikey";
                        
                        $xml = simplexml_load_file($map_call);
                        $google_ok = array_key_exists('status', (array) $xml ) && $xml->status == 'OK' ? true : false;
                        
                        $distancia = $google_ok == true ? $xml->row->element->distance->value[0] : 99999909;
                        
                        $achei = 0;
                        $ultima_distancia = -1;
                        
                        // REGRAS DE ENVIO
                        if( $dsp_shipping_tax_is_fixed != "Sim" ):
    
                                if( count($dsp_metabox_preco_por_km) > 0 ):
                                    foreach( $dsp_metabox_preco_por_km as $sub ){
                                        $ultima_distancia++;
                                        if( $achei==0 && $distancia <= $sub['distancia_da_origem'] ):
                                            
                                            $cost               = $sub['valor_para_o_envio'];
                                            $ultima_distancia   = $distancia;
                                            $achei              = 1;
                                            
                                            $logger->info( "CORRESPONDENCIA: $ultima_distancia", array( 'source' => 'dsp-entrega-por-km' ) );
                                            
                                        endif;
                                    }
                                endif;
    
                                if( $distancia == "" ) $cost = 0;
                                
                                if( $achei == 1 ):
                                    
                                      if( $dsp_shipping_tax_fee_fixed != "" && $dsp_shipping_tax_fee_fixed !="0" && $cost != 0 ) : $cost = $cost + $dsp_shipping_tax_fee_fixed; endif;
                                      if( $dsp_shipping_tax_min_fixed != "" && $dsp_shipping_tax_min_fixed !="0" && $cost != 0 && $cost<$dsp_shipping_tax_min_fixed): $cost = $dsp_shipping_tax_min_fixed;  endif;
    
                                      $rate = array(
                                          'id' => $this->id,
                                          'label' => $this->title,
                                          'cost' => $cost
                                      );
    
                                      if($cost!=0 && $cost!="" && $distancia>$dsp_shipping_max_distance_free_in_km) $this->add_rate( $rate );
                                      if($cost==0 && $dsp_shipping_tax_if_zipcode_no_found!="" && $distancia>$dsp_shipping_max_distance_free_in_km || $cost=="" && $dsp_shipping_tax_if_zipcode_no_found!="" && $distancia>$dsp_shipping_max_distance_free_in_km):
    
                                        $rate = array(
                                            'id' => $this->id,
                                            'label' => $this->title,
                                            'cost' => $dsp_shipping_tax_if_zipcode_no_found
                                        );
    
                                        $this->add_rate( $rate );
    
                                      endif;
    
                                      if($distancia<=$dsp_shipping_max_distance_free_in_km && $dsp_shipping_tax_is_free=="Sim"):
    
                                             $rate = array(
                                                'id' => $this->id,
                                                'label' => $this->title,
                                                'cost' => 0
                                            );
    
                                            $this->add_rate( $rate );
    
    
                                      endif;
    
                                endif;
    
                        // VALOR POR KM
                        else:
                                $distancia_origem = $distancia;
                                $distancia = $distancia / 1000;
    
                                $cost = $distancia * $dsp_shipping_tax_is_fixed;
                                $cost = number_format($cost,2,",",".");
                                
                                $logger->info( "CALC KM: ".$distancia, array( 'source' => 'entrega-por-km' ) );
                                  if($distancia_origem<=$dsp_shipping_max_distance_free_in_km && $dsp_shipping_tax_is_free=="Sim"):
                                             $rate = array(
                                                'id' => $this->id,
                                                'label' => $this->title,
                                                'cost' => 0
                                            );
                                            $this->add_rate( $rate );
                                    else:
                                      if($dsp_shipping_tax_fee_fixed!="" && $dsp_shipping_tax_fee_fixed!="0"): $cost = $cost + $dsp_shipping_tax_fee_fixed; endif;
                                      if($dsp_shipping_tax_min_fixed!="" && $dsp_shipping_tax_min_fixed!="0" && $cost<$dsp_shipping_tax_min_fixed): $cost = $dsp_shipping_tax_min_fixed;  endif;
    
                                $rate = array(
                                          'id' => $this->id,
                                          'label' => $this->title,
                                          'cost' => $cost
                                );
                              endif;
                                if($cost!=0 && $cost!="") $this->add_rate( $rate );
                                if($cost==0 && $dsp_shipping_tax_if_zipcode_no_found!="" || $cost=="" && $dsp_shipping_tax_if_zipcode_no_found!=""):
    
                                  $logger->info( "ENTRE 2", array( 'source' => 'entrega-por-km' ) );
                                  $logger->info( $distancia_origem, array( 'source' => 'entrega-por-km' ) );
                                  $logger->info( $dsp_shipping_max_distance_free_in_km, array( 'source' => 'entrega-por-km' ) );
                                  $logger->info( $dsp_shipping_tax_is_free, array( 'source' => 'entrega-por-km' ) );
    
                                 
    
                                   if($distancia_origem<=$dsp_shipping_max_distance_free_in_km && $dsp_shipping_tax_is_free=="Sim"):
    
                                             $rate = array(
                                                'id' => $this->id,
                                                'label' => $this->title,
                                                'cost' => 0
                                            );
    
                                            $this->add_rate( $rate );
    
                                    else:
                                                 $rate = array(
                                                'id' => $this->id,
                                                'label' => $this->title,
                                                'cost' => $dsp_shipping_tax_if_zipcode_no_found
                                            );
                                    endif;
                                  $this->add_rate( $rate );
                                endif;
                        endif;
                    // }
                }
            }
        }
    }
 
    add_action( 'woocommerce_shipping_init', 'dsp_price_by_km_shipping_method' );
 
    function add_dsp_price_by_km_shipping_method( $methods ) {
        $methods[] = 'dsp_price_by_km_Shipping_Method';
        return $methods;
    }
 
    add_filter( 'woocommerce_shipping_methods', 'add_dsp_price_by_km_shipping_method' );
}
//add_action( 'woocommerce_review_order_before_cart_contents', 'dsp_price_by_km_validate_order' , 10 );


add_action( 'wp_head', 'remove_change_cep');
function remove_change_cep(){
    $settings_price_by_km = get_option('settings_price_by_km'); //Get all settings from dashboard manager this plugin
    $site_tipe = array_key_exists('dsp_is_site_type', $settings_price_by_km ) == true ? $settings_price_by_km['dsp_is_site_type'] : "filho";
    if($site_tipe == 'filho'){ ?>
        <style>
            .woocommerce-shipping-calculator {
                display: none !important;
            }
        </style>
        <?php
    }
}

add_action( 'wp_footer',  function(){ 
    if( function_exists('is_cart') ) {
        if( is_cart() ){ ?>
            <script>
            
                // Cookies : https://stackoverflow.com/a/1599291
                function createCookie(name, value, days) {
                    if (days) {
                        var date = new Date();
                        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                        var expires = "; expires=" + date.toGMTString();
                    }
                    else var expires = "";               
                
                    document.cookie = name + "=" + value + expires + "; path=/";
                }
                
                function readCookie(name) {
                    var nameEQ = name + "=";
                    var ca = document.cookie.split(';');
                    for (var i = 0; i < ca.length; i++) {
                        var c = ca[i];
                        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
                    }
                    return null;
                }
                
                function eraseCookie(name) {
                    createCookie(name, "", -1);
                }
            
                function getShippingCustomer(){
                    jQuery('#calc_shipping_country').val( readCookie('c_country') );
                    jQuery(`#calc_shipping_country`).trigger('change');
                    
                    jQuery('#calc_shipping_state').val( readCookie('c_state') );
                    jQuery(`#calc_shipping_state`).trigger('change');
                    
                    jQuery('#calc_shipping_city').val( readCookie('c_city') );
                    jQuery('#calc_shipping_postcode').val( readCookie('c_zipcode') );
                    
                    jQuery('button[name="calc_shipping"]').click();
                    window.stop();
                    return;
                }
                getShippingCustomer();
            </script>
            <?php
        }
    }
}, 12);