<?php

/*
Plugin Name: Data-Triplify JSON
Description: Triplify your wordpress posts
Author: Douglas Paranhos & Eduardo Andrade
Version: 0.1
Author URI: http://dontpad.com/lalala
*/

include_once dirname( __FILE__ ) .'/render.php';
include_once dirname( __FILE__ ) .'/TYPE_TEXT.php';
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

add_action('admin_init', 'flush_rewrite_rules');

add_action('admin_menu', 'triplificator_admin_actions');
function triplificator_admin_actions(){
	add_options_page('Data-Triplify', 'Data-Triplify', 'manage_options', __FILE__, 'triplify');
}

add_action('init', 'custom_rewrite_tag', 10, 0);
function custom_rewrite_tag() {
  add_rewrite_tag('%type%', '([^&]+)');
  add_rewrite_tag('%structure%', '([^&]+)');
}

add_action('init', 'configure_Data_Triplify', 10, 0);
function configure_Data_Triplify(){
	if ( ! defined( 'TRIPLIFY_PLUGIN_UPLOAD_PATH' ) ){
		$upload_dir = wp_upload_dir();

		define( 'TRIPLIFY_PLUGIN_UPLOAD_PATH', $upload_dir['basedir']."/data-triplify/" );
	}
}

add_action('generate_rewrite_rules', 'triplificator_add_rewrite_rules');
function triplificator_add_rewrite_rules(){

	global $wp_rewrite;
	
	$url_base = get_option("triplify_url_base_dados", "tri");

	$keytag = '%type%';
	$keytag2 = '%structure%';
	
	$wp_rewrite->add_rewrite_tag($keytag, '(.+?)', 'type=');
	$wp_rewrite->add_rewrite_tag($keytag2, '(.+?)', 'structure=');
	
	$keywords_structure = $wp_rewrite->root . "$url_base/$keytag/$keytag2";
	$keywords_rewrite = $wp_rewrite->generate_rewrite_rules($keywords_structure);
	
	$wp_rewrite->rules = $keywords_rewrite + $wp_rewrite->rules;
	return $wp_rewrite->rules;
}

add_action('template_redirect', 'my_page_template_redirect' );
function my_page_template_redirect(){
	global $wp_query;

	$type = get_query_var( 'type' ) ? get_query_var( 'type' ) : false;
	$structure = get_query_var( 'structure' ) ? get_query_var( 'structure' ) : 'JSON';

	if($type != false){
		if($type === 'info'){
			echo "RESTful service working";
			exit();
		}
		
		//chamar método que salva opções no banco
		new TYPE_TEXT( $type, $structure );
		exit();
	}

}

function triplify(){
	global $wpdb;
	//creating datatable
	$table_name = "wp_triplify_configurations";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "CREATE TABLE $table_name (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					tipo VARCHAR(55) NOT NULL,
					coluna VARCHAR(100) NOT NULL,
					valor_correspondente VARCHAR(100) NOT NULL,
					uri BOOLEAN NOT NULL,
					UNIQUE KEY id (id)
				);";
		
		$wpdb->query($sql);
	}
	
	$table_name = "wp_triplify_prefixes";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "CREATE TABLE $table_name (
					prefixo VARCHAR(55) NOT NULL,
					uri VARCHAR(100) NOT NULL,
					UNIQUE KEY id (prefixo)
				);";
		
		$wpdb->query($sql);
		$wpdb->insert('wp_triplify_prefixes', array( 'prefixo' => 'foaf', 'uri' => 'http://xmlns.com/foaf/0.1/' ));
		$wpdb->insert('wp_triplify_prefixes', array( 'prefixo' => 'dc', 'uri' => 'http://purl.org/dc/elements/1.1/' ));
		$wpdb->insert('wp_triplify_prefixes', array( 'prefixo' => 'rdf', 'uri' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#/' ));
		$wpdb->insert('wp_triplify_prefixes', array( 'prefixo' => 'rdfs', 'uri' => 'http://www.w3.org/2000/01/rdf-schema#/' ));
		$wpdb->insert('wp_triplify_prefixes', array( 'prefixo' => 'owl', 'uri' => 'http://www.w3.org/2002/07/owl#/' ));
		$wpdb->insert('wp_triplify_prefixes', array( 'prefixo' => 'xsd', 'uri' => 'http://www.w3.org/2001/XMLSchema#/' ));
	}
	
	$table_name = "wp_dc_prefix";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {//se a tabela existe
		
		$prefixos_definidos_data_cube = $wpdb->get_results("SELECT * FROM $table_name");
		foreach($prefixos_definidos_data_cube as $prefixo){
			$ja_existente = $wpdb->get_row("SELECT * FROM  WHERE prefixo='".$prefixo->prefix."'", OBJECT);
			if($ja_existente != null) {
				$wpdb->insert('wp_triplify_prefixes', array( 'prefixo' => $prefixo->prefix, 'uri' => $prefixo->uri ));
			}
		}
	}

	new Render();
}

add_action( 'wp_ajax_triplify_action', 'triplify_action_callback' );
function triplify_action_callback() {
	
	global $wpdb;
	
	//saving correspondences
	foreach(array_values($_POST['arrayCorrespondencias']) as $opcoes){
		$post = $_POST["post_type"];
		$coluna = $opcoes["coluna"];
		$valor_correspondente = $opcoes["valor"];
		
		if($opcoes["uri"] == 'true'){
			$uri = true;
		} else {
			$uri = false;
		}
		
		$tabela = 'wp_triplify_configurations';
		$valor_anterior_banco = $wpdb->get_row("SELECT uri FROM {$wpdb->prefix}triplify_configurations WHERE tipo='".$post."' and coluna='".$coluna."'", OBJECT);
		if($valor_anterior_banco != null){
			$wpdb->update($tabela, array('tipo' => $post, 'coluna' => $coluna, 'uri' => $uri, 'valor_correspondente' => $opcoes["valor"]), array('tipo' => $post, 'coluna' => $coluna));
		} else {
			$wpdb->insert($tabela, array('tipo' => $post, 'coluna' => $coluna, 'uri' => $uri, 'valor_correspondente' => $opcoes["valor"]));
		}
		
	}
	
	//saving base url
	$option_name = "#triplificator_uri_base#".$_POST["post_type"];
	$uri_base_value = $_POST['uri_base'];
	
	if(get_option( $option_name, null ) == null) add_option($option_name, $uri_base_value);
	else update_option($option_name, $uri_base_value);
	
	wp_die();
}
/* EOF */
