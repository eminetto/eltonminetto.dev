<?php
include('lib/4sq/EpiFoursquare.php');
include('lib/4sq/EpiCurl.php');
include('lib/4sq/EpiSequence.php');

$radius_mt = 50000;
$lat = '-26.30177964';
$lng = '-48.84925057';


$clientId = 'QXYSS5BY2XKUXBD2KHBYZ1AYICRE2WDMB13XRS3JF1XXYJB5';
$clientSecret = 'GGSMWRSB1WUGJ1THVXZMNOCJRPW4TX3NQHPAINPHNR3XVLGZ';
$redirectUrl = 'http://coderockr.dyndns.org:4080/geo/callback_4s.php';

 // exchange the request token for an access token
$url = "https://foursquare.com/oauth2/access_token?client_id=$clientId&client_secret=$clientSecret&grant_type=authorization_code&redirect_uri=$redirectUrl&code=".$_GET['code'];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_AUTOREFERER, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER , true);
curl_setopt($ch, CURLOPT_VERBOSE ,  false);
curl_setopt($ch,  CURLOPT_HEADER , false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 0.1);
$result = json_decode(curl_exec($ch));
$token = $result->access_token;
echo $token;
/*$fsObj = new EpiFoursquare($clientId, $clientSecret);
$fsObj->setAccessToken($result->access_token);
$res = $fsObj->get('/venues/search', array('ll' => "$lat,$lng"));
var_dump($res->result);*/

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
echo '<pre>';
foreach($result as $r) {
	foreach($r->groups as $g) {
		foreach($g->items as $i) {
			echo $i->name, ' - ', $i->location->address, '<br>';
		}
	}
}

