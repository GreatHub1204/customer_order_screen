<?php

require_once ('../function/RecommendSupported.php');
$Recommend_supported = new RecommendSupported();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
header("Content-type: application/json; charset=UTF-8");

$input_json = file_get_contents('php://input');
$post = json_decode( $input_json, true );

$result['success'] = false;
$result['message'] = '他の端末で既に操作されています。<br>一度画面を更新してください。';

// 対象のレコードが存在しないことを確認
if ($Recommend_supported->searchRecord($post['store_id'], $post['uriba_id'], $post['dai'], $post['dan'], $post['ichi'], $post['item_id'], $post['RecID']) === 0) {
    // レコメンド対応レコードの作成
    try {
        // insert
        $Recommend_supported->insertRecord($post['store_id'], $post['uriba_id'], $post['dai'], $post['dan'], $post['ichi'], $post['item_id'], $post['RecID'], $post['status'], $post['uridai']);
        $result['message'] = '対応状況の更新が完了しました。';
        $result['success'] = true;
    } catch (\Exception $e) {
        $result['success'] = false;
        $result['message'] = '対応状況更新時にエラーが発生しました。<br>管理者へ連絡してください。';
    }
}

echo json_encode($result);
