<?php

//https://console.developers.google.com/   获取key和secret
function get_oauth_url($appKey){
    $callBackUrl = 'call_back_url';//回调地址
    $params = array(
        'scope'         =>	'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
        'state'         =>	'email profile',
        'redirect_uri'  =>  $callBackUrl,
        'response_type' =>	'code',
        'client_id'     =>	$appKey
    );
    return 'https://accounts.google.com/o/oauth2/auth?'.http_build_query($params);//此地址重定向到google，登录成功后会重定向到$callBackUrl，并携带code参数
}

//第三方登录成功后回调
function call_back(){
    $code = $_REQUEST['code'];//第三方登录成功后会带此参数
    $appKey = 'app_key';
    $appSecret = 'app_secret';
    $callBackUrl = 'call_back_url';//回调地址
    $fields = array(
        'code'          =>  $code,
        'client_id'     =>	$appKey,
        'client_secret' =>	$appSecret,
        'redirect_uri'  =>  $callBackUrl,
        'grant_type'    => 'authorization_code'
    );
    $url_access_token = 'https://www.googleapis.com/oauth2/v4/token';
    $token = curl_request($url_access_token, 'post', $fields);

    if (isset($token['error'])) {
        $info['error'] = $token['error_description'];
        return $info;
    }
    $graph_url = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token='. $token['access_token'];
    $infos = curl_request($graph_url);
    return $infos;
}

function curl_request($api, $method = 'GET', $params = array(), $headers = [])
{
    $curl = curl_init();

    switch (strtoupper($method)) {
        case 'GET' :
            if (!empty($params)) {
                $api .= (strpos($api, '?') ? '&' : '?') . http_build_query($params);
            }
            curl_setopt($curl, CURLOPT_HTTPGET, TRUE);
            break;
        case 'POST' :
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

            break;
        case 'PUT' :
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
            break;
        case 'DELETE' :
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
            break;
    }

    curl_setopt($curl, CURLOPT_URL, $api);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($curl);
    if ($response === FALSE) {
        $error = curl_error($curl);
        curl_close($curl);
        return FALSE;
    }else{
        $response = json_decode($response, true);
    }
    curl_close($curl);

    return $response;
}

