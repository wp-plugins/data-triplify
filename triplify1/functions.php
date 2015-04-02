<?php

function utf8_strtr($str)
{
	$from = array( "á", "à", "â", "ã", "ä", "é", "è", "ê", "ë", "í", "ì", "î", "ï", "ó", "ò", "ô", "õ", "ö", "ú", "ù", "û", "ü", "ç" , "Á", "À", "Â", "Ã", "Ä", "É", "È", "Ê", "Ë", "Í", "Ì", "Î", "Ï", "Ó", "Ò", "Ô", "Õ", "Ö", "Ú", "Ù", "Û", "Ü", "Ç" ); 
	$to = array( "a", "a", "a", "a", "a", "e", "e", "e", "e", "i", "i", "i", "i", "o", "o", "o", "o", "o", "u", "u", "u", "u", "c" , "A", "A", "A", "A", "A", "E", "E", "E", "E", "I", "I", "I", "I", "O", "O", "O", "O", "O", "U", "U", "U", "U", "C" ); 

	return str_replace( $from, $to, $str); 
}

function format_property($property)
{
	//$result = utf8_encode($property);
	$result = utf8_strtr($property);
	//$result = utf8_strtr($result);
	$result = str_replace(' ', '-', $result);
	$result = strtolower($result);
	
	return  $result;
}

function getPrefix($Str){
	if(strcmp($Str, 'dc') == 0) return "dc";
	else if(strcmp(strtolower($Str), 'foaf') == 0) return "foaf";
	else if(strcmp(strtolower($Str), 'owl') == 0) return "owl";
	else if(strcmp(strtolower($Str), 'rdf') == 0) return "rdf";
	else if(strcmp(strtolower($Str), 'rdfs') == 0) return "rdfs";
	else if(strcmp(strtolower($Str), 'xsd') == 0) return "xsd";
	
}

?>
