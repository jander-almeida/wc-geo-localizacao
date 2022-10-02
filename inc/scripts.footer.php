<?php

defined('ABSPATH') || die('Você não tem poder aqui');

/**
 * Scripts para serem injetados no rodapé
*/
add_action('wp_footer', 'googleMapsStart', 12);
function googleMapsStart(){ ?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js" integrity="sha512-pHVGpX7F/27yZ0ISY+VVjyULApbDlD0/X0rgGbTqCE7WFW5MezNTWG/dnhtbBuICzsd0WQPgpE4REBLv+UqChw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    
    <?php
    if( !is_cart() || !is_checkout() || !is_product() ){
//             $wcfm_marketplace_options = get_option('wcfm_marketplace_options');
//             $apiKey_googleMaps = isset($wcfm_marketplace_options['wcfm_google_map_api']) && !empty($wcfm_marketplace_options['wcfm_google_map_api'])? sanitize_text_field($wcfm_marketplace_options['wcfm_google_map_api']) : 'INSERT_YOUR_API_KEY_GOOGLE_MAPS_HERE';
            
		            $dataSets = get_option('settings_price_by_km');
            
            $apiKey_googleMaps = array_key_exists( 'dsp_google_apikey', $dataSets) && !empty($dataSets['dsp_google_apikey']) ? $dataSets['dsp_google_apikey'] : 'INSERT_YOUR_API_KEY_GOOGLE_MAPS_HERE';
		
            $coord = getCoordinate(); //Obter coordenadas do usuário atual ou do server pré-configurado
            $coord_lat = isset($coord['lat']) ? trim(strval($coord['lat']) ) : '';
            $coord_lon = isset($coord['lon']) ? trim(strval($coord['lon']) ) : '';
            
        ?>
        
        <script src="https://maps.googleapis.com/maps/api/js?key=<?php _e($apiKey_googleMaps)?>&libraries=places&region=in"></script>
        <script type="text/javascript">
			function isURL(str) {
				var pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
				'((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.?)+[a-z]{2,}|'+ // domain name
				'((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
				'(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
				'(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
				'(\\#[-a-z\\d_]*)?$','i'); // fragment locator
				return pattern.test(str);
			}
			function getOnlyCep(str){
				var str = "Av. Curua-Una - São José Operário, Santarém - PA, 68020-650, Brasil";
				var str = str.match(/, [0-9](.*?)[0-9]+,/);
				return str[0].replace(/\.|\-/g, '').replaceAll(',', '').replaceAll(' ', '') ;
			}
        		// VARIAVEIS GLOBAIS DO MAP
        		var input;
        		var map;
        		var b;
        		var directionsDisplay; // Instanciaremos ele mais tarde, que será o nosso google.maps.DirectionsRenderer
        		// SETAR AS COORDANADAS PADRÃO CASO NÃO AS TENHAMOS
        		
        		var pscLat = "<?php _e($coord_lat)?>";
        		var pscLon = "<?php _e($coord_lon)?>";
        
        		function initMapa() {
        		    if( jQuery("#dsp-filtra").length > 0 ){
            		    console.log("INICIANDO FUNÇÃO PARA ESTANCIAR O MAPA GOOGLEMAPS");
            		    console.log("%c NESSA FUNÇÃO VAMOS INICIAR O AUTOCOMPLETE", "background:#ff0000;color:#fff;");
            		    // AUTO COMPLETO DO ENDEREÇO
            		    var input = document.querySelector("#dsp-filtra");
            		    var autoComplete = new google.maps.places.Autocomplete(input);
            		    var directionsService = new google.maps.DirectionsService();
            		    google.maps.event.addDomListener(window, 'load', autoComplete);
        		    }
        		}
        
        		function getCoordinates(address) {
        		    if( address.trim() == "" ) {
		                b.close();
		                var c = jQuery.confirm({
		                    title: 'DADOS VAZIOS!',
		                    type: "red",
		                    content: 'Preencha os dados corretamente para prosseguir',
		                    buttons: {
		                        heyThere: {
		                            text: 'Ok', // With spaces and symbols
		                            action: function() {
		                                c.close();
		                                jQuery('#dsp-filtra').val("");
		                            }
		                        }
		                    }
		                });
        		        return;
        		    }
        		    fetch("https://maps.googleapis.com/maps/api/geocode/json?address=" + address + '&key=<?php _e($apiKey_googleMaps)?>').then(response => response.json()).then(data => {
        		        console.log('inData: ', data);
        		        const cepOrigem	= data.status == 'OK' ? getOnlyCep(data.results[0].formatted_address) : false;
        		        const latitude	= data.status == 'OK' ? data.results[0].geometry.location.lat : false;
            		    const longitude	= data.status == 'OK' ? data.results[0].geometry.location.lng : false;
        		        console.log('CEP Origem: ', cepOrigem);
						
						if( !latitude || !longitude ){
							b.close();
							alert('Sem lojas encontradas perto de você');
							return;
						}
        		      //  try{

            		  //      console.log({
            		  //          latitude,
            		  //          longitude
            		  //      });
        		      //  } catch(er){
        		      //          b.close();
        		      //          var c = jQuery.confirm({
        		      //              title: 'NÃO LOCALIZADO!',
        		      //              type: "red",
        		      //              content: 'OS DADOS DIGITADOS NÃO SÃO CORRETOS, TENTE NOVAMENTE.',
        		      //              buttons: {
        		      //                  heyThere: {
        		      //                      text: 'Ok', // With spaces and symbols
        		      //                      action: function() {
        		      //                          c.close();
        		      //                          jQuery('#dsp-filtra').val("");
        		      //                      }
        		      //                  }
        		      //              }
        		      //          });
        		      //      return 'Dados não localizados';
        		      //  }
        		        
        		      //  try{
        		      //      latitude, longitude
        		      //  } catch(e){
        		      //      b.close();
        		      //      return "Latitude e/ou Longitude não localizada";
        		      //  }
        		        console.log(`<?php _e(site_url('wp-json/wp/v2/aproximated/'))?>?lat=${latitude}&lon=${longitude}&cep=${000000}`);
        		        fetch(`<?php _e(site_url('wp-json/wp/v2/aproximated/'))?>?lat=${latitude}&lon=${longitude}&cep=00000000`, {mode: "no-cors"}).then(ret => ret.json() ).then( ret => {
        		            // Sucesso
        		            console.log('DEPURAR: ', ret,);
        		            // VAMOS PERCORRER TODOS OS LOJISTAS PARA VER QUAL É O MAIS PRÓXIMO
        		            var menorDistancia = 6000000;
        		            var idLojista = 0;
        		            if(ret.lojistas.length > 0 ){
        		                ret.lojistas.forEach((localet, index)=>{
        		                    console.log(localet);
        		                    if (localet.distancia < menorDistancia) {
            		                    menorDistancia = localet.distancia;
            		                    idLojista = localet.id;
            		                    url2redirect = localet.url;
            		                    urlDomain = localet.domain;
            		                }
        		                });
        		            }
        		          //  return;
        		                //Obter O CEP do endereço fornecido		        
                                var findZipcode = data.results[0].address_components;
                                findZipcode.forEach((item)=>{
                                    //CEP
                                    if( item.types.includes('postal_code') ){
                                        var zip_code = item.long_name.replace(/[^0-9]/g,'');
                                        localStorage.setItem( "billing_postcode", zip_code );
                                    }
                                    //País
                                    if( item.types.includes('country') ){
                                        localStorage.setItem( "billing_country", item.short_name );
                                    }
                                    //Estado
                                    if( item.types.includes('administrative_area_level_1') ){
                                        localStorage.setItem( "billing_state", item.short_name );
                                    }
                                    //Cidade
                                    if( item.types.includes('administrative_area_level_2') ){
                                        localStorage.setItem( "billing_city", item.short_name );
                                    }
                                    //Bairro
                                    if( item.types.includes('sublocality_level_1') ){
                                        localStorage.setItem( "billing_address_2", item.short_name );
                                    }
                                    //Logradouro
                                    if( item.types.includes('route') ){
                                        localStorage.setItem( "billing_address_1", item.short_name );
                                    }
                                    
                                    fetch(`${urlDomain}/wp-json/wp/v2/aproximated/?lat=${latitude}&lon=${longitude}&cep=${zip_code}`, {mode: "no-cors"});
                                });
        		            if( getOnlyCep() )
        		            if ( data.status == 'OK') {
        		                
        		                location.href = urlDomain; //Redirecionar para a loja mais próxima
        		              //  b.close();
        		              //  var c = jQuery.confirm({
        		              //      title: 'ENCONTRAMOS!',
        		              //      type: "green",
        		              //      content: ' MAIS PRÓXIMO DE VOCÊ',
        		              //      buttons: {
        		              //          heyThere: {
        		              //              text: 'AVANÇAR', // With spaces and symbols
        		              //              action: function() {
        		              //                  location.href = url2redirect;
        		              //              }
        		              //          }
        		              //      }
        		              //  });
        		            } else if ( data.status == 'ZERO_RESULTS'){
        		                b.close();
        		                var c = jQuery.confirm({
        		                    title: 'NÃO LOCALIZADO!',
        		                    type: "red",
        		                    content: 'INDISPONÍVEL NA SUA REGIÃO.',
        		                    buttons: {
        		                        heyThere: {
        		                            text: 'Ok', // With spaces and symbols
        		                            action: function() {
        		                                c.close();
        		                                jQuery('#dsp-filtra').val("");
        		                            }
        		                        }
        		                    }
        		                });
        		            }
        		        }).catch(function(error) {
        		            // Algo deu errado
        		            console.log(error);
        		        }).then(function() {
        		            // Será executado em todo caso
        		        });
        		    })
        		}
        		// APÓS O CLICK EM UM LOCAL, VAMOS APAGAR O MAPA, E FAZER APARECER A SELEÇÃO DO TIPO DE TREINO
        		jQuery("#dsp-filtra").change(function() {
        		    // FAZER APARECER NA TELA A DIV PARA SALVAR O DESTINO
        		    var destino = jQuery("#dsp-filtra").val();
        		    console.log("DESTINO ESCOLHIDO");
        		    console.log(destino);
        		    b = jQuery.confirm({
        		        title: 'PESQUISANDO',
        		        type: "blue",
        		        content: 'AGUARDE',
        		        buttons: {
        		            heyThere: {
        		                text: 'Ok', // With spaces and symbols,
        		                isHidden: true
        		            }
        		        }
        		    });
        		    setTimeout(function() {
        		        var destino = jQuery("#dsp-filtra").val();
        		        console.log("DESTINO ESCOLHIDO (POS SEG)");
        		        console.log(destino);
        		        getCoordinates(destino);
        		    }, 3000);
        		});
        		jQuery("body .pac-container").click(function() {
        		    console.log("CLICOU NO PAC");
        		});
        		// CORREÇÃO PARA SELEÇÃO DO DESTINO ONMOBILE
        		jQuery(document).on({
        		    'DOMNodeInserted': function() {
        		        jQuery('.pac-item, .pac-item span', this).addClass('no-fastclick');
        		        console.log("PAC GOOGLE: ");
        		    }
        		}, '.pac-container');
        		// POSSIBILITAR O AUTOCOMPLETE
        		setTimeout("initMapa()", 3000);
        </script>
	<?php
    }
    
    if( is_checkout() || is_cart() ){ ?>
        <script>
        jQuery(document).ready(function(){
            var billing_datas = [
                "billing_country",
                "billing_state",
                "billing_city",
                "billing_address_1",
                "billing_address_2",
                "billing_postcode"
            ].forEach((shavi)=>{
                jQuery(`#${shavi}`).attr('readonly', 'true');
                jQuery(`#${shavi}_field`).attr('readonly', 'true');
                jQuery(`#${shavi}`).addClass('readonly_field');
                jQuery(`#${shavi}_field span`).attr('readonly', 'true');
                jQuery(`#${shavi}`).val(localStorage.getItem(shavi) );
                
                // shavi == 'calc_shipping_state' ? jQuery(`#calc_shipping_state`).val(localStorage.getItem('billing_state') ) : "";
                
                if( jQuery(`#${shavi}`).is("select") ){
                    jQuery(`#${shavi}`).select2({disabled:'readonly'});
                    try {
                        jQuery(`#${shavi}`).select2().trigger('change');
                        // jQuery(`#${shavi}`).closest('span').attr( 'readonly', 'true' );
                        jQuery(`#${shavi}`).select2("readonly", true);
                    }
                    catch(error){
                        console.info(error);
                    }
                }
            });
        });

        </script>
        <?php
    }
     ?>
     <script>
     	//Mascarar campo do input
		jQuery(document).ready(function(){
		    jQuery('#billing_nascimento').mask('00/00/0000');
		    /**
		     * Formatar o mesmo campo para CPF ou CNPJ, conforme a 
		     * Source: https://pt.stackoverflow.com/a/200389
		    */
            var options = {
                onKeyPress: function (cpf, ev, el, op) {
                    var masks = ['000.000.000-000', '00.000.000/0000-00'];
                    jQuery('#billing_cpf_cnpj').mask((cpf.length > 14) ? masks[1] : masks[0], op);
                }
            }
            jQuery('#billing_cpf_cnpj').length > 11 ? jQuery('#billing_cpf_cnpj').mask('00.000.000/0000-00', options) : jQuery('#billing_cpf_cnpj').mask('000.000.000-00#', options);
        });
    </script>
     <?php
}
