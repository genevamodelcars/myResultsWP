<?php
/***
live function
LiveTiming
*/
	
	header("Access-Control-Allow-Origin: *"); 
	header('Access-Control-Allow-Methods: GET, POST, OPTIONS');	

	require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';
	
	$xml=simplexml_load_file("http://www.genevamodelcars.ch/wp-content/plugins/myResults/config.xml");
	$connected = true;
	//error_reporting(E_ERROR | E_PARSE);
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
  	  	echo 'Echec lors de l\'ouverture de la base de donnée.';
  	  	exit();
		}

	if ($connected == true){
		$db = $mongo->selectDataBase($dbactivename);
		$statuscollection = $db->selectCollection("active");
		$active[] = null;
		echo '<table class="myResults_livetiming_table"><tr><th width="30%">' . '' . '</th><th>' . 'lap' . '</th><th>' . 'lastlap' . '</th><th>' . 'bestlap' . '</th><th>' . 'average' . '</th><th>'  . 'total' . '</th></tr>';
		
		//Find lasttransponderupdate
		$cursor = $statuscollection->find();
		foreach($cursor as $jokes) {
 		//echo $jokes[lastupdate]->sec;
		
		//Check if lastupdate is recent
		$int = (strtotime(date('Y-M-d H:i:s')) - strtotime(date('Y-M-d H:i:s',$jokes[lastupdate]->toDateTime()->format('U'))));
		if ($int <= 120){
			$active[] = $jokes[transponder];
		}		
		}
		//print_r($active);
		unset($active[0]);
		
		if (sizeof($active) == 0){
			echo '<tr><td colspan="6" style="text-transform: initial;">No driver on track at the moment.</td></tr>';
			
		}
		else {
				foreach ($active as $transponder) {
			$activetrans = "T" . strval($transponder) . "_" . "resumesession";
			//echo $activetrans;
			$activetrans_collection = $db->selectCollection("$activetrans");
			
			$sessiondata = $activetrans_collection->findOne();
			
			$statuscollection = $db->selectCollection("active");
	                $cursor = $statuscollection->findOne(array("transponder" => $transponder));
			if ($cursor[name] != null) {
			$transponder = $cursor[name];
			} else {
			$transponder = $sessiondata["transponder"];
		        }		
			$laptime = $sessiondata["lastlap"];
			$lapnumber = $sessiondata["lap"];
			$bestlap = $sessiondata["bestlap"];
			$averagelap = $sessiondata["average"];
			$totaltime = $sessiondata["total"];
			
			if($laptime == 0){
				$lapstyle = 'color: #FFFF00;';
			}
			else{
				if ($laptime < $averagelap){
					if ($laptime <= $bestlap){
						$lapstyle = 'color: #FF00FF;font-weight: 600;';
					} else {
						$lapstyle = 'color: #00FF00;font-weight: 600;';
					}
				} else {
					$lapstyle = '';
				}
			}
		echo '<tr><td><a style="color: white;" href=/myresults/?transponder=' . $sessiondata["transponder"] . '&daydate=' . date('dmY') . '>' . $transponder . '</a></td><td>' . $lapnumber . '</td><td style = "' . $lapstyle .'">' . number_format($laptime,3) . '</td><td>' . number_format($bestlap,3) . '</td><td>' . number_format($averagelap,3) . '</td><td>' . number_format($totaltime,3) . '</td></tr>';		
					
		}
		}

		//print_r($data);
		echo '</table>';
		//Deconnection                
		//$mongo->close();
		}

	}

?>