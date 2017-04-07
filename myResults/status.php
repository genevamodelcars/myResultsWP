<?php
/***
Console status
Status from lastupdate of mylaps.
*/
	
	$xml=simplexml_load_file("http://www.genevamodelcars.ch/wp-content/plugins/myResults/config.xml");
	$connected = true;
	$limit = 5;

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
		$statuscollection = $db->selectCollection("status");
		
		//$cursor = $statuscollection->find()->sort(array('_id'=>-1))->limit(1);
		//$json = json_encode(iterator_to_array($cursor, false), true);

		$last= $statuscollection->findOne(array('date' => 'date'), array('Date'));
		$lastdate = $last["Date"];
		date_default_timezone_set('Europe/Zurich');

		//echo date('Y-M-d H:i:s', $lastdate->sec);
		//echo date('Y-M-d H:i:s');
		
		$int = (strtotime(date('Y-M-d H:i:s')) - strtotime(date('Y-M-d H:i:s' , $lastdate->sec)));
		if ($int >= 300){

			echo '<div class="myResults_status myResults_status-danger">Hors ligne</div>';
		
		} else {
		
			echo '<div class="myResults_status myResults_status-success">En ligne</div>';
		
		}	
		
		//Deconnection                
		//$mongo->close();
		}

	}

?>