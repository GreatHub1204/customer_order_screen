<?php

require_once (__DIR__.'/../config/config.php');

class RecommendSupported
{
    private $db;

    const SUPPORTED_FLG_COMPLETE = 1; // 完了
    const SUPPORTED_FLG_PENDING = 2; // 保留
    const SUPPORTED_FLG_NOT_AVAILABLE = 3; // 不可

    public function __construct()
    {
        $dns = sprintf("mysql:host=%s;dbname=%s", DB_HOST, DB_NAME);
        $this->db = new PDO($dns, DB_USER, DB_PASSWORD);
    }

    /**
     * @param $store_id
     * @param $uriba_id
     * @param $dai
     * @param $dan
     * @param $ichi
     * @param $item_id
     * @param $RecID
     * @return int
     */
    public function searchRecord($store_id, $uriba_id, $dai, $dan, $ichi, $item_id, $RecID): int
    {
        $sql = 'SELECT count(*) as count FROM レコメンド対応テーブル where 店舗ID = ? AND 売場ID = ? AND 台 = ? AND 段 = ? AND 位置 = ? AND 商品ID = ? AND RecID = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$store_id, $uriba_id, $dai, $dan, $ichi, $item_id, $RecID]);
        $result = $stmt->fetch();
        return $result['count'];
    }

    /**
     * @param $store_id
     * @param $uriba_id
     * @param $dai
     * @param $dan
     * @param $ichi
     * @param $item_id
     * @param $RecID
     * @param $status
     * @param $uridai
     * @return void
     */
    public function insertRecord($store_id, $uriba_id, $dai, $dan, $ichi, $item_id, $RecID, $status, $uridai)
    {
        $sql = "INSERT INTO レコメンド対応テーブル
                (店舗ID, 売場ID, 台, 段, 位置, 商品ID, RecID, 対応フラグ, 売場大ID)
                VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ";
        $stmt = $this->db->prepare($sql);
        $res = $stmt->execute([$store_id, $uriba_id, $dai, $dan, $ichi, $item_id, $RecID, $status, $uridai]);
    }

}
