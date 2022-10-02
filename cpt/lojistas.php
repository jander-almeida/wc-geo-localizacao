<?php

defined('ABSPATH') || die('Você não tem poder aqui');

// Register Custom Post Type
function custom_post_type_lojistas() {

	$labels = array(
		'name'                  => _x( 'Lojistas', 'Post Type General Name', 'dsp_lojistas' ),
		'singular_name'         => _x( 'Lojista', 'Post Type Singular Name', 'dsp_lojistas' ),
		'menu_name'             => __( 'Lojas externas', 'dsp_lojistas' ),
		'name_admin_bar'        => __( 'Lojistas externos', 'dsp_lojistas' ),
		'archives'              => __( 'Item Archives', 'dsp_lojistas' ),
		'attributes'            => __( 'Item Attributes', 'dsp_lojistas' ),
		'parent_item_colon'     => __( 'Parent Item:', 'dsp_lojistas' ),
		'all_items'             => __( 'All Items', 'dsp_lojistas' ),
		'add_new_item'          => __( 'Add New Loja', 'dsp_lojistas' ),
		'add_new'               => __( 'Add New', 'dsp_lojistas' ),
		'new_item'              => __( 'New Item', 'dsp_lojistas' ),
		'edit_item'             => __( 'Edit Item', 'dsp_lojistas' ),
		'update_item'           => __( 'Update Item', 'dsp_lojistas' ),
		'view_item'             => __( 'View Item', 'dsp_lojistas' ),
		'view_items'            => __( 'View Items', 'dsp_lojistas' ),
		'search_items'          => __( 'Search Item', 'dsp_lojistas' ),
		'not_found'             => __( 'Not found', 'dsp_lojistas' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'dsp_lojistas' ),
		'featured_image'        => __( 'Featured Image', 'dsp_lojistas' ),
		'set_featured_image'    => __( 'Set featured image', 'dsp_lojistas' ),
		'remove_featured_image' => __( 'Remove featured image', 'dsp_lojistas' ),
		'use_featured_image'    => __( 'Use as featured image', 'dsp_lojistas' ),
		'insert_into_item'      => __( 'Insert into item', 'dsp_lojistas' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'dsp_lojistas' ),
		'items_list'            => __( 'Items list', 'dsp_lojistas' ),
		'items_list_navigation' => __( 'Items list navigation', 'dsp_lojistas' ),
		'filter_items_list'     => __( 'Filter items list', 'dsp_lojistas' ),
	);
	$args = array(
		'label'                 => __( 'Lojista', 'dsp_lojistas' ),
		'description'           => __( 'Lista de lojas para vincular a loja pai', 'dsp_lojistas' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'revisions' ),
		'taxonomies'            => array(),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'menu_icon'             => 'dashicons-store',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => false,
		'exclude_from_search'   => true,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
		'map_meta_cap'          => true,
        'capabilities' => array(
            'edit_post'          => 'update_core',
            'read_post'          => 'update_core',
            'delete_post'        => 'update_core',
            'edit_posts'         => 'update_core',
            'edit_others_posts'  => 'update_core',
            'delete_posts'       => 'update_core',
            'publish_posts'      => 'update_core',
            'read_private_posts' => 'update_core'
        ),
		'show_in_rest'          => true,
	);
	register_post_type( 'lojistas', $args );

}
add_action( 'init', 'custom_post_type_lojistas', 12 );

/** 
 * ==================================================================================
 *                          Custom METABOX to CPT
 * ==================================================================================
*/

/**
 * Metabox:: Informações do lojistas
*/
add_action( 'cmb2_admin_init', 'dsp_metabox_informacoes_lojistas' );
function dsp_metabox_informacoes_lojistas() {
	/**
	 * Sample metabox to demonstrate each field type included
	 */
	$dsp_lojas = new_cmb2_box( array(
		'id'            => 'dsp_metabox_informacoes_lojistas_fields',
		'title'         => esc_html__( 'Informações do Lojista' ),
		'object_types'  => array( 'lojistas' ), // Post type
		'priority'      => 'high',
		'show_names'    => true, // Show field names on the left
	) );

	$dsp_lojas->add_field(
	    array(
    		'name'       => esc_html__( 'CEP'  ),
    		'desc'       => esc_html__( 'O CEP da Loja' ),
    		'id'         => 'dsp_cep',
    		'type'       => 'text',
    		'attributes'    => array(
    		    "placeholder"   => "EX: 09009-999"
    		)
	    )
	);
	$dsp_lojas->add_field(
	    array(
    		'name'       => esc_html__( 'Nome do gerente'  ),
    		'desc'       => esc_html__( 'O nome do gerente da Loja.' ),
    		'id'         => 'dsp_gerente',
    		'type'       => 'text',
    		'attributes'    => array(
    		    "placeholder"   => "EX: Caio Rolando da Rocha"
    		)
	    )
	);
    $dsp_lojas->add_field(
	    array(
    		'name'          => esc_html__( 'Endereço completo'  ),
    		'desc'          => esc_html__( 'O endereço completo da loja' ),
    		'id'            => 'dsp_endereco',
    		'type'          => 'text',
    		'attributes'    => array(
    		    "placeholder"   => "Rua sem nome, 200, Brasília - DF, 09009-999"
    		)
	    )
    );
    $dsp_lojas->add_field(
	    array(
    		'name'          => esc_html__( 'Latitude'  ),
    		'desc'          => esc_html__( 'A latitude da loja (veja no Google Maps)' ),
    		'id'            => 'dsp_latitude',
    		'type'          => 'text',
    		'attributes'    => array(
    		    "placeholder"   => "-27.9901232"
    		)
	    )
    );
    $dsp_lojas->add_field(
	    array(
    		'name'          => esc_html__( 'Longitude'  ),
    		'desc'          => esc_html__( 'A longitude da loja (veja no Google Maps)' ),
    		'id'            => 'dsp_longitude',
    		'type'          => 'text',
    		'attributes'    => array(
    		    "placeholder"   => "-35.978912"
    		)
	    )
    );
    $sitedemo = site_url("/site-de-teste");
    $dsp_lojas->add_field(
	    array(
    		'name'          => esc_html__( 'Site'  ),
    		'desc'          => esc_html__( 'Digite o site para que será redirecionado' ),
    		'id'            => 'dsp_website',
    		'type'          => 'text',
    		'attributes'    => array(
    		    "placeholder"   => "Ex: $sitedemo"
    		)
	    )
    );
}
