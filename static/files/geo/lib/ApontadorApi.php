<?php
/**
 * ApontadorApi.php
 *
 * Encapsulamento (bem simplificado) dos mecanismos de chamada oAuth à Apontador API.
 * Configure os dados da sua aplicação no ApontadorApiConfig antes de usar.
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
 *
 */

require_once("OAuth.php");

class ApontadorApiException extends Exception
{

	public $status_code;
	public $response;

}

class ApontadorApi
{

    protected $type = "json";

    public $key, $secret, $callbackurl, $oauth_token,$oauth_token_secret;

    public function __construct($key, $secret, $callbackurl) {
        $this->key = $key;
        $this->secret = $secret;
        $this->callbackurl = $callbackurl;
    }


    function setOAuthToken($oauth_token, $oauth_token_secret)
    {
        $this->oauth_token = $oauth_token;
        $this->oauth_token_secret = $oauth_token_secret;

        return;
    }


    function setKeySecret($key, $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
        return;
    }
    

    function apontadorRedirectAutorizacao() {

	$consumer = new OAuthConsumer($this->key, $this->secret, NULL);
	$signature_method = new OAuthSignatureMethod_HMAC_SHA1();

 	// Passo 1: Pedir o par de tokens inicial (oauth_token e oauth_token_secret) para o Apontador
	$endpoint = "http://api.apontador.com.br/v1/oauth/request_token";
	$req_req = OAuthRequest::from_consumer_and_token($consumer, NULL, "GET", $endpoint, array());
	$req_req->sign_request($signature_method, $consumer, NULL);
	parse_str(file_get_contents($req_req));

	// Passo 2: Redirecionar o usuário para o Apontador, para que ele autorize o uso dos seus dados.
	$endpoint = "http://api.apontador.com.br/v1/oauth/authorize";
	$oauth_callback = "$this->callbackurl?&key=$this->key&secret=$this->secret&token=$oauth_token&token_secret=$oauth_token_secret&endpoint=" . urlencode($endpoint);
	$auth_url = $endpoint . "?oauth_token=$oauth_token&oauth_callback=" . urlencode($oauth_callback) . "";
	header("Location: $auth_url");

    }

    /**
     * Processa o retorno (callback) de uma autorização, obtendo os dados de acesso definitivos
     * (token+secret) e o ID do usuário.
     *
     * A função acessa diretamente o request ($_REQUEST) para obter os dados.
     *
     * @return mixed dados de acesso (oauth_token e oauth_token_secret) e user_id do Apontador.
     */
    function apontadorProcessaAutorizacao() {

        $consumer = new OAuthConsumer($this->key, $this->secret, NULL);
	$signature_method = new OAuthSignatureMethod_HMAC_SHA1();

        $token = $_REQUEST["oauth_token"];
        $verifier = $_REQUEST["oauth_verifier"];
        if ((!$token) || (!$verifier)) {
             return null;
        }

        // Passo 3: Passa o token e verificador para o Apontador, que vai validar o callback
        //          e devolver o token de acesso definitivo
        $endpoint = "http://api.apontador.com.br/v1/oauth/access_token?oauth_verifier=$verifier";
        $parsed = parse_url($endpoint);
        $params = array();
        parse_str($parsed['query'], $params);
        $acc_req = OAuthRequest::from_consumer_and_token($consumer, NULL, "GET", $endpoint, $params);
        $acc_req->sign_request($signature_method, $consumer, NULL);
        parse_str(file_get_contents($acc_req), $access_token);

        $this->oauth_token = $access_token['$oauth_token'];
        $this->oauth_token_secret = $access_token['$oauth_token_secret'];
        
        return $access_token;

    }

    /**
     * Efetua uma chamada a um método API
     *
     * @param verbo string GET, POST, PUT ou DELETE, conforme o método/intenção
     * @param metodo string path do métdodo, sem "/" no começo (ex.: "users/self")
     * @param params mixed parâmetros da chamada (array associativo)
     * @param oauth_token string token de autorização do usuário. Se omitido, a chamada usa HTTP Basic Auth
     * @param oauth_token_secret string secret do token de autorização do usuário (ignorado se oauth_token não for passado)
     * @return resultado da chamada.
     */
    function apontadorChamaApi($verbo="GET", $metodo, $params=array(), $oauth_token="", $oauth_token_secret="") {

            $endpoint = "http://api.apontador.com.br/v1/$metodo";
            //$endpoint = "http://192.168.3.234:8080/freeapi/$metodo";
            if (!$oauth_token) {
                    $queryparams = http_build_query($params);
                    $auth_hash = base64_encode("$this->key:$this->secret");
                    return $this->_post("$endpoint?$queryparams", "GET", null, "Authorization: $auth_hash");
            } else {
                    // OAuth
                    $consumer = new OAuthConsumer($this->key, $this->secret, NULL);
                    $token = new OAuthConsumer($oauth_token, $oauth_token_secret);
                    $signature_method = new OAuthSignatureMethod_HMAC_SHA1();
                    $req_req = OAuthRequest::from_consumer_and_token($consumer, $token, $verbo, $endpoint, $params);
                    $req_req->sign_request($signature_method, $consumer, $token);
                    if ($verbo=="GET") {
                            return $this->_post($req_req, $verbo);
                    } else {
                            return $this->_post($endpoint, $verbo, $req_req->to_postdata());
                    }
            }
    }



