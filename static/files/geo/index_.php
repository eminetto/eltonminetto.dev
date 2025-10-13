<?php
/**
 * index.php
 * 
 * Exibe um formulário com opções de chamada da API (incluindo a autorização OAuth)
 * e processa o último botão clicado neste formulário.
 * 
 * Você pode remover os comentários das atribuições fixas (ex.: "//$radius_mt = 1000;")
 * para fazer testes adicionais.
 * 
 * Copyright 2010 Apontador/LBSLocal
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http: *www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

//
//  As funções abaixo exigem o token e secret, isto é, a autorização do usuário Apontador, caso
//  não seja especificado nenhum usuário. Para obter este token/secret, é feita a autorização do
//  usuário:
// 
//  $apontadorApi->setOAuthToken($_COOKIE['oauth_token'], $_COOKIE['oauth_token_secret']);
//  $apontadorApi->getUserInfo($user_id);
//  $apontadorApi->getUserReviews($user_id, $page, $limit);
//  $apontadorApi->getUserPlaces($user_id, $page, $limit);
//  $apontadorApi->getUserCheckins($user_id, $page, $limit, $user_checkins);
// 
//  All inserts for Apontador User you need this step
//  $apontadorApi->doUserCheckins($place_id);
//  $apontadorApi->doPlaceThumbsDown($place_id);
//  $apontadorApi->doPlaceThumbsUp($place_id);
//  $apontadorApi->doPlaceReview($place_id, $rating, $content);
// 
//

require_once("lib/ApontadorApi.php");
require_once("config.php");

// Objeto que dá acesso à API. Os dados de inicialização ficam no config.php
$apontadorApi = new ApontadorApi($key, $secret, $callbackurl);


try {
$acao = $_REQUEST['acao'];
if ($acao == "authorizeUser") {

    // Usuário pediu autorização
	$apontadorApi->apontadorRedirectAutorizacao();
	die();

}
elseif ($acao == "getCategories") {

    //Get List of Categories
    $categories = $apontadorApi->getCategories($term);

    //Return test
    for($k=0;$k<sizeof($categories);$k++){
            /*
            $categories[$k]['id'] = $category->id;
            $categories[$k]['name'] = $category->name;
             */
            $resultado .= $categories[$k]['id'] ." ".$categories[$k]['name']."\n";
     }


}
elseif ($acao == "getTopCategories") {

    //Get List of Categories
    $categories = $apontadorApi->getTopCategories();

    //Return test
    for($k=0;$k<sizeof($categories);$k++){
            /*
            $categories[$k]['id'] = $category->id;
            $categories[$k]['name'] = $category->name;
             */
            $resultado .= $categories[$k]['id'] ." ".$categories[$k]['name']."\n";
     }

}
elseif ($acao == "getSubCategories") {

    //Get List of SubCategories
    $category = $_REQUEST['category'];

    $subcategories = $apontadorApi->getSubCategories($category, $term);

    //Return test
    for($k=0;$k<sizeof($subcategories);$k++){
            /*
            $subcategories[$k]['id'] = $category->id;
            $subcategories[$k]['name'] = $category->name;
             */
            $resultado .= $subcategories[$k]['id'] ." ".$subcategories[$k]['name']."\n";
     }

}elseif ($acao == "searchByPoint") {

    //Search by Point

    //$radius_mt = 1000;
    //$lat = '-23.5934';
    //$lng = '-46.6876';
    $radius_mt = $_REQUEST['radius_mt'];
    $lat = $_REQUEST['lat'];
    $lng = $_REQUEST['lng'];
    $places = $apontadorApi->searchByPoint($lat, $lng, $radius_mt, $term, $category_id, $sort_by, $order, $rating, $limit,
                            $user_id, $page);
    //Return test
    //echo $places[0]['address'];
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
            $resultado .= $places[$k]['lbsid'] ." ".$places[$k]['name']." ".$places[$k]['address']."\n";
     }

}elseif ($acao == "searchByAddress") {

    //Search by Address

    //$radius_mt = 15000;
    //$city = 'Sao Paulo';
    //$state = 'SP';
    //$street = 'R Itacema';
    //$term = 'santo grao';
    $radius_mt = $_REQUEST['radius_mt'];
    $city = $_REQUEST['city'];
    $state = $_REQUEST['state'];
    $street = $_REQUEST['street'];
    $number = $_REQUEST['number'];
    $term = $_REQUEST['term'];

    $places = $apontadorApi->searchByAddress($term, $country, $state, $city, $street, $number, $district, $radius_mt,
                            $category_id, $sort_by, $order, $rating, $limit,
                            $user_id, $page);

    //Return test
    //echo $places[0]['address'];
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
            $resultado .= $places[$k]['lbsid'] ." ".$places[$k]['name']." ".$places[$k]['address']."\n";
     }
    

}elseif ($acao == "searchByZipCode") {

    //Search by ZipCode
    //$radius_mt = 2000;
    //$zipcode = '04530051';
    //$term = 'santo grao';
    $radius_mt = $_REQUEST['radius_mt'];
    $zipcode = $_REQUEST['zipcode'];
    $term = $_REQUEST['term'];

    $sort_by = 'distance';
    $places = $apontadorApi->searchByZipCode($term, $zipcode, $radius_mt,
                            $category_id, $sort_by, $order, $rating, $limit,
                            $user_id, $page);

    //Return test
    //echo $places[0]['address'];
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
            $resultado .= $places[$k]['lbsid'] ." ".$places[$k]['name']." ".$places[$k]['address']."\n";
     }

}elseif ($acao == "searchByBox") {

    //Search by Box
    //$se_lat = "-24.00";
    //$se_lng = "-46.00";
    //$nw_lat = "-22.00";
    //$nw_lng = "-48.00";
    //$term = 'santo grao';
    $se_lat = $_REQUEST['se_lat'];
    $se_lng = $_REQUEST['se_lng'];
    $nw_lat = $_REQUEST['nw_lat'];
    $nw_lng = $_REQUEST['nw_lng'];
    $term = $_REQUEST['term'];

    $sort_by = 'distance';
    $places = $apontadorApi->searchByBox($se_lat, $se_lng, $nw_lat, $nw_lng, $term, $category_id, $sort_by, $order,
                            $rating, $limit, $user_id, $page);

    //Return test
    //echo $places[0]['address'];
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
            $resultado .= $places[$k]['lbsid'] ." ".$places[$k]['name']." ".$places[$k]['address']."\n";
     }

}elseif ($acao == "getPlaceById") {

    //Get Place By Id
    //$place_id = 'YQX3JSTQ';
    $place_id = $_REQUEST['place_id'];
    try {
	    $place = $apontadorApi->getPlaceById($place_id);
	    $resultado = "Id:" . $place['lbsid'] . "\n" . "Nome:". $place['name'] . "\n" . "Address" . $place['address'];
	} catch (ApontadorApiException $e) {
		// In this case, we want to avoid an error for an empty ID (which returns a
		// 405, since the URL would be valid on a PUT) and a 404
		if ($e->status_code == "405" || $e->status_code == "404") {
			$resultado = "Erro: Informe o ID";
		}
	}

    //Return test
    /*
     *      $place['lbsid'] = $aux_place->id;
            $place['name'] = $aux_place->name;
            $place['description'] = $aux_place->description;
            $place['clicks'] = $aux_place->click_count;
            $place['review_count'] = $aux_place->point->review_count;
            $place['average_rating'] = $aux_place->avarage_rating;
            $place['thumbs'] = $aux_place->thumbs->total;
            $place['thumbs_up'] = $aux_place->thumbs->up;
            $place['category'] = $aux_place->category->name;
            $place['category_id'] = $aux_place->category->id;
            $place['subcategory'] = $aux_place->category->subcategory->name;
            $place['subcategory_id'] = $aux_place->category->subcategory->id;
            $place['lat'] = $aux_place->point->lat;
            $place['lng'] = $aux_place->point->lng;
            $place['apontador_link'] = $aux_place->main_url;
            $place['place_link'] = $aux_place->other_url;
            $place['place_icon'] = $aux_place->icon_url;
            $place['address'] = $aux_place->address->street . ' ' . $aux_place->address->number;
            $place['complement'] = $aux_place->address->complement;
            $place['zipcode'] = $aux_place->address->zipcode;
            $place['district'] = $aux_place->address->district;
            $place['city'] = $aux_place->address->city->name . ' - ' . $aux_place->address->city->state;
            $place['country'] = $aux_place->address->city->country;
            $place['phone'] = '+' . $aux_place->phone->country . ' ' . $aux_place->phone->area . ' ' . $aux_place->phone->number;
            $place['user_id'] = $aux_place->created->user->id;
            $place['user'] = $aux_place->created->user->name;

     */

}elseif ($acao == "getPlacePhotos") {

    //getPlacePhotos
    //$place_id = 'YQX3JSTQ';
    $place_id = $_REQUEST['place_id'];
    $photos = $apontadorApi->getPlacePhotos($place_id);

    //Return test
    for($k=0;$k<sizeof($photos);$k++){
            $resultado .= $photos[$k]."\n";
     }

}elseif ($acao == "getPlaceReviews") {

    //getPlaceReviews
    //$place_id = 'YQX3JSTQ';
    $place_id = $_REQUEST['place_id'];
    $reviews = $apontadorApi->getPlaceReviews($place_id, $page, $limit);

    //Return test
    for($k=0;$k<sizeof($reviews);$k++){
           $resultado .= $reviews[$k]['lbsid'] ." ".$reviews[$k]['name']." ".$reviews[$k]['rating']." ".$reviews[$k]['content']."\n";
    }


}elseif ($acao == "getUserInfo") {

    //Get User Info

    //1 - get by userid
    //$user_id = "9764713347";
    //2 - get by OAuth
    $apontadorApi->setOAuthToken($_COOKIE['oauth_token'], $_COOKIE['oauth_token_secret']);
	$user_info = $apontadorApi->getUserInfo($user_id);
	$resultado .= $user_info['id'] ." ".$user_info['name']." ".$user_info['birthday']." ".$user_info['photo_url']."\n";

    //Return test
    /*
    $user_info['id'] = $usr_info->id;
    $user_info['name'] = $usr_info->name;
    $user_info['birthday'] = $usr_info->birthday;
    $user_info['photo_url'] = $usr_info->photo_url;
    $user_info['photo_medium_url'] = $usr_info->photo_medium_url;
    $user_info['photo_small_url'] = $usr_info->photo_small_url;
    $user_info['total_places'] =  $usr_info->stats->places;
    $user_info['total_photos'] = $usr_info->stats->photos;
    $user_info['total_reviews'] = $usr_info->stats->reviews;
    */


}elseif ($acao == "getUserReviews") {

    //Get User Reviews

    //1 - get by userid
    //$user_id = "9764713347";
    //2 - get by OAuth
    $apontadorApi->setOAuthToken($_COOKIE['oauth_token'], $_COOKIE['oauth_token_secret']);
    $reviews = $apontadorApi->getUserReviews($user_id, $page, $limit);

    //Return test
    for($k=0;$k<sizeof($reviews);$k++){
            /*
                    $user_reviews[$k]['lbsid'] = $review->place->id;
                    $user_reviews[$k]['name'] = $review->place->name;
                    $user_reviews[$k]['id'] = $review->id;
                    $user_reviews[$k]['rating'] = $review->rating;
                    $user_reviews[$k]['content'] = $review->content;
                    $user_reviews[$k]['timestamp'] = $review->created->timestamp;
                    $user_reviews[$k]['user_id'] = $review->created->user->id;
                    $user_reviews[$k]['user'] = $review->created->user->name;
                    $user_reviews[$k]['user_photo'] = $review->created->user->photo_url;
            */
            $resultado .= $reviews[$k]['lbsid'] ." ".$reviews[$k]['name']." ".$reviews[$k]['rating']." ".$reviews[$k]['content']."\n";
     }


}elseif ($acao == "getUserPlaces") {

    //Get User Places

    //1 - get by userid
    //$user_id = "9764713347";
    //2 - get by OAuth
    $apontadorApi->setOAuthToken($_COOKIE['oauth_token'], $_COOKIE['oauth_token_secret']);
    $places = $apontadorApi->getUserPlaces($user_id, $page, $limit);

    //Return test
    //echo $places[0]['address'];
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
            $resultado .= $places[$k]['lbsid'] ." ".$places[$k]['name']." ".$places[$k]['address']."\n";
     }

}elseif ($acao == "searchUserByName") {

    //Search User By Name
    //$name = "Rafael Siqueira";
    $name = $_REQUEST["name"];
    $users = $apontadorApi->searchUserByName($name, $limit, $user_id, $page);

    //Return test
    for($k=0;$k<sizeof($users);$k++){
            $resultado .= $users[$k]['id'] ." ".$users[$k]['name']." ".$users[$k]['photo_url']."\n";
     }


}elseif ($acao == "searchUserByAddress") {


    //Search User By Address
    $city = 'Sao Paulo';
    $state = 'SP';
    $country = 'BR';
    $users = $apontadorApi->searchUserByAddress($country, $state, $city, $limit, $user_id, $page);

    //Return test
    for($k=0;$k<sizeof($users);$k++){
            $resultado .= $users[$k]['id'] ." ".$places[$k]['name']."\n";
     }

}elseif ($acao == "getUserCheckins") {

    //Get User Checkins
    $limit = 20;

    //1 - get by userid
    //$user_id = "9764713347";
    //2 - get by OAuth
    $apontadorApi->setOAuthToken($_COOKIE['oauth_token'], $_COOKIE['oauth_token_secret']);
    $user_checkins = $apontadorApi->getUserCheckins($user_id, $page, $limit, $user_checkins);

    //Return test
    //Return test
    for($k=0;$k<sizeof($user_checkins);$k++){
            $resultado .= $user_checkins[$k]['date'] ." ".$user_checkins[$k]['name']."\n";
     }

}elseif ($acao == "doUserCheckins") {

    //Do User Checkin
    //$place_id = "C4015454280A6U0A62";
	$place_id = $_REQUEST['place_id'];
    
    //Set OAuth token
    $apontadorApi->setOAuthToken($_COOKIE['oauth_token'], $_COOKIE['oauth_token_secret']);
	    $user_checkin = $apontadorApi->doUserCheckins($place_id);
	    //Return test
    	$resultado = $user_checkin['lbsid']." ".$user_checkin['name']." ".$user_checkin['date'];

}elseif ($acao == "getPlaceCheckins") {

    //Get Place Checkins
    //$place_id = "C4015454280A6U0A62";
    $place_id = $_REQUEST['place_id'];
    
    $user_checkins = $apontadorApi->getPlaceCheckins($place_id, $page, $limit);

    //Return test
    for($k=0;$k<sizeof($user_checkins);$k++){
            $resultado .= $user_checkins[0]['last_visit']. " ". $user_checkins[0]['user'] ." ". $user_checkins[0]['user_photo']. "\n";
     }
}elseif ($acao == "doPlaceThumbsUp") {

    //Do Place ThumbsUp(needs oauth_token, oauth_token_secret)
    //$place_id = "C4015454280A6U0A62";
	$place_id = $_REQUEST['place_id'];
    
    //Set OAuth token
    $apontadorApi->setOAuthToken($_COOKIE['oauth_token'], $_COOKIE['oauth_token_secret']);
    $place_stats = $apontadorApi->doPlaceThumbsUp($place_id);

    //Return test
    $resultado = "Total Thumbs:".$place_stats['thumbs_total']." ThumbsUp:".$place_stats['thumbs_up'];

}elseif ($acao == "doPlaceThumbsDown") {

    //Do Place ThumbsDown(needs oauth_token, oauth_token_secret)
    //$place_id = "C4015454280A6U0A62";
	$place_id = $_REQUEST['place_id'];
    
    //Set OAuth token
    $apontadorApi->setOAuthToken($_COOKIE['oauth_token'], $_COOKIE['oauth_token_secret']);
    $place_stats = $apontadorApi->doPlaceThumbsDown($place_id);

    //Return test
    $resultado = "Total Thumbs:".$place_stats['thumbs_total']." ThumbsUp:".$place_stats['thumbs_up'];

}elseif ($acao == "doPlaceReview") {

    //Do Place Review(needs oauth_token, oauth_token_secret)
    //$place_id = "C4015454280A6U0A62";
    //$content = "muito bom para trabalhar neste lugar 6.";
    //$rating = 5;
    $place_id = $_REQUEST['place_id'];
    $rating = $_REQUEST['rating'];
    $content = $_REQUEST['content'];

    //Set OAuth token
    $apontadorApi->setOAuthToken($_COOKIE['oauth_token'], $_COOKIE['oauth_token_secret']);
    try {
    	$place_review = $apontadorApi->doPlaceReview($place_id, $rating, $content);
    
	    //Return test
	    $resultado  = "Nota:" . $place_review['rating'] ."\n". $place_review['content'];
    } catch (ApontadorAPIException $e) {
    	// Neste caso, um usuário não-autorizado pode receber um 405, pois a URL é
    	// válida para GET com autenticação Basic, mas não para POST.
		if ($e->status_code == "405") {
			$resultado = "Erro: Não autorizado";
		}
    	
    }

} elseif($acao == 'sendPhoto'){

    $place_id = "C4015454280A6U0A62";

    if(isset($_POST['acao']))
    {
        $size=$_FILES['fileupload']['size'];
        if($size>1048576)
        {
                echo "error file size > 1 MB";
                unlink($_FILES['fileupload']['tmp_name']);
                exit;
        }
        $data = base64_encode(file_get_contents($_FILES['fileupload']['tmp_name']));

        $apontadorApi->setOAuthToken($_COOKIE['oauth_token'], $_COOKIE['oauth_token_secret']);
        $retorno = $apontadorApi->sendPhoto($place_id, $data);


    }

}
    } catch (ApontadorApiException $e) {
    	if ($e->status_code == "401") {
    		$resultado = "Você precisa autorizar a aplicação para este usuário via OAuth";
    	} else {
	    	$resultado = $e->getMessage() . "\nRetorno:" . $e->response;
	    }
    }

