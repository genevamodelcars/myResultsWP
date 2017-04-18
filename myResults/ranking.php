<?php
/***
Ranking function
Call the last pass entries
*/
	//Variables	
	$xml=simplexml_load_file("http://www.genevamodelcars.ch/wp-content/plugins/myResults/config.xml");
	$connected = true;
	$dayupdate = 7;
	
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
		$mongo = new MongoClient($dbservername);
		}
		catch ( MongoConnectionException $e ) 
		{
		$connected = false;
  	  	echo 'Echec lors de l\'ouverture de la base de donnée.';
  	  	exit();
		}

	if ($connected == true){
		//echo 'Hello';

		$db = $mongo->selectDB($dbactivename);
		$rankingcollection = $db->selectCollection("ranking");
		$cursor = $rankingcollection->find()->sort(array('score'=>-1));

		$statuscollection = $db->selectCollection("active");

		foreach($cursor as $jokes) {
		if ((strtotime('monday')-strtotime(date('d-M-y', $jokes[date]->sec))) > 0)
		{
			//echo intval(date("W"));
			//echo intval(date('W', $jokes[date]->sec));

			if ((strtotime('monday this week')-strtotime(date('d-M-y', $jokes[date]->sec))) > ($dayupdate*86400))
			{
				$lastweekranking[] = $jokes[transponder];
			} else {
				$weekranking[] = $jokes[transponder];
				$weekscore[] = $jokes[score];
				$weekdate[] = date('dmY', $jokes[date]->sec);
			}
			
			if ((strtotime('first monday this month')- strtotime(date('d-M-y', $jokes[date]->sec))) > ($dayupdate*86400*date('t')))
			{
				$monthranking[] = $jokes[transponder];
				$monthscore[] = $jokes[score];
				$monthdate[] = date('dmY', $jokes[date]->sec);
			}

			$ranking[] = $jokes[transponder];
			$score[] = $jokes[score];
			$date[] = date('d-M-Y', $jokes[date]->sec);
			$dateurl[] = date('dmY', $jokes[date]->sec);
		
		}
		}

		$rankingindex= array_values($ranking);
		$ranking = array_values(array_unique($ranking, SORT_NUMERIC));
		$lastweekranking = array_values(array_unique($lastweekranking, SORT_NUMERIC));
		$weekrankingindex = array_values($weekranking);
		$weekranking = array_values(array_unique($weekranking, SORT_NUMERIC));
		$monthrankingindex = array_values($monthranking);
		$monthranking = array_values(array_unique($monthranking, SORT_NUMERIC));
	
		//print_r($ranking);		
		//print_r($rankingindex);
		//print_r($lastweekranking);
		//print_r($score);
		
		echo '<div style = "text-align: right;"><b>Prochaine mise à jour: </b><span class="label label-success">' . date('d M Y', strtotime('next monday')) . '</span></div>';		

		echo '	<div class="col-md-6"><h2><span class="label label-default" style = "background-color: #777;">Top 5</span>   Semaine   ' . (date('W')-1) . '</h2><table class="table">
    			<thead>
     			<tr>
       			<th class="col-md-1">Rank</th>
      			<th class="col-md-3">Transponder</th>
       			<th class="col-md-2">Score</th>
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

		if ($cursor[name] != null) {
			$transponder = $cursor[name];
		}


		if ($i == 0)
		{
		$class = '';
		} else {
		$class = '';
		}

		echo '<tr class = "' . $class . '"><td style = "vertical-align: middle;">' . ($i + 1) . '</td><td style = "vertical-align: middle;"><a style="text-decoration: underline;" href=/myresults/?transponder=' . $weekranking[$i] . '&daydate=' . $weekdate[array_search($weekranking[$i], $weekrankingindex)] . '>' . $transponder  . '</td><td>' . number_format($scoredata,3, ',',"'") . '</td></tr>';
		}

		echo   '</tr>
   			</tbody>
 			</table>
			</div>';

		echo '	<div class="col-md-6"><h2><span class="label label-default" style = "background-color: #777;">Top 5</span>   ' . $month[date('n', strtotime('last month'))] . '</h2><table class="table">
    			<thead>
     			<tr>
       			<th class="col-md-1">Rank</th>
      			<th class="col-md-3">Transponder</th>
       			<th class="col-md-2">Score</th>
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

		if ($cursor[name] != null) {
			$transponder = $cursor[name];
		}
		
		if ($i == 0)
		{
		$class = '';
		} else {
		$class = '';
		}
		echo '<tr class = "' . $class . '"><td style = "vertical-align: middle;">' . ($i + 1) . '</td><td style = "vertical-align: middle;"><a style="text-decoration: underline;" href=/myresults/?transponder=' . $monthranking[$i] . '&daydate=' . $monthdate[array_search($monthranking[$i], $monthrankingindex)] . '>' . $transponder  . '</td><td>' . number_format($scoredata,3, ',',"'") . '</td></tr>';
		}

		echo   '</tr>
   			</tbody>
 			</table>
			</div>';
		
		echo '	<div class="col-md-12"><h2>Classement général</h2><table class="table">
    			<thead>
     			<tr>
       			<th class="col-md-1">Rank</th>
      			<th class="col-md-3">Transponder</th>
			<th class="col-md-8">Score</th>
      			</tr>
    			</thead>
    			<tbody>';		
		
		for ($i = 0; $i < count($ranking); $i++)
		{
			$transponder = $ranking[$i];
			$scoredata = $score[array_search($ranking[$i], $rankingindex)];
			$percentdata = number_format(($scoredata-6000)/40,3);
			$colorvaluered = number_format(($scoredata-8000)/2000*20,0);
			$colorvalueblue = number_format((1-(($scoredata-8000)/2000))*100+155,0);

			$cursor = $statuscollection->findOne(array("transponder" => $transponder));
			if ($cursor[name] != null) {
			$transponder = $cursor[name];
			}
			
			$pos = $i;

			if ((array_search($ranking[$i], $lastweekranking) - $pos) > 0)
			{	
				$diff = '   <span class="glyphicon glyphicon-chevron-up" style = "color: #5cb85c; margin-left: 20px; font-size: larger; margin-right: 10px"></span> +' . (array_search($ranking[$i], $lastweekranking) - $pos) ;
			} elseif ((array_search($ranking[$i], $lastweekranking) - $pos) < 0)
			{
				$diff = '   <span class="glyphicon glyphicon-chevron-down" style = "color: #d9534f; margin-left: 20px; font-size: larger; margin-right: 10px"></span>' . (array_search($ranking[$i], $lastweekranking)- $pos) ;
			} else {
				$diff = '   <span class="glyphicon glyphicon-minus" style = "color: #f0ad4e; margin-left: 20px; font-size: larger; margin-right: 10px"></span> +/-0';
			}
			
			if ($i == 0)
			{
			$class = '';
			} else {
			$class = '';
			}
			if ($scoredata > 6000)
			{
			$scorewidth = $percentdata;
			} else {
			$scorewidth = 1;
			}

			

			echo '<tr class = "' . $class . '"><td style = "vertical-align: middle;">' . ($i + 1) . $diff . '</td><td style = "vertical-align: middle;"><a style="text-decoration: underline;" href=/myresults/?transponder=' . $ranking[$i] . '&daydate=' . $dateurl[array_search($ranking[$i], $rankingindex)] . '>' . $transponder . '</a></td><td><div class="progress" style = "margin-bottom: 5px; margin-top: 5px;"><div class="progress-bar" role="progressbar" style="background-color: rgba(' . $colorvaluered . ', 150,' . $colorvalueblue . ',.7); width:' . $scorewidth . '%;"><span class="progress-type">' . number_format($scoredata,3, ',',"'") . ' (' . $date[array_search($ranking[$i], $rankingindex)] . ')' . '</span></div></div></td></tr>';
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