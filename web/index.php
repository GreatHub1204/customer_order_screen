<?php

require_once (__DIR__.'/function/User.php');
$User = new User();

// ログインチェック
if (!$login_id = $User->getSession()) {
    $User->redirectLogin();
    exit();
}

// ユーザーデータ取得
$user_data = $User->getUserByLoginId($login_id);

$destinator_flag = $user_data['配信者'];
$user_id = $user_data['担当者ID'];
$user_name = $user_data['担当者名'];
$store_id = $user_data['店舗ID'];
$store_name = $user_data['店舗名'];

// 店舗・担当者情報の作成
$login_info = '【店舗】'.$store_id.'：'.$store_name.'　【担当】'.$user_name;

// // 配信先情報の取得
// require_once (__DIR__.'/function/Reception.php');
// $Reception = new Reception();
// $reception_list = $Reception->getReceptionIdByLoginId($login_id);

//iframe用のURL生成
$iframe_url = 'https://osirasev00-skg.bubbleapps.io?ats=.*(';
// $tmp = $reception_list;
// foreach($reception_list as $rec){
// 	$iframe_url.=$rec['お知らせ配信先ID'];
// 	if(next($tmp)){
// 		$iframe_url.='|';
// 	}
// }
$iframe_url.=')';

?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <link rel="icon" href="/assets/img/favicon.ico" />
  <title>CPM拡張　EOBシステム</title>
  <script type="module" crossorigin src="/assets/js/menu.js"></script>
  <link rel="stylesheet" href="/assets/css/menu.css">
</head>

<style>
  a:hover {
    color: blue;
    font-weight: bold;
  }
</style>


<body style="text-align: center;">


<table width="800px" height="100px" align="center" style="margin: auto;">

	<tr>
	<td align="left">
		<p style="text-align:left"><?php echo $login_info?></p>
	</td>
	<td align="right">
		<a href="logout.php" style="display: inline-block; width: 80px; padding: 10px 20px; border: 1px solid black; text-decoration: none; margin: 0 10px; color: #ffffff; border-radius: 10px; background-color: #dc143c;">ログアウト</a>
	</td>
	</tr>
</table>


<table width="800px" height="150px" align="center" style="margin: auto;">

	<tr>
	<td>
		<a href="./eob" style="display: inline-block; width: 150px; padding: 10px 20px; border: 1px solid black; text-decoration: none; margin: 0 10px; color: #ffffff; border-radius: 10px; background-color: #dc143c;" target="_blank">自動発注データ補正</a>
	</td>
	<td>
		<a href="./history" style="display: inline-block; width: 150px; padding: 10px 20px; border: 1px solid black; text-decoration: none; margin: 0 10px; color: #ffffff; border-radius: 10px; background-color: #dc143c;" target="_blank">自動発注履歴確認</a>
	</td>
	<td>
		<a href="./custum_order/index.php" style="display: inline-block; width: 150px; padding: 10px 20px; border: 1px solid black; text-decoration: none; margin: 0 10px; color: #000000; border-radius: 10px; background-color: #f5deb3;" target="_blank">客注発注</a>

	</td>
	<td>
		<a href="./custum_order_list/index.php" style="display: inline-block; width: 150px; padding: 10px 20px; border: 1px solid black; text-decoration: none; margin: 0 10px; color: #000000; border-radius: 10px; background-color: #f5deb3;" target="_blank">客注履歴確認</a>
	</td>
	</tr>

	<tr>
	<td>
		<a href="./recommend/" onclick="checkOnline(this); return false;" style="display: inline-block; width: 150px; padding: 10px 20px; border: 1px solid black; text-decoration: none; margin: 0 10px; color: #ffffff; border-radius: 10px; background-color: #0000cd;" target="_blank">レコメンド一覧</a>
	</td>
	<td>

		<a href="./stock_confirmation/?flg_minus=on" onclick="checkOnline(this); return false;" style="display: inline-block; width: 150px; padding: 10px 20px; border: 1px solid black; text-decoration: none; margin: 0 10px; color: #ffffff; border-radius: 10px; background-color: #008080" target="_blank">在庫確認</a>

	</td>
	<td>
	---
	</td>
	<td>
	---
	</td>
	</tr>
</table>

</div>

<div style="border: solid 1px; width: 900px; margin: 0 auto;"></div>
<br>


<table width="800px" height="150px" align="center" style="margin: auto;">

	<tr>
		<td>
		<div id="uploadBtn"></div>
		</td>
	</tr>
	<tr>
		<td>
		<div id="dlBtn" data-uid="<?= $user_id ?>"></div>
		</td>
	</tr>

</table>

<iframe src="<?php echo $iframe_url ?>" height="350" width="900" style="border:none;"></iframe>
	<?php if($destinator_flag == 1){?>
		<a href="https://osirasev00-skg.bubbleapps.io?usid=<?php echo $user_id;?>&usname=<?php echo $user_name;?>" style="display: inline-block; width: 150px; padding: 10px 20px; border: 1px solid black; text-decoration: none; margin: 0 10px; color: #ffffff; border-radius: 10px; background-color: #008080" target="_blank">お知らせ<br>管理</a>
	<?php }?>
<br><br>
<div id="dev-tool"></div>
<script>
    const checkOnline = async (self) => {
        const date = new Date();
        const timestamp = date.getTime();

        try {
            await fetch(`/assets/img/favicon.ico?${timestamp}`);
            window.open(self.href, '_blank');
        } catch {
            alert("ネットワーク接続がありません。もう一度お試しください。\n解決しない場合は管理者まで連絡してください。");
        }
        return false;
    };
</script>
</body>
</html>

