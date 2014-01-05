<?php
// get the current date
$dateToday=date('Y-m-d'); // today's date
$dateBase=strtotime(date('Y-m-d')); // today's date in milliseconds for the next two dates
$dateNextMonth=date('Y-m-d',strtotime('+1 month',$dateBase));
$dateNextWeek=date('Y-m-d',strtotime('+1 week',$dateBase));
$dateLastMonth=date('Y-m-d',strtotime('-1 month',$dateBase));
$dateLastWeek=date('Y-m-d',strtotime('-1 week',$dateBase));

// load dates from url
$prefRating=$_GET['rating'];
$dateStart=$_GET['start'];
$dateEnd=$_GET['end'];
if(isset($dateStart)){
	$fromThisDate=$dateStart;
}
if(isset($dateEnd)){
	$toThisDate=$dateEnd;
}
if(empty($fromThisDate) || empty($toThisDate)){
//	$fromThisDate=$dateToday;
//	$toThisDate=$dateNextMonth;
	$fromThisDate=$dateLastWeek;
	$toThisDate=$dateToday;
}
$filter="Instant/AvailableFrom gt datetime'$fromThisDate' and Instant/AvailableFrom lt datetime'$toThisDate' and (Type ne 'Episode' and Type ne 'Season') and (Rating ne 'TV-MA' and Rating ne 'TV-PG')";
if(isset($prefRating)){
	$filter.=" and Instant/Rating eq '".$prefRating."'";
}

$netflix_xml='http://odata.netflix.com/Catalog/Titles?$filter='.$filter; // load the netflix xml
//echo $netflix_xml;
$streamingTitles=simplexml_load_file($netflix_xml);
$totalmovies=sizeOf($streamingTitles->entry);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head>
<title>Johnny Leche's Spiff Movies - Coming Soon To Netflix Instant Streaming</title>
<link href="http://www.dohnetwork.com/doh.ico" rel="shortcut icon" />
<meta name="description" content="Johnny Leche's Spiff Movies -- Find out what movies are coming to Netflix Instant Streaming in the next month." />
<meta name="keywords" content="netflix,instant,streaming,movies,coming soon" />
<link rel="canonical" href="http://movies.dohnetwork.com/netflix.php" />
<meta charset="UTF-8" />
<script src="http://www.dohnetwork.com/javascripts/global.js" type="text/javascript"></script>
<script src="http://www.dohnetwork.com/javascripts/homepage.js" type="text/javascript"></script>
<link href="netflix.css" rel="stylesheet" type="text/css">
</head><body>
<?php include_once("global/analyticstracking.php") ?>
<!-- START CHROME -->
<div style="z-index:100;"><?php include_once('/WWW/website/chrome/index.php'); ?></div>

<?php // set up dates in human readable format for display purposes
$dateDisplayFrom=new DateTime($fromThisDate);
$dateDisplayTo=new DateTime($toThisDate);
// show number of titles released in your time frame
if($toThisDate<=$dateToday){
	echo '<h1>'.$totalmovies.' new ';
if(isset($prefRating)){
	if($prefRating=="NR" || $prefRating=="UR"){$prefRating="unrated";}
	echo 'rated '.$prefRating.' ';
}
	echo 'titles were released on <a href="http://movies.netflix.com/" target="_blank" title="Check out what is currently available on Netflix Instant Streaming!">Netflix Instant Streaming</a> between '.$dateDisplayFrom->format('F d, Y').' and '.$dateDisplayTo->format('F d, Y');
	if($totalmovies==0){
		echo '<br>Expand your search to a longer time span';
	}
	echo '.</h1>';
} else if($fromThisDate>$dateToday){
	echo '<h1>'.$totalmovies.' new ';
if(isset($prefRating)){
// change nr and ur to unrated for clarity
	if($prefRating=="NR" || $prefRating=="UR"){$prefRating="unrated";}
	echo 'rated '.$prefRating.' ';
}
	echo 'titles are scheduled to be released on <a href="http://movies.netflix.com/" target="_blank" title="Check out what is currently available on Netflix Instant Streaming!">Netflix Instant Streaming</a> between '.$dateDisplayFrom->format('F d, Y').' and '.$dateDisplayTo->format('F d, Y').'.</h1>';
} else {
	echo '<h1>'.$totalmovies.' new ';
if(isset($prefRating)){
	if($prefRating=="NR" || $prefRating=="UR"){$prefRating="unrated";}
	echo 'rated '.$prefRating.' ';
}
	echo 'titles have been or are scheduled to be released on <a href="http://movies.netflix.com/" target="_blank" title="Check out what is currently available on Netflix Instant Streaming!">Netflix Instant Streaming</a> between '.$dateDisplayFrom->format('F d, Y').' and '.$dateDisplayTo->format('F d, Y').'.</h1>';
}
// time span nav
echo '<h2>
	<div class="nav-timespan">
		<div><a href="http://netflix.dohnetwork.com/?start='.$dateLastMonth.'&end='.$dateToday.'" target="_parent">Last 30 Days</a></div>
		<div><a href="http://netflix.dohnetwork.com/?start='.$dateLastWeek.'&end='.$dateToday.'" target="_parent">Last 7 Days</a></div>
		<div><a href="http://netflix.dohnetwork.com/?start='.$dateToday.'&end='.$dateNextWeek.'" target="_parent">Next 7 Days</a></div>
		<div><a href="http://netflix.dohnetwork.com/?start='.$dateToday.'&end='.$dateNextMonth.'" target="_parent">Next 30 Days</a></div>
	</div>
</h2><div class="clearboth"></div>';

