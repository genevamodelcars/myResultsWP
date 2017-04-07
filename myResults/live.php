<?php
/***
Console function
LiveTiming
*/
	
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
		$db = $mongo->selectDB($dbactivename);
		$statuscollection = $db->selectCollection("active");
		$active[] = null;
		echo '<table class="myResults_livetiming_table"><tr><th>' . 'transponder' . '</th><th>' . 'lap' . '</th><th>' . 'lastlap' . '</th><th>' . 'bestlap' . '</th><th>' . 'average' . '</th><th>'  . 'total' . '</th></tr>';
		
		//Find lasttransponderupdate
		$cursor = $statuscollection->find();
		foreach($cursor as $jokes) {
 		//echo $jokes[lastupdate]->sec;
		
		//Check if lastupdate is recent
		$int = (strtotime(date('Y-M-d H:i:s')) - strtotime(date('Y-M-d H:i:s' , $jokes[lastupdate]->sec)));
		if ($int <= 120){
			$active[] = $jokes[transponder];
		}		
		}
		//print_r($active);
		unset($active[0]);
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
			

		echo '<tr><td><a href=/myresults/?transponder=' . $sessiondata["transponder"] . '&daydate=' . date('dmY') . '>' . $transponder . '</a></td><td>' . $lapnumber . '</td><td>' . number_format($laptime,3) . '</td><td>' . number_format($bestlap,3) . '</td><td>' . number_format($averagelap,3) . '</td><td>' . number_format($totaltime,3) . '</td></tr>';		
					
		}

		//print_r($data);
		echo '</table>';
		//Deconnection                
		$mongo->close();
		}

	}

?>
