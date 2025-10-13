<?php
//mysql 
/*** mysql hostname ***/
$hostname = 'localhost';

/*** mysql username ***/
$username = 'root';

/*** mysql password ***/
$password = 'cr123456';

try {
    $dbh = new PDO("mysql:host=$hostname;dbname=geo", $username, $password);
    /*** echo a message saying we have connected ***/
    }
catch(PDOException $e)
    {
    echo $e->getMessage();
}

$radius_mt = 1000;
$lat = $_POST['lat'];
$lng = $_POST['lng'];
if(!$lat) 
	$lat = '-26.305088';
if(!$lng)
	$lng = '-48.846093';
	
//insere a pesquisa
$dbh->exec("insert into search values(null,$lat,$lng)");
$search_id = $dbh->lastInsertId();
if($search_id == 0) {
	$sql = "select id from search where lat=$lat and lng=$lng";
	foreach($dbh->query($sql) as $r)
		$search_id = $r['id'];
}
$sql = "select * from place where search_id = $search_id";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$result = $stmt->fetch();
if(!$result) {
	$key = "ZfsAQXoYFukaw3izt45kgkS-zdL9z6AA2_RJzX7wJl4~";
	$secret = "icxfXOJfOkAZglhl3IYmlg5k-pE~";
	$callbackurl = "http://coderockr.dyndns.org:4080/geo/callback.php";
	include('lib/ApontadorApi.php');
	$apontadorApi = new ApontadorApi(
	                      $key,
	                      $secret,
	                      $callbackurl);
	$places = $apontadorApi->searchByPoint($lat, $lng, $radius_mt, $term, $category_id, $sort_by, $order, $rating, $limit, $user_id, $page);
	for($k=0;$k<sizeof($places);$k++){
	  		/*
	        $places[$k]['lbsid'] = $place->id;
	        $places[$k]['name'] = $place->name;
	        $places[$k]['category'] = $place->category->name;
	        $places[$k]['address'] = $place->address->street . ' ' . $place->address->number;
	        $places[$k]['category_id'] = $place->category->id;
	        $places[$k]['subcategory'] = $place->category->subcategory->name;
	        $places[$k]['subcategory_id'] = $place->category->subcategory->id;
	        $places[$k]['average_rating'] = $place->average_rating;
	        $places[$k]['review_count'] = $place->review_count;
	        $places[$k]['lat'] = $place->point->lat;
	        $places[$k]['lng'] = $place->point->lng;
	        $places[$k]['apontador_link'] = $place->main_url;
	        $places[$k]['place_link'] = $place->other_url;
	        $places[$k]['place_icon'] = $place->icon_url;
	        $places[$k]['address'] = $place->address->street . ' ' . $place->address->number;
	        $places[$k]['complement'] = $place->address->complement;
	        $places[$k]['zipcode'] = $place->address->zipcode;
	        $places[$k]['district'] = $place->address->district;
	        $places[$k]['city'] = $place->address->city->name . ' - ' . $place->address->city->state;
	        $places[$k]['country'] = $place->address->city->country;
	        */
			//$resultado .= $places[$k]['lbsid'] ." ".$places[$k]['name']." ".$places[$k]['address']."<br>";
			//echo $places[$k]['name']." ".$places[$k]['address']."<br>";
			//insert
			$sql = "insert into place values(null, '".$places[$k]['name']."', '".$places[$k]['address']."','".$places[$k]['lbsid']."',null,".$places[$k]['lat'].",".$places[$k]['lng'].",$search_id)";
			$dbh->exec($sql);
			$place_id = $dbh->lastInsertId();
			$reviews = $apontadorApi->getPlaceReviews($places[$k]['lbsid']);
			if($reviews) {
				foreach($reviews as $r) {
					$sql = "insert into content values (null, ".$place_id.", 'Review by ".$r['user']."', '".$r['content']."',now(),null,'Review')";
					$dbh->exec($sql);
				}
			}
	 }
	//echo $resultado;
	//4square
	$clientId = 'QXYSS5BY2XKUXBD2KHBYZ1AYICRE2WDMB13XRS3JF1XXYJB5';
	$clientSecret = 'GGSMWRSB1WUGJ1THVXZMNOCJRPW4TX3NQHPAINPHNR3XVLGZ';
	$redirectUrl = 'http://coderockr.dyndns.org:4080/geo/callback_4s.php';

	//4square
	//para pegar um novo token
	//$url = "https://foursquare.com/oauth2/authenticate?client_id=$clientId&response_type=code&redirect_uri=$redirectUrl";
	//echo "<a href=\"$url\">Click here</a>";
	$token = 'QBN22321QNHLSNIXAOQ2SNBPRPWLSOGPDWKLZIZA4AN400AF';
	$url = "https://api.foursquare.com/v2/venues/search?ll=$lat,$lng&oauth_token=$token";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_AUTOREFERER, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER , true);
	curl_setopt($ch, CURLOPT_VERBOSE ,  false);
	curl_setopt($ch,  CURLOPT_HEADER , false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 0.1);
	$result = json_decode(curl_exec($ch));
	//echo '<h2>Foursquare</h2>';
	foreach($result as $r) {
		foreach($r->groups as $g) {
			foreach($g->items as $i) {
				//echo $i->name, ' - ', $i->location->address, '<br>';
				//insert
				$sql = "insert into place values(null, '".$i->name."', '".$i->location->address."',null,'".$i->id."',".$i->location->lat.",".$i->location->lng.",$search_id)";
				$dbh->exec($sql);
				$place_id = $dbh->lastInsertId();
				//verifica se tem tips
				$url = "https://api.foursquare.com/v2/venues/$i->id/tips?oauth_token=$token";
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_AUTOREFERER, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER , true);
				curl_setopt($ch, CURLOPT_VERBOSE ,  false);
				curl_setopt($ch,  CURLOPT_HEADER , false);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_TIMEOUT, 0.1);
				$result = json_decode(curl_exec($ch));
				if($result->response->tips->count > 0) {
					foreach($result->response->tips->items as $i) {
						$sql = "insert into content values (null, ".$place_id.", 'Tip by ".$i->user->firstName." ".$i->user->lastName."', '".$i->text."',now(),null,'Tip')";
						$dbh->exec($sql);
					}
				}
			}
		}
	}
}
$stmt = null;
$sql = "select * from place where search_id = $search_id order by place.name";
$stmt = $dbh->prepare($sql);
$stmt->execute();
?>
<div role="main" class="ui-content" data-role="content">
	<ul role="listbox" class="ui-listview" data-role="listview">
	<?php
	while ($row = $stmt->fetch()) {
	    $address = $row['address']. '<br>';
		if($row['foursquare_id'])
			$address .= ' by foursquare';
		if($row['apontador_id'])
			$address .= ' by apontador';
		
		//verifica se tem conteúdo
		$place_id = $row['id'];
		$sql = "select 1 as c from content where place_id = $place_id";
		$stmt1 = $dbh->prepare($sql);
		$stmt1->execute();
		$result = $stmt1->fetch();
		if($result) 
			$href = "content.php?place_id=$place_id";
		else
			$href = false;
			
		
		?>
		<li class="ui-btn ui-btn-icon-right ui-li ui-btn-up-c" data-theme="c" tabindex="0" role="option">
		<div class="ui-btn-inner">
			<div class="ui-btn-text">
				<h3 class="ui-li-heading">
					<?php
					if($href) {
						?>
						<a class="ui-link-inherit" href="<?php echo $href;?>"><?php echo $row['name']; ?></a>
						<?php
					}
					else {
						echo $row['name'];
					}
					
					?>
				</h3>
				<p class="ui-li-desc"><?php echo $address; ?></p>
			</div>
			<?php
			if($href) {
				?>
				<span class="ui-icon ui-icon-arrow-r"></span>
				<?php
			}
			?>	
				
		</div>
		</li>
		<?php
		
	}
	?>
	</ul>
</div>