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
$user_id = $login_id;
$user_name = $user['担当者名'];
$store_id = $user['店舗ID'];
$store_name = $user['店舗名'];
$large_cat = $User->getLargeCategory($login_id);


$RecID = $_GET['recommend-kind'] ?? '01'; // レコメンドID
$now_page_num = $_GET['page'] ?? 1; // 現在のページ数

require_once ('../function/RecommendMaster.php');
$Recommend_master = new RecommendMaster();
$recommend_master_list = $Recommend_master->getList();

require_once ('../function/RecommendIndex.php');
$Recommend_index = new RecommendIndex($RecID, $store_id, $large_cat, $now_page_num);
$recommend_list = $Recommend_index->getRecommendList();
$recommend_list = $Recommend_index->convertRecommendJson($recommend_list);

$supported_number = $Recommend_index->getSupportedNumber($store_id, $RecID, $large_cat);
$item_count = $Recommend_index->getRecommendCountAll($RecID, $store_id, $large_cat);
$rows = $Recommend_index->getRows();

require_once ('../function/Baiba.php');
$Baiba = new Baiba();
$baisho_names = $Baiba->getBaishoNames($rows);
$baichu_names = $Baiba->getBaichuNames($rows);
$baidai_names = $Baiba->getBaidaiNames($rows);

require_once ('../function/RecommendSupported.php');
$Recommend_supported = new RecommendSupported();

$today = new \DateTime('now');

?>

<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <link rel="icon" href="/web/assets/img/favicon.ico" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>レコメンド一覧</title>
    <meta name="description" content="レコメンド一覧" />
    <link href="/web/assets/css/destyle.min.css" rel="stylesheet" />
    <link href="/web/assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="/web/assets/css/common.css" rel="stylesheet" />
    <link href="/web/assets/css/recommend.css" rel="stylesheet" />
    <?php if($RecID !== $Recommend_master::ID_EXCESSIVE_ORDERS):?>
    <link href="/web/assets/css/modal_stock_adjustment.css" rel="stylesheet" />
    <?php endif;?>
