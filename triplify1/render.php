<?php

include_once('ReadCSVFile.php');

class Render {
	
	function __construct() {
		
		
	add_action( 'admin_footer', 'triplify_javascript' );
		if(!isset($_POST['termoPesquisado']) && !isset($_POST['triplify-csv-file']) && !isset($_POST['salvar_prefixos'])){
			//print_r($_POST);
	?>
			<div>
				<div style="border-style: dotted; border-width: 1px; background-color: #f5f5dc;">
					<form action="" method="POST">
						<h3>Digite a URL que deseja acessar para verificar os dados triplificados:</h3>
						<br/>
							<code><?php bloginfo('url'); ?>/</code> <input name="url_base" value="<?php echo get_option("triplify_url_base_dados", "tri");?>" id="postType_base"/>
						<h3>Post-types já triplificados:<h3>
						<br/>
							<?php 
								global $wpdb;
								$url  = get_bloginfo('url');
								$url_base = get_option("triplify_url_base_dados", "tri");
								$posts = $wpdb->get_results("SELECT distinct tipo FROM {$wpdb->prefix}triplify_configurations");

								if(!empty($posts)){
									foreach($posts as $tipo){
										echo "<code><a href=\"$url\\$url_base\\$tipo->tipo\">$tipo->tipo</a></code>" ;
									}
								} else {
									echo "Nenhum tipo triplificado ainda.";
								}
							?>
						<h3>Desejo pesquisar o post-type e configurá-lo manualmente para triplificar: </h3>
						<br/>
						<input name="postType" value="" id="postType"/>
						<button name="termoPesquisado" type="submit" class="button-primary">Pesquisar</button>
					</form>
				</div>
				<div style="border-style: dotted; border-width: 1px; background-color: #d8f5da; margin-top: 2px;">
					<form action="" method="POST" enctype="multipart/form-data">
						<h3>Desejo fazer leitura de arquivo CSV com tipo e configurações lá contidas: </h3>
						<h4>O arquivo deve estar da seguinte forma:</h4>
						<h6>O delimitador deve ser ;(ponto e vírgula)<br/>
						Na primeira linha, os tipos a serem aplicadas aquelas correspondências definidas<br/>
						Na segunda linha, a URI_base de cada um dos tipos <red>respectivamente</red><br/>
						O resto das linhas deve conter na ordem: <red>A coluna, sua correspondência e se o valor mostrado será uma URI ou não</red></h6>
						<br/>
						<div class="pure-control-group">
							<input id="triplify-file" name="triplify-csv-file" type="file" value="" data-validate="validate(required)" />
							<button name="triplify-csv-file" type="submit" class="button-primary">Importar</button>
						</div>
					</form>
				</div>
				<div style="border-style: dotted; border-width: 1px; background-color: #f5e2df; margin-top: 2px;">
					<h3>Prefixos já contidos no banco:</h3><?php
						global $wpdb;
						$prefixos = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}triplify_prefixes");

						if(!empty($prefixos)){
							foreach($prefixos as $prefixo){
								echo "<code><a href=\"$prefixo->uri\">$prefixo->prefixo</a></code>";
							}
						} else {
							echo "Nenhum tipo triplificado ainda.";
						}?>
					<h3>Adicionar novos prefixos, caso queira adicionar um já existente, apenas sua URI será trocada no banco. Digite o prefixo sem ':'</h3>
					<form action="" method="POST">
						<div>
							<input class="prefixo_salvar" name="prefixo_salvar" value="Prefixo" id="prefixoSalvar"/>
							<input class="prefixo_salvar" name="uri_salvar" value="URI" id="uriSalvar"/>
							<button name="salvar_prefixos" type="submit" class="button-primary">Salvar</button>
						</div>
					</form>
				</div>
			</div>
	<?php
		} else if(isset($_POST["postType"])){
			$termo = $this->pegaValores($_POST["postType"]);
			
			global $wpdb;
			$resultado = $wpdb->get_results("SELECT distinct meta_key FROM $wpdb->postmeta WHERE post_id in(SELECT ID FROM $wpdb->posts WHERE post_type = '".$termo."')");
			if(empty($resultado)){
				?><div>
					Não foram encontrado post_types do tipo <?php if(trim($termo) == "")echo "vazio"; else echo $termo; ?>.
				  </div><?php
				  die();
			}
			
			$this->salvaUrlBase($_POST["url_base"]);
	?>
			<div id="corpo">
				<h2>Você está procurando por <?php echo $termo; ?></h2>
				
				<h4> Defina a URI Base dos posts:</h4>
				<?php $uriBase = get_option("#triplificator_uri_base#".$_POST["postType"], 'URI base');
				echo"<input name='uriBase' value='".$uriBase."'  id='uriBase'/>"
				?>
				<br/>
				
				<h4>Defina as equivalências e marque o checkbox caso o resultado mostrado por essa coluna seja uma URI: </h4>
				<h6>Marque a opção "não me interessa" caso o valor daquela correspondência não te importar.<br/>
				Caso essa opção seja marcada a coluna não aparecerá nos dados triplificados.<br/>
				Selecionar o "não me interessa" ou deixar a correspondência com vazio ou "correspondencia" dá no mesmo.<br/>
				O checkbox serve apenas para a página ficar mais limpa caso o usuário ache que esteja muito poluída.</h6>
<?php

				$correspondecias;
				$contador = 1;
				$post = $_POST["postType"];
					
				$tabela = $wpdb->prefix . 'posts';
				foreach ( $wpdb->get_col( "DESC " . $tabela, 0 ) as $coluna ){
					$registro = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}triplify_configurations WHERE tipo='".$post."' and coluna='".$coluna."'", OBJECT);
					if($registro == null) {
						$valor = 'correspondencia';
						$checked = "";
					}
					else{
						$valor = $registro->valor_correspondente;
						if($registro->uri == true) $checked = 'checked';
						else $checked = "";
					}
					
					
					echo "<div id='conjunto$contador'><p>".
					$contador."- <input type='checkbox' id='uri".$contador."' ".$checked."/>".
					$coluna." => ".
					"<input class='input_triplify_posts' value='". $valor ."' id='correspondencia".$contador."' mk='".$coluna."' contador='".$contador."'/>".
					"não me interessa". "<input class='checkbox_nao_interessa' type='checkbox' id='nao_me_interessa$contador'/>".
					"</p></div>";
					$contador++;
				}
				
