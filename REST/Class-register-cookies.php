<?php

/**
 * Class para consultar a LOJA mais perto
 * Deve ser passado as coordenadas do usuário
 * As lojas já devem ter suas coordenadas cadastradas para serem encontradas.
 * 
 * Exemplo: http://youdomain.com/wp-json/wp/v2/aproximated/?lat=<coord>&lon=<coord>&cep_destino=<CEP_do_cliente>
 */

defined( 'ABSPATH' ) || exit;

class registerCookies {
    public function __construct() {
        
        //Os objetos do ambiente, passe no constructor
        $version = '2';
        $namespace = "wp/v$version";
        $base = 'register_cookies';
        $this->base_rest = $base;
        
        register_rest_route($namespace, '/' . $base, array(
            'methods' => 'POST',
            'callback' => array($this, 'dsp_run_register_cookies'),
            'permission_callback' => array( $this, 'dsp_check_permission' ),
            'args' => array(
                'country' => array(
                    'description'   => 'Code Alpha-2: Ex: BR',
                    'type'          => 'string',
                    'require'       => true
                ),
                'state' => array(
                    'description'   => 'Code Alpha-2, Ex: SP',
                    'type'          => 'string',
                    'require'       => true
                ),
                'city' => array(
                    'description'   => 'Nome da cidade',
                    'type'          => 'string',
                    'require'       => true
                ),
                'zipcode' => array(
                    'description'   => 'CEP, ex: 090000-000',
                    'type'          => 'string',
                    'require'       => true
                )
            ),
        ));
    }
    
    public function dsp_check_permission(){
        return true;
    }
    /**
     * Processar e listar lojas para obter as coordenadas e obter a loja mais perto
     * @param Object $request
     * @return Object
    */
    public function dsp_run_register_cookies($request) {
        $body = $request->get_body_params();
        
        $current_site = site_url('/', 'https');
        
        if( count($body) && !empty($body) || $body !== null ){
            foreach( $body as $key => $val ){
                setcookie("c_$key", $val, time()+3600, '/' );
            }
        }
        
        $data = array(
            'registers_cookies' => $body,
        );
        wp_send_json($data, 200); 
    }
	/**
	 * Extract only numbers
	 * @param string $str
	 * @return integer
	*/
	public function getOnlyNumber($str){
        $str = trim($str);
        $str = str_replace( "-", "", $str);
        $str = str_replace( ".", "", $str);
        $str = (int) filter_var($str, FILTER_SANITIZE_NUMBER_INT);
        return $str;
	}
	/**
	 * Extrair o domínio com schema
	 * @param string $url
	 * @return string
	*/
	public function getDomainbyUrl($url){
	    if( filter_var($url, FILTER_VALIDATE_URL) ){
            $url = parse_url($url);
            $url_scheme = $url['scheme'];
            $url_host   = $url['host'];
            return "$url_scheme://$url_host";
	    }
	    return "URL Invalid";
	}

}

add_action('rest_api_init', function () {
    new registerCookies; // Rodar,
});