    function _post($url, $method, $data = null, $optional_headers = null)
    {
            $params = array('http' => array(
                                            'method' => $method,
                                            'ignore_errors' => true
                                    ));
            if ($optional_headers !== null) {
                    $params['http']['header'] = $optional_headers;
            }
            if ($data !== null) {
                    $params['http']['content'] = $data;
            }
            $ctx = stream_context_create($params);
            $fp = @fopen($url, 'rb', false, $ctx);
            $response = @stream_get_contents($fp);
            list($version, $status_code, $msg) = explode(' ', $http_response_header[0], 3);
            if ($status_code != "200") {
            	$ex = new ApontadorApiException("$status_code $msg");
            	$ex->status_code = $status_code;
            	$ex->response = $response;
            	throw $ex;
            }
            return $response;
    }




    /*************************
     *
     * List of Categories
     *
     * term	Opcional	Termo de busca usado para restringir a lista de categorias. Se não for informado, a chamada retornará todas as categorias de locais do Apontador.
     * type	Opcional, default=xml	 Formato de retorno. Pode ser xml ou json.
     *
     *************************/
    function getCategories($term)
    {
            $params = array();
            $params['type'] = $this->type;
            if($term != "")$params['term'] = $term;

            //Basic Authentication
            $call_method = "categories";
            $return = $this->apontadorChamaApi("GET", $call_method, $params);
            //echo $return;

            $aRetorno = json_decode($return);
            //var_dump($aRetorno);
            $categories = array();
            foreach($aRetorno->categories as $k=>$category){
                $category = $category->category;
                $categories[$k]['id'] = $category->id;
                $categories[$k]['name'] = $category->name;
            }

            return $categories;


    }

    /*************************
     *
     * List Top Categories
     *
     * type	Opcional, default=xml	 Formato de retorno. Pode ser xml ou json
     *
     *************************/
    function getTopCategories()
    {
            $params = array();
            $params['type'] = $this->type;

            //Basic Authentication
            $call_method = "categories/top";
            $return = $this->apontadorChamaApi("GET", $call_method, $params);
            //echo $return;

            $aRetorno = json_decode($return);
            //var_dump($aRetorno);
            $categories = array();
            foreach($aRetorno->categories as $k=>$category){
                $category = $category->category;
                $categories[$k]['id'] = $category->id;
                $categories[$k]['name'] = $category->name;
            }
            return $categories;

    }


    /*************************
     *
     * List of SubCategories
     *
     * CATEGORYID	Obrigatório	 Identificador único da categoria.
     * Obs.: Este parâmetro faz parte da chamada (URL).
     * term	Opcional	Termo de busca usado para restringir a lista de sub-categorias. Se não for informado, a chamada retornará todas as sub-categorias naquela categoria.
     * type	Opcional, default=xml	 Formato de retorno. Pode ser xml ou json.
     *
     *************************/
    function getSubCategories($category, $term)
    {
            $params = array();
            $params['type'] = $this->type;
            if($term != "")$params['term'] = $term;

            //Basic Authentication
            $call_method = "categories/".$category."/subcategories";
            $return = $this->apontadorChamaApi("GET", $call_method, $params);
            //echo $return;



            $aRetorno = json_decode($return);
            //var_dump($aRetorno);
            $subcategories = array();
            foreach($aRetorno->category->subcategories as $k=>$subcategory){
                $subcategory = $subcategory->subcategory;
                $subcategories[$k]['id'] = $subcategory->id;
                $subcategories[$k]['name'] = $subcategory->name;
            }

            return $subcategories;


    }


