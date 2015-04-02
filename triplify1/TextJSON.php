<?php

include_once( 'jsonld/jsonld.php' );
include_once( 'functions.php' );

class TextJSON {
	
	function __construct($option_URI_base, $array_contendo_prefixos_usados, $prefixos, $posts) {
		
		global $wpdb;
		$prefixos_banco = $wpdb->get_results("SELECT * FROM wp_triplify_prefixes");
		
		$context = array();
		
		foreach($posts as $post){
			$property = "rdf:about";
			$post->$property = $option_URI_base.$post->ID;
			foreach($array_contendo_prefixos_usados as $object){
				foreach($prefixos_banco as $prefixo){
					if(strcmp(strtolower($object->prefix), strtolower($prefixo->prefixo)) == 0){
						if($object->uri == 1){
							$context = array_merge($context, array($object->prefix => (object)array("@id" => $prefixo->uri.$object->prefix, "@type" => "@id")));
						} else {
							$context = array_merge($context, array($object->prefix => (object)array("@id" => $prefixo->uri.$object->prefix)));
						}
					}
				}
			}
			
			/*foreach(get_object_vars($post) as $key){
				$post->$key = htmlentities(((array)$post->$key));
				unset ($post->$key);
				//print_r($post->$key);
			}*/
			
			$compacted = jsonld_compact((object)$post, (object)$context);
			echo json_encode($compacted, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_HEX_QUOT | JSON_HEX_TAG);
		}
	}
}

?>
