<?php
/***
Activlist function
List transponder of last hour
*/

	header("Access-Control-Allow-Origin: *"); 
	header('Access-Control-Allow-Methods: GET, POST, OPTIONS');	
	
	require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';
	
	$xml=simplexml_load_file("http://www.genevamodelcars.ch/wp-content/plugins/myResults/config.xml");
	$connected = true;
	//error_reporting(E_ERROR | E_PARSE);
	date_default_timezone_set('Europe/Zurich');
	
	/*ni_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);*/
		
	if($xml===FALSE) {
			exit('Echec lors de l\'ouverture du fichier config.');
	} else {	
			$dbservername = $xml->dbserver->adress;
			$dbactivename = $xml->dbserver->dbname;

	try {
			$mongo = new MongoDB\Client($dbservername);
			
	}
	catch (MongoDB\Driver\Exception\ConnectionTimeoutException $e)
	{
		$connected = false;
  	  	echo 'Echec lors de l\'ouverture de la base de donnée.';
  	  	exit();
		}

	if ($connected == true){
		$db = $mongo->selectDataBase($dbactivename);
		$statuscollection = $db->selectCollection("active");
				
		//Find lasttransponderupdate
		$cursor = $statuscollection->find(array(),array("sort" => (array("lastupdate" => -1))));
		//print_r($cursor);
		//->sort(array("lastupdate" => -1));
		
		echo '<table class="myResults_livetiming_table"><tr><th>' . '' . '</th><th>' . 'time' . '</th></tr>';		


		$activedrivers = 0;
		foreach($cursor as $jokes) {
		
		//Check if lastupdate is today
		$int = (strtotime(date('Y-M-d H:i:s')) - strtotime(date('Y-M-d H:i:s' , $jokes[lastupdate]->toDateTime()->format('U'))));
		//print_r($jokes);
		//echo ($int) . '<br>';
		//echo date('H:i:s' , $jokes[lastupdate]->toDateTime()->format('U'));
		
		if ($jokes[name] != null) {
			$transponder = $jokes[name];
			} else {
			$transponder = $jokes[transponder];
		        }

			if($int < 1800){
				$activedrivers = $activedrivers+1;
				echo '<tr><td><a style="color: white;" href=/myresults/?transponder=' . $jokes[transponder] . '&daydate=' . date('dmY') . '>' . $transponder . '</a></td><td>' . date('H:i:s' , $jokes[lastupdate]->toDateTime()->format('U')) . '</td></tr>';
			}	
		}
		if ($activedrivers == 0){
			echo '<tr><td colspan="2" style="text-transform: initial;">No driver on track during the last 30 minutes.</td></tr>';
			
		}
		
		echo '</table>';

		//Deconnection                
		//$mongo->close();
		}

	}

?>