    /*************************
     *
     * Search By Point
     *
     *
     * term	Opcional	Termo de busca (ex.: "loja").
     * lat	Obrigatório	Latitude do ponto, usando "." para decimal.
     * lng	Obrigatório	Longitude do ponto, usando "." para decimal.
     * radius_mt	Opcional, default=2000	 Distância máxima (em metros) dos locais retornados a partir do ponto. Observação: a "cerca" de retorno não é necessariamente um círculo, mas está contida neste círculo.
     * category_id	Opcional	 Restringe a busca a uma categoria. O método categories pode ser usado para recuperar a lista dos IDs possíveis.
     * sort_by	Opcional, default=distance	 Define como os resultados devem ser ordenados. Os valores possíveis atualmente são distance (ordena pela distância) e rating (ordena pela nota média de avaliação).
     * order	Opcional, default=ascending	 Direção da ordenação. Para rating, pode ser ascending (ordem ascendente) ou descending (ordem descendente). Para distância, apenas a ordem ascendente está disponível (isto é, os resultados mais próximos vêm primeiro).
     * rating	Opcional	 Se informado, restringe os resultados àqueles com uma determinada nota média em avaliações (ou dentro de uma faixa de notas). Exemplos: 3 (apenas resultados com média 3), 4-5 (apenas resultados com média entre 4 e 5). As notas de avaliação variam de 1 a 5, mas nem todos os estabelecimentos têm nota.
     * page	Opcional, default=1	 Número da página atual (entre 1 e 10). O primeiro resultado retornado é o "page*limit-ésimo".
     * limit	Opcional, default=10	 Número máximo de resultados por página (entre 1 e 20).
     * user_id	Opcional	Se informado, limita a busca a pontos criados pelo usuário referenciado.
     * type	Opcional, default=xml	 Formato de retorno. Pode ser xml,json ou kml.
     *
     *
     *************************/
    function searchByPoint($lat, $lng, $radius_mt, $term, $category_id, $sort_by, $order, $rating, $limit,
                            $user_id, $page)
    {

            //Search by Point
            if($radius_mt=="") $radius_mt = 1000;

            $params = array();
            $params['type'] = $this->type;
            if($page != "")$params['page'] = $page;
            if($limit != "")$params['limit'] = $limit;
            if($radius_mt != "")$params['radius_mt'] = $radius_mt;
            if($lat != "")$params['lat'] = $lat;
            if($lng != "")$params['lng'] = $lng;
            if($term != "")$params['term'] = urlencode($term);
            if($category_id != "")$params['category_id'] = $category_id;
            if($sort_by != "")$params['sort_by'] = $sort_by;
            if($order != "")$params['order'] = $order;
            if($rating != "")$params['rating'] = $rating;
            if($user_id != "")$params['user_id'] = $user_id;

            //Basic Authentication
            $call_method = "search/places/bypoint";
            $return = $this->apontadorChamaApi("GET", $call_method, $params);
            //echo $return;

            $aRetorno = json_decode($return);
            $places = array();
            //var_dump($aRetorno);
            if(intval($aRetorno->search->result_count) > 0){
                foreach($aRetorno->search->places as $k=>$place){
                    $place = $place->place;
                    $places[$k]['lbsid'] = $place->id;
                    $places[$k]['name'] = $place->name;
                    $places[$k]['category'] = $place->category->name;
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
                }
            }
            return $places;
    }




    /*************************
     *
     * Search By Address
     *
     *
     * term	Opcional	Termo de busca (ex.: "loja").
     * country	Opcional, default=BR	 Código de duas letras do país, no padrão ISO-3166-1 alpha 2. Hoje apenas BR é suportado, mas outros serão adicionados no futuro.
     * state	Obrigatório	Unidade Federativa (e.g.: "SP" para o Estado de São Paulo).
     * city	Obrigatório	Nome da cidade (ex.: "Campinas").
     * street	Opcional	Nome da rua.
     * number	Opcional	Número na rua.
     * district	Opcional	Bairro.
     * radius_mt	Opcional, default=2000	 Distância máxima (em metros) dos locais retornados a partir do endereço. Observação: a "cerca" de retorno não é necessariamente um círculo, mas está contida neste círculo.
     * category_id	Opcional	 Restringe a busca a uma categoria. O método categories pode ser usado para recuperar a lista dos IDs possíveis.
     * sort_by	Opcional, default=distance	 Define como os resultados devem ser ordenados. Os valores possíveis atualmente são distance (ordena pela distância) e rating (ordena pela nota média de avaliação).
     * order	Opcional, default=ascending	 Direção da ordenação. Para rating, pode ser ascending (ordem ascendente) ou descending (ordem descendente). Para distância, apenas a ordem ascendente está disponível (isto é, os resultados mais próximos vêm primeiro).
     * rating	Opcional	 Se informado, restringe os resultados àqueles com uma determinada nota média em avaliações (ou dentro de uma faixa de notas). Exemplos: 3 (apenas resultados com média 3), 4-5 (apenas resultados com média entre 4 e 5). As notas de avaliação variam de 1 a 5, mas nem todos os estabelecimentos têm nota.
     * page	Opcional, default=1	 Número da página atual (começando no 1). O primeiro resultado retornado é o "page*limit-ésimo".
     * limit	Opcional, default=10	 Número máximo de resultados por página.
     * user_id	Opcional	Se informado, limita a busca a pontos criados pelo usuário referenciado.
     * type	Opcional, default=xml	 Formato de retorno. Pode ser xml, json ou kml.*
     *
     *************************/
    function searchByAddress($term, $country, $state, $city, $street, $number, $district, $radius_mt,
                            $category_id, $sort_by, $order, $rating, $limit,
                            $user_id, $page)
    {



            $params = array();
            $params['type'] = $this->type;
            if($page != "")$params['page'] = $page;
            if($limit != "")$params['limit'] = $limit;
            if($radius_mt != "")$params['radius_mt'] = $radius_mt;
            if($country != "")$params['country'] = $country;
            if($state != "")$params['state'] = $state;
            if($city != "")$params['city'] = urlencode($city);
            if($street != "")$params['street'] = urlencode($street);
            if($number != "")$params['number'] = $number;
            if($district != "")$params['district'] = $district;
            if($term != "")$params['term'] = urlencode($term);
            if($category_id != "")$params['category_id'] = $category_id;
            if($sort_by != "")$params['sort_by'] = $sort_by;
            if($order != "")$params['order'] = $order;
            if($rating != "")$params['rating'] = $rating;
            if($user_id != "")$params['user_id'] = $user_id;

            //Basic Authentication
            $call_method = "search/places/byaddress";
            $return = $this->apontadorChamaApi("GET", $call_method, $params);
            //echo $return;

            $aRetorno = json_decode($return);
            $places = array();
            //var_dump($aRetorno);
            if(intval($aRetorno->search->result_count) > 0){
                foreach($aRetorno->search->places as $k=>$place){
                    $place = $place->place;
                    $places[$k]['lbsid'] = $place->id;
                    $places[$k]['name'] = $place->name;
                    $places[$k]['category'] = $place->category->name;
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

                }
            }

            return $places;
    }


