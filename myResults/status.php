<?php
/***
Console status
Status from lastupdate of mylaps.
*/
	header("Access-Control-Allow-Origin: *"); 
	header('Access-Control-Allow-Methods: GET, POST, OPTIONS');	

	$limit = 5;

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
		$statuscollection = $db->selectCollection("status");
		
		//$cursor = $statuscollection->find()->sort(array('_id'=>-1))->limit(1);
		//$json = json_encode(iterator_to_array($cursor, false), true);

		
		//$last= $statuscollection->findOne(array('date' => 'date'), array('Date'));
		$last= $statuscollection->findOne(['date' => 'date'], array('Date'));
		//print_r($last);
		
		$lastdate = $last["Date"];
		//print_r($lastdate);
		
		date_default_timezone_set('Europe/Zurich');

		//echo date('Y-M-d H:i:s', $lastdate->sec);
		//echo date('Y-M-d H:i:s');
		
		$int = (strtotime(date('Y-M-d H:i:s')) - strtotime(date('Y-M-d H:i:s'),$lastdate->toDateTime()->format('U')));
		//print_r(strtotime(date('Y-M-d H:i:s')));
		//print_r(strtotime(date('Y-M-d H:i:s'),$lastdate->toDateTime()->format('U')));
		if ($int >= 300){

			echo '<div class="myResults_status><span class="myResults_status-red"></span>Offline</div>';
		
		} else {
		
			echo '<div class="myResults_status"><span class="myResults_status-green"></span>Online</div>';
		
		}	
		
		//Deconnection                
		//$mongo->close();
		}

	}

?>