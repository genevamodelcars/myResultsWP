<?php
/***
Stats function
LiveTiming
*/
?>

<?php

	require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';

	$xml=simplexml_load_file("http://www.genevamodelcars.ch/wp-content/plugins/myResults/config.xml");
	$connected = true;
        //error_reporting(E_ERROR | E_PARSE);
        date_default_timezone_set('Europe/Zurich');

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

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

		//Display results

        $yeardate = $_GET['yeardate'];

		if ($yeardate == NULL)
		{
			$db = $mongo->selectDataBase($dbactivename);
			$yeardate = $dbactivename;
		}
		else
		{
        	$db = $mongo->selectDataBase($yeardate);
		}

		//$db = $mongo->selectDB($dbactivename);
		/*
		$opendate = strtotime("1 april");
		$current = $opendate;
		$closedate = strtotime("1 november");
		$translist= array();
		$translistsize = array();

		while( $current < $closedate ) {
		
			$translist[date('dmY', $current)] = Null;
			$current +=86400;
   		}
		
		*/
		$collections = $db->listCollections();
		
		$laps_y = 0;
			
		/*foreach ($collections as $collection) {
   			
			$laps = 0;
			
			//echo $collection;
			if (strlen($collection) == 22){
				//Table with date and number of people
				$translistdate[substr($collection, 14, 8)][] = substr(substr($collection, 0, 13),5,8);
				
				$activetrans = substr($collection, 5, 18);
				//echo $activetrans;
				$activetrans_collection = $db->selectCollection("$activetrans");
				
				$transponderdata = $activetrans_collection->find()->sort(array('_id'=>1));
				foreach ($transponderdata as $time) {
					$laps++;
				}
				//echo $laps . '-';
			}
			
			$laps_y = $laps_y+$laps;
		}
		*/
		
		echo $laps_y;

		/*$current = $opendate;
		$i = 0;		
		while( $current < $closedate ) {
		
			$translistsize[$i] = array($current, count($translist[date('dmY', $current)]));
			$current +=86400;
			$i++;
   		}
		*/
		//print_r($translistsize );

		//print_r($data);
		//Deconnection                
		//$mongo->close();
		}
	}
?>