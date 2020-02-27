<?php
/***
Console function
Call the last pass entries
*/
	header("Access-Control-Allow-Origin: *"); 
	header('Access-Control-Allow-Methods: GET, POST, OPTIONS');	
	
	require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';
	
	$xml=simplexml_load_file("http://www.genevamodelcars.ch/wp-content/plugins/myResults/config.xml");
	$connected = true;
	$limit = 5;
	
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
		//echo 'Hello';
		$db = $mongo->selectDataBase($dbactivename);
		$statuscollection = $db->selectCollection("status");

		//for ($i = 0; $i < $limit; $i++) {
		//Affiche les dernières entrées de type "pass"
		//$cursor = $passcollection->find()->sort(array('_id'=>-1))->skip($i)->limit(1);
		//echo '<div style ="padding: 10px;">' . json_encode(iterator_to_array($cursor, false), true) . '</div>';
		
		//Date and Message from last update.
		$last= $statuscollection->findOne(array('date' => 'date'), array('Date'));
		$lastdate = $last["Date"];
		$last= $statuscollection->findOne(array('Lastmessage' => 'Lastmessage'), array('Message'));
		$lastmessage = $last["Message"];
		date_default_timezone_set('Europe/Zurich');

		$dateRef = date('d-M-Y H:i:s' , $lastdate->toDateTime()->format('U'));
		$messageRef = json_encode($lastmessage);

		echo '<div class="myResults_console_date">' . $dateRef . ':' . '</div><div class="myResults_console_message">' . $messageRef . '</div>';

		//Deconnection                
		//$mongo->close();
		}

	}

?>