<?php
require_once(__DIR__ . '/../function/User.php');
$User = new User();
// ログインチェック
if (!$login_id = $User->getSession()) {
    $User->redirectLogin();
    exit();
}

// ユーザー情報取得
$user = $User->getUserByLoginId($login_id);
$user_categories = $User->getLargeCategory($login_id);
$user_id = $login_id;
$user_name = $user['担当者名'];
$store_id = $user['店舗ID'];
$store_name = $user['店舗名'];

$login_info = '【店舗】' . $store_id . '：' . $store_name . '　【担当】' . $user_name;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>客注発注画面</title>
    <link rel="icon" href="/assets/img/favicon.ico" />
    <link rel="stylesheet" href="/../assets/css/style.css">
    <link rel="stylesheet" href="/../assets/css/responsive.css">
</head>

<body>
    <header>
        <div class="container">

            <div class="store-info">
                <P><?= $login_info ?></P>
            </div>
            <div class="order-screen">
                <p>客注発注画面</p>
            </div>
        </div>
    </header>
    <main>
        <section class="register-info">
            <div class="container">
                <div class="origin-info">
                    <p>登録日: 2023年 8月 22日 08時 01分 テスト太郎 </p>
                </div>
                <div class="changed-info">
                    <p>更新日: 2023年 8月 22日 08時 02分 テスト花子 </p>
                </div>
            </div>
        </section>
        <section class="order-input">
            <div class="container">
                <div class="QRcode">
                    <label for="QRcode">バーコードスキャン</label>
                    <input type="text" id="code_input" class="input-style">
                </div>
                <div class="order-amount">
                    <label for="order-amount">発注数</label>
                    <input type="number" min="1" class="input-style">
                </div>
                <div class="delivery-date">
                    <label for="delivery-date">納品日</label>
                    <input type="date" onchange="run()" id="delivery" class="input-style">
                </div>
                <div class="handover-date">
                    <label for="handover-date">引渡し日</label>
                    <input type="text" id="srt" disabled class="input-style add-handover-padding-right">
                </div>
                <div class="order-date">
                    <label for="order-date">発注日</label>
                    <input type="date" class="input-style">
                </div>
                <div class="order-memo none" id="memo_display_show">
                    <label for="order-memo">メモ</label>
                    <textarea name="memo" id="myTextarea" placeholder="全角70文字まで（半角では１４０文字）"></textarea>
                </div>
                <div class="order-memo add-memo-padding-right" id="memo_display_none">
                    <a href="#" id="memo-event">メモの追加</a>
                </div>
            </div>
        </section>
        <section class="display-info">
            <div class="container">
                <div class="manufacture-name">
                    <p>メーカー名 :</p>
                    <p>株式会社</p>
                </div>
            </div>
            <div class="container">
                <div class="product-name">
                    <p>商品名 :</p>
                    <p>三菱パジェロ</p>
                </div>
            </div>
            <div class="container">
                <div class="standard">
                    <p>規格 :</p>
                    <p>JIS Q9000：2015</p>
                </div>
            </div>
            <div class="container">
                <div class="order-unit">
                    <p>発注単位 :</p>
                    <p>1</p>
                </div>
            </div>
            <div class="container">
                <div class="quantity">
                    <p>入数 :</p>
                    <p>1</p>
                </div>
            </div>
            <div class="container">
                <div class="supplier-name">
                    <p>仕入先名 :</p>
                    <p>株式会社</p>
                </div>
            </div>
        </section>
        <section class="register-button">
            <button class="register-button-element">登 録</button>
        </section>
        <form action="" method="post" id="custum_order">
            <section class="overlay overlay-hidden">

            </section>
            <section class="modal modal-hidden">
                <div class="modal-close-button">
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-text">
                    <p>登録しますよろしいですか？</p>
                </div>
                <div class="modal-button-group">
                    <button class="modal-button-yes">はい</button>
                    <button type="button" class="modal-button-no">いいえ</button>
                </div>
            </section>
        </form>
    </main>
    <footer></footer>
    <script src="/../assets/js/input.js"></script>
</body>

</html>