				foreach($resultado as $resultadoX)
				{
					$registro = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}triplify_configurations WHERE tipo='".$post."' and coluna='".$resultadoX->meta_key."'", OBJECT);
					if($registro == null){
						$valor = 'correspondencia';
						$checked = "";
					}
					else{
						$valor = $registro->valor_correspondente;
						if($registro->uri == true) $checked = 'checked';
						else $checked = "";
					}
					
					echo "<div id='conjunto$contador'><p>".
					$contador."- <input  type='checkbox' id='uri".$contador."' ".$checked."/>".
					$resultadoX->meta_key." => ".
					"<input class='input_triplify' value='". $valor ."' id='correspondencia".$contador."'  mk='".$resultadoX->meta_key."' contador='".$contador."'/>". 
					"não me interessa". "<input class='checkbox_nao_interessa' type='checkbox' id='nao_me_interessa$contador'/>".
					"</p></div>";
					$contador++;
				}

?>
				<input type='hidden' id='post_type' name='post_type' value="<?php echo $termo; ?>" />
				<br/>
				<button id="id" name="triplify" class="button-primary">Salvar opções</button>
			</div><?php
		} else if(isset($_POST['triplify-csv-file'])){
			$objeto = new ReadCSVFile();
			echo $objeto->retorno;
			if($objeto->mensagemErro == null) {
				?><div>
					<h2>Opções salvas!</h2>
					Formatos suportados atualmente: JSON-LD, RDF e XML.
				</div><?php
			} else{
				?><div>
					<h2>Falha no upload do arquivo! <?php echo $objeto->mensagemErro?></h2>
				</div><?php
			}
			
		} else if(isset($_POST['salvar_prefixos'])){
			
			global $wpdb;
			
			$prefixo = $_POST['prefixo_salvar'];
			$uri = $_POST['uri_salvar'];
			$registro = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}triplify_prefixes WHERE prefixo = \"$prefixo\" ", OBJECT);
			
			if($registro == null){
				$wpdb->insert("wp_triplify_prefixes", array( 'prefixo' => $prefixo, 'uri' => $uri));
				?><div>
					<h2>Prefixo salvo!</h2>
				</div><?php
			} else {
				$wpdb->update("$wp_triplify_prefixes", array( 'prefixo' => $prefixo, 'uri' => $uri), array('prefixo' => $prefixo));
				?><div>
					<h2>Prefixo atualizado!</h2>
				</div><?php
			}
			
		}?>
		<div id="corpo2" style="display:none">
			<h2>Opções salvas!</h2>
			<h3>Acesse <code><a href="<?php bloginfo('url'); echo '/'.get_option('triplify_url_base_dados', 'tri') .'/'; echo $termo;?>"><?php bloginfo('url');?>/<?php echo get_option("triplify_url_base_dados", "tri")?>/<?php echo $termo; ?></a></code> para obter os dados em formato JSON, caso queria os dados em outros formatos, basta adicionar /formato à URL para acessá-los.</h3>
			Formatos suportados atualmente: JSON-LD, RDF e XML.
		</div>
		<?php
	
		function triplify_javascript() { ?>
			<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
			<script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
			<script type="text/javascript">
			jQuery(document).ready(function($) {
				$("#id").click(function(){
					var post_type = $('#post_type').val(); 
					var arrayCorrespondencias = new Array();
					$('.input_triplify').each(function(k,v,w){
						var mk 	= $(this).attr('mk');
						var v	= $(this).val();
						var contadorX = $(this).attr('contador').toString();
			
						if($.trim(v) != 'correspondencia' &&  $.trim(v) != ''){
							var post_triplify = new Object();
							
							post_triplify.coluna = mk;
							post_triplify.valor = v;
							
							var identificador = "#uri";
							var concatenate = identificador.concat(contadorX);
							var checkbox = $(concatenate);
							if(checkbox.is(":checked")){
								post_triplify.uri = 'true';
							} else {
								post_triplify.uri = 'false';
							}
							
							arrayCorrespondencias.push(post_triplify);
						}
					});
					$('.input_triplify_posts').each(function(k,v,w){
						var mk 	= $(this).attr('mk');
						var v	= $(this).val();
						var contadorX = $(this).attr('contador').toString();
			
						if($.trim(v) != 'correspondencia' &&  $.trim(v) != ''){
							
							var post_triplify = new Object();
							
							post_triplify.coluna = mk;
							post_triplify.valor = v;
							
							var identificador = "#uri";
							var concatenate = identificador.concat(contadorX);
							var checkbox = $(concatenate);
							if(checkbox.is(":checked")){
								post_triplify.uri = 'true';
							} else {
								post_triplify.uri = 'false';
							}

							arrayCorrespondencias.push(post_triplify);
						}
					});
					var uri_base = $('#uriBase').val();
					var data = {
							'action' : 'triplify_action',
							'post_type': post_type,
							'uri_base': uri_base,
							'arrayCorrespondencias': arrayCorrespondencias
					};
					$.post(ajaxurl, data, function(response) { 
						<!-- ver o que fazer quando falhar a requisição -->
					});
					
					$("#corpo").hide(1000);
					$("#corpo2").show(1000);
					
				});
				var availableTags = [
					"dc:abstract", 
					"dc:accessRights", 
					"dc:accrualMethod", 
					"dc:accrualPeriodicity", 
					"dc:accrualPolicy",
					"dc:alternative",
					"dc:audience",
					"dc:available",
					"dc:bibliographicCitation", 
					"dc:conformsTo", 
					"dc:contributor", 
					"dc:coverage", 
					"dc:created", 
					"dc:creator", 
					"dc:date", 
					"dc:dateAccepted", 
					"dc:dateCopyrighted", 
					"dc:dateSubmitted", 
					"dc:description", 
					"dc:educationLevel", 
					"dc:extent", 
					"dc:format", 
					"dc:hasFormat", 
					"dc:hasPart", 
					"dc:hasVersion", 
					"dc:identifier", 
					"dc:instructionalMethod", 
					"dc:isFormatOf", 
					"dc:isPartOf", 
					"dc:isReferencedBy", 
					"dc:isReplacedBy", 
					"dc:isRequiredBy", 
					"dc:issued", 
					"dc:isVersionOf", 
					"dc:language", 
					"dc:license", 
					"dc:mediator", 
					"dc:medium", 
					"dc:modified", 
					"dc:provenance", 
					"dc:publisher", 
					"dc:references", 
					"dc:relation", 
					"dc:replaces", 
					"dc:requires", 
					"dc:rights", 
					"dc:rightsHolder", 
					"dc:source", 
					"dc:spatial", 
					"dc:subject", 
					"dc:tableOfContents", 
					"dc:temporal", 
					"dc:title", 
					"dc:type", 
					"dc:valid",
					"dc:contributor",
					"dc:coverage",
					"dc:creator",
					"dc:date",
					"dc:description",
					"dc:format",
					"dc:identifier",
					"dc:language",
					"dc:publisher",
					"dc:relation",
					"dc:rights",
					"dc:source",
					"dc:subject",
					"dc:title",
					"dc:type",
					"foaf:Agent",
					"foaf:Person",
					"foaf:name",
					"foaf:title",
					"foaf:img",
					"foaf:depiction",
					"foaf:depicts",
					"foaf:familyName",
					"foaf:givenName",
					"foaf:knows",
					"foaf:based_near",
					"foaf:age",
					"foaf:made",
					"foaf:maker",
					"foaf:primaryTopic",
					"foaf:primaryTopicOf",
					"foaf:Project",
					"foaf:Organization",
					"foaf:Group",
					"foaf:member",
					"foaf:Document",
					"foaf:Image",
					"foaf:nick",
					"foaf:mbox",
					"foaf:homepage",
					"foaf:weblog",
					"foaf:openid",
					"foaf:jabberID",
					"foaf:mbox_sha1sum",
					"foaf:interest",
					"foaf:topic_interest",
					"foaf:topic",
					"foaf:page",
					"foaf:workplaceHomepage",
					"foaf:workInfoHomepage",
					"foaf:schoolHomepage",
					"foaf:publications",
					"foaf:currentProject",
					"foaf:pastProject",
					"foaf:account",
					"foaf:OnlineAccount",
					"foaf:accountName",
					"foaf:accountServiceHomepage",
					"foaf:PersonalProfileDocument",
					"foaf:tipjar",
					"foaf:sha1",
					"foaf:thumbnail",
					"foaf:logo",
					"owl:AllDifferent",
					"owl:AllDisjointClasses",
					"owl:AllDisjointProperties",
					"owl:Annotation",
					"owl:AnnotationProperty",
					"owl:AsymmetricProperty",
					"owl:Axiom",
					"owl:Class",
					"owl:DataRange",
					"owl:DatatypeProperty",
					"owl:DeprecatedClass",
					"owl:DeprecatedProperty",
					"owl:FunctionalProperty",
					"owl:InverseFunctionalProperty",
					"owl:IrreflexiveProperty",
					"owl:NamedIndividual",
					"owl:NegativePropertyAssertion",
					"owl:Nothing",
					"owl:ObjectProperty",
					"owl:Ontology",
					"owl:OntologyProperty",
					"owl:ReflexiveProperty",
					"owl:Restriction",
					"owl:SymmetricProperty",
					"owl:TransitiveProperty",
					"owl:Thing",
					"owl:allValuesFrom",
					"owl:annotatedProperty",
					"owl:annotatedSource",
					"owl:annotatedTarget",
					"owl:assertionProperty",
					"owl:backwardCompatibleWith",
					"owl:bottomDataProperty",
					"owl:bottomObjectProperty",
					"owl:cardinality",
					"owl:complementOf",
					"owl:datatypeComplementOf",
					"owl:deprecated",
					"owl:differentFrom",
					"owl:disjointUnionOf",
					"owl:disjointWith",
					"owl:distinctMembers",
					"owl:equivalentClass",
					"owl:equivalentProperty",
					"owl:hasKey",
					"owl:hasSelf",
					"owl:hasValue",
					"owl:imports",
					"owl:incompatibleWith",
					"owl:intersectionOf",
					"owl:inverseOf",
					"owl:maxCardinality",
					"owl:maxQualifiedCardinality",
					"owl:members",
					"owl:minCardinality",
					"owl:minQualifiedCardinality",
					"owl:onClass",
					"owl:onDataRange",
					"owl:onDatatype",
					"owl:oneOf",
					"owl:onProperties",
					"owl:onProperty",
					"owl:priorVersion",
					"owl:propertyChainAxiom",
					"owl:propertyDisjointWith",
					"owl:qualifiedCardinality",
					"owl:sameAs",
					"owl:someValuesFrom",
					"owl:sourceIndividual",
					"owl:targetIndividual",
					"owl:targetValue",
					"owl:topDataProperty",
					"owl:topObjectProperty",
					"owl:unionOf",
					"owl:versionInfo",
					"owl:versionIRI",
					"owl:withRestrictions",
					"rdf:HTML",
					"rdf:langString",
					"rdf:PlainLiteral",
					"rdf:type",
					"rdf:Property",
					"rdf:Statement",
					"rdf:subject",
					"rdf:predicate",
					"rdf:object",
					"rdf:Bag",
					"rdf:Seq",
					"rdf:Alt",
					"rdf:value",
					"rdf:List",
					"rdf:nil",
					"rdf:first",
					"rdf:rest",
					"rdf:XMLLiteral",
					"rdfs:Resource",
					"rdfs:Class",
					"rdfs:subClassOf",
					"rdfs:subPropertyOf",
					"rdfs:comment",
					"rdfs:label",
					"rdfs:domain",
					"rdfs:range",
					"rdfs:seeAlso",
					"rdfs:isDefinedBy",
					"rdfs:Literal",
					"rdfs:Container",
					"rdfs:ContainerMembershipProperty",
					"rdfs:member",
					"rdfs:Datatype"
				];
				$(".input_triplify_posts").autocomplete({
				  source: availableTags
				});
				$(".input_triplify").autocomplete({
				  source: availableTags
				});
				$(".input_triplify_posts").click(function(){
					if($(this).val() == 'correspondencia'){
						$(this).val('');
					}
				});
				$(".input_triplify").click(function(){
					if($(this).val() == 'correspondencia'){
						$(this).val('');
					}
				});
				$(".prefixo_salvar").click(function(){
					if($(this).val() == 'Prefixo' || $(this).val() == 'URI'){
						$(this).val('');
					}
				});
				$(".checkbox_nao_interessa").change(function(){
					var idx = $(this).attr('id');
					var contador = idx.replace("nao_me_interessa", "");
					var contadorX = "#conjunto";
					var elementoEsconder = contadorX.concat(contador);
					
					var correspondencia = "#correspondencia";
					var correspondenciaX = correspondencia.concat(contador);
					
					$(correspondenciaX).val("");
					$(elementoEsconder).hide();
				});
			});
			</script> <?php
		}
	
	}
	
	function salvaUrlBase($option){
		$option_saved = get_option("triplify_url_base_dados", null);
		if($option_saved == null) add_option("triplify_url_base_dados", $option);
		else if(strcmp(strtolower($option_saved), strtolower($option)) == 0) return;
		else update_option("triplify_url_base_dados", $option);
	}
	
	function pegaValores($data) {
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		$data = strtolower($data);
		return $data;
	}
}
 ?>
