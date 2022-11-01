<?php

defined('ABSPATH') || die('Você não tem poder aqui');

/**
 * Hook in and register a metabox to handle a theme options page and adds a menu item.
 */
function yourprefix_register_main_options_metabox() {

    $user_id = get_current_user_id();
    $user = get_userdata($user_id);
    $roles = $user->roles;
    $capability = in_array('administrator', $user->roles) ? 'administrator' : (in_array('shop_manager', $user->roles) ? 'shop_manager' : '');

	/**
	 * Registers main options page menu item and form.
	 * //settings_price_by_km
	 */
	$args = array(
		'id'            => 'settings_price_by_km_page',
		'title'         => 'Preço por KM',
		'object_types'  => array( 'options-page' ),
		'option_key'    => 'settings_price_by_km',
		'tab_group'     => 'settings_price_by_km_options',
		'tab_title'     => 'Configurações',
		'capability'    => $capability,
	);

	// 'tab_group' property is supported in > 2.4.0.
	if ( version_compare( CMB2_VERSION, '2.4.0' ) ) {
		$args['display_cb'] = 'settings_price_by_km_options_display_with_tabs';
	}

	$main_options = new_cmb2_box( $args );
    
    if( in_array('administrator', $user->roles) || in_array('shop_manager', $user->roles) ){
    	
    	if( in_array('administrator', $user->roles) ){
        	$main_options->add_field(
        	    array(
            		'name'    => 'Qual o tipo de site?',
            		'desc'    => "Se for um site filho, o shortcode de pesquisa e mudança de CEP no checkout não irá funcionar",
            		'id'      => 'dsp_is_site_type',
            		'type'    => 'select',
            		'show_option_none' => false,
            		'capability'        => 'update_core',
            		'options'           => array(
            			'pai'   => esc_html__( 'Este é um site PAI' ),
            			'filho'   => esc_html__( 'Este é um site FILHO' ),
            		),
            		'default'   => 'filho',
            		
        	    )
        	);
    	}
    
    	$main_options->add_field(
    	    array(
        		'name'    => 'Nome do método de envio',
        		'desc'    => 'O nome do método de envio (exibido no frontend)',
        		'id'      => 'dsp_shipping_name',
        		'type'    => 'text',
        		'required'  => true,
        		'attributes' => array(
        		   'placeholder' => "Ex: Frete"
        		)
    	    )
    	);
    	
    	$frete_url = admin_url('meusite/teste');
    	$main_options->add_field(
    	    array(
        		'name'    => 'Descrição do método de envio',
        		'desc'    => "Outras informações sobre a disponibilidade desse método de envio podem ser encontradas nesse <a href=\"$frete_url\">link</a>",
        		'id'      => 'dsp_shipping_description',
        		'type'    => 'textarea_small',
        		'attributes' => array(
        		   'placeholder' => "Meu método de envio favorito"
        		),
        		'required'  => true,
    	    )
    	);
    
    	$main_options->add_field(
    	    array(
        		'name'    => 'CEP de origem para o cálculo',
        		'desc'    => 'Este CEP é usado inicialmente para o cálculo do frete, é o ponto de partida',
        		'id'      => 'dsp_shipping_zipcode',
        		'type'    => 'text',
        		'attributes' => array(
        		   'placeholder' => "Ex: 68.030-650"
        		)
    	    )
    	);
    	
    	$currency_symbol = get_woocommerce_currency_symbol();
    	$main_options->add_field(
    	    array(
        		'name'    => 'Cobrar valor fixo por cada km de distancia',
        		'desc'    => "Se você marcar que \"Sim\", o campo das regras será ignorado, e considerado apenas o campo do valor $currency_symbol fixo por Km",
        		'id'      => 'dsp_shipping_tax_is_fixed',
        		'type'    => 'select',
        		'show_option_none' => true,
        		'options'          => array(
        			'sim'   => esc_html__( 'Sim' ),
        			'nao'   => esc_html__( 'Não' ),
        		),
    	    )
    	);
    	$main_options->add_field(
    	    array(
        		'name'    => 'Valor gratuíto até certa distância?',
        		'desc'    => "Se você marcar que \"Sim\", até uma determinada distância o valor será $currency_symbol 0,00.",
        		'id'      => 'dsp_shipping_tax_is_free',
        		'type'    => 'select',
        		'show_option_none' => true,
        		'options'          => array(
        			'sim'   => esc_html__( 'Sim' ),
        			'nao'   => esc_html__( 'Não' ),
        		),
    	    )
    	);
    	
    	$main_options->add_field(
    	    array(
        		'name'    => 'Distância máxima para valor gratuíto em caso de opção marcada como sim.',
        		'desc'    => 'Apenas números, casas decimais separados por ponto, o valor é em <code>METROS</code>. (Campo opcional)',
        		'id'      => 'dsp_shipping_max_distance_free_in_km',
        		'type'    => 'text',
        		'attributes' => array(
        		   'type'           => 'number',
        		   'placeholder' => "Ex: 5400.43",
        		   'min'        => '0.01',
        		   'step'       => '0.01',
        		   'max'        => '9999999999.00',
        		)
    	    )
    	);
    
    	$main_options->add_field(
    	    array(
        		'name'    => "Valor $currency_symbol fixo por Km",
        		'id'      => 'dsp_shipping_tax_fix_per_km',
        		'type'    => 'text',
        		'attributes' => array(
        		   'type'           => 'number',
        		   'placeholder' => "Ex: 10.25",
        		   'min'        => '0.01',
        		   'step'       => '0.01',
        		   'max'        => '99999999.00',
        		)
    	    )
    	);
    	$main_options->add_field(
    	    array(
        		'name'    => "Valor $currency_symbol em caso de CEP não encontrado",
        		'desc'    => 'Por diversas razões, alguns CEPs podem não ser reconhecidos pela GoogleMaps API. Nesses casos, defina um valor fixo para a entrega express. Caso deixe em branco, a opção estará indisponível.',
        		'id'      => 'dsp_shipping_tax_if_zipcode_no_found',
        		'type'    => 'text',
        		'attributes' => array(
        		   'type'           => 'number',
        		   'placeholder' => "Ex: 112.21",
        		   'min'        => '0.01',
        		   'step'       => '0.01',
        		   'max'        => '9999999.00',
        		)
    	    )
    	);
    	$main_options->add_field(
    	    array(
        		'name'    => "Valor mínimo $currency_symbol independente da regra",
        		'desc'    => '???',
        		'id'      => 'dsp_shipping_tax_min_fixed',
        		'type'    => 'text',
        		'attributes' => array(
        		   'type'           => 'number',
        		   'placeholder' => "Ex: 5400.43",
        		   'min'        => '0.01',
        		   'step'       => '0.01',
        		   'max'        => '9999999999.00',
        		)
    	    )
    	);
    	$main_options->add_field(
    	    array(
        		'name'    => "Valor taxa de serviço $currency_symbol independente da regra (será somado ao total calculado)",
        		'desc'    => 'É um valor fixo adicionado ao total do pedido.',
        		'id'      => 'dsp_shipping_tax_fee_fixed',
        		'type'    => 'text',
        		'attributes' => array(
        		   'type'           => 'number',
        		   'placeholder' => "Ex: 5400.43",
        		   'min'        => '0.01',
        		   'step'       => '0.01',
        		   'max'        => '9999999999.00',
        		)
    	    )
    	);
    
    	$main_options->add_field(
    	    array(
        		'name'    => 'Google API KEY',
        		'desc'    => 'Google API para calcular preços, distâncias, e tudo mais',
        		'id'      => 'dsp_google_apikey',
        		'type'    => 'text',
        		'required'  => true,
        		'attributes' => array(
        		   'type' => "password",
        		)
    	    )
    	);
    
    	// $group_field_id is the field id string, so in this case: 'yourprefix_group_demo'
    	$group_field_id = $main_options->add_field(
    	    array(
        		'id'          => 'dsp_metabox_preco_por_km',
        		'title'        => 'Regras de preços por KM',
        		'type'        => 'group',
        		'description' => esc_html__( 'As distâncias devem ser consideradas por metros, exemplo para 1km digite 1000, 2Km digite 2000 e assim por diante.'  ),
        		'options'     => array(
        			'group_title'    => esc_html__( 'Regra {#}'  ), // {#} gets replaced by row number
        			'add_button'     => esc_html__( 'Adicionar outra regra'  ),
        			'remove_button'  => esc_html__( 'Remover regra'  ),
        			'sortable'       => true,
        			// 'closed'      => true, // true to have the groups closed by default
        			'remove_confirm' => esc_html__( 'Você tem certeza que deseja remover?'  ), // Performs confirmation before removing group.
    		    ),
    		    'before_row'    => "<h4>Regras de preços por KM</h4>"
    	    )
    	);
    
    	$main_options->add_group_field( $group_field_id, array(
    		'name'       => esc_html__( 'Distância da origem'  ),
    		'id'         => 'distancia_da_origem',
    		'description' => esc_html__( 'Distância da origem em metros, somente números, exemplo: 1000 é igual a 1KM' ),
    		'type'       => 'text',
    		'attributes'  => array(
    		    'type'  => "number",
    		    'min'   => "0.00",
    		    'step'  => "0.01",
    		    "max"   => "999999999.99"
    		 )
    		// 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
    	) );
    
    	$main_options->add_group_field( $group_field_id, array(
    		'name'        => esc_html__( 'Valor por envio'  ),
    		'description' => esc_html__( 'Centavos separados por ponto, ex: 13.56'  ),
    		'id'          => 'valor_para_o_envio',
    		'type'        => 'text',
    		'attributes'  => array(
    		    'type'  => "number",
    		    'min'   => "0.00",
    		    'step'  => "0.01",
    		    "max"   => "999999999.99"
    		 )
    	) );
    }
}
add_action( 'cmb2_admin_init', 'yourprefix_register_main_options_metabox' );

