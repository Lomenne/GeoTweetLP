
<?php
	require "twitteroauth-master/autoload.php";

	use Abraham\TwitterOAuth\TwitterOAuth;
	$consumer_key='7LPlcPyylCDBaqkdN3tkP74BH'; //Provide your application consumer key
	$consumer_secret='YETaClLBC177KlmjnPTIKNYy9KfqyYYvXi0hZsCW9RsaXse04E'; //Provide your application consumer secret 
	$oauth_token = '1055492300-VefC6HbW8ikzxGAeq9fQDp06KFUVUY71GUu3jUX'; //Provide your oAuth Token
	$oauth_token_secret = 'CocfW9JF9M7cADp6ZWdjLlW6cr87oxIY8wjE8ySvM5llG'; //Provide your oAuth Token Secret

	$connection = new TwitterOAuth($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret);
	$connection->setOauthToken($oauth_token,$oauth_token_secret);
	$content = $connection->get("account/verify_credentials");
	if(isset($_POST["hashtagInput"])){
		$content = $connection->get("search/tweets", ["q" => "%23".$_POST["hashtagInput"]]);
	}else{
		$content = $connection->get("search/tweets", ["q" => "%23LPDev"]);
	}
	$latitude = array();
	$longitude = array();
	$contenu = array();
	$username = array();
	$image = array();

	$j = 0;
	for ($i=0; $i < count($content->statuses); $i++) {
		if($content->statuses[$i]->geo != null) {
			$latitude[$j] = $content->statuses[$i]->geo->coordinates[0];
			$longitude[$j] = $content->statuses[$i]->geo->coordinates[1];
			$contenu[$j] = $content->statuses[$i]->text;
			$username[$j] = $content->statuses[$i]->user->name;
			$image[$j] = $content->statuses[$i]->user->profile_image_url;
			$j++;
		}

		else if($content->statuses[$i]->coordinates != null) {
			$latitude[$j] = $content->statuses[$i]->coordinates->coordinates[0];
			$longitude[$j] = $content->statuses[$i]->coordinates->coordinates[1];
			$contenu[$j] = $content->statuses[$i]->text;
			$username[$j] = $content->statuses[$i]->user->name;
			$image[$j] = $content->statuses[$i]->user->profile_image_url;
			$j++;
		}

		else if($content->statuses[$i]->place != null) {
			$latitude[$j] = $content->statuses[$i]->place->bounding_box->coordinates[0][0][1];
			$longitude[$j] = $content->statuses[$i]->place->bounding_box->coordinates[0][0][0];
			$contenu[$j] = $content->statuses[$i]->text;
			$username[$j] = $content->statuses[$i]->user->name;
			$image[$j] = $content->statuses[$i]->user->profile_image_url;
			$j++;
		}
	}
	
	$longitudeJS = json_encode($longitude);
	$latitudeJS = json_encode($latitude);
	$contenuJS = json_encode($contenu);
	$usernameJS = json_encode($username);
	$imageJS = json_encode($image);
?>
<!DOCTYPE html>
<html lang="en" ng-app="myApp">
  	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">    
		<title>LPGeoTweet - MENNELLA, PAROUX et DE ROBERT</title>
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<link href="css/main.css" rel="stylesheet">
		<link rel="icon" href="img/favicon-F.png" />
		<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDMgi50QAMmPAHAMu_nOHOzUb8zyyIFzeo"></script>
		<script>
			function initialisation() {
				var localisation = new google.maps.LatLng(43.617095, 7.072194);
				var lat = <?php echo $latitudeJS; ?>;
				var long = <?php echo $longitudeJS; ?>;
				var cont = <?php echo $contenuJS; ?>;
				var username = <?php echo $usernameJS; ?>;
				var image = <?php echo $imageJS; ?>;
				var prev_infowindow = false;

				var optionsCarte = {
		          	zoom: 7,
		          	center: localisation
	        	}

		        var maCarte = new google.maps.Map(document.getElementById("map"), optionsCarte);

		        for (var i = 0; i < lat.length; i++) {
		        	var localisation = new google.maps.LatLng(lat[i], long[i]);

		        	var contentString = "<h3><img class='profile' src='"+image[i]+"'/> "+username[i]+" : </h3><p>"+cont[i]+"</p>";

		        	var infowindow = new google.maps.InfoWindow();


		        	var marker = new google.maps.Marker({
		            position: localisation,
		            title: cont[i]
		        	});

		        	google.maps.event.addListener(marker,'click', (function(marker,contentString,infowindow){ 
					    return function() {
			        		if( prev_infowindow ) {
					           prev_infowindow.close();
					        }
					        infowindow.setContent(contentString);
					        infowindow.open(map,marker);
				        	prev_infowindow = infowindow;
					    };
					})(marker,contentString,infowindow));  

		        	marker.setMap(maCarte);
		        }  
			}
		  	google.maps.event.addDomListener(window, 'load', initialisation);
		</script>
		<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
		  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>
		<div class="contain-fluid">
			<div class="row">
				<div class="col-xs-10" id="map">
					MAPS
				</div>
				<div class="col-xs-2" id="deroulante">
					<form action="" method="POST">
						<div class="form-group">
							<label for="inputFindHashtag">Trouver un HashTag :</label>
							<input type="text" id="hashtagInput" class="form-control" name="hashtagInput" placeholder="Trouver un HashTag">
						</div>
						<button type="submit"  value="find" class="btn btn-primary"> Valider </button>
					</form>
					<h3>Les tweets</h3>
					<?php 
						for ($i=0; $i < count($contenu); $i++) { 
							echo '<img class="profile" src="'.$image[$i].'"/> <b>'.$username[$i].' : </b>';
							echo '<p>'.$contenu[$i].'</p>';
						}
					?>
				</div>
			</div>
		</div>
	</body>
</html>
