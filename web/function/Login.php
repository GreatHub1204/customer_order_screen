<?php

require_once(__DIR__.'/../config/config.php');

class Login
{
    const ROLE_USER = 1;
    const ROLE_ADMIN = 2;
    const ROLE_ADMIN_MASTER = 3;

    const ROLE_LIST = [
        self::ROLE_USER => '一般ユーザー',
        self::ROLE_ADMIN => '管理者',
        self::ROLE_ADMIN_MASTER => 'アドミン権限',
    ];

    private $db;

    public function __construct()
    {
        $dns = sprintf("mysql:host=%s;dbname=%s", DB_HOST, DB_NAME);
        $this->db = new PDO($dns, DB_USER, DB_PASSWORD);
    }

    /**
     * @param $post
     * @param $isCreate
     * @return array
     */
    public function validate($post, $isCreate)
    {
        $results = [
            'success' => true,
            'message' => ''
        ];

        // ログインID 必須 バリデーション
        if (empty($post['login_id'])) {
            $results['message'] .= "ログインIDは必須です。\n";
            $results['success'] = false;
        }
        if (!empty($post['login_id']) && mb_strlen($post['login_id']) > 20) {
            $results['message'] .= "ログインIDは20文字以内で入力してください。\n";
            $results['success'] = false;
        }
        if (!empty($post['login_id']) && !preg_match("/^[a-zA-Z0-9]+$/", $post['login_id'])) {
            $results['message'] .= "ログインIDは英数字で入力してください。\n";
            $results['success'] = false;
        }

        // パスワード　パスワード　必須　英数字と!#$%&()<>?+
        if ($isCreate && empty($post['password']) && $post['password'] !== "0") {
            $results['message'] .= "パスワードは必須です。\n";
            $results['success'] = false;
        }

        if (!empty($post['password']) && mb_strlen($post['password']) > 20) {
            $results['message'] .= "パスワードは20文字以内で入力してください。\n";
            $results['success'] = false;
        }

        return $results;
    }

    /**
     * @param $post
     * @return array
     */
    public function loginCheck($post): array
    {
        $results = [
            'success' => true,
            'message' => '',
            'role' => 0,
        ];

        // ログインIDを検索
        $sql = "SELECT * FROM ユーザーマスターテーブル WHERE 担当者ID = ? AND ユーザー種別 !=" . self::ROLE_ADMIN_MASTER;
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$post['login_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 担当者IDがDBに存在しない場合はエラー
        if(!$user) {
            $results['success'] = false;
            $results['message'] = "ログインIDもしくはログインパスワードが違っています。もう一度入力してログインしてください。";
            return $results;
        }

        // ログインカウントが5以上ならエラー
        if ((int)$user['ログインカウント'] >= 5) {
            $results['success'] = false;
            $results['message'] = "このログインIDは連続してログインに失敗しましたので、現在ロックされ利用できません。システム管理者にご連絡の上ロックを解除してもらってください。";
            return $results;
        }

        // パスワード認証
        if (!password_verify($post["password"], $user["ログインPW"])) {
            $results['success'] = false;
            $results['message'] = "ログインIDもしくはログインパスワードが違っています。もう一度入力してログインしてください。";

            // ミスでcount+
            $count = (int)$user["ログインカウント"] + 1;
            $sql2 = "UPDATE ユーザーマスターテーブル SET ログインカウント = ? WHERE システムID = ?";
            $stmt2 = $this->db->prepare($sql2);
            $res = $stmt2->execute([$count, $user['システムID']]);

            // ミス5回になったら専用メッセージ
            if ($count === 5) {
                $results['success'] = false;
                $results['message'] = "５回連続してログインに失敗しました。このログインIDはロックされましたので、これ以上ログインを試行できまません。システム管理者にご連絡の上ロックを解除してもらってください。";
            }
        }

        $results['role'] = $user['ユーザー種別'];
        return $results;
    }


    /**
     * @param $loginId
     * @return void
     */
    public function setSession($loginId)
    {
        // ログインできたらセッションにログインIDをセット
        session_start();
        $_SESSION['login_id'] = $loginId;

        // ログインカウントをリセット
        $sql = "UPDATE ユーザーマスターテーブル SET ログインカウント = 0 WHERE 担当者ID = ?";
        $stmt = $this->db->prepare($sql);
        $res = $stmt->execute([$loginId]);
    }

    /**
     * @param $role
     * @return string
     */
    public function getRedirectUrl($role): string
    {
        // リダイレクト
        $protocol = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];

        if ((int)$role === self::ROLE_ADMIN_MASTER) {
            $url = $protocol . $host . "/admin/user/";
        } else {
            $url = $protocol . $host . "/";
        }
        return $url;
    }

    /**
     * @param $login_id
     * @return mixed
     */
    public function getUser($login_id)
    {
        $sql = "SELECT * FROM ユーザーマスターテーブル WHERE 担当者ID = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$login_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

}
