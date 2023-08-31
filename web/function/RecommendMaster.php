<?php

require_once (__DIR__.'/../config/config.php');

class RecommendMaster
{
    private $db;

    const DELETE_FLG_OFF = 0;
    const DELETE_FLG_ON = 1;

    const ID_MINUS_STOCK = '01';
    const ID_EXCESSIVE_ORDERS = '03';

    public function __construct()
    {
        $dns = sprintf("mysql:host=%s;dbname=%s", DB_HOST, DB_NAME);
        $this->db = new PDO($dns, DB_USER, DB_PASSWORD);
    }

    public function getList()
    {
        $sql = 'SELECT * FROM cpm_recommend_master where 削除フラグ = ' . self::DELETE_FLG_OFF;
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
