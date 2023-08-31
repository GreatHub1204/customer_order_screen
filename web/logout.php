<?php
// セッションの開始
session_start();

// セッション変数を全て解除
$_SESSION = array();

// セッションクッキーも削除
if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();
  setcookie(session_name(), '', time() - 42000,
    $params["path"], $params["domain"],
    $params["secure"], $params["httponly"]
  );
}

// セッションを破棄
session_destroy();

// ログインページやトップページにリダイレクト
header('Location: index.php');
exit();
?>