    /*************************
     *
     * Search By ZipCode
     *
     *
     * term	Opcional	Termo de busca (ex.: "loja").
     * zipcode	Obrigatório	CEP que estabelece o ponto central da busca.
     * radius_mt	Opcional, default=2000	 Distância máxima (em metros) dos locais retornados a partir do endereço. Observação: a "cerca" de retorno não é necessariamente um círculo, mas está contida neste círculo.
     * category_id	Opcional	 Restringe a busca a uma categoria. O método categories pode ser usado para recuperar a lista dos IDs possíveis.
     * sort_by	Opcional, default=distance	 Define como os resultados devem ser ordenados. Os valores possíveis atualmente são distance (ordena pela distância) e rating (ordena pela nota média de avaliação).
     * order	Opcional, default=ascending	 Direção da ordenação. Para rating, pode ser ascending (ordem ascendente) ou descending (ordem descendente). Para distância, apenas a ordem ascendente está disponível (isto é, os resultados mais próximos vêm primeiro).
     * rating	Opcional	 Se informado, restringe os resultados àqueles com uma determinada nota média em avaliações (ou dentro de uma faixa de notas). Exemplos: 3 (apenas resultados com média 3), 4-5 (apenas resultados com média entre 4 e 5). As notas de avaliação variam de 1 a 5, mas nem todos os estabelecimentos têm nota.
     * page	Opcional, default=1	 Número da página atual (começando no 1). O primeiro resultado retornado é o "page*limit-ésimo".
     * limit	Opcional, default=10	 Número máximo de resultados por página.
     * user_id	Opcional	Se informado, limita a busca a pontos criados pelo usuário referenciado.
     * type	Opcional, default=xml	 Formato de retorno. Pode ser xml, json ou kml.*
     *
     *************************/
    function searchByZipCode($term, $zipcode, $radius_mt,
                            $category_id, $sort_by, $order, $rating, $limit,
                            $user_id, $page)
    {


            $params = array();
            $params['type'] = $this->type;
            if($page != "")$params['page'] = $page;
            if($limit != "")$params['limit'] = $limit;
            if($radius_mt != "")$params['radius_mt'] = $radius_mt;
            if($zipcode != "")$params['zipcode'] = $zipcode;
            if($term != "")$params['term'] = urlencode($term);
            if($category_id != "")$params['category_id'] = $category_id;
            if($sort_by != "")$params['sort_by'] = $sort_by;
            if($order != "")$params['order'] = $order;
            if($rating != "")$params['rating'] = $rating;
            if($user_id != "")$params['user_id'] = $user_id;

            //Basic Authentication
            $call_method = "search/places/byzipcode";
            $return = $this->apontadorChamaApi("GET", $call_method, $params);
            //echo $return;

            $aRetorno = json_decode($return);
            $places = array();
            //var_dump($aRetorno);
            if(intval($aRetorno->search->result_count) > 0){
                foreach($aRetorno->search->places as $k=>$place){
                    $place = $place->place;
                    $places[$k]['lbsid'] = $place->id;
                    $places[$k]['name'] = $place->name;
                    $places[$k]['category'] = $place->category->name;
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
                    //echo $places[$k]['address'];

                }
            }

            return $places;
    }


