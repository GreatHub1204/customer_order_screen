<?php

require_once ('../function/User.php');
$User = new User();

// ログインチェック
if (!$login_id = $User->getSession()) {
    $User->redirectLogin();
    exit();
}
// ユーザー情報取得
$user = $User->getUserByLoginId($login_id);

$error_flg = true;
if (isset($_GET['shop_code']) && isset($_GET['item_code']) && isset($_GET['date']) && isset($_GET['RecID']) && isset($_GET['recommend_title'])) {
    $shop_code = $_GET['shop_code'];
    $item_code = $_GET['item_code'];
    $date = $_GET['date'];
    $RecID = $_GET['RecID'];
    $recommend_title = $_GET['recommend_title'];
    $error_flg = false;
}

?>

<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <link rel="icon" href="/web/assets/img/favicon.ico" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>過剰発注</title>
    <meta name="description" content="過剰発注の調整画面" />
    <link href="/web/assets/css/destyle.min.css" rel="stylesheet" />
    <link href="/web/assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="/web/assets/css/common.css" rel="stylesheet" />
</head>
<body>
<div id="wrapper">

    <?php if ($error_flg) : ?>
    <div class="text-center p-5">
        <p>問題が発生しました。再読み込みを行ってください。</p>
        <p>それでも問題が解決しない場合は管理者へお問い合わせください。</p>
    </div>
    <?php else : ?>
    <div class="text-center p-5">
        <p>店舗コード：<?php echo $shop_code;?></p>
        <p>商品コード：<?php echo $item_code;?></p>
        <p>日付：<?php echo $date;?></p>
        <p>RecID：<?php echo $RecID;?></p>
        <p>レコメンド種別名称：<?php echo $recommend_title;?></p>
        <div><img src="../assets/img/bg-tmp-a.png" alt=""></div>
    </div>
    <?php endif; ?>

</div>
<script src="/web/assets/js/bootstrap.bundle.min.js"></script>
<script src="/web/assets/js/common.js"></script>
</body>
</html>
