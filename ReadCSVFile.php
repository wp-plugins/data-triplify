<?php

class dt_ReadCSVFile {
	
	// @var string CSV upload directory name
    public $uploadDir = 'data-triplify';
	
	// @var containing all types to triplify
	public $types = null;
	
	// @var containing all url-bases of the post-types triplified
	public $urls_bases = null;
	
	// @var correspondences of the triplify
	public $correspondencias = null;
	
	public $dt_csv_file_name = null;
	
	public $retorno = null;
	
	public $mensagemErro = null;
	
	function __construct() {
		
		$retorno = $this->dt_move_file();
		if($retorno == false) return $retorno;
		
		if($this->dt_csv_header() == false || $this->dt_csv_file_data_rows() == false){
			$retorno = false;
		}
		else {
			$retorno = true;
		}
	}
	
	function dt_csv_header(){
		$tdCerto = true;
		$this->dt_check_upload_dir_permission();
		ini_set("auto_detect_line_endings", true);
		
		$file = $this->dt_get_upload_directory() . "/$this->dt_csv_file_name";
		//echo $file;
		// Check whether file is present in the given file location
        $fileexists = file_exists($file);
        if ($fileexists) {
            $resource = fopen($file, 'r');
			if($resource == false) echo $resource;
			$init = 0;
            while ($keys = fgetcsv($resource, '', ';', '"')) {//$this->delim
                if ($init == 0) {
					//print_r($keys);
                    $this->types = $keys;
					if(count($keys)<1){
						$this->mensagemErro = "Line 1, with post-types is empty.";
						$tdCerto = false;
						return $tdCerto;
					}
					foreach($keys as $key){
						if(trim($key) == ""){
							$this->mensagemErro = "Line 1, with post-types is empty, or maybe it has two semicolons in a row.";
							$tdCerto = false;
							return $tdCerto;
						}
					}
					
				}else if($init == 1){
					//print_r($keys);
					$this->urls_bases = $keys;
					//echo count($keys).":";
					if(count($keys)<1){
						$this->mensagemErro = "Line 2, with each post-type's URI is empty.";
						$tdCerto = false;
						return $tdCerto;
					}
					
					foreach($keys as $key){
						if(trim($key) == ""){
							$this->mensagemErro = "Line 2, with each post-type's URI is empty, or it has 2 semicolons in a row.";
							$tdCerto = false;
							return $tdCerto;
						}
					}
					
					break;
				}
                $init++;
            }
			//echo " ".count($this->types)."::".count($this->urls_bases);
			if(count($this->types) != count($this->urls_bases)){
				$this->mensagemErro = "O número de colunas dos post_types e de suas respoectivas URI_base, possuem número de elementos diferentes.";
				$this->mensagemErro = "Numbers of post-types and URI's mismatch.";
				$tdCerto = false;
				return $tdCerto;
			}
			
			$contador = 0;
			foreach($this->types as $type){
				$option = "#triplificator_uri_base#".$type;
				
				if(get_option($option, null) != null) add_option($option, $this->urls_bases[$contador]);
				else update_option($option, $this->urls_bases[$contador]);
				
				$contador++;
			}
			
            fclose($resource);
            ini_set("auto_detect_line_endings", false);
			return $tdCerto;
        }
		$this->mensagemErro = "Upload failed, please try again.";
		$tdCerto = false;
		return $tdCerto;
	}
	
	function dt_csv_file_data_rows(){//$delim
		$tdCerto = true;
		
		$this->dt_check_upload_dir_permission();
		ini_set("auto_detect_line_endings", true);
		
		//$data_rows = array();
		global $wpdb;
		
		$file = $this->dt_get_upload_directory() . "/$this->dt_csv_file_name";
		# Check whether file is present in the given file location
        $fileexists = file_exists($file);
		
		$tabela = "wp_triplify_configurations";

        if ($fileexists) {
            $resource = fopen($file, 'r');

            $init = 0;
            while ($keys = fgetcsv($resource, '', ';', '"')) {//$this->delim
                if ($init != 0 && $init != 1) {
					if(count($keys) != 3){
						$linhaErro = $init + 1;
						$this->mensagemErro = "Line $linhaErro has incorrect format.";
						$tdCerto = false;
						return $tdCerto;
					}
					$explode = explode(':', $keys[1]);
					$object = new dt_prefixColumnUri();
					$object->prefix = $explode[0];
					$object->coluna = $explode[1];
					if($keys[2] == "true"){
						$object->uri = 1;
					} else {
						$object->uri = 0;
					}
					$object->fullProperty = $keys[1];
					
					foreach($this->types as $type){
						$jaExisteNoBanco = $wpdb->get_row("SELECT * FROM $tabela WHERE tipo='".$type."' and coluna='".$keys[0]."'", OBJECT);
						if($jaExisteNoBanco != null){
							$wpdb->update($tabela, array('tipo' => $type, 'coluna' => $keys[0], 'uri' => $object->uri, 'valor_correspondente' => $object->fullProperty), array('tipo' => $type, 'coluna' => $keys[0]));
						} else{
							$wpdb->insert($tabela, array('tipo' => $type, 'coluna' => $keys[0], 'uri' => $object->uri, 'valor_correspondente' => $object->fullProperty));
						}
					}
				}
				
                $init++;
            }
            fclose($resource);
            ini_set("auto_detect_line_endings", false);
			$tdCerto = true;
			return $tdCerto;
        } else {
			$this->mensagemErro = "Upload failed, please try again.";
			$tdCerto = false;
			return $tdCerto;
		} 
	}
	
    function dt_get_upload_directory(){
        $upload_dir = wp_upload_dir();
		//print_r($upload_dir);
        return $upload_dir ['basedir'] . "/" . $this->uploadDir;
    }
	
	function dt_check_upload_dir_permission(){
        $this->dt_get_upload_directory();
        $upload_dir = wp_upload_dir();
		
		//print_r ($upload_dir);
        if (!is_dir($upload_dir ['basedir'])) {
            print " <div style='font-size:16px;margin-left:20px;margin-top:25px;'>UPLOAD PERMISSION ERROR 
			</div><br/>
			<div style='margin-left:20px;'>
			<form class='add:the-list: validate' method='post' action=''>
			<input type='submit' class='button-primary' name='Import Again' value='IMPORT AGAIN'/>
			</form>
			</div>";
            $this->freeze();
        } else {
            if (!is_dir($this->dt_get_upload_directory())) {
                wp_mkdir_p($this->dt_get_upload_directory());
            }
        }
    }
	
	function dt_move_file(){
        $tdCerto = false;
		if ($_FILES ["triplify-csv-file"] ["error"] == 0) {

            $tmp_name = $_FILES ["triplify-csv-file"] ["tmp_name"];
			$this->dt_csv_file_name = $_FILES ["triplify-csv-file"] ["name"];
			$fileType = pathinfo($this->dt_csv_file_name,PATHINFO_EXTENSION);
			if($fileType != "csv"){
				$this->mensagemErro = "Select file isn't a .csv file .";
				$tdCerto = false;
				return $tdCerto;
			}
			move_uploaded_file($tmp_name, $this->dt_get_upload_directory() . "/$this->dt_csv_file_name");
        
			$tdCerto = true;
		}

		return $tdCerto;
    }
}

?>