    /*************************
     *
     * Search By Box
     *
     *
     * term	Opcional	Termo de busca (ex.: "loja").
     * se_lat	Obrigatório	Latitude do canto inferior-direito (sudeste) da caixa.
     * se_lng	Obrigatório	Longitude do canto inferior direito (sudeste) da caixa.
     * nw_lat	Obrigatório	Latitude do canto superior esquerdo (noroeste) da caixa.
     * nw_lng	Obrigatório	Longitude do canto superior esquerdo (noroeste) da caixa.
     * category_id	Opcional	 Restringe a busca a uma categoria. O método categories pode ser usado para recuperar a lista dos IDs possíveis.
     * sort_by	Opcional, default=distance	 Define como os resultados devem ser ordenados. Os valores possíveis atualmente são distance (ordena pela distância) e rating (ordena pela nota média de avaliação).
     * order	Opcional, default=ascending	 Direção da ordenação. Para rating, pode ser ascending (ordem ascendente) ou descending (ordem descendente). Para distância, apenas a ordem ascendente está disponível (isto é, os resultados mais próximos vêm primeiro).
     * rating	Opcional	 Se informado, restringe os resultados àqueles com uma determinada nota média em avaliações (ou dentro de uma faixa de notas). Exemplos: 3 (apenas resultados com média 3), 4-5 (apenas resultados com média entre 4 e 5). As notas de avaliação variam de 1 a 5, mas nem todos os estabelecimentos têm nota.
     * page	Opcional, default=1	 Número da página atual (entre 1 e 10). O primeiro resultado retornado é o "page*limit-ésimo".
     * limit	Opcional, default=10	 Número máximo de resultados por página (entre 1 e 20).
     * user_id	Opcional	Se informado, limita a busca a pontos criados pelo usuário referenciado.
     * type	Opcional, default=xml	 Formato de retorno. Pode ser xml,json ou kml.
     *
     *
     *************************/
    function searchByBox($se_lat, $se_lng, $nw_lat, $nw_lng, $term, $category_id, $sort_by, $order,
                            $rating, $limit, $user_id, $page)
    {


            $params = array();
            $params['type'] = $this->type;
            if($page != "")$params['page'] = $page;
            if($limit != "")$params['limit'] = $limit;
            if($se_lat != "")$params['se_lat'] = $se_lat;
            if($se_lng != "")$params['se_lng'] = $se_lng;
            if($nw_lat != "")$params['nw_lat'] = $nw_lat;
            if($nw_lng != "")$params['nw_lng'] = $nw_lng;
            if($term != "")$params['term'] = urlencode($term);
            if($category_id != "")$params['category_id'] = $category_id;
            if($sort_by != "")$params['sort_by'] = $sort_by;
            if($order != "")$params['order'] = $order;
            if($rating != "")$params['rating'] = $rating;
            if($user_id != "")$params['user_id'] = $user_id;

            //Basic Authentication
            $call_method = "search/places/bybox";
            $return = $this->apontadorChamaApi("GET", $call_method, $params);
            //echo $return;

            $aRetorno = json_decode($return);
            $places = array();
            //var_dump($aRetorno);
            if(intval($aRetorno->search->result_count) > 0){
                foreach($aRetorno->search->places as $k=>$place){
                    $place = $place->place;
                    $places[$k]['lbsid'] = $place->id;
                    $places[$k]['name'] = $place->name;
                    $places[$k]['category'] = $place->category->name;
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
                }
            }

            return $places;
    }




    /*************************
     *
     * Get Place by iD
     *
     *
     *
     *************************/
    function getPlaceLatLng($place_id)
    {
            $params = array();
            $params['type'] = $this->type;

            //Basic Authentication
            $call_method = "places/".$place_id;
            $return = $this->apontadorChamaApi("GET", $call_method, $params);
            //echo $return;

            $aRetorno = json_decode($return);
       //	var_dump($aRetorno);
            $place = $aRetorno->place;
            $latlng['lat'] = $place->point->lat;
            $latlng['lng'] = $place->point->lng;
            $latlng['url'] = $place->main_url;

            return $latlng;

    }

    /*************************
     *
     * Get Place by iD
     *
     *
     *
     *************************/
    function getPlaceById($place_id)
    {

            $params = array();
            $params['type'] = $this->type;

            //Basic Authentication
            $call_method = "places/".$place_id;
            $return = $this->apontadorChamaApi("GET", $call_method, $params);
            //echo $return;

            $aRetorno = json_decode($return);
            //var_dump($aRetorno);
            $aux_place = $aRetorno->place;
            $place['lbsid'] = $aux_place->id;
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

            return $place;


    }

    /*************************
     *
     * Place Photos
     *
     * term	Opcional	Termo de busca usado para restringir a lista de categorias. Se não for informado, a chamada retornará todas as categorias de locais do Apontador.
     * type	Opcional, default=xml	 Formato de retorno. Pode ser xml ou json.
     *
     *************************/
    function getPlacePhotos($place_id)
    {

            $params = array();
            $params['type'] = $this->type;

            //Basic Authentication
            $call_method = "places/".$place_id."/photos";
            $return = $this->apontadorChamaApi("GET", $call_method, $params);
            //echo $return;

            $aRetorno = json_decode($return);
            //var_dump($aRetorno);
            $photos = array();
            foreach($aRetorno->place->photos as $k=>$photo){
                $photos[$k] = $photo;
            }

            return $photos;
    }

    /*************************
     *
     * Place Reviews
     *
     * PLACEID	Obrigatório	 Identificador único do local (obtido sempre que um local é retornado por outros métodos da API).
     * Obs.: Este parâmetro faz parte da chamada (URL).
     * page	Opcional, default=1	 Número da página atual (começando no 1). O primeiro resultado retornado é o "page*limit-ésimo".
     * limit	Opcional, default=10	 Número máximo de resultados por página.
     * type	Opcional, default=xml	 Formato de retorno. Pode ser xml ou json. *
     *
     *************************/
    function getPlaceReviews($place_id, $page, $limit)
    {
            $params = array();
            $params['type'] = $this->type;
            if($page != "")$params['page'] = $page;
            if($limit != "")$params['limit'] = $limit;

            //Basic Authentication
            $call_method = "places/".$place_id."/reviews";
            $return = $this->apontadorChamaApi("GET", $call_method, $params);
            //echo $return;

            $aRetorno = json_decode($return);
            $reviews = array();
            foreach($aRetorno->place->reviews as $k=>$review){
                $review = $review->review;
                $reviews[$k]['id'] = $review->id;
                $reviews[$k]['rating'] = $review->rating;
                $reviews[$k]['content'] = $review->content;
                $reviews[$k]['timestamp'] = $review->created->timestamp;
                $reviews[$k]['user_id'] = $review->created->user->id;
                $reviews[$k]['user'] = $review->created->user->name;
            }

            return $reviews;
    }



