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
       $cep = str_replace( "-", "", $cep);
       $cep = str_replace( ".", "", $cep);
       $cep = (int) filter_var($cep, FILTER_SANITIZE_NUMBER_INT);
       $current_site = site_url('/', 'https');
       
        $a = 0;
        $lojistas = [];
           
       $is_multisite = is_multisite();
       if( !$is_multisite ){
           $url_stores = get_option('wcfm_store_url', true);
           
    		$args = array(
    		    'role'  => 'wcfm_vendor'
            );
    		
            $user_query = new WP_User_Query( $args );
            $lojas = $user_query->get_results();
            $total = count($lojas);
            
            if( $total > 0){
                foreach($lojas as $loja ){
                    $slug_vendor = $loja->user_nicename;
                    $lojistas[$a]["is_multisite"]   = is_multisite();
                    $lojistas[$a]["nome_lojista"]   = get_user_meta( $loja->ID, 'wcfmmp_store_name', true);
                    $lojistas[$a]["id"]             = $loja->ID;
                    $lojistas[$a]["lat"]            = trim( get_user_meta( $loja->ID, '_wcfm_store_lat', true) );
                    $lojistas[$a]["lon"]            = trim( get_user_meta( $loja->ID, '_wcfm_store_lng', true) );
                    $lojistas[$a]["distancia"]      = $this->haversineGreatCircleDistance($lat, $lon, $lojistas[$a]["lat"], $lojistas[$a]["lon"]);
                    $lojistas[$a]["url"]            = site_url("$url_stores/$slug_vendor/");

                    $a++;
                }
            }
            
        } else {
            $sites = wp_get_sites( $args );
            $total = count($sites);
            if( $sites > 0){
                foreach($sites as $site ){
                    $slug_vendor = $site->user_nicename;
                    $lojistas[$a]["is_multisite"]   = is_multisite();
                    $lojistas[$a]["id"]             = $site['blog_id'];
                    $lojistas[$a]["lat"]            = "";
                    $lojistas[$a]["lon"]            = "";
                    $lojistas[$a]["distancia"]      = $this->haversineGreatCircleDistance($lat, $lon, $lojistas[$a]["lat"], $lojistas[$a]["lon"]);
                    $lojistas[$a]["url"]            = site_url($site['path']);
                    // $lojistas[$a]["domain"]         = getDomainbyUrl($lojistas[$a]["url"]);
                    // $lojistas[$a]["test"]            = $site;
                    $a++;
                }
            }
            
        }
        
        $data = array(
            'sucesso'       => "200",
            'cep_cliente'   => $cep,
            'current'       => $current_site,
            'lojistas'      => $lojistas,
            "total"         => $total,
        );
        wp_reset_query();
        wp_send_json($data, 200); 
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

}

add_action('rest_api_init', function () {
    new storeAproximated; // Rodar,
});