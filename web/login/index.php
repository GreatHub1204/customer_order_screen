<?php

require_once ('../function/Login.php');
$Login = new Login();

session_start();
if (isset($_SESSION['login_id'])) {
    $user = $Login->getUser($_SESSION['login_id']);
    $url = $Login->getRedirectUrl($user['ユーザー種別']);
    header("Location: {$url}");
    exit();
}

// セッションデータの破棄: 小林修正20230709
session_destroy();
?>

<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <link rel="icon" href="/assets/img/favicon.ico" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EOBシステム ログイン</title>
    <meta name="description" content="EOBシステム ログイン" />
    <link href="/assets/css/destyle.min.css" rel="stylesheet" />
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="/assets/css/login.css" rel="stylesheet" />
</head>
<body>
<div id="wrapper" class="text-center m-5">
    <header id="header" class="">
        <h1 class="mb-5">EOBシステム ログイン</h1>
    </header>
    <article id="main" class="pb-5">
        <form action="login.php" method="POST" class="" id="form-login">
            <div class="input-wrap mb-5">
                <div class="mb-3 row">
                    <label for="input-login-id" class="col-sm-4 col-form-label text-nowrap">ログインID</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control text-start" id="input-login-id" name="login_id" maxlength="20">
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="input-password" class="col-sm-4 col-form-label text-nowrap">パスワード</label>
                    <div class="col-sm-8">
                        <input type="password" class="form-control text-start" id="input-password" name="password" maxlength="20">
                    </div>
                </div>
            </div>
            <button class="btn btn-success ps-5 pe-5" id="btn-login" onclick="LoginCheck(); return false;">ログイン</button>
        </form>
    </article>
</div>
<script src="/assets/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/login.js"></script>
</body>
</html>
