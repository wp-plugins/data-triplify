<?php

include_once( "arc2/ARC2.php" );
require_once( "functions.php" );

class TextRDF {
	
	function __construct($option_URI_base, $array_contendo_objetos_usados, $prefixos, $posts) {
		
		global $wpdb;
		$prefixos_banco = $wpdb->get_results("SELECT * FROM wp_triplify_prefixes");
		
		$RDF = '<?xml version="1.0" encoding="UTF-8"?>';
		$RDF = $RDF."<rdf:RDF ";
		$RDF = $RDF."xmlns:rdf= \"http://www.w3.org/1999/02/22-rdf-syntax-ns#\" ";//every post have to have
		
		print_r($prefixos);
		
		foreach($prefixos_banco as $prefix){//always there will be at maximum one of each.
			if(in_array(strtolower($prefix->prefixo), $prefixos) && $prefix->prefixo != "rdf") $RDF = $RDF."xmlns:".$prefix->prefixo."= "."\"$prefix->uri\" ";
		}
		$RDF = $RDF.">";
		echo htmlentities($RDF);
		
		foreach($posts as $post){
			$RDF= "<rdf:Description ";
			$RDF= $RDF."rdf:about=\"".$option_URI_base.$post->ID."\"";
			$RDF= $RDF.">";
			echo htmlentities($RDF);
			
			foreach($array_contendo_objetos_usados as $object){
				$property = $object->fullProperty;
				echo htmlentities("<".$object->fullProperty.">");
				echo $post->$property;
				echo htmlentities("</".$object->fullProperty.">");
			}
			echo htmlentities("</rdf:Description>");
		}
		
		echo htmlentities("</rdf:RDF>");
	}
	
}

?>