/**
 * A CMB2 options-page display callback override which adds tab navigation among
 * CMB2 options pages which share this same display callback.
 *
 * @param CMB2_Options_Hookup $cmb_options The CMB2_Options_Hookup object.
 */
function settings_price_by_km_options_display_with_tabs( $cmb_options ) {
	$tabs = settings_price_by_km_options_page_tabs( $cmb_options );
	?>
	<div class="wrap cmb2-options-page option-<?php echo $cmb_options->option_key; ?>">
		<?php if ( get_admin_page_title() ) : ?>
			<h2><?php echo wp_kses_post( get_admin_page_title() ); ?></h2>
		<?php endif; ?>
		<h2 class="nav-tab-wrapper">
			<?php foreach ( $tabs as $option_key => $tab_title ) : ?>
				<a class="nav-tab<?php if ( isset( $_GET['page'] ) && $option_key === $_GET['page'] ) : ?> nav-tab-active<?php endif; ?>" href="<?php menu_page_url( $option_key ); ?>"><?php echo wp_kses_post( $tab_title ); ?></a>
			<?php endforeach; ?>
		</h2>
		<form class="cmb-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" id="<?php echo $cmb_options->cmb->cmb_id; ?>" enctype="multipart/form-data" encoding="multipart/form-data">
			<input type="hidden" name="action" value="<?php echo esc_attr( $cmb_options->option_key ); ?>">
			<?php $cmb_options->options_page_metabox(); ?>
			<?php submit_button( esc_attr( $cmb_options->cmb->prop( 'save_button' ) ), 'primary', 'submit-cmb' ); ?>
		</form>
	</div>
	<?php
}

