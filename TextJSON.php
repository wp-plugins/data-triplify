<?php

include_once( 'jsonld/jsonld.php' );
include_once( 'functions.php' );

class dt_TextJSON {
	
	function __construct($option_URI_base, $array_contendo_objetos_usados, $prefixos, $posts) {
		
		global $wpdb;
		$prefixos_banco = $wpdb->get_results("SELECT * FROM wp_triplify_prefixes");
		
		/*$context = array();
		
		foreach($posts as $post){
			$property = "rdf:about";
			$post->$property = $option_URI_base.$post->ID;
			foreach($array_contendo_objetos_usados as $object){
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
			
			$normalizado = new stdClass();
			foreach($post as $key => $value){
				if(!empty($post->$key))
				$string = htmlentities($value, ENT_XHTML);
				$normalizado->$key = $string;
			}
			
			$compacted = jsonld_compact((object)$normalizado, (object)$context);
			
			$pronto = json_encode($compacted, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
			$pronto = str_replace('="', "", $pronto);
			echo $pronto;
		}*/
		echo "{"; //global keys
		
		echo "\"@context\"	:{"; //context keys
		echo "\"rdf\": { \"@id\": \"http://www.w3.org/1999/02/22-rdf-syntax-ns#\", \"@type\": \"@id\" }";//rdf:about
		foreach($prefixos_banco as $prefix){//always will have at maximum one of each.
			if(in_array(strtolower($prefix->prefixo), $prefixos) && strtolower($prefix->prefixo) != "rdf") {
				if(substr($prefix->uri, -1) != "/"){
					$prefix->uri = $prefix->uri."/";
				}
				echo ", \"".$prefix->prefixo."\":";
				//echo $prefix->prefixo."= "."\"$prefix->uri\" ";
				echo "{\"@id\":";
				echo "\"".$prefix->uri."\"";
				echo "}";
			}
		}
		echo "}, \"@graph\": [";// context keys
		$contador = 0;
		foreach($posts as $post){
			if($contador > 0){
				echo ", ";
			}else {
				$contador = 1;
			}
			echo "{";
			echo "\"rdf:about\":";
			echo " \"".$option_URI_base."/".$post->ID."\"";
			$contador_de_dentro = 0;
			foreach($array_contendo_objetos_usados as $object){
				if($contador > 0){
					echo ", ";
				}else {
					$contador = 1;
				}
				$property = $object->fullProperty;
				echo " \"".$property."\":	";
				$post->$property = str_replace("\"","'",$post->$property);
				if($object->uri == 0) echo htmlentities("\"".$post->$property."\"");
				else echo htmlentities($post->$property);
			}
			echo "}";
		}
		echo "]}";// global keys*/
	}
}

?>