    /*************************
     *
     * UserInfo
     *
     * PLACEID	Obrigatório	 Identificador único do local (obtido sempre que um local é retornado por outros métodos da API).
     * Obs.: Este parâmetro faz parte da chamada (URL).
     * page	Opcional, default=1	 Número da página atual (começando no 1). O primeiro resultado retornado é o "page*limit-ésimo".
     * limit	Opcional, default=10	 Número máximo de resultados por página.
     * type	Opcional, default=xml	 Formato de retorno. Pode ser xml ou json. *
     *
     *************************/
    function getUserInfo($user_id)
    {
            

            $params = array();
            $params['type'] = $this->type;

            if($user_id == "")
            {
                //OAuth
                $call_method = "users/self";
                $usr_info = $this->apontadorChamaApi("GET", $call_method, $params, $this->oauth_token, $this->oauth_token_secret);
            }
            else
            {
                $call_method = "users/".$user_id;
                $usr_info = $this->apontadorChamaApi("GET", $call_method, $params);
            }

            //adiciona o usuario na tb upload_foto a fim de sabermos qtas fotos ele enviou usando o upload_multiplo
            //var_dump($usr_info);
            $usr_info = json_decode($usr_info);
            $usr_info = $usr_info->user;
            $user_info['id'] = $usr_info->id;
            $user_info['name'] = $usr_info->name;
            $user_info['birthday'] = $usr_info->birthday;
            $user_info['photo_url'] = $usr_info->photo_url;
            $user_info['photo_medium_url'] = $usr_info->photo_medium_url;
            $user_info['photo_small_url'] = $usr_info->photo_small_url;
            $user_info['total_places'] =  $usr_info->stats->places;
            $user_info['total_photos'] = $usr_info->stats->photos;
            $user_info['total_reviews'] = $usr_info->stats->reviews;
    
            return $user_info;
    }


    function getUserReviews($user_id, $page, $limit)
    {
            
            $params = array();
            $params['type'] = $this->type;
            if($page != "")$params['page'] = $page;
            if($limit != "")$params['limit'] = $limit;

            if($user_id == "")
            {
                //OAuth
                $call_method = "users/self/reviews";
                $usr_reviews = $this->apontadorChamaApi("GET", $call_method, $params, $this->oauth_token, $this->oauth_token_secret);
            }
            else
            {
                $call_method = "users/".$user_id."/reviews";
                $usr_reviews = $this->apontadorChamaApi("GET", $call_method, $params);
            }


            //echo $usr_reviews;
            $usr_reviews = json_decode($usr_reviews);
            $user_reviews = array();
            //<result_count>10</result_count>
            //<current_page>1</current_page>
            if(intval($usr_reviews->user->result_count) > 0){
                foreach($usr_reviews->user->reviews as $k=>$review){
                    $review = $review->review;
                    $user_reviews[$k]['lbsid'] = $review->place->id;
                    $user_reviews[$k]['name'] = $review->place->name;
                    $user_reviews[$k]['id'] = $review->id;
                    $user_reviews[$k]['rating'] = $review->rating;
                    $user_reviews[$k]['content'] = $review->content;
                    $user_reviews[$k]['timestamp'] = $review->created->timestamp;
                    $user_reviews[$k]['user_id'] = $review->created->user->id;
                    $user_reviews[$k]['user'] = $review->created->user->name;
                    $user_reviews[$k]['user_photo'] = $review->created->user->photo_url;
                }
            }

            return $user_reviews;
    }


    function getUserPlaces($user_id, $page, $limit)
    {
            
            $params = array();
            $params['type'] = $this->type;
            if($page != "")$params['page'] = $page;
            if($limit != "")$params['limit'] = $limit;

            if($user_id == "")
            {
                //OAuth
                $call_method = "users/self/places";
                $usr_places = $this->apontadorChamaApi("GET", $call_method, $params, $this->oauth_token, $this->oauth_token_secret);
            }
            else
            {
                $call_method = "users/".$user_id."/places";
                $usr_places = $this->apontadorChamaApi("GET", $call_method, $params);
            }

            $usr_places = json_decode($usr_places);
            $user_places = array();
            //var_dump($aRetorno);
            if(intval($usr_places->user->result_count) > 0){
                foreach($usr_places->user->places as $k=>$place){
                    $place = $place->place;
                    $user_places[$k]['lbsid'] = $place->id;
                    $user_places[$k]['name'] = $place->name;
                    $user_places[$k]['category'] = $place->category->name;
                    $user_places[$k]['category_id'] = $place->category->id;
                    $user_places[$k]['subcategory'] = $place->category->subcategory->name;
                    $user_places[$k]['subcategory_id'] = $place->category->subcategory->id;
                    $user_places[$k]['average_rating'] = $place->average_rating;
                    $user_places[$k]['review_count'] = $place->review_count;
                    $user_places[$k]['lat'] = $place->point->lat;
                    $user_places[$k]['lng'] = $place->point->lng;
                    $user_places[$k]['apontador_link'] = $place->main_url;
                    $user_places[$k]['place_link'] = $place->other_url;
                    $user_places[$k]['place_icon'] = $place->icon_url;
                    $user_places[$k]['address'] = $place->address->street . ' ' . $place->address->number;
                    $user_places[$k]['complement'] = $place->address->complement;
                    $user_places[$k]['zipcode'] = $place->address->zipcode;
                    $user_places[$k]['district'] = $place->address->district;
                    $user_places[$k]['city'] = $place->address->city->name . ' - ' . $place->address->city->state;
                    $user_places[$k]['country'] = $place->address->city->country;
                }
            }

            return $user_places;
    }