/**
 * Gets navigation tabs array for CMB2 options pages which share the given
 * display_cb param.
 *
 * @param CMB2_Options_Hookup $cmb_options The CMB2_Options_Hookup object.
 *
 * @return array Array of tab information.
 */
function settings_price_by_km_options_page_tabs( $cmb_options ) {
	$tab_group = $cmb_options->cmb->prop( 'tab_group' );
	$tabs      = array();

	foreach ( CMB2_Boxes::get_all() as $cmb_id => $cmb ) {
		if ( $tab_group === $cmb->prop( 'tab_group' ) ) {
			$tabs[ $cmb->options_page_keys()[0] ] = $cmb->prop( 'tab_title' )
				? $cmb->prop( 'tab_title' )
				: $cmb->prop( 'title' );
		}
	}

	return $tabs;
}

add_action('admin_footer', function(){
    $settings_jos = get_option('settings_price_by_km');
    echo "<pre style='padding-left: 25%;'>";
    if( isset($settings_jos) && count($settings_jos) ){
        foreach( $settings_jos['dsp_metabox_preco_por_km'] as $price_by_Km ){
            $distancia  = isset($price_by_Km['distancia_da_origem'])    ? floatval($price_by_Km['distancia_da_origem']) : 0.00;
            $preco_km   = isset($price_by_Km['valor_para_o_envio'])     ? floatval($price_by_Km['valor_para_o_envio']) : 0.00;
        }
    }
    print_r($settings_jos);
    //echo "</pre>";
    
});