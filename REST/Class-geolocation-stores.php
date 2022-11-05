<?php

/**
 * Class para consultar a LOJA mais perto
 * Deve ser passado as coordenadas do usuário
 * As lojas já devem ter suas coordenadas cadastradas para serem encontradas.
 * 
 * Exemplo: http://youdomain.com/wp-json/wp/v2/aproximated/?lat=<coord>&lon=<coord>&cep_destino=<CEP_do_cliente>
 */

defined( 'ABSPATH' ) || exit;

class storeAproximated {
    public function __construct() {
        
        //Os objetos do ambiente, passe no constructor
        $version = '2';
        $namespace = "wp/v$version";
        $base = 'aproximated';
        $this->base_rest = $base;
        
        register_rest_route($namespace, '/' . $base, array(
            'methods' => 'GET',
            'callback' => array($this, 'dsp_run_get_location'),
            'permission_callback' => array( $this, 'dsp_check_permission' ),
            'args' => array(
                'lat' => array(
                    'description'   => 'Insira a Latitude',
                    'type'          => 'string',
                    'require'       => true
                ),
                'lon' => array(
                    'description'   => 'Insira a Longitude',
                    'type'          => 'string',
                    'require'       => true
                ),
                'cep' => array(
                    'description'   => 'Adicone o CEP do cliente',
                    'type'          => 'string',
                    'require'       => true
                ),
				'redirect' => array(
                    'description'   => 'URL que será direcionado',
                    'type'          => 'string',
                    'require'       => false
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
    public function dsp_run_get_location($request) {
        $lat = trim($request['lat']);
        $lon = trim($request['lon']);
        $cep = trim($request['cep']);
        $cep = $this->formatterCep($cep);
		$site = trim($request['redirect']);  //URL do site para redirecionar
		setcookie("c_lat", $lat, time()+3600, '/' );
		setcookie("c_lon", $lon, time()+3600, '/' );
		setcookie("c_cep", $cep, time()+3600, '/' );
       $current_site = site_url('/', 'https');
		$a = 0;
        $lojistas = [];
    
        $args =  array(
            'post_type'     => 'lojistas',
            'post_status'   => 'publish',
            'fields'        => 'ids',
        );
        $query = new WP_Query( $args );
        $total = $query->post_count;

        if( $total > 0){
            foreach($query->posts as $id ){
                $site_list = get_post_meta($id);
                $lojistas[$a]["id"]             = $id;
                $lojistas[$a]["lat"]            = $site_list['dsp_latitude'][0];
                $lojistas[$a]["lon"]            = $site_list['dsp_longitude'][0];
                $lojistas[$a]["distancia"]      = $this->haversineGreatCircleDistance($lat, $lon, $lojistas[$a]["lat"], $lojistas[$a]["lon"]);
                $lojistas[$a]["url"]            = site_url($site['path']);
                $lojistas[$a]['cep']            = $this->formatterCep( $site_list['dsp_cep'][0]);
                $lojistas[$a]["domain"]         = $site_list['dsp_website'][0];
                $a++;
            }
        }
        
        $data = array(
            'sucesso'       => "200",
            'cep_cliente'   => $cep,
            'current'       => $current_site,
            'lojistas'      => $lojistas,
            "total"         => $total,
			"cookies"		=> $_COOKIES,
        );
        wp_reset_query();
        if( filter_var($site, FILTER_VALIDATE_URL) ){
			header("Location: $site");
		} else {
			wp_send_json($data, 200); 
		}
    }
	
	/**
	 * CALCULAR DISTANCIAS
	 * @param string $latitudeFrom      : Latitude do usuário
	 * @param string $longitudeFrom     : Longitude do usuário
	 * @param string $latitudeTo        : Latitude da loja
	 * @param string $longitudeTo       : Longitude da Loja
	 * @param float $earthRadius        : circuferência do planeta TERRA
	 * @return float
	 * 
	 */
	public function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000){
	  // convert from degrees to radians
	  $latFrom  = deg2rad($latitudeFrom);
	  $lonFrom  = deg2rad($longitudeFrom);
	  $latTo    = deg2rad($latitudeTo);
	  $lonTo    = deg2rad($longitudeTo);

	  $latDelta = $latTo - $latFrom;
	  $lonDelta = $lonTo - $lonFrom;

	  $angle    = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
	  
	  return $angle * $earthRadius;
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
    public function formatterCep($cep){
        $cep = str_replace( "-", "", $cep);
        $cep = str_replace( ".", "", $cep);
        $cep = (int) filter_var($cep, FILTER_SANITIZE_NUMBER_INT);
        return $cep;
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
		return $body;
        //wp_send_json($data, 200); 
    }}

add_action('rest_api_init', function () {
    new storeAproximated; // Rodar,
});