    function searchUserByName($name, $limit, $user_id, $page)
    {

            $params = array();
            $params['type'] = $this->type;
            if($page != "")$params['page'] = $page;
            if($limit != "")$params['limit'] = $limit;
            if($name != "")$params['name'] = urlencode($name);

            //Basic Authentication
            $call_method = "search/users/byname";
            $return = $this->apontadorChamaApi("GET", $call_method, $params);
            //echo $return;

            $aRetorno = json_decode($return);
            $users = array();
            //var_dump($aRetorno);
            if(intval($aRetorno->search->result_count) > 0){
                foreach($aRetorno->search->users as $k=>$user){
                    $user = $user->user;
                    $users[$k]['id'] = $user->id;
                    $users[$k]['name'] = $user->name;
                    $users[$k]['photo_url'] = $user->photo_url;
                    $users[$k]['photo_medium_url'] = $user->photo_medium_url;
                    $users[$k]['photo_small_url'] = $user->photo_small_url;
                    $users[$k]['total_places'] = $user->stats->places;
                    $users[$k]['total_photos'] = $user->stats->photos;
                    $users[$k]['total_reviews'] = $user->stats->reviews;
                }
            }

            return $users;
    }

    function searchUserByAddress($country, $state, $city, $limit, $user_id, $page)
    {

            $params = array();
            $params['type'] = $this->type;
            if($page != "")$params['page'] = $page;
            if($limit != "")$params['limit'] = $limit;
            if($country != "")$params['country'] = $country;
            if($state != "")$params['state'] = $state;
            if($city != "")$params['city'] = $city;
            //var_dump($params);
            
            //Basic Authentication
            $call_method = "search/users/byaddress";
            $return = $this->apontadorChamaApi("GET", $call_method, $params);
            //echo $return;

            $aRetorno = json_decode($return);
            $users = array();
            //var_dump($aRetorno);
            if(intval($aRetorno->search->result_count) > 0){
                foreach($aRetorno->search->users as $k=>$user){
                    $user = $user->user;
                    $users[$k]['id'] = $user->id;
                    $users[$k]['name'] = $user->name;
                    $users[$k]['photo_url'] = $user->photo_url;
                    $users[$k]['photo_medium_url'] = $user->photo_medium_url;
                    $users[$k]['photo_small_url'] = $user->photo_small_url;
                    $users[$k]['total_places'] = $user->stats->places;
                    $users[$k]['total_photos'] = $user->stats->photos;
                    $users[$k]['total_reviews'] = $user->stats->reviews;
                }
            }

            return $users;
    }


    function doUserCheckins($place_id)
    {
            $params = array();
            $params['type'] = $this->type;
            if($place_id != "")$params['place_id'] = $place_id;

            //OAuth
            $call_method = "users/self/visits";
            $checkin = $this->apontadorChamaApi("PUT", $call_method, $params, $this->oauth_token, $this->oauth_token_secret);

            //echo $usr_checkins;

            $checkin = json_decode($checkin);
            $checkin = $checkin->visit;
            $user_checkin['lbsid'] = $checkin->place->id;
            $user_checkin['name'] = $checkin->place->name;
            $user_checkin['date'] = $checkin->date;

            return $user_checkin;

    }

    function getUserCheckins($user_id, $page, $limit)
    {
            $params = array();
            $params['type'] = $this->type;
            if($page != "")$params['page'] = $page;
            if($limit != "")$params['limit'] = $limit;

            if($user_id == "")
            {
                //OAuth
                $call_method = "users/self/visits";
                $usr_checkins = $this->apontadorChamaApi("GET", $call_method, $params, $this->oauth_token, $this->oauth_token_secret);
            }
            else
            {
                $call_method = "users/".$user_id."/visits";
                $usr_checkins = $this->apontadorChamaApi("GET", $call_method, $params);
            }


            //echo $usr_checkins;
            $usr_checkins = json_decode($usr_checkins);
            $user_checkins = array();
            //<result_count>10</result_count>
            //<current_page>1</current_page>
            foreach($usr_checkins->visits as $k=>$checkin){
                    $checkin = $checkin->visit;
                    $user_checkins[$k]['lbsid'] = $checkin->place->id;
                    $user_checkins[$k]['name'] = $checkin->place->name;
                    $user_checkins[$k]['date'] = $checkin->date;

            }

            return $user_checkins;

    }

