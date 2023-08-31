<?php

require_once(__DIR__.'/../config/config.php');

class User
{

    private $db;

    public function __construct()
    {
        $dns = sprintf("mysql:host=%s;dbname=%s", DB_HOST, DB_NAME);
        $this->db = new PDO($dns, DB_USER, DB_PASSWORD);
    }

    /**
     * @param $login_id
     * @return mixed
     */
    public function getUserByLoginId($login_id)
    {
        $sql = "SELECT u.*, s.店舗名 FROM ユーザーマスターテーブル as u 
        INNER JOIN 店舗マスター as s ON u.店舗ID = s.店舗ID 
         WHERE u.担当者ID = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$login_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param $login_id
     * @return array
     */
    public function getLargeCategory($login_id)
    {
        $sql = "select * FROM 売場管理テーブル WHERE 担当者ID = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$login_id]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $cats = [];
        $allkey = array_search( 'ALL', array_column( $results, '売場大ID'));
        if($allkey === false) { // ALL以外
            foreach($results as $key => $value){
                $cats[] = $value['売場大ID'];
            }
        } else { // ALL
            $sql2 = "select * FROM 売場大マスター";
            $stmt = $this->db->query($sql2);
            $largeCats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($largeCats as $largeKey => $largeValue) {
                $cats[] = $largeValue['売場大ID'];
            }
        }

        return $cats;
    }


    /**
     * @return false|mixed
     */
    public function getSession()
    {
        session_start();
        if (!isset($_SESSION['login_id'])) {
            return false;
        }

        return $_SESSION['login_id'];
    }

    /**
     * @return void
     */
    public function redirectLogin()
    {
        $protocol = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $url = $protocol . $host . "./login/";
        header("Location: {$url}");
        exit();
    }

}
