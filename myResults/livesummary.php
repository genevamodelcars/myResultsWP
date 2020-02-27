<?php
/***
Live summary function
List of people at the track today
*/

	header("Access-Control-Allow-Origin: *"); 
	header('Access-Control-Allow-Methods: GET, POST, OPTIONS');	
	
	require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';
	
	$xml=simplexml_load_file("http://www.genevamodelcars.ch/wp-content/plugins/myResults/config.xml");
	$connected = true;
    date_default_timezone_set('Europe/Zurich');
	
	/*ini_set('display_errors', 1);
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
  	  	//echo 'Echec lors de l\'ouverture de la base de donnée.';
  	  	exit();
		}

	if ($connected == true){
		$db = $mongo->selectDataBase($dbactivename);
		$statuscollection = $db->selectCollection("active");
				
		//Find lasttransponderupdate
		$cursor = $statuscollection->find(array(),array("sort" => (array("lastupdate" => -1))));
		$count = 0;
	    $countday = 0;
		$countyear = 0;

		foreach($cursor as $jokes) {
		//Check if lastupdate is today
		$sec = (strtotime(date('Y-M-d H:i:s')) - strtotime(date('Y-M-d H:i:s' , $jokes[lastupdate]->toDateTime()->format('U'))));
		$day = (strtotime(date('Y-M-d')) - strtotime(date('Y-M-d' , $jokes[lastupdate]->toDateTime()->format('U'))));
        $year = (strtotime(date('Y')) - strtotime(date('Y' , $jokes[lastupdate]->toDateTime()->format('U'))));

			if ($jokes[name] != null) {
				$transponder = $jokes[name];
			} else {
				$transponder = $jokes[transponder];
			}

			if($sec < 300){
				$count += 1;
			}
			if($day < 82800) {
				$countday += 1;
			}
			if($year < 86400*365) {
				$countyear += 1;
			}
		}

		$message = '';

		if ($countday != 0) {
			if ($countday > 1){
				
				$message .=  '<span class = "myResults_livesummary_number">' . $countday . '</span> pilotes présents au circuit aujourd\'hui.';
			} else {
				$message .=  '<span class = "myResults_livesummary_number">' . $countday . '</span> pilote présent au circuit aujourd\'hui.';
			}
		}
		if ($count != 0) {
			if ($count > 1) {
				$message .= ' ' . '<span class = "myResults_livesummary_number"><a href="/myresults/live">' . $count . '</span> sont entrain de rouler!</a>';
			} else {
				$message .= ' ' . '<span class = "myResults_livesummary_number"><a href="/myresults/live">' . $count . '</span> est entrain de rouler!</a>';
			}
		}

		echo '<div class = "myResults_livesummary">' . $message . '</div>';

		//Deconnection                
		//$mongo->close();
		}

	}

?>
