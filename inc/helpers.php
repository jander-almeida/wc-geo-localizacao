<?php

defined('ABSPATH') || die ('Você não tem poder aqui');

/**
 * Mask in PHP
 * @link : https://gist.github.com/leonirlopes/5a4a1f796c776d4a695b2d8ca78ab108
	$cnpj = '11222333000199';
	$cpf = '00100200300';
	$cep = '08665110';
	$data = '10102010';
	$hora = '021050';

	echo mask($cnpj, '##.###.###/####-##').'<br>';
	echo mask($cpf, '###.###.###-##').'<br>';
	echo mask($cep, '#####-###').'<br>';
	echo mask($data, '##/##/####').'<br>';
	echo mask($data, '##/##/####').'<br>';
	echo mask($data, '[##][##][####]').'<br>';
	echo mask($data, '(##)(##)(####)').'<br>';
	echo mask($hora, 'Agora são ## horas ## minutos e ## segundos').'<br>';
	echo mask($hora, '##:##:##');
 */
function mask($val, $mask) {
    $maskared = '';
    $k = 0;
    for ($i = 0; $i <= strlen($mask) - 1; ++$i) {
        if ($mask[$i] == '#') {
            if (isset($val[$k])) {
                $maskared .= $val[$k++];
            }
        } else {
            if (isset($mask[$i])) {
                $maskared .= $mask[$i];
            }
        }
    }

    return $maskared;
}

/**
 * Get store by cep
 */
add_action('wp_ajax_get_location_cep' , 'get_location_cep');
add_action('wp_ajax_nopriv_get_location_cep','get_location_cep');
function get_location_cep(){
	$cep = $_POST['cep'];
	$cep = sanitize_text_field( urldecode($_POST['cep']) );
	$cep = mask($cep, "##.###-###");
	
	$return['url']		= '';
	$return['id']		= 0;
	$return['count']	= 0;
	$return['cep']		= $cep;
	$return['msg']		= 'Nenhuma loja encontrada na região fornecida';
	
	if( isset( $cep ) ){
		$args = array(
			'meta_query' => array(
				'relation' => 'OR',
					array(
						'key'     => '_wcfm_zip',
						'value'   => $cep,
						'compare' => '='
					)
				)
		);
		
		$user_query = new WP_User_Query( $args );
		$total = count($user_query->get_results());
		
		if( $total > 0 ){
			$slugLoja['slug_base'] = get_option('wcfm_store_url', true); //URL BASE das lojas WCFM
			$slugLoja['slug_user'] = $user_query->get_results()[0]->user_nicename; //Slug WCFM
			$url_loja = site_url(implode($slugLoja, '/') ); //LINK completo da loja encontrada
			
			$loja = $total > 1 ? "$total lojas encontradas" : ( $total == 0 ? "Nenhuma loja encontrada" : "1 loja encontrada" );
			
			$return['url']		= $url_loja;
			$return['id']		= $user_query->get_results()[0]->ID;
			$return['count']	= $total;
			$return['cep']		= $cep;
			$return['msg']		= $loja;
		}
	}
	wp_send_json($return, 200);
	die();
}


/**
 * Obter o IP do usuário e retornar informações da localização atual
 * @source : https://ip-api.com/docs/api:serialized_php
 * @return object
*/
function getCoordinate() {
    $ip = $_SERVER['REMOTE_ADDR'];
    $r = wp_remote_get("http://ip-api.com/php/$ip");
    $r = wp_remote_retrieve_body($r);
    $r = maybe_unserialize($r);
    return $r;
}

/**
 * Source: https://woocommerce.com/document/tutorial-customising-checkout-fields-using-actions-and-filters/#section-3
*/
add_filter('woocommerce_billing_fields', 'custom_woocommerce_billing_fields');

function custom_woocommerce_billing_fields($fields) {

    $fields['billing_house_number'] = array(
        'label'         => __('Número da casa ou da Obra', 'woocommerce'), // Add custom field label
        'placeholder'   => _x('EX: 200-A', 'placeholder', 'woocommerce'), // Add custom field placeholder
        'required'      => true, // if field is required or not
        'clear'         => false, // add clear or not
        'type'          => 'text', // add field type
        'class'         => array('my-css'),    // add class name
        'priority'      => 51
    );
    $fields['billing_cpf_cnpj'] = array(
        'label'         => __('CNP ou CNPJ', 'woocommerce'), // Add custom field label
        'placeholder'   => _x('CPF ou CNPJ', 'placeholder', 'woocommerce'), // Add custom field placeholder
        'required'      => true, // if field is required or not
        'clear'         => false, // add clear or not
        'type'          => 'text', // add field type
        'class'         => array(''),    // add class name
        // 'priority'      => 55
    );
    $fields['billing_nascimento'] = array(
        'label'         => __('Data de nascimento', 'woocommerce'), // Add custom field label
        'placeholder'   => _x('01/01/1990', 'placeholder', 'woocommerce'), // Add custom field placeholder
        'required'      => true, // if field is required or not
        'clear'         => false, // add clear or not
        'type'          => 'text', // add field type
        'class'         => array(''),    // add class name
        // 'priority'      => 55
    );
    // echo "<pre>";
    // print_r($fields);
    // echo "</pre>";
    return $fields;
}
add_filter( 'woocommerce_billing_fields', 'wc_npr_filter_phone', 10, 1 );
function wc_npr_filter_phone( $address_fields ) {
	$address_fields['billing_state']['required'] = false;
	return $address_fields;
}
/**
 * Update the order meta with field value
 */
add_action( 'woocommerce_checkout_update_order_meta', 'my_custom_checkout_field_update_order_meta' );

function my_custom_checkout_field_update_order_meta( $order_id ) {
    // if ( ! empty( $_POST['my_field_name'] ) ) {
        update_post_meta( $order_id, 'billing_house_number', sanitize_text_field( $_POST['billing_house_number'] ) );
        update_post_meta( $order_id, 'billing_cpf_cnpj', sanitize_text_field( $_POST['billing_cpf_cnpj'] ) );
        update_post_meta( $order_id, 'billing_nascimento', sanitize_text_field( $_POST['billing_nascimento'] ) );
    // }
}

/**
 * Display field value on the order edit page
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'my_custom_checkout_field_display_admin_order_meta', 10, 1 );

function my_custom_checkout_field_display_admin_order_meta($order){
    echo '<p><strong>'.__('Numero da casa ou obra').':</strong> ' . get_post_meta( $order->id, 'billing_house_number', true ) . '</p>';
    echo '<p><strong>'.__('CPF ou CNPJ:').':</strong> ' . get_post_meta( $order->id, 'billing_cpf_cnpj', true ) . '</p>';
    echo '<p><strong>'.__('Data nascimento').':</strong> ' . get_post_meta( $order->id, 'billing_nascimento', true ) . '</p>';
}

/* Disable all payment gateways at checkout */
add_filter( 'woocommerce_cart_needs_payment', '__return_false' );