$currentURL="http://netflix.dohnetwork.com/?start=".$fromThisDate."&end=".$toThisDate;
// ratings nav
echo '<h2>
	<div class="nav-ratings">
		<div><a href="'.$currentURL.'&rating=G" target="_parent">G</a></div>
		<div><a href="'.$currentURL.'&rating=PG" target="_parent">PG</a></div>
		<div><a href="'.$currentURL.'&rating=PG-13" target="_parent">PG-13</a></div>
		<div><a href="'.$currentURL.'&rating=PG-14" target="_parent">PG-14</a></div>
		<div><a href="'.$currentURL.'&rating=PG-T" target="_parent">PG-T</a></div>
		<div><a href="'.$currentURL.'&rating=R" target="_parent">R</a></div>
		<div><a href="'.$currentURL.'&rating=NR" target="_parent">NR</a></div>
		<div><a href="'.$currentURL.'&rating=UR" target="_parent">UR</a></div>
		<div><a href="'.$currentURL.'" target="_parent">All</a></div>
	</div>
</h2>';
?>

<div class="movies">
<?php
// loop through all movies and get info from netflix api
for($counter=0;$counter<$totalmovies;$counter++){
	$title=$streamingTitles->entry[$counter]->title;
	$Url=$streamingTitles->entry[$counter]->children('m',true)->properties->children('d',true)->Url;
	$ReleaseYear=$streamingTitles->entry[$counter]->children('m',true)->properties->children('d',true)->ReleaseYear;
	$Rating=$streamingTitles->entry[$counter]->children('m',true)->properties->children('d',true)->Instant->children('d',true)->Rating;
	$summary=$streamingTitles->entry[$counter]->summary;
	$Synopsis=$streamingTitles->entry[$counter]->children('m',true)->properties->children('d',true)->Synopsis;
	$ShortSynopsis=$streamingTitles->entry[$counter]->children('m',true)->properties->children('d',true)->ShortSynopsis;
	$AverageRating=$streamingTitles->entry[$counter]->children('m',true)->properties->children('d',true)->AverageRating;
	$AvailableFrom=$streamingTitles->entry[$counter]->children('m',true)->properties->children('d',true)->Instant->children('d',true)->AvailableFrom;
	$dateConvert=explode('T',$AvailableFrom);
	$newDate = date("F d, Y", strtotime($dateConvert[0]));
	$SmallUrl=$streamingTitles->entry[$counter]->children('m',true)->properties->children('d',true)->BoxArt->children('d',true)->SmallUrl;
	$MediumUrl=$streamingTitles->entry[$counter]->children('m',true)->properties->children('d',true)->BoxArt->children('d',true)->MediumUrl;
	$LargeUrl=$streamingTitles->entry[$counter]->children('m',true)->properties->children('d',true)->BoxArt->children('d',true)->LargeUrl;
	$Runtime=$streamingTitles->entry[$counter]->children('m',true)->properties->children('d',true)->Runtime;
	$HighDefinitionAvailable=$streamingTitles->entry[$counter]->children('m',true)->properties->children('d',true)->Instant->children('d',true)->HighDefinitionAvailable;
/*
	echo '<br>id: '.$streamingTitles->entry[$counter]->id;
	echo '<br>Name: '.$streamingTitles->entry[$counter]->children('m',true)->properties->children('d',true)->Name;
	echo '<br>BoxArt: '.$streamingTitles->entry[$counter]->children('m',true)->properties->children('d',true)->BoxArt->attributes('m', true)->type;
	echo '<br>Available: '.$streamingTitles->entry[$counter]->children('m',true)->properties->children('d',true)->Instant->children('d',true)->Available;
	echo '<br>AvailableTo: '.$streamingTitles->entry[$counter]->children('m',true)->properties->children('d',true)->Instant->children('d',true)->AvailableTo;
*/
	$stars=($AverageRating*100/5);
	echo '	<div class="movie">
		<div class="poster"><a href="'.$Url.'" target="_blank" title="Click here to watch '.$title.' on Netflix!"><img src="'.$MediumUrl.'" /></a></div>
		<div class="title"><strong><a href="'.$Url.'" target="_blank" title="Click here to watch '.$title.' on Netflix!">'.$title.'</a></strong> ('.$ReleaseYear.')
			<div class="starcontainer">
				<div class="stars" style="width:'.$stars.'%;"></div>
			</div>
		</div>
		<div class="AvailableFrom">Available '.$newDate.'</div>
		<div class="Synopsis">'.$Synopsis.'</div>
		<div class="info">';
//if($Rating=="NR"){
//	echo 'This title is not rated.';
//}else{
	echo '<div class="rating">'.$Rating.'</div>';
//}
	if($HighDefinitionAvailable=="true"){
		echo '<div class="hidef">HD</div>';
	}
echo 'Run time: '.date("g:i",-57600 + $Runtime);
	echo '</div>
		<div class="clearboth"></div>
	</div>
';
} ?>

<div style="padding:200px 0px;font-weight:bold;">The Netflix API is no longer available. See <a href="http://odata.netflix.com/" target="_blank">http://odata.netflix.com/</a> for more info.</div>

</div>
<div class="poweredby">Powered by</div>
<div align="center"><div id="netflix"><a href="http://www.netflix.com" target="_blank"><img src="https://netflix.hs.llnwd.net/e1/us/layout/headers/logos/nf_logo.png" title="Powered by Netflix" /></a></div></div>

<div class="footer"><script src="http://www.dohnetwork.com/javascripts/footer.js" type="text/javascript"></script></div></td>
</body></html>
