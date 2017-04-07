<?php
/***
Console function
LiveTiming
*/
?>

<script type="text/javascript">

function barChartPlotter(e) {
  var ctx = e.drawingContext;
  var points = e.points;
  var y_bottom = e.dygraph.toDomYCoord(0);  // see <a href="http://dygraphs.com/jsdoc/symbols/Dygraph.html#toDomYCoord">jsdoc</a>

  // This should really be based on the minimum gap
  var bar_width = 2/3 * (points[1].canvasx - points[0].canvasx);
  ctx.fillStyle = e.color;  // a lighter shade might be more aesthetically pleasing
 	

  // Do the actual plotting.
  for (var i = 0; i < points.length; i++) {
    var p = points[i];
    var center_x = p.canvasx;  // center of the bar

    ctx.fillRect(center_x - bar_width / 2, p.canvasy, bar_width, y_bottom - p.canvasy);
    ctx.strokeRect(center_x - bar_width / 2, p.canvasy, bar_width, y_bottom - p.canvasy);
  }
}

var makeGraph = function(data, start, stop, div) {

document.getElementById("div"),

g = new Dygraph(
    
// containing div
    div,

    // Data
    data,
{           stackedGraph: false,
	    dateWindow: [start, stop],
 	    animatedZooms: true,
            ylabel: 'Time [s]',
            colors: ["#f0ad4e"],
            plotter: barChartPlotter,
            legend: 'follow',
	    axes: {
    x: {
        axisLabelFormatter: function(num, gran, opts, g) {
            if (num == Math.floor(num)) {
                return Dygraph.numberAxisLabelFormatter(num, gran, opts, g);
            } else {
                return '';
            }
	},
        axisTickSize: 1,
	drawGrid: false
        }	
   }
	 		
});
};

var makeGraphresume = function(data, start, stop, div) {

document.getElementById("div"),

g = new Dygraph(
    
// containing div
    div,

    // Data
    data,
{           stackedGraph: false,
	    fillGraph: true,
            title: 'Average/Bestlap by session',
	    dateWindow: [start, stop],
 	    animatedZooms: true,
            ylabel: 'Time [s]',
	    
            colors: ["#bad8a0","#f0ad4e"],
	    axes: {
    x: {
        axisLabelFormatter: function(num, gran, opts, g) {
            if (num == Math.floor(num)  && num != 0) {
                return 'Session' + Dygraph.numberAxisLabelFormatter(num, gran, opts, g);
            } else {
                return '';
            }
	},
        axisTickSize: 1,
	drawGrid: false
        }	
   },	
	 legend: 'follow'		
});
};
</script>

<?php

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

		//Display results
		$date = $_GET['daydate'];
		$transponder = $_GET['transponder'];
		$lastntime = 0;

		$db = $mongo->selectDB($dbactivename);
		$activetrans = "T" . strval($transponder) . "_" . $date;
		$activetrans_collection = $db->selectCollection("$activetrans");

		date_default_timezone_set('Europe/Zurich');

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
		
		$transponderdata = $activetrans_collection->find()->sort(array('_id'=>1));
		foreach ($transponderdata as $time) {
			$lapdata[$i] = (intval($time["RTC_Time"]) - $lastnlap) / 1000000 ;
			$RTCtime[$i] = date('H:i:s' ,intval($time["RTC_Time"] / 1000000));
			//echo $lapdata[$i];
			$lastnlap = intval($time["RTC_Time"]);
			//echo $time["RTC_Time"] . ":";


			if ($lapdata[$i] > 120 or $lapdata[$i] <= 0)  {
			$lapdata[$i] = 0;
			$totaltimelast = 0;
			$bestlapinit = 120;
			$j = 0;
			$sessioncounter++;
			$sessionpos[$sessioncounter] = $i;		
			} else {

			if ($lapdata[$i] < $bestlapinit) {

			$bestlap[$sessioncounter] = $lapdata[$i];
			$bestlapinit = $lapdata[$i];
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
		$size = count($lapdata);
		$sessionpos[$sessioncounter+1] = $size;
		$sizesession = $sessioncounter;
		if ($size > 1) {

		//Timetable
		echo '<div class="row"><div id="graphdiv0" class = "dypgraph_big"></div></div><br><hr>';
                $arraytographresume = array_slice($arraytographresume,1,$sizesession);
 
		?>
 		<script>
 		var data = <?php echo json_encode($arraytographresume); ?>;
		var start = 0.8;
		var stop = <?php echo ($sizesession+0.2); ?>;
 		makeGraphresume(data, start, stop, graphdiv0);
 		</script>
 		<?php

		echo '<div class="row"><div class="col-md-5"><H3><span class="label label-danger">Session ' . ($sessioncounter ) . '</span></H3><br><div class="alert alert-info">Average : ' . number_format($average[$sessioncounter],3) . '<br /><b>Bestlap :  ' . number_format($bestlap[$sessioncounter],3) . '</b></div><br><div id="graphdiv' . $sessioncounter . '" class = "dypgraph_small"></div><br></div><div class="col-md-7"><table class="myResults_livetiming_table"><tr><th>' . 'lap' . '</th><th>' . 'laptime' . '</th><th>' . 'total' . '</th><th>' . 'time' . '</th></tr>';

		for ($i=$size-1; $i > 0; $i--) {
		
		if ($lapdata[$i] == 0) {

		$sessioncounter--;

		echo '<tr><td>' . $position[$i] . '</td><td>' . number_format($lapdata[$i],3) . '</td><td>' . number_format($totaltimesession[$i],3) . '</td><td>' . $RTCtime[$i] . '</td></tr></table></div></div><hr><br><div class="row"><div class="col-md-5"><H3><span class="label label-danger">Session '. ($sessioncounter ) . '</span></H3><br><div class="alert alert-info">Average : ' . number_format($average[$sessioncounter],3) . '<br /><b>Bestlap :  ' . number_format($bestlap[$sessioncounter],3) . '</b></div><br><div id="graphdiv' . $sessioncounter . '" class = "dypgraph_small"></div><br></div><div class="col-md-7"><table class="myResults_livetiming_table"><tr><th>' . 'lap' . '</th><th>' . 'laptime' . '</th><th>' . 'total' . '</th><th>' . 'time' . '</th></tr>';

		} else {
   		
		echo '<tr><td>' . $position[$i] . '</td><td>' . number_format($lapdata[$i],3) . '</td><td>' . number_format($totaltimesession[$i],3) . '</td><td>' . $RTCtime[$i] . '</td></tr>';
		
		}		
		}		

		echo '<tr><td>' . 0 . '</td><td>' . number_format(0,3) . '</td><td>' . number_format(0,3) . '</td><td>' . $RTCtime[0] . '</td></tr></table></div></div>';

		}

		$sliced_array[] = null;
		for ($i = 1; $i < ($sizesession+1); $i++) {
		
  		$sliced_array = array_slice($arraytographsession, $sessionpos[$i], $sessionpos[$i+1]-$sessionpos[$i]);
		if (count($sliced_array) > 1) {
		
		?>
		<script>
 		var data = <?php echo json_encode($sliced_array); ?>;
		var start = 0;
		var stop = <?php echo count($sliced_array); ?>;
		div = <?php echo 'graphdiv' . ($i); ?>;
 		makeGraph(data, start, stop, div);
 		</script>
 		<?php
		}
		}

		//print_r($data);
		//Deconnection                
		$mongo->close();
		}
	}
?>