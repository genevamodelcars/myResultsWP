<?php
/***
Calendar function
LiveTiming
*/
?>

<?php

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';

$xml=simplexml_load_file("http://www.genevamodelcars.ch/wp-content/plugins/myResults/config.xml");
$connected = true;
//error_reporting(E_ERROR | E_PARSE);
date_default_timezone_set('Europe/Zurich');
	
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
		$daycalendar = $_GET["daycalendar"];
		
		if ($yeardate == NULL)
		{
			$db = $mongo->selectDataBase($dbactivename);
			$yeardate = $dbactivename;
		}
		else
		{
        	$db = $mongo->selectDataBase($yeardate);
		}
		
		$opendate = strtotime('1 january' . $yeardate);
		$current = $opendate;
		$closedate = strtotime('31 december' . $yeardate);
		$calendar = array();
		$translist = array();
		$max = 0;
		$print;
		
		while( $current < $closedate ) {
		
			$calendar[date('dmY', $current)] = array();
			$current +=86400;

   		}
			
		$collections = $db->listCollections();
		
		foreach ($collections as $collection) {
			
			//echo $collection->getName() . '<br>';
			//echo substr($collection->getName(), 5, 18);
			
			//echo $collection;
			if (strlen($collection->getName()) == 17){
				//Table with date and number of people
				//$translistdate[substr($collection->getName(), 9, 8)][] = substr(substr($collection->getName(), 0, 13),5,8);
				//print_r($translistdate);
				$activetrans = substr($collection->getName(), 0, 17);
				/*echo $activetrans;
				echo '<br>';*/
				$activetrans_collection = $db->selectCollection("$activetrans");
				
				//TODO Add array of sessions
				//$calendar[substr($collection, 14, 8)] = $translist;
				array_push($calendar[substr($collection->getName(), 9, 8)], $activetrans);
				if ($max < sizeof($calendar[substr($collection->getName(), 9, 8)])) {
						$max = sizeof($calendar[substr($collection->getName(), 9, 8)]);
				}
			}
		
		}
			
		//print_r($calendar);
		
		if ($daycalendar != Null) {
			if (sizeof($calendar[$daycalendar]) == 0) {
				$print .= 'Aucun pilote ce jour-ci';
			} else {
			foreach($calendar[$daycalendar] as $key => $value)
				{
				  $sessionday = substr($value,9,8);
				  $transponder = substr($value, 1, 7);
				  
				  $statuscollection = $db->selectCollection("active");
	              $cursor = $statuscollection->findOne(array("transponder" => $transponder));
				  if ($cursor[name] != null) {
					$transpondername = $cursor[name];
				  } else {
					$transpondername = $transponder;
				  }
				  
				  $print .= '<a href=/myresults/?yeardate=' . $yeardate . '&transponder=' . $transponder . '&daydate=' . $sessionday . '>' . $transpondername . '</a><br>';
				}
			}
		} else {
			$print .= 'Veuillez choisir une date';
		}
			
		$current = $opendate;
		while( $current <= $closedate ) {
			if (date("d",$current) == 1) {
				$print .= '<br><div class="myResults_calendar_month"><h2>' . date("F",$current). '</h2></div>';
			}
			
			if (date("Y-m-d", $current) > date("Y-m-d")){
				$backcolor = '150,150,150';
				$sizenum = 'height: 35px; width: 35px;font-size:18px;';
			} else {
				if ((sizeof($calendar[date('dmY', $current)]) / $max) == 0){
					$backcolor = '20,20,20';
					$sizenum = 'height: 35px; width: 35px;font-size:18px;';
				} else {
				$backcolor = (sizeof($calendar[date('dmY', $current)]) / $max * 255) . ',50,50';
				$sizenum = 'height: ' . ((sizeof($calendar[date('dmY', $current)]) / $max * 30) + 40) .'px; width: ' . ((sizeof($calendar[date('dmY', $current)]) / $max * 30) + 40) .'px;font-size:' . ((sizeof($calendar[date('dmY', $current)]) / $max * 15) + 22) . 'px;';
				}	
			}	
			
			$print .= '<a class="myResults_calendar_number" style="' . $sizenum . 'background-color:rgb('. $backcolor . ');" href="/myresults/calendrier/?yeardate=' . $yeardate . '&daycalendar=' . date("dmY", $current) . '">' . date("d",$current) . "</a>";
		
			//$translistsize[$i] = array($current, count($translist[date('dmY', $current)]));
			// sizeof($calendar[date('dmY', $current)])
			$current +=86400;
   		}
		
		//print_r($translistsize);
		echo $print;
		//print_r($data);
		//Deconnection                
		//$mongo->close();
		}
}

?>
