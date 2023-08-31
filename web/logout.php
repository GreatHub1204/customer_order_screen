<?php
// �Z�b�V�����̊J�n
session_start();

// �Z�b�V�����ϐ���S�ĉ���
$_SESSION = array();

// �Z�b�V�����N�b�L�[���폜
if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();
  setcookie(session_name(), '', time() - 42000,
    $params["path"], $params["domain"],
    $params["secure"], $params["httponly"]
  );
}

// �Z�b�V������j��
session_destroy();

// ���O�C���y�[�W��g�b�v�y�[�W�Ƀ��_�C���N�g
header('Location: index.php');
exit();
?>
