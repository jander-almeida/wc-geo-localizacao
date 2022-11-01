<?php

/**
 * Injetar todos os shortcodes aqui
*/

add_shortcode('filtrar_lojas', function($atts){
    if(defined('REST_REQUEST')) return; //Não rodar no Editor Gutenberg
    $rand_key = wp_rand(100,999);
    $sitename = get_bloginfo('name');
print <<<FILTRAR
    <div class="row p-3 m-3">
        <div class="dsp-title-filter"></div>
        <div class="input-group flex-nowrap border border-1 rounded-pill bg-white">
          <span class="input-group-text bg-white border-0" id="addon-wrapping"><i class="bi bi-geo-alt"></i></span>
          <input type="text" class="form-contro bg-white border-0" placeholder="Digite o cep" aria-label="cep" aria-describedby="addon-wrapping" id="dsp-filtra">
        </div>
        <div class="subcontent">
          
        </div> 
    </div>
FILTRAR;
    
});