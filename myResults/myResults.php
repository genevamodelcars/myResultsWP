<?php
/***
 Plugin Name: myResults by genevamodelcars
 Description: genevamodelcars plugin for display results.
 Version: 0.2
 Author: BDM for genevamodelcars
*/

// Avoid direct calls to this file
if ( !defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

class myResults
{
	const VERSION = '0.1';

	/**
	 * Constructor. Hooks all interactions to initialize the class.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @see	 add_shortcode()
	 */
	public function __construct()
	{
		add_shortcode('myResults_console', array($this, 'myResults_console_fct') );
		add_shortcode('myResults_live', array($this, 'myResults_live_fct') );
		add_shortcode('myResults_status', array($this, 'myResults_status_fct') );
		add_shortcode('myResults_activelist', array($this, 'myResults_activelist_fct') );
		add_shortcode('myResults_transponder', array($this, 'myResults_transponder_fct') );
		add_shortcode('myResults_ranking', array($this, 'myResults_ranking_fct') );
	} // END __construct()

	/**
	 * Fetch and return required events.
	 * @param  array $atts 	shortcode attributes
	 * @return string 	shortcode output
	 */
	public function myResults_console_fct()
	{
  			
	$string_console = '<div id="auto_load_console_div" class="auto_load_div"></div>';

	?>

   	<script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>
   	<script>
	
      	function auto_load_console(){
        	$.ajax({
          	url: "http://www.genevamodelcars.ch/wp-content/plugins/myResults/console.php",
          	cache: false,
          	success: function(data){
             	$("#auto_load_console_div").html(data);
          	} 
        	});
      	}
	
      	$(document).ready(function(){
	
        	auto_load_console(); //Call auto_load() function when DOM is Ready
	
      	});

    	  //Refresh auto_load() function after 500 milliseconds
    	  setInterval(auto_load_console,500);
  	 </script>
	<?php

	return $string_console;
	}

	public function myResults_status_fct()
	{
  			
	$string_status = '<div id="auto_load_status_div" class="auto_load_div"></div>';

	?>

   	<script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>
   	<script>
	
      	function auto_load_status(){
        	$.ajax({
          	url: "http://www.genevamodelcars.ch/wp-content/plugins/myResults/status.php",
          	cache: false,
          	success: function(data){
             	$("#auto_load_status_div").html(data);
          	} 
        	});
      	}
	
      	$(document).ready(function(){
	
        	auto_load_status(); //Call auto_load() function when DOM is Ready
	
      	});

    	  //Refresh auto_load() function after 500 milliseconds
    	  setInterval(auto_load_status,500);
  	 </script>
	<?php

	return $string_status;
	}

	public function myResults_live_fct()
	{
  			
	$string_live = '<div style="overflow-x:auto;"><div id="auto_load_live_div" class="auto_load_div"></div></div>';

	?>

   	<script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>
   	<script>

      	function auto_load_live(){
        	$.ajax({
          	url: "http://www.genevamodelcars.ch/wp-content/plugins/myResults/live.php",
          	cache: false,
          	success: function(data){
             	$("#auto_load_live_div").html(data);
          	} 
        	});
      	}
	
      	$(document).ready(function(){
	
        	auto_load_live(); //Call auto_load() function when DOM is Ready
		
		//Refresh auto_load() function after 500 milliseconds
    	  	setInterval(auto_load_live,500);
	
      	});
 

  	 </script>
	<?php

	return $string_live;
	}

	public function myResults_activelist_fct()
	{
  			
	$string_activelist = '<div id="auto_load_activelist_div" class="auto_load_div"></div>';

	?>

   	<script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>
   	<script>
	
      	function auto_load_activelist(){
        	$.ajax({
          	url: "http://www.genevamodelcars.ch/wp-content/plugins/myResults/activelist.php",
          	cache: false,
	       	success: function(data){
             	$("#auto_load_activelist_div").html(data);
          	} 
        	});
      	}
	
      	$(document).ready(function(){
	
        	auto_load_activelist(); //Call auto_load() function when DOM is Ready
	
      	});

    	  //Refresh auto_load() function after 500 milliseconds
    	  setInterval(auto_load_activelist,500);
  	 </script>
	<?php

	return $string_activelist;
	}

	public function myResults_transponder_fct()
	{

	//Get URL variables
	$queryURL = parse_url( html_entity_decode( esc_url( add_query_arg( $arr_params ) ) ) );
	parse_str( $queryURL['query'], $getVar );
	$transponder = $getVar['transponder'];
	$daydate= $getVar['daydate'];;
	//echo 'transponder' . $transponder;
	//echo 'daydate' . $daydate;

	//List transponder and daysession
	$xml=simplexml_load_file("http://www.genevamodelcars.ch/wp-content/plugins/myResults/config.xml");
	$connected = true;

	if($xml===FALSE) {
		exit('Echec lors de l\'ouverture du fichier config.');
	} else {	
		$dbservername = $xml->dbserver->adress;
		$dbactivename = $xml->dbserver->dbname;

	try {
		$mongo = new MongoClient($dbservername);
		}
		catch ( MongoConnectionException $e ) 
		{
		$connected = false;
  	  	echo 'Echec lors de l\'ouverture de la base de donnée.';
  	  	exit();
		}

		if ($connected == true){
		
		//TODO
		$db = $mongo->selectDB($dbactivename);

		//List transponder
		$statuscollection = $db->selectCollection("active");
		$cursor = $statuscollection->find();
		$i = 0;
		
		if(isset($_POST["formSubmit"])) 
		{               
 		
		$varname = $_POST['formName'];	
		$statuscollection->update(array('transponder' => $transponder),array('$set' => array('name' => $varname)));
		$varminlap = $_POST['formminlap'];	
		$statuscollection->update(array('transponder' => $transponder),array('$set' => array('minlap' => $varminlap)));
		$varmaxlap = $_POST['formmaxlap'];	
		$statuscollection->update(array('transponder' => $transponder),array('$set' => array('maxlap' => $varmaxlap)));
		
		}

		foreach($cursor as $jokes) {
			$transponderlist[$i] = $jokes[transponder];
			//echo $transponderlist[$i];
			$i++;
		}
		sort($transponderlist);

		echo '<div class="container"><form id="formtransponder" method="get"><div class="form-group"><div class="col-md-3"><label for="sel1">Transpondeur:</label><select class="form-control" id="sel1" name = "transponder" onchange="change()" >';
		
   		echo '<option selected value="">Choisir un transpondeur</option>';
		
		$statuscollection = $db->selectCollection("active");
		
		foreach($transponderlist as $value)
   		{
	        $cursor = $statuscollection->findOne(array("transponder" => $value));
		if ($cursor[name] != null) {
			$transpondername = $cursor[name];
		} else {
			$transpondername = $cursor[transponder];
		}			

		if ($value == $transponder)
			{
   			echo '<option selected value="' . $value . '">' . $transpondername . '</option>';
   		} else {
			echo '<option value="' . $value . '">' . $transpondername . '</option>';
		}

		}

		echo '</select></div>';

		//List daydate by transponder
		$list = $db->getCollectionNames();
		$i = 0;
		foreach ($list as $collection) {
    			if (strpos($collection, 'T' . $transponder . '_') !== false and strpos($collection,'resumesession') == false) {
   			 	$listdate[$i] = str_replace('T' . $transponder . '_','',$collection);
				//echo $listdate[$i];
				$i++;
			}
		}

		echo '<div class="col-md-3"><label for="sel2">Date:</label><select class="form-control" id = "sel2" name = "daydate" onchange="change()" >';
		echo '<option selected value="">Choisir une date</option>';
		
		rsort($listdate);
		foreach($listdate as $value)
   		{
   		if ($value == $daydate)
		{
   		echo '<option selected value="'.$value.'">' . substr_replace(substr_replace($value, '-', 4, 0), '-', 2, 0) . '</option>';
   		} else {

		echo '<option value="'.$value.'">' . substr_replace(substr_replace($value, '-', 4, 0), '-', 2, 0) . '</option>';
		}
   		}

		echo '</select></div></div></form>';

		if ($transponder != null) {

		$cursor = $statuscollection->findOne(array("transponder" => $transponder));
		
		if ($cursor[name] != null) {
			$transpondername = $cursor[name];
		} else {
			$transpondername = "";
		}

		if ($cursor[minlap] != null) {
			$minlap = $cursor[minlap];
		} else {
			$minlap = 0;
		}

		if ($cursor[maxlap] != null) {
			$maxlap = $cursor[maxlap];
		} else {
			$maxlap = 120;
		}
		
		if ( is_user_logged_in() ) {
			$usermessage = '
			<div><b>Numéro du transpondeur :</b>   ' . $transponder . '</div><br />
			<div><form action="" method="post">
			<label for="sel3">Modifier nom du transponder:</label><br />
    			<input id = "sel3" style = "width: 100%;" type="text" name="formName" maxlength="24" value="' . $transpondername . '">
			<label for="sel4">Modifier la valeur du minlap [sec]:</label><br />
    			<input id = "sel4" style = "width: 100%;" type="number" name="formminlap" min="0" max="60" step=".1" value="' . $minlap . '">
			<label for="sel5">Modifier la valeur du maxlap [sec]:</label><br />
    			<input id = "sel5" style = "width: 100%;" type="number" name="formmaxlap" min="30" max="360" step="1" value="' . $maxlap . '">
    			<br/><div style="text-align: right; margin-top: 10px;"><input class="btn btn-default" type="submit" name="formSubmit" value="Valider">
			</form></div></div>
			';

		} else {
			$usermessage = '<div"><b>Information :   </b><a href="/login" title = "login">Connectez-vous</a> pour assigner un nom au transpondeur ou pour modifier un filtre.</div>';
		}
			
		echo '<div class="col-md-6"><div style="text-align: right; margin-top: 25px;"><a class="btn btn-danger" role="button" data-toggle="collapse" href="#EditionName" aria-expanded="false" aria-controls="EditionName">Filtres</a></div></div><div class="col-md-12"><br /><div class="collapse" id="EditionName"><div class="alert alert-warning" >' . $usermessage . '</div></div></div>';
		
		}

		echo '</div><hr>';		

		?>
		<script>
		function change(){
		document.getElementById("formtransponder").submit();
			
		}
		</script>
		<?php

		//Deconnection                
		$mongo->close();
		}
	}
  			
	$string_results = '<div class = "loader" style="position: relative; text-align: center;"><button class="btn btn-lg btn-success"><span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span>  Chargement...</button><br></div><div id="auto_load_results_div" class="auto_load_div"></div>';

	?>
		
	<script src="//cdnjs.cloudflare.com/ajax/libs/dygraph/1.1.1/dygraph-combined.js"></script>
	<script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>
   	<script>
	
      	function auto_load_results(){
        	$.ajax({
          	url: "http://www.genevamodelcars.ch/wp-content/plugins/myResults/results.php",
          	cache: false,
		data: { "transponder": "<?php echo $transponder; ?>" , "daydate": "<?php echo $daydate; ?>"},
          	success: function(data){
		$(".loader").fadeOut(100),
             	$("#auto_load_results_div").html(data);
        	} 
        	});
      	}
	
      	$(document).ready(function(){
	
        	auto_load_results(); //Call auto_load() function when DOM is Ready
	
      	});

  	</script>

	<?php

	return $string_results;
	
	}
	
public function myResults_ranking_fct()
	{
  			
	$string_ranking = '<div class = "loader" style="position: relative; text-align: center;"><button class="btn btn-lg btn-success"><span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span>  Chargement...</button><br></div><div id="auto_load_ranking_div" class="auto_load_div"></div>';

	?>

   	<script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>
   	<script>
	
      	function auto_load_ranking(){
        	$.ajax({
          	url: "http://www.genevamodelcars.ch/wp-content/plugins/myResults/ranking.php",
          	cache: false,
	       	success: function(data){
		$(".loader").fadeOut(100),
             	$("#auto_load_ranking_div").html(data);
          	} 
        	});
      	}
	
      	$(document).ready(function(){
	
        	auto_load_ranking(); //Call auto_load() function when DOM is Ready
	
      	});

  	 </script>
	<?php

	return $string_ranking;
	}
	
	/**
	 * Checks if the plugin attribute is valid
	 *
	 * @since 1.0.5
	 *
	 * @param string $prop
	 * @return boolean
	 */
	private function isValid( $prop )
	{
		return ($prop !== 'false');
	}

	/**
	 * Fetch and trims the excerpt to specified length
	 *
	 * @param integer $limit Characters to show
	 * @param string $source  content or excerpt
	 *
	 * @return string
	 */
	private function get_excerpt( $limit, $source = null )
	{
		$excerpt = get_the_excerpt();
		if( $source == "content" ) {
			$excerpt = get_the_content();
		}

		$excerpt = preg_replace(" (\[.*?\])", '', $excerpt);
		$excerpt = strip_tags( strip_shortcodes($excerpt) );
		$excerpt = substr($excerpt, 0, $limit);
		$excerpt = trim(preg_replace( '/\s+/', ' ', $excerpt));
		$excerpt .= '...';

		return $excerpt;
	}
}

/**
 * Instantiate the main class
 *
 * @since 1.0.0
 * @access public
 *
 */
global $myResults;
$myResults = new myResults();
?>