?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
	<h1>Aplicação Exemplo</h1>
	<p>Teste a <a href="http://api.apontador.com.br">Apontador API</a> aqui!</p>
                <form action="index.php">
                <h2>Autorização (OAuth)</h2>
		<p>
			<? if ($_COOKIE['oauth_token']) { ?>
				Já temos um token de autorização desa apliação.<br/>
				 Quer autorizar novamente? <input type="submit" name="acao" value="authorizeUser" />			<? } else { ?>
				Você não autorizou através do OAuth esta aplicação no Apontador.
				<input type="submit" name="acao" value="authorizeUser" />
			<? } ?>
		</p>
		<? if ($resultado) { ?>
			<h2>Resultado</h2>
			<p>Resultado do último pedido:</p>
			<textarea rows="10" cols="80" readonly="readonly" scroll="both"><?=$resultado?></textarea><br/>
		<? } ?>

		<h2>Dados pessoais (requer autorização OAuth):</h2>
		<input type="submit" name="acao" value="getUserInfo" />
		<input type="submit" name="acao" value="getUserReviews" />
                <input type="submit" name="acao" value="getUserPlaces" />
                <input type="submit" name="acao" value="getUserCheckins" /><br/>

	</form>
        <form action="index.php">
		<h2>Categorias e Subcategorias</h2>
		<input type="submit" name="acao" value="getCategories" />
		<input type="submit" name="acao" value="getTopCategories" /><br>
                Categoria_id(Restaurantes é 67: <input type="text" name="category" value="67" />
		<input type="submit" name="acao" value="getSubCategories" /><br/>

	</form>
        <form action="index.php">
		<h2>Dados do local</h2>
		<label for="PLACEID">PLACEID(Ex:"C4015454280A6U0A62"):</label>
                <input type="text" name="place_id" value="C4015454280A6U0A62"><br/>
                <input type="submit" name="acao" value="getPlaceById" />
		<input type="submit" name="acao" value="getPlaceReviews" />
        	<input type="submit" name="acao" value="getPlacePhotos" />
        	<input type="submit" name="acao" value="getPlaceCheckins" /><br>
        </form>
	<form action="index.php">
		<h2>Busca local (por cep):</h2>
		O que: <input type="text" name="term" value="Santo Grao" />
		Cep: <input type="text" name="zipcode" value="04551060">
		Raio (m): <input type="text" name="radius_mt" value="5000">
		<input type="submit" name="acao" value="searchByZipCode" /><br/>
		<br/>
	</form>
        <form action="index.php">
		<h2>Busca local (por endereco):</h2>
		O que: <input type="text" name="term" value="Santo Grao" />
		Cidade: <input type="text" name="city" value="Sao Paulo">
		Estado: <input type="text" name="state" value="SP">
		Endereco: <input type="text" name="street" value="R Funchal">
		Numero: <input type="text" name="number" value="129">
		Raio (m): <input type="text" name="radius_mt" value="5000">
		<input type="submit" name="acao" value="searchByAddress" /><br/>
		<br/>
	</form>
        <form action="index.php">
		<h2>Busca local (ao redor de uma latitude ou longitude):</h2>
		Lat: <input type="text" name="lat" value="-23.5934">
		Long: <input type="text" name="lng" value="-46.6876">
		Raio (m): <input type="text" name="radius_mt" value="5000">
		<input type="submit" name="acao" value="searchByPoint" /><br/>
		<br/>
	</form>
        <form action="index.php">
		<h2>Busca local (por boundingbox):</h2>
		O que: <input type="text" name="term" value="Santo Grao" />
	        LatMax: <input type="text" name="nw_lat" value="-22.00">
		LatMin: <input type="text" name="se_lat" value="-24.00">
		LongMax: <input type="text" name="se_lng" value="-46.00">
		LongMin: <input type="text" name="nw_lng" value="-48.00">
		<input type="submit" name="acao" value="searchByBox" /><br/>
		<br/>
	</form>
                <form action="index.php">
		<h2>Busca de Usuarios por Nome:</h2>
		Nome: <input type="text" name="name" value="Rafael Siqueira" />
	        <input type="submit" name="acao" value="searchUserByName" /><br/>
		<br/>
	</form>
        <form action="index.php">
		<h2>Recomendo ou nao recomendo ou fazer checkin no local</h2>
		<label for="PLACEID">PLACEID(Ex:"C4015454280A6U0A62"):</label>
                <input type="text" name="place_id" value="C4015454280A6U0A62"><br/>
                <input type="submit" name="acao" value="doPlaceThumbsUp" />
		<input type="submit" name="acao" value="doPlaceThumbsDown" />
        	<input type="submit" name="acao" value="doUserCheckins" /><br>
        </form>
	<form action="index.php">
		<h2>Cadastre uma avaliação</h2>
		<label for="PLACEID">PLACEID(Ex:"C4015454280A6U0A62"):</label>
                <input type="text" name="place_id" value="C4015454280A6U0A62"><br/>
                <label for="content">Escreva a avaliacao</label>
		<textarea name="content" cols="60" rows="4"></textarea><br/>
		<label for="rating">Nota:</label>
		<input type="radio" name="rating" value="1" checked="checked"/>1
		<input type="radio" name="rating" value="2"/>2
		<input type="radio" name="rating" value="3"/>3
		<input type="radio" name="rating" value="4"/>4
		<input type="radio" name="rating" value="5"/>5
		<input type="submit" name="acao" value="doPlaceReview" />
	</form>
        <form method="post" enctype="multipart/form-data" action="index.php">
            <h2>Enviar Foto para um Local - Precisa do OAuth</h2>
	    <label for="PLACEID">PLACEID(Ex:"C4015454280A6U0A62"):</label>
            <input type="text" name="place_id" value="C4015454280A6U0A62"><br/>
            <input type="file" name="fileupload" size="19" id="fileupload"><br>
            <input type="submit" name="acao" value="sendPhoto">
        </form>
</body>
</html>
