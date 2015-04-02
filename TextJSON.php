<?php

include_once( 'jsonld/jsonld.php' );
include_once( 'functions.php' );

class dt_TextJSON {
	
	function __construct($option_URI_base, $array_contendo_objetos_usados, $prefixos, $posts) {
		
		global $wpdb;
		$prefixos_banco = $wpdb->get_results("SELECT * FROM wp_triplify_prefixes");
		
		$context = array();
		
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
		}
		/*echo "{"; //global keys
		
		echo "\"@context\"	:{"; //context keys
		echo "\"rdf\": { \"@id\": \"http://purl.org/dc/elements/1.1/dc\", \"@type\": \"@id\" }";//rdf:about
		foreach($prefixos_banco as $prefix){//always there will be at maximum one of each.
			if(in_array(strtolower($prefix->prefixo), $prefixos) && $prefix->prefixo != "rdf") {
				echo ", \"".$prefix->prefixo."\":";
				//echo $prefix->prefixo."= "."\"$prefix->uri\" ";
				echo "{\"@id\":";
				echo "\"".$prefix->uri."\"";
				echo "}";
			}
		}
		echo "},";// context keys
		foreach($posts as $post){
			//echo "{";
			echo "\"rdf:about\":".$option_URI_base.$post->ID;
			foreach($array_contendo_objetos_usados as $object){
				$property = $object->fullProperty;
				//if($object->uri == 0) echo "\"$property\"";
				//else echo $property;
				//echo "\"$post->$property\"";
				echo " \"".$property."\":	";
				if($object->uri == 0)echo "\"".$post->$property."\",";
				else echo $post->$property.",";
			}
			//echo "}";
		}
		echo "}";// global keys*/
	}
}

?>
