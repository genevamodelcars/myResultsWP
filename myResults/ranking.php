<?php
/***
Ranking function
Call the last pass entries
*/
	header("Access-Control-Allow-Origin: *"); 
	header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
	
	require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';

	//Variables	
	$xml=simplexml_load_file("http://www.genevamodelcars.ch/html/wp-content/plugins/myResults/config.xml");
	$connected = true;
	$dayupdate = 7;
	
	/*ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);*/
	
	//Time format in french
	$month = array("", "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"); 
    date_default_timezone_set('Europe/Zurich');
    error_reporting(E_ERROR | E_PARSE);

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

		$yeardate = $_GET['yeardate'];
		$actualseason = true;

		if ($yeardate == NULL)
		{
			$db = $mongo->selectDataBase($dbactivename);
			$yeardate = $dbactivename;
		}
		else
		{
        	$db = $mongo->selectDataBase($yeardate);
			if ($yeardate != $dbactivename) {
			$actualseason = false;
			}
		}

		//$db = $mongo->selectDB($dbactivename);
		$rankingcollection = $db->selectCollection("ranking");
		$cursor = $rankingcollection->find(array(),array("sort" => array('score'=>-1)));
		

		$statuscollection = $db->selectCollection("active");

		foreach($cursor as $jokes) {
		if ((strtotime('monday')-strtotime(date('d-M-y',$jokes[date]->toDateTime()->format('U')))) > 0) 
		{
			//echo intval(date("W"));
			//echo intval(date('W', $jokes[date]->sec));
			//echo strtotime('monday this week') . '<br>';
			//echo strtotime(date('d-M-y',$jokes[date]->toDateTime()->format('U'))) . '<br>';

			if ((strtotime('monday this week') - strtotime(date('d-M-y',$jokes[date]->toDateTime()->format('U')))) > ($dayupdate*86400))
			{
				$lastweekranking[] = $jokes[transponder];
			} elseif ((strtotime('monday this week') - strtotime(date('d-M-y',$jokes[date]->toDateTime()->format('U')))) > 0) {
				$weekranking[] = $jokes[transponder];
				if($jokes[score]<0){
					$weekscore[] = 0;
				}
				else {
					$weekscore[] = $jokes[score];
				}
				$weekdate[] = date('dmY', $jokes[date]->toDateTime()->format('U'));
			}
			
			if (((strtotime('first monday of this month')- strtotime(date('d-M-y',$jokes[date]->toDateTime()->format('U')))) < ($dayupdate*86400/7*date('t', strtotime('last month')))) && ((strtotime('first monday of this month')- strtotime(date('d-M-y',$jokes[date]->toDateTime()->format('U')))) > 0))
			{
				$monthranking[] = $jokes[transponder];
				if($jokes[score]<0){
					$monthscore[] = 0;
				}
				else{
				$monthscore[] = $jokes[score];
				}
				$monthdate[] = date('dmY', $jokes[date]->toDateTime()->format('U'));
			}

			$ranking[] = $jokes[transponder];
			if($jokes[score]<0){
				$score[] = 0;
			}
			else{
			$score[] = $jokes[score];
			}
			$date[] = date('d-M-Y', $jokes[date]->toDateTime()->format('U'));
			$dateurl[] = date('dmY', $jokes[date]->toDateTime()->format('U'));
		
		}
		}

		$rankingindex= array_values($ranking);
		$ranking = array_values(array_unique($ranking, SORT_NUMERIC));
		$lastweekranking = array_values(array_unique($lastweekranking, SORT_NUMERIC));
		$weekrankingindex = array_values($weekranking);
		$weekranking = array_values(array_unique($weekranking, SORT_NUMERIC));
		$monthrankingindex = array_values($monthranking);
		$monthranking = array_values(array_unique($monthranking, SORT_NUMERIC));
	
		//print_r($monthrankingindex);		
		//print_r($monthranking);
		//print_r(date('t', strtotime('last month')));
		//print_r($lastweekranking);
		//print_r($score);
		
		if ($actualseason == TRUE) {	

		echo '<div style = "text-align: right;"><b>Prochaine mise à jour: </b><p">' . date('d M Y', strtotime('next monday')) . '</p></div>';	

		echo '	<div ><h2><span class="label label-default" style = "background-color: #666;">Top 5</span>   Semaine   ' . (date('W')-1) . '</h2><table class="myResults_livetiming_table">
    			<thead>
     			<tr>
       			<th style="width:25%;">Rank</th>
      			<th>Transponder</th>
       			<th>Score</th>
				<th style="width: 30%;"></th>
      			</tr>
    			</thead>
    			<tbody>';

		if (count($weekranking) >= 5)
		{
			$count = 5;
		} else {
			$count = count($weekranking);
		}
		
		for ($i = 0; $i < $count; $i++)
		{
		$transponder = $weekranking[$i];
		$cursor = $statuscollection->findOne(array("transponder" => $transponder));
		$scoredata = $weekscore[array_search($weekranking[$i], $weekrankingindex)];
		
		if($scoredata > 9010)
		{
			$scoredatabar = number_format(abs($scoredata - 9000) / 1000 *100,2);
			$scoredatacolor = number_format(abs($scoredata - 9000) / 1000 *255,0);
		}
		else { 
			$scoredatabar = 1;
			$scoredatacolor = number_format(0 * 255,0);
		}	
		
		if ($cursor[name] != null) {
			$transponder = $cursor[name];
		}


		if ($i == 0)
		{
		$class = '';
		} else {
		$class = '';
		}

		echo '<tr class = "' . $class . '"><td style = "vertical-align: middle;">' . ($i + 1) . '</td><td style = "vertical-align: middle;"><a style="color: white;" href=/myresults/?yeardate=' . $yeardate . '&transponder=' . $weekranking[$i] . '&daydate=' . $weekdate[array_search($weekranking[$i], $weekrankingindex)] . '>' . $transponder  . '</td><td>' . number_format($scoredata,1, ',',"'") . '</td><td><span style="height: 15px;display:block; width:'. $scoredatabar . '%; background-color:rgb(50,180,' . $scoredatacolor . ');"></span></td></tr>';
		}

		echo   '</tr>
   			</tbody>
 			</table>
			</div>';

		echo '	<div class="col-md-6"><h2><span class="label label-default" style = "background-color: #666;">Top 5</span>   ' . $month[date('n', strtotime('last month'))] . '</h2><table class="myResults_livetiming_table">
    			<thead>
     			<tr>
       			<th style="width:25%;">Rank</th>
      			<th>Transponder</th>
       			<th>Score</th>
				<th style="width: 30%;"></th>
      			</tr>
    			</thead>
    			<tbody>';

		if (count($monthranking) >= 5)
		{
			$count = 5;
		} else {
			$count = count($monthranking);
		}		

		for ($i = 0; $i < $count; $i++)
		{
		$transponder = $monthranking[$i];
		$cursor = $statuscollection->findOne(array("transponder" => $transponder));
		$scoredata = $monthscore[array_search($monthranking[$i], $monthrankingindex)];
		
		if($scoredata > 9010)
		{
			$scoredatabar = number_format(abs($scoredata - 9000) / 1000 *100,2);
			$scoredatacolor = number_format(abs($scoredata - 9000) / 1000 *255,0);
		}
		else { 
			$scoredatabar = 1;
			$scoredatacolor = number_format(0 * 255,0);
		}

		if ($cursor[name] != null) {
			$transponder = $cursor[name];
		}


		if ($i == 0)
		{
		$class = '';
		} else {
		$class = '';
		}

		if ($cursor[name] != null) {
			$transponder = $cursor[name];
		}
		
		if ($i == 0)
		{
		$class = '';
		} else {
		$class = '';
		}
		echo '<tr class = "' . $class . '"><td style = "vertical-align: middle;">' . ($i + 1) . '</td><td style = "vertical-align: middle;"><a style="color: white;" href=/myresults/?yeardate=' . $yeardate . '&transponder=' . $monthranking[$i] . '&daydate=' . $monthdate[array_search($monthranking[$i], $monthrankingindex)] . '>' . $transponder  . '</td><td>' . number_format($scoredata,1, ',',"'") . '</td><td><span style="height: 15px;display:block; width:'. $scoredatabar . '%; background-color:rgb(50,180,' . $scoredatacolor . ');"></span></td></tr>';
		}

		echo   '</tr>
   			</tbody>
 			</table>
			</div>';

		}
		
		echo '	<div><h2>Classement général</h2><table class="myResults_livetiming_table">
    			<thead>
     			<tr>
       			<th style="width:25%;">Rank</th>
      			<th>Transponder</th>
				<th>Score</th>
				<th style="width: 30%;"></th>
      			</tr>
    			</thead>
    			<tbody>';		
		
		for ($i = 0; $i < count($ranking); $i++)
		{
			$transponder = $ranking[$i];
			$scoredata = $score[array_search($ranking[$i], $rankingindex)];
			$percentdata = number_format(($scoredata-9000)/10,3);
			
			if($scoredata > 9010)
			{
				$scoredatabar = number_format(abs($scoredata - 9000) / 1000 *100,2);
				$scoredatacolor = number_format(abs($scoredata - 9000) / 1000 *255,0);
			}
			else { 
				$scoredatabar = 1;
				$scoredatacolor = number_format(0 * 255,0);
			}

			$cursor = $statuscollection->findOne(array("transponder" => $transponder));
			if ($cursor[name] != null) {
			$transponder = $cursor[name];
			}
			
			$pos = $i;

			if ((array_search($ranking[$i], $lastweekranking) - $pos) > 0)
			{	
				$diff = '   <span style = "color: #5cb85c; margin-left: 20px; margin-right: 10px">↑</span> +' . (array_search($ranking[$i], $lastweekranking) - $pos) ;
			} elseif ((array_search($ranking[$i], $lastweekranking) - $pos) < 0)
			{
				$diff = '   <span style = "color: #d9534f; margin-left: 20px; margin-right: 10px">↓</span>' . (array_search($ranking[$i], $lastweekranking)- $pos) ;
			} else {
				$diff = '   <span style = "color: #f0ad4e; margin-left: 20px; margin-right: 10px">=</span> +/-0';
			}
			
			if ($i == 0)
			{
			$class = '';
			} else {
			$class = '';
			}
			if ($scoredata > 9000)
			{
			$scorewidth = $percentdata;
			} else {
			$scorewidth = 1;
			}

			

			//echo '<tr class = "' . $class . '"><td style = "vertical-align: middle;">' . ($i + 1) . $diff . '</td><td style = "vertical-align: middle;"><a style="text-decoration: underline;" href=/myresults/?yeardate=' . $yeardate . '&transponder=' . $ranking[$i] . '&daydate=' . $dateurl[array_search($ranking[$i], $rankingindex)] . '>' . $transponder . '</a></td><td><div class="progress" style = "margin-bottom: 5px; margin-top: 5px;"><div class="progress-bar" role="progressbar" style="background-color: rgba(' . $colorvaluered . ', 150,' . $colorvalueblue . ',.7); width:' . $scorewidth . '%;"><span class="progress-type">' . number_format($scoredata,3, ',',"'") . ' (' . $date[array_search($ranking[$i], $rankingindex)] . ')' . '</span></div></div></td></tr>';
			echo '<tr class = "' . $class . '"><td style = "vertical-align: middle;">' . ($i + 1) . $diff . '</td><td style = "vertical-align: middle;"><a style="color: white;" href=/myresults/?yeardate=' . $yeardate . '&transponder=' . $ranking[$i] . '&daydate=' . $dateurl[array_search($ranking[$i], $rankingindex)] . '>' . $transponder . '</a></td><td>' . number_format($scoredata,1, ',',"'") . '</td><td><span style="height: 15px;display:block; width:'. $scoredatabar . '%; background-color:rgb(50,180,' . $scoredatacolor . ');"></span></td></tr>';
		}
	
		echo   '</tr>
   			</tbody>
 			</table>
			</div>';

		//Deconnection                
		//$mongo->close();
		}

	}
	

?>
