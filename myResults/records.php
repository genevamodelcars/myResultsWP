<?php
/***
records function
Track records
*/

$xml=simplexml_load_file("http://www.genevamodelcars.ch/wp-content/plugins/myResults/layout/records.xml");

if($xml===FALSE) {
			exit('Echec lors de l\'ouverture du fichier config.');
	} else {
		
		$i = 0;
		
		$printoutstart = '<div class="slideshow-container">';
		$printoutend = '<a class="prev" onclick="plusSlides(-1)">&#10094;</a>
						<a class="next" onclick="plusSlides(1)">&#10095;</a>
						</div>
						<br>
						<div style="text-align:center">';
		
		foreach ($xml->layout as $layout) {
			$i++;
			$printoutstart .= '<div class="mySlides fade">
			<div class="tracktitle"><h2>Layout: ' . $layout->trackname . '</h2></div>
			<div class="recordcontainer">
			<div class="recorddata">
				<div class="recordtitle">Horaire</div>
				<div class="recordtime">Bestlap: '. $layout->clockwise->timeval . '</div>
				<div class="recorddate">Date: '. $layout->clockwise->dateval . '</div>
				<div class="recorddriver">Driver: '. $layout->clockwise->driver . '</div>
				<div class="recordcat">Category: '. $layout->clockwise->catergory . '</div>
			</div>
			<div class="recorddata">
				<div class="recordtitle">Anti-horaire</div>
				<div class="recordtime">Bestlap: '. $layout->counterclockwise->timeval . '</div>
				<div class="recorddate">Date: '. $layout->counterclockwise->dateval . '</div>
				<div class="recorddriver">Driver: '. $layout->counterclockwise->driver . '</div>
				<div class="recordcat">Category: '. $layout->counterclockwise->catergory . '</div>
			</div>
			</div>
			<img src="http://www.genevamodelcars.ch/wp-content/plugins/myResults/layout/'. $layout->img . '" style="width:100%">
			</div>';
			
			$printoutend .= '<span class="dot" onclick="currentSlide(' . $i . ')"></span>';
			
		}
		
		$printoutend .= '</div>';
		
		echo $printoutstart . $printoutend;
	
	}
	
?>

<style>

.recordcontainer {
	margin: 0;
    text-align: center;
}

.recorddata{
	float: left;
	width: 50%;
	margin-bottom: 50px;
}

.recordtitle {
	text-transform: uppercase;
	font-weight: 700;
}
	
/* Slideshow container */
.slideshow-container {
  max-width: 1000px;
  position: relative;
  margin: auto;
}

/* Hide the images by default */
.mySlides {
  display: none;
}

/* Next & previous buttons */
.prev, .next {
  cursor: pointer;
  position: absolute;
  top: 240px;
  width: auto;
  margin-top: -22px;
  padding: 16px;
  color: white;
  font-weight: bold;
  font-size: 12px;
  transition: 0.6s ease;
  border-radius: 0 3px 3px 0;
  user-select: none;
  background-color: rgba(0,0,0,0.8);
}

/* Position the "next button" to the right */
.next {
  right: 0;
  border-radius: 3px;
}

/* Position the "next button" to the right */
.prev {
  left: 0;
  border-radius: 3px;
}

/* On hover, add a black background color with a little bit see-through */
.prev:hover, .next:hover {
}

/* Caption text */
.text {
  color: black;
  font-size: 15px;
  padding: 8px 12px;
  position: absolute;
  bottom: -15px;
  width: 100%;
  text-align: center;
}

/* Number text (1/3 etc) */
.numbertext {
  color: #f2f2f2;
  font-size: 12px;
  padding: 8px 12px;
  position: absolute;
  top: 0;
}

/* The dots/bullets/indicators */
.dot {
  cursor: pointer;
  height: 15px;
  width: 15px;
  margin: 0 2px;
  background-color: #bbb;
  border-radius: 50%;
  display: inline-block;
  transition: background-color 0.6s ease;
}

.active, .dot:hover {
  background-color: #717171;
}

</style>

<script>
	var slideIndex = 1;
	showSlides(slideIndex);

	// Next/previous controls
	function plusSlides(n) {
	  showSlides(slideIndex += n);
	}

	// Thumbnail image controls
	function currentSlide(n) {
	  showSlides(slideIndex = n);
	}

	function showSlides(n) {
	  var i;
	  var slides = document.getElementsByClassName("mySlides");
	  var dots = document.getElementsByClassName("dot");
	  if (n > slides.length) {slideIndex = 1}
	  if (n < 1) {slideIndex = slides.length}
	  for (i = 0; i < slides.length; i++) {
		  slides[i].style.display = "none";
	  }
	  for (i = 0; i < dots.length; i++) {
		  dots[i].className = dots[i].className.replace(" active", "");
	  }
	  slides[slideIndex-1].style.display = "block";
	  dots[slideIndex-1].className += " active";
	}
</script>