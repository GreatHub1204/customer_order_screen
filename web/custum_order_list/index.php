<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>客注発注履歴確認画面</title>
    <link rel="icon" href="/assets/img/favicon.ico" />
    <link rel="stylesheet" href="/../assets/css/style.css">
    <link rel="stylesheet" href="/../assets/css/responsive.css">
</head>

<body>
    <!-- header section -->
    <header>
        <div class="container">
            <div class="store-info">
                <P>[店舗] <span>999: 渋谷店</span> 【担当】<span>山田太郎</span> </P>
            </div>
            <div class="order-screen">
                <p>客注発注履歴確認画面</p>
            </div>
        </div>
    </header>
    <!-- main section -->
    <main>
        <section class="tools">
            <div class="container flex vertical-align add-style">
                <div class="date-type">
                    <div>
                        <input type="radio" id = "register-date" name="select-date-type">
                        <label for="register-date">登録日</label>
                    </div>
                    <div>
                        <input type="radio" id="ordered-date" name="select-date-type">
                        <label for="ordered-date">発注日</label>
                    </div>
                    <div>
                        <input type="radio" id="delivery-date" name="select-date-type">
                        <label for="delivery-date">納品日</label>
                    </div>
                </div>
                <div class="period flex">
                    <input type="date" class="period-input-style">
                    <p>~</p>
                    <input type="date" class="period-input-style">
                </div>
                <button class="display-button">表 示</button>
                <div class="QRcode-search flex">
                    <label for="JAN">JAN コード</label>
                    <input type="number" class="input-QRcode-style">
                </div>
                <button class="search-button">検 索</button>
            </div>
        </section>
        <section class="order-history">
            <div class="container">
                <div class="list-head">
                    <div class="list-head-row flex">
                        <div class="column-1">
                            <p>商品コード</p>
                        </div>
                        <div class="column-2">
                            <p>商品名</p>
                        </div>
                        <div class="column-3">
                            <p>発注数</p>
                        </div>
                        <div class="column-4">
                            <p>登録日</p>
                        </div>
                        <div class="column-5">
                            <p>発注日</p>
                        </div>
                        <div class="column-6">
                            <p>納品日</p>
                        </div>
                        <div class="column-7">
                            <p>入力者</p>
                        </div>
                        <div class="column-8">
                            <p>処理</p>
                        </div>
                    </div>
                </div>
                <div class="list-body">
                    <div>
                        <div class="list-body-row flex">
                            <div class="column-1 add-border">
                                <p class="font2">4901234567890</p>
                            </div>
                            <div class="column-2 add-border">
                                <p class="font2">あいうえおかきくけこさしすせそたちってと</p>
                            </div>
                            <div class="column-3 add-border">
                                <p class="font2">20</p>
                            </div>
                            <div class="column-4 add-border">
                                <p class="font2">12/10</p>
                            </div>
                            <div class="column-5 add-border">
                                <p class="font2">12/10</p>
                            </div>
                            <div class="column-6 add-border">
                                <p class="font2">12/10</p>
                            </div>
                            <div class="column-7 add-border">
                                <p class="font2">テスト太郎</p>
                            </div>
                            <div class="column-8 add-border">
                                <div>
                                    <button class="triplets-button list-correction-button">
                                        <p class="font2">修正</p>
                                    </button>
                                    <button class="triplets-button list-remove-button">
                                        <p class="font2">削除</p>
                                    </button>
                                    <button class="triplets-button list-delivery-button">
                                        <p class="font2">引渡</p>
                                    </button>
                                </div>
                                <P style="display: none;">引渡済</P>
                            </div>
                        </div>
                        <div class="list-comment vertical-align flex">
                            <p>注文太郎さん。電話090-123-1234入荷したら連絡してください。連終可能時間は午前中。</p>
                        </div>
                    </div>
                    <div>
                        <div class="list-body-row flex">
                            <div class="column-1 add-border">
                                <p class="font2">4901234567890</p>
                            </div>
                            <div class="column-2 add-border">
                                <p class="font2">あいうえおかきくけこさしすせそたちってと</p>
                            </div>
                            <div class="column-3 add-border">
                                <p class="font2">20</p>
                            </div>
                            <div class="column-4 add-border">
                                <p class="font2">12/10</p>
                            </div>
                            <div class="column-5 add-border">
                                <p class="font2">12/10</p>
                            </div>
                            <div class="column-6 add-border">
                                <p class="font2">12/10</p>
                            </div>
                            <div class="column-7 add-border">
                                <p class="font2">テスト太郎</p>
                            </div>
                            <div class="column-8 add-border">
                                <div style="display: none;">
                                    <button class="triplets-button list-correction-button">
                                        <p class="font2">修正</p>
                                    </button>
                                    <button class="triplets-button list-remove-button">
                                        <p class="font2">削除</p>
                                    </button>
                                    <button class="triplets-button list-delivery-button">
                                        <p class="font2">引渡</p>
                                    </button>
                                </div>
                                <P class="delivery-result" style="display: block;">引渡済</P>
                            </div>
                        </div>
                        <div class="list-comment vertical-align flex">
                            <p>注文太郎さん。電話090-123-1234入荷したら連絡してください。連終可能時間は午前中。</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <!-- footer section -->
    <footer>

    </footer>
<script src="/../assets/script/input.js"></script>
</body>

</html>