    function getPlaceCheckins($place_id, $page, $limit)
    {
            $params = array();
            $params['type'] = $this->type;
            if($page != "")$params['page'] = $page;
            if($limit != "")$params['limit'] = $limit;

            //Basic Authentication
            $call_method = "places/".$place_id."/visitors";
            $return = $this->apontadorChamaApi("GET", $call_method, $params);
            //echo $return;

            $aRetorno = json_decode($return);
            $user_checkins = array();
            foreach($aRetorno->visitors as $k=>$visitor){
                $checkin = $visitor->visitor;
                $user_checkins[$k]['last_visit'] = $checkin->last_visit;
                $user_checkins[$k]['total_visits'] = $checkin->visits;
                $user_checkins[$k]['user_id'] = $checkin->user->id;
                $user_checkins[$k]['user'] = $checkin->user->name;
                $user_checkins[$k]['user_photo'] = $checkin->user->photo_url;
                $user_checkins[$k]['user_photo_medium'] = $checkin->user->photo_medium_url;
                $user_checkins[$k]['user_photo_small'] = $checkin->user->photo_small_url;
            }

            return $user_checkins;

    }

    function doPlaceThumbsUp($place_id)
    {
            $params = array();
            $params['type'] = $this->type;
            if($place_id != "")$params['place_id'] = $place_id;

            //OAuth
            $call_method = "places/".$place_id."/voteup";
            $vote = $this->apontadorChamaApi("PUT", $call_method, $params, $this->oauth_token, $this->oauth_token_secret);

            $vote = json_decode($vote);
            $vote = $vote->place;
            $place_stats['thumbs_total'] = $vote->thumbs->total;
            $place_stats['thumbs_up'] = $vote->thumbs->up;

            return $place_stats;

    }

    function doPlaceThumbsDown($place_id)
    {
            $params = array();
            $params['type'] = $this->type;
            if($place_id != "")$params['place_id'] = $place_id;

            //OAuth
            $call_method = "places/".$place_id."/votedown";
            $vote = $this->apontadorChamaApi("PUT", $call_method, $params, $this->oauth_token, $this->oauth_token_secret);

            $vote = json_decode($vote);
            $vote = $vote->place;
            $place_stats['thumbs_total'] = $vote->thumbs->total;
            $place_stats['thumbs_up'] = $vote->thumbs->up;

            return $place_stats;

    }


    function doPlaceReview($place_id, $rating, $content)
    {
            $params = array();
            $params['type'] = $this->type;
            $params['rating'] = $rating;
            $params['content'] = $content;

            //OAuth
            $call_method = "places/".$place_id."/reviews/new";
            $aux_review = $this->apontadorChamaApi("PUT", $call_method, $params, $this->oauth_token, $this->oauth_token_secret);

            $aux_review = json_decode($aux_review);
            foreach($aux_review->place->reviews as $k=>$review){
                $review = $review->review;
                $place_review['id'] = $review->id;
                $place_review['rating'] = $review->rating;
                $place_review['content'] = $review->content;
                $place_review['timestamp'] = $review->created->timestamp;
                $place_review['user_id'] = $review->created->user->id;
                $place_review['user'] = $review->created->user->name;
            }

            return $place_review;

    }

    function addNewPlace($name, $address_street, $address_number,
            $address_complement, $address_district, $address_city_name, $address_city_state,
            $address_city_country, $category_id, $subcategory_id, $point_lat, $point_lng,
            $phone_country, $phone_area, $phone_number, $description, $tags, $icon_url, $other_url)
    {

            $params = array();
            $params['type'] = $this->type;
            $params['name'] = urlencode($name);
            $params['address_street'] = urlencode($address_street);
            $params['address_number'] = urlencode($address_number);
            $params['address_city_name'] = urlencode($address_city_name);
            $params['address_city_state'] = urlencode($address_city_state);
            $params['category_id'] = urlencode($category_id);
            $params['subcategory_id'] = urlencode($subcategory_id);
            if($address_district != "")$params['address_district'] = urlencode($address_district);
            if($address_complement != "")$params['address_complement'] = urlencode($address_complement);
            if($address_city_country != "")$params['address_city_country'] = $address_city_country;
            if($point_lat != "")$params['point_lat'] = $point_lat;
            if($point_lng != "")$params['point_lng'] = $point_lng;
            if($phone_country != "")$params['phone_country'] = $phone_country;
            if($phone_area != "")$params['phone_area'] = $phone_area;
            if($phone_number != "")$params['phone_number'] = $phone_number;
            if($description != "")$params['description'] = urlencode($description);
            if($tags != "")$params['tags'] = urlencode($tags);
            if($icon_url != "")$params['icon_url'] = urlencode($icon_url);
            if($other_url != "")$params['other_url'] = urlencode($other_url);

            //OAuth
            $call_method = "places/new";
            $return = $this->apontadorChamaApi("PUT", $call_method, $params, $this->oauth_token, $this->oauth_token_secret);

            //echo $usr_checkins;

            $aRetorno = json_decode($return);
            //var_dump($aRetorno);
            $aux_place = $aRetorno->place;
            $place['lbsid'] = $aux_place->id;
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

            return $place;
    }
    
    function sendPhoto($place_id, $data)
    {
            $params = array();
            $params['type'] = $this->type;
            $params['place_id'] = $place_id;
            $params['content'] = $data;

            //OAuth
            $call_method = "places/".$place_id."/photos/new";
            $photo = $this->apontadorChamaApi("PUT", $call_method, $params, $this->oauth_token, $this->oauth_token_secret);
            $photo = json_decode($photo);
            //echo  var_dump($photo);

            $place_photos = array();
            foreach($photo->place->photos as $k=>$place_photo){
                    $place_photos[$k] = $place_photo;
            }
            
            return $place_photos;

    }
}
?>