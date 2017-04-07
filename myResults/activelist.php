<?php
/***
Activlist function
List transponder of last hour
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
				
		//Find lasttransponderupdate
		$cursor = $statuscollection->find()->sort(array("lastupdate" => -1));
		
		echo '<table class="myResults_livetiming_table"><tr><th>' . 'transponder' . '</th><th>' . 'time' . '</th></tr>';		

		foreach($cursor as $jokes) {
		//Check if lastupdate is today
		$int = (strtotime(date('Y-M-d H:i:s')) - strtotime(date('Y-M-d H:i:s' , $jokes[lastupdate]->sec)));
		
		if ($jokes[name] != null) {
			$transponder = $jokes[name];
			} else {
			$transponder = $jokes[transponder];
		        }

			if($int < 1800){
				echo '<tr><td><a href=/myresults/?transponder=' . $jokes[transponder] . '&daydate=' . date('dmY') . '>' . $transponder . '</a></td><td>' . date('H:i:s' , $jokes[lastupdate]->sec) . '</td></tr>';
			}		
		}
		
		echo '</table>';

		//Deconnection                
		$mongo->close();
		}

	}

?>