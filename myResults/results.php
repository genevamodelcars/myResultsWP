<?php
/***
Console function
LiveTiming
*/
?>

<?php
	error_reporting(0);
        /*error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);*/
    date_default_timezone_set('Europe/Zurich');
	
	/*ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);*/
	
	require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';
	
	$xml=simplexml_load_file("http://www.genevamodelcars.ch/wp-content/plugins/myResults/config.xml");
	$connected = true;
    error_reporting(E_ERROR | E_PARSE);

	if($xml===FALSE) {
		exit('Echec lors de l\'ouverture du fichier config.');
	} else {	
		$dbservername = $xml->dbserver->adress;
		$dbactivename = $xml->dbserver->dbname;

	try {
		//$mongo = new MongoClient($dbservername);
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
		$date = $_GET['daydate'];
		$transponder = $_GET['transponder'];
        $yeardate = $_GET['yeardate'];
		$lastntime = 0;

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

		$statuscollection = $db->selectCollection("active");
		$cursor = $statuscollection->findOne(array("transponder" => $transponder));
		
		if ($cursor[minlap] != null) {
			$minlap = $cursor[minlap];
		} else {
			$minlap = 0;
		}

		if ($cursor[maxlap] != null) {
			$maxlap = $cursor[maxlap];
		} else {
			$maxlap = 120;
		}


		$activetrans = "T" . strval($transponder) . "_" . $date;
		$activetrans_collection = $db->selectCollection("$activetrans");
		
		
		$i = 0;
		$j = 0;
		$sessioncounter = 0;
		$lastnlap = 0;
		$totaltimelast = 0;
		$bestlapinit = 120;
		$lapdata[] = null;
		$totaltimesession[] = null;
		$RTCtime[] = null;
		$average[] = null;
		$bestlap[] = null;
		$arraytographresume[] = null;
		$arraytographsession[] = null;
		$sessionpos[] = null;
		$sessionpos[0] = 0;
			
		//$transponderdata = $activetrans_collection->find()->sort(array('_id'=>1));
		$transponderdata = $activetrans_collection->find();
		//print_r($transponderdata);

		foreach ($transponderdata as $time) {
			$lapdata[$i] = (intval($time["RTC_Time"]) - $lastnlap) / 1000000 ;
			
			$RTCtime[$i] = date('H:i:s' ,intval($time["RTC_Time"] / 1000000));
			//echo $lapdata[$i];

			if ($lapdata[$i] > $minlap) {
			
			$lastnlap = intval($time["RTC_Time"]);
			//echo $time["RTC_Time"] . ":";

			if ($lapdata[$i] > $maxlap or $lapdata[$i] < 0)  {

			$lapdata[$i] = 0;
			$totaltimelast = 0;
			$bestlapinit = 120;
			$worstlapinit = 0;
			$j = 0;
			$sessioncounter++;
			$sessionpos[$sessioncounter] = $i;

			
			if ($lapdata[$i-1] == 0 and $i !=0) {
			$i--;
			$sessioncounter--;
			$RTCtime[$i] = date('H:i:s' ,intval($time["RTC_Time"] / 1000000));
			}
			
			} else {

			if ($lapdata[$i]< $bestlapinit) {

			$bestlap[$sessioncounter] = $lapdata[$i];
			$bestlapinit = $lapdata[$i];
			}
			
			if ($lapdata[$i]> $worstlapinit) {

			$worstlap[$sessioncounter] = $lapdata[$i];
			$worstlapinit = $lapdata[$i];
			}
			}

			$totaltimelast = $totaltimelast + $lapdata[$i];
			$totaltimesession[$i] = $totaltimelast;
			
			$average[$sessioncounter] = $totaltimelast / $j;

			$position[$i] = $j;
			$arraytographresume[$sessioncounter] = array($sessioncounter, $average[$sessioncounter], $bestlap[$sessioncounter]);
			$arraytographsession[$i] = array($position[$i], $lapdata[$i]);
			
			$i++;
			$j++;
			
			}
			}
		$size = count($lapdata);
		$sessionpos[$sessioncounter+1] = $size;
		$sizesession = $sessioncounter;
		$totnblaps = $size - $sizesession;

		$absbestlap = $bestlap;
		sort($absbestlap);

		$absaverage = $average;
		sort($absaverage);
		
		if ($size > 1) {

		//echo $size;

		echo '<div class="row"><div><H3>Absolute bestlap : ' . number_format($absbestlap[1],3) . ' | Absolute best average : ' . number_format($absaverage[1],3) . ' | Total number of laps : ' . $totnblaps . '</h3></div></div><div class="row"></br><div><h4 style = "text-transform: uppercase;">Summary by session</h4><table class="myResults_livetiming_table"><tr><th>' . 'session' . '</th><th>' . 'bestlap' . '</th><th>' . 'average' . '</th><th>' . 'Laps' . '</th><th>' . 'Total' . '</th><th>' . 'EndTime' . '</th><th style="width:30%">' . 'Best/Average' . '</th></tr>';


		for ($i=$sessioncounter; $i > 0; $i--) {
			
			if ($bestlap[$i] == $absbestlap[1]) {
				$bestbarstyle = 'background-color: #FF00FF;';
			} else {
				$bestbarstyle = 'background-color: white;';
			}
			
			if ($average[$i] == $absaverage[1]) {
				$averagebarstyle = 'background-color: #FF00FF;';
			} else {
				$averagebarstyle = 'background-color: lightgray;';
			}
			
			$bestbar = number_format(($bestlap[$i]) / $maxlap * 100,2);
			$averagebar = number_format(($average[$i]) / $maxlap * 100,2) - $bestbar;
			
			//echo 'Session ' . $i . ' - ' . $bestlap[$i] . ' - ' . $average[$i] . '</br>';
			echo '<tr><td>' . $i . '</td><td>' . number_format($bestlap[$i],3) . '</td><td>' . number_format($average[$i],3). '</td><td>' . ($sessionpos[$i+1]-$sessionpos[$i]-1) . '</td><td>' . number_format($totaltimesession[$sessionpos[$i+1]-1],3) . '</td><td>' . Date('H:i',strtotime($RTCtime[$sessionpos[$i+1]-1])-7200) . '</td><td><span style="height: 15px;display:inline-block; width:'. $bestbar . '%;'. $bestbarstyle .'"></span><span style="height: 15px;display:inline-block; width:'. $averagebar . '%;'. $averagebarstyle .'"></span></td></tr>';

		}

		echo '</table>';
		echo '<div class="row"><h4 style = "text-transform: uppercase;">detailed results</h4><div><H3><span>Session ' . ($sessioncounter ) . '</span></H3><div>Average : ' . number_format($average[$sessioncounter],3) . ' | <b>Bestlap :  ' . number_format($bestlap[$sessioncounter],3) . '</b></div><br></div><div><table class="myResults_livetiming_table"><tr><th>' . 'lap' . '</th><th>' . 'laptime' . '</th><th>' . 'total' . '</th><th>' . 'time' . '</th><th style="width:30%">' . '' . '</th></tr>';

		for ($i=$size-1; $i > 0; $i--) {

		if ($lapdata[$i] == 0 ) {
			if ($lapdata[$i-1] == 0) {				
			} else {
				$sessioncounter--;
				echo '<tr><td>' . $position[$i] . '</td><td>' . number_format($lapdata[$i],3) . '</td><td>' . number_format($totaltimesession[$i],3) . '</td><td>' . Date('H:i:s',strtotime($RTCtime[$i])-7200) . '</td><td></td></tr></table></div></div><hr><br><div class="row"><div><H3><span>Session '. ($sessioncounter ) . '</span></H3><div>Average : ' . number_format($average[$sessioncounter],3) . ' | <b>Bestlap :  ' . number_format($bestlap[$sessioncounter],3) . '</b></div><br></div><div><table class="myResults_livetiming_table"><tr><th>' . 'lap' . '</th><th>' . 'laptime' . '</th><th>' . 'total' . '</th><th>' . 'time' . '</th><th style="width:30%">' . '' . '</th></tr>';
			}
		} else {
			
			if($lapdata[$i] == 0){
					$lapstyle = 'color: #FFFF00;';
					$lapstylebar = 'background-color: #FFFF00;';
				}
			else{
				if ($lapdata[$i] < $average[$sessioncounter]){
					if ($lapdata[$i] <= $bestlap[$sessioncounter]){
						$lapstyle = 'color: #FF00FF;font-weight: 600;';
						$lapstylebar = 'background-color: #FF00FF;';
					} else {
						$lapstyle = 'color: #00FF00;font-weight: 600;';
						$lapstylebar = 'background-color: #00FF00;';
					}
				} else {
					$lapstyle = '';
					$lapstylebar = 'background-color: white;';
				}
			}
			
			$lapbardev = number_format(($average[$sessioncounter] - $lapdata[$i]) / ($worstlap[$sessioncounter]- $bestlap[$sessioncounter]) * 50,2);
			if ($lapbardev < 0){
				$lapbarmargin = number_format(50, 2);
				//echo $lapbarmargin . ' | ' . $average[$sessioncounter] . ' | ' . $worstlap[$sessioncounter] . ' | ' . $bestlap[$sessioncounter];
				$lapbarcolor = 'background-color: rgb(255,' . (255 - number_format(4*(abs($average[$sessioncounter]- $lapdata[$i]) / $worstlap[$sessioncounter]) * 255,0)) . ', 0);';
				
			}
			else	{
				$lapbarmargin = number_format(50 - $lapbardev, 2);
				
				if ($lapdata[$i] == $bestlap[$sessioncounter]){
					$lapbarcolor = 'background-color:#FF00FF;';
				}
				else {
					$lapbarcolor = 'background-color: rgb(' . (255 - number_format(22*(abs($average[$sessioncounter]- $lapdata[$i]) / $worstlap[$sessioncounter]) * 255,0)) . ', 255, 0);';
				}
			}
			
			$lapbar = number_format(($lapdata[$i]) / $maxlap * 100,2);
   		
		echo '<tr><td>' . $position[$i] . '</td><td style = "'. $lapstyle .'">' . number_format($lapdata[$i],3) . '</td><td>' . number_format($totaltimesession[$i],3) . '</td><td>' . Date('H:i:s',strtotime($RTCtime[$i])-7200) . '</td><td><span style="height: 15px;display:block; width:'. abs($lapbardev) . '%; margin-left:' . $lapbarmargin . '%;' . $lapbarcolor .'"></span></td></tr>';
		}		
		}		

		echo '<tr><td>' . 0 . '</td><td>' . number_format(0,3) . '</td><td>' . number_format(0,3) . '</td><td>' . Date('H:i:s',strtotime($RTCtime[0])-7200) . '</td><td></td></tr></table></div></div>';

		}
	
		//Debug
		/*for ($i=$size-1; $i > 0; $i--) {
			echo $lapdata[$i];
		}*/
		/* old graph
		$sliced_array[] = null;
		for ($i = 1; $i < ($sizesession+1); $i++) {
		
  		$sliced_array = array_slice($arraytographsession, $sessionpos[$i], $sessionpos[$i+1]-$sessionpos[$i]);
		if (count($sliced_array) > 1) {
		
		}
		}*/

		//print_r($data);
		//Deconnection                
		//$mongo->close();
		}
	}
?>