</head>
<body>
<div id="wrapper" class="d-flex flex-column justify-content-between">
    <div class="flex-grow-1 h-100 overflow-scroll">
        <header id="header" class="">
            <div class="help-wrap d-flex justify-content-end">
                <button type="button" class="btn border ps-3 pe-3" data-bs-toggle="modal" data-bs-target="#help-modal">ヘルプ</button>
                <div class="modal fade text-dark" id="help-modal" tabindex="-1" aria-hidden="true" style="display: none;">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">レコメンドヘルプ</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <table class="fs-4 table table-bordered table-striped">
                                    <thead class="bg-primary text-white">
                                    <tr>
                                        <th>ID</th>
                                        <th>タイトル</th>
                                        <th>アクション</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach($recommend_master_list as $help_key => $help_item) : ?>
                                        <tr>
                                            <td><?php echo $help_item['RecID'];?></td>
                                            <td><?php echo $help_item['タイトル'];?></td>
                                            <td><?php echo $help_item['アクション'];?></td>
                                        </tr>
                                    <?php endforeach;?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-between p-3">
                <div class="left">
                    <h1 class="h1 fs-4 shop-name m-0"><?php echo $store_id;?></h1>
                    <p class="shop-user mb-2"><?php echo $user_id;?></p>
                    <h2 class="m-0">レコメンド一覧</h2>
                </div>
                <div class="right">
                    <div class="supported-wrap d-flex bg-warning mb-2 fs-5">
                        <div class="p-2 ps-5 pe-5">未対応数：<span id="unsupported-number"><?php echo $item_count - $supported_number;?></span></div>
                        <div class="p-2 ps-5 pe-5">対応数：<span id="supported-number"><?php echo $supported_number;?></span></div>
                    </div>
                    <p class="date text-center m-0"><?php echo $today->format('Y年m月d日');?></p>
                </div>
            </div>
        </header>

        <div class="recommend-filter-wrap p-3">
            <form class="filter-wrap d-flex align-items-center justify-content-between gap-4 p-1 ps-4" action="index.php" method="GET">
                <div class="recommend-item">
                    <p class="mb-1">レコメンド対象商品</p>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="only-unsupported-recommend" name="unsupported-recommend-flg">
                        <label class="form-check-label" for="only-unsupported-recommend">未対応品のみ表示</label>
                    </div>
                </div>
                <div class="">
                    <p class="m-0">レコメンド種別</p>
                    <div class="d-flex align-items-center">
                        <select class="form-select align-self-center text-nowrap p-2 pe-5" id="" name="recommend-kind">
                            <?php foreach($recommend_master_list as $rec_key => $rec_item) : ?>
                                <option value="<?php echo $rec_item['RecID'];?>" <?php if($RecID === $rec_item['RecID']){echo 'selected';}?>>
                                    <?php echo sprintf("%s : %s", $rec_item['RecID'], $rec_item['タイトル']);?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="btn-wrap align-self-center text-nowrap p-2">
                            <button class="btn btn-primary ps-5 pe-5">表示</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <article id="main" class="pb-5">
            <?php if(count($recommend_list) === 0): ?>
                <div class="fs-1 text-center p-5">該当するデータがありません。</div>
            <?php endif; ?>
            <?php $itemCnt = 1;?>
            <?php foreach($recommend_list as $recKey => $recommend) : ?>
                <section class="item-wrap p-3">
                    <h3 class="recommend-item-wrap-header p-2 m-0">
                        売場大：<?php echo $baidai_names[$recommend[0]['売場大ID']];?>　
                        売場中：<?php echo $baichu_names[sprintf("%s-%s", $recommend[0]['売場大ID'], $recommend[0]['売場中ID'])];?>　
                        売場小：<?php echo $baisho_names[sprintf("%s-%s-%s", $recommend[0]['売場大ID'], $recommend[0]['売場中ID'], $recommend[0]['売場小ID'])];?></h3>
                    <?php foreach($recommend as $itemKey => $item):?>
                        <div id="<?php echo $itemCnt;?>-wrap" class="p-4 border-bottom item-detail-wrap border-secondary<?php if(!is_null($item['対応フラグ'])){ echo ' bg-light-dark item-supported';} ?>">
                            <div class="d-flex justify-content-between">
                                <div class="item-recommend">
                                    <?php $key = array_search($item['RecID'], array_column($recommend_master_list, 'RecID'), true);?>
                                    <h4 class="text-primary"><?php echo $recommend_master_list[$key]['RecID']?> : <?php echo $recommend_master_list[$key]['タイトル'];?></h4>
                                    <p><?php echo $item['商品ID'];?></p>
                                </div>
                                <div class="item-detail text-end">
                                    <p class="fs-5 m-0"><?php echo $item['商品名'];?></p>
                                    <p class="fs-5 fw-bold mb-4">台：<?php echo $item['台'];?>、段：<?php echo $item['段'];?>、位置：<?php echo $item['位置'];?>，フェイス数：<?php echo $item['フェイス数'];?>、積上げ数：<?php echo $item['積上数'];?>、奥行き：<?php echo $item['奥行陳列数'];?></p>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between gap-5">
                                <div class="reccomend-data">
                                    <p class="fs-5 fw-bold m-0"><?php echo $item['レコメンド'] ?? '';?></p>
                                </div>
                                <div class="btn-wrap d-flex align-items-end gap-4">
                                    <?php
                                    $query_base = [
                                        'shop_code' => $store_id,
                                        'item_code' => $item['商品ID'],
                                        'date' => $today->format('Y年m月d日'),
                                        'RecID' => $RecID,
                                        'recommend_title' => $recommend_master_list[$key]['タイトル'],
                                    ];
                                    $query = http_build_query($query_base);
                                    ?>
                                    <?php if($RecID === $Recommend_master::ID_EXCESSIVE_ORDERS):?>
                                        <a class="btn btn-primary rounded-pill d-flex align-items-center text-nowrap me-5 ps-5 pe-5 fw-bold" href="over-ordering.php?<?php echo $query;?>" target="_blank">アクション</a>
                                    <?php else:?>
                                        <div class="btn btn-primary rounded-pill d-flex align-items-center text-nowrap me-5 ps-5 pe-5 fw-bold table-td-link"
                                             data-store-id="<?php echo $store_id;?>"
                                             data-item-id="<?php echo $item['商品ID'];?>"
                                             data-item-name="<?php echo $item['商品名'];?>"
                                             data-yesterday-stock="<?php echo $item['前日在庫'];?>"
                                             data-date="<?php echo $today->format('Y-m-d');?>"
                                             data-store-name="<?php echo $store_name;?>"
                                             data-user-id="<?php echo $user_id;?>"
                                             data-page="recommend"
                                        >アクション</div>
                                    <?php endif;?>
                                    <?php if((int)$item['対応フラグ'] === $Recommend_supported::SUPPORTED_FLG_COMPLETE) : ?>
                                        <button class="btn btn-danger rounded-pill disabled text-nowrap ps-5 pe-5 fw-bold" data-recommend-id="">完了</button>
                                    <?php elseif((int)$item['対応フラグ'] === $Recommend_supported::SUPPORTED_FLG_PENDING) :?>
                                        <button class="btn btn-success rounded-pill disabled text-nowrap ps-5 pe-5 fw-bold" data-recommend-id="">保留</button>
                                    <?php elseif((int)$item['対応フラグ'] === $Recommend_supported::SUPPORTED_FLG_NOT_AVAILABLE) :?>
                                        <button class="btn border border-dark rounded-pill disabled text-nowrap ps-5 pe-5 fw-bold" data-recommend-id="">不可</button>
                                    <?php else : ?>
                                        <button class="btn-show-status-update-modal btn btn-danger rounded-pill text-nowrap fw-bold ps-5 pe-5" id="btn-complete-<?php echo $itemCnt;?>-confirm" data-status="<?php echo $Recommend_supported::SUPPORTED_FLG_COMPLETE;?>" data-item-count="<?php echo $itemCnt;?>">完了</button>
                                        <button class="btn-show-status-update-modal btn btn-success rounded-pill text-nowrap fw-bold ps-5 pe-5" id="btn-pending-<?php echo $itemCnt;?>-confirm" data-status="<?php echo $Recommend_supported::SUPPORTED_FLG_PENDING;?>" data-item-count="<?php echo $itemCnt;?>">保留</button>
                                        <button class="btn-show-status-update-modal btn border border-dark rounded-pill text-nowrap fw-bold ps-5 pe-5" id="btn-not-available-<?php echo $itemCnt;?>-confirm" data-status="<?php echo $Recommend_supported::SUPPORTED_FLG_NOT_AVAILABLE;?>" data-item-count="<?php echo $itemCnt;?>">不可</button>
                                        <div id="update-data-<?php echo $itemCnt;?>" class="d-none"
                                             data-store-id="<?php echo $item['店舗ID'];?>"
                                             data-uriba-id="<?php echo $item['売場ID'];?>"
                                             data-dai="<?php echo $item['台'];?>"
                                             data-dan="<?php echo $item['段'];?>"
                                             data-ichi="<?php echo $item['位置'];?>"
                                             data-item-id="<?php echo $item['商品ID'];?>"
                                             data-Rec-ID="<?php echo $RecID;?>"
                                             data-status=""
                                             data-item-count="<?php echo $itemCnt;?>"
                                             data-uri-dai="<?php echo $recommend[0]['売場大ID'];?>"
                                        ></div>
                                    <?php endif;?>
                                </div>
                            </div>
                        </div>
                        <?php $itemCnt++;?>
                    <?php endforeach;?>
                </section>
            <?php endforeach; ?>
        </article>


        <?php
        // 在庫補正モーダル
        if ($RecID !== $Recommend_master::ID_EXCESSIVE_ORDERS) {
            include("../function/modal_stock_adjustment.php");
        }
        ?>

        <!-- status更新確認モーダル -->
        <div class="modal fade" id="update-confirm-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">確認画面</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="update-confirm-message">完了処理を実行します。<br>よろしいですか？</div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border border-secondary rounded-pill ps-5 pe-5" data-bs-dismiss="modal">閉じる</button>
                        <button id="btn-rec-change-status" class="btn-confirm-submit btn btn-warning rounded-pill text-nowrap fw-bold ps-5 pe-5">実行</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- status結果モーダル -->
        <div class="modal fade" id="modal-update-status" tabindex="-1" aria-labelledby="updateStatus" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="text-center p-5">
                            <p class="message fs-3" id="modal-update-status-message"></p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="pagination-wrap text-center">
            <?php
            $max_page = ceil($item_count / $Recommend_index::PAGE_VIEW); // 最大ページ数
            $fromRecord = ($now_page_num - 1) * $Recommend_index::PAGE_VIEW + 1;
            $toRecord = $now_page_num * $Recommend_index::PAGE_VIEW;
            if ((float)$now_page_num === $max_page && $item_count !== $Recommend_index::PAGE_VIEW * $max_page) {
                $toRecord = $item_count;
            }

            if ((int)$now_page_num === 1 || (int)$now_page_num === (int)$max_page) {
                $range = 4;
            } elseif ((int)$now_page_num === 2 || (int)$now_page_num === (int)$max_page - 1) {
                $range = 3;
            } else {
                $range = 2;
            }
            ?>

            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php if ($now_page_num >= 2){ ?>
                        <li class="page-item">
                            <a href="index.php?<?php echo http_build_query(['page' => $now_page_num - 1] + $_GET, '', '&amp;'); ?>" class="page-link">«</a>
                        </li>
                    <?php }else{?>
                        <li class="page-item disabled">
                            <a class="page-link">«</a>
                        </li>
                    <?php } ?>
                    <?php for ($i = 1; $i <= $max_page; $i++) : ?>
                        <?php if($i >= $now_page_num - $range && $i <= $now_page_num + $range) : ?>
                            <?php if($i == $now_page_num) : ?>
                                <li class="page-item active">
                                    <a href="index.php?<?php echo http_build_query(['page' => $i] + $_GET, '', '&amp;'); ?>" class="page-link"><?php echo $i; ?></a>
                                </li>
                            <?php else: ?>
                                <li class="page-item">
                                    <a href="index.php?<?php echo http_build_query(['page' => $i] + $_GET, '', '&amp;'); ?>" class="page-link"><?php echo $i; ?></a>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <?php if($now_page_num < $max_page) : ?>
                        <li class="page-item">
                            <a href="index.php?<?php echo http_build_query(['page' => $now_page_num + 1] + $_GET, '', '&amp;'); ?>" class="page-link">»</a>
                        </li>
                    <?php else : ?>
                        <li class="page-item disabled">
                            <a href="index.php?<?php echo http_build_query(['page' => $now_page_num + 1] + $_GET, '', '&amp;'); ?>" class="page-link">»</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </footer>
</div>
<script src="/web/assets/js/bootstrap.bundle.min.js"></script>
<script src="/web/assets/js/common.js"></script>
<script src="/web/assets/js/recommend.js"></script>
<?php if($RecID !== $Recommend_master::ID_EXCESSIVE_ORDERS):?>
<script src="/web/assets/js/modal_stock_adjustment.js"></script>
<?php endif;?>
</body>
</html>
