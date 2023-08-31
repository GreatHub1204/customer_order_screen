<?php

require_once ('../function/Login.php');
$Login = new Login();



$input_json = file_get_contents('php://input');
$post = json_decode( $input_json, true );

$response = ['message' => 'message', "status" => "error", "redirect_url" => ""];

// バリデーション
$validation = $Login->validate($post, true);
if(!$validation['success']) {
    $response['message'] = $validation["message"];
    echo json_encode($response);
    return;
}

// ログインIDとパスワード認証
$loginResult = $Login->loginCheck($post);
if(!$loginResult['success']) {
    $response['message'] = $loginResult["message"];
    echo json_encode($response);
    return;
}

$Login->setSession($post['login_id']);
$response['redirect_url'] = $Login->getRedirectUrl($loginResult['role']);

// 成功
$response['message'] = "ログインに成功しました。";
$response['status'] = "success";

echo json_encode($response);
return;
