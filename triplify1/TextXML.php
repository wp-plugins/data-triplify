<?php

include_once( "arc2/ARC2.php" );
require_once( "functions.php" );

class TextXML {
	
	function __construct($option_URI_base, $array_contendo_objetos_usados, $prefixos, $posts) {
		global $wpdb;
		$prefixos_banco = $wpdb->get_results("SELECT * FROM wp_triplify_prefixes");
		
		echo htmlentities('<?xml version="1.0"?>');
		echo htmlentities ("<posts>");
		foreach($posts as $post){
			$XML = "<post ";
			foreach($prefixos_banco as $prefix){//always there will be at maximum one of each.
				if(in_array(strtolower($prefix->prefixo), $prefixos)) $XML = $XML."xmlns:".$prefix->prefixo."= "."\"$prefix->uri\" ";
			}
			$XML = $XML.">";
			$XML = $XML."<URI>";
			$XML = $XML.$option_URI_base.$post->ID;
			$XML = $XML."</URI>";
			echo htmlentities($XML);
			
			foreach($array_contendo_objetos_usados as $object){
				$property = $object->fullProperty;
				echo htmlentities("<".$object->fullProperty.">");
				echo $post->$property;
				echo htmlentities("</".$object->fullProperty.">");
			}
			echo htmlentities("</post>");
		}
		echo htmlentities ("</posts>");
	}
	
}

?>
