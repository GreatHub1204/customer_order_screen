<?php

require_once(__DIR__.'/../config/config.php');

class RecommendIndex
{

    const PAGE_VIEW = 50; // 表示件数

    private $db;
    private $rows;
    private $count;

    public function __construct($RecID, $store_id, $large_cat, $now_page_num)
    {
        $dns = sprintf("mysql:host=%s;dbname=%s", DB_HOST, DB_NAME);
        $this->db = new PDO($dns, DB_USER, DB_PASSWORD);
        $this->rows = $this->getRecommendData($RecID, $store_id, $large_cat, $now_page_num);
    }

    /**
     * レコメンドデータの一覧
     * @return array
     */
    public function getRecommendList(): array
    {
        // 結果セットを売場IDでグループ化してから台、段、位置でソート
        $groups = $this->groupByBaibaId();
        foreach ($groups as &$group) {
            $group = $this->sortByDaiDanIchi($group);
        }
        return $groups;
    }

    /**
     * @param $RecID
     * @param $store_id
     * @param $large_cat
     * @return mixed
     */
    public function getRecommendCountAll($RecID, $store_id, $large_cat)
    {

        $sql = "
SELECT DISTINCT
  cpm_recomend_data.商品ID
FROM
  cpm_recomend_data
    INNER JOIN 陳列マスター_eob
      ON cpm_recomend_data.店舗ID = 陳列マスター_eob.店舗ID AND cpm_recomend_data.商品ID = 陳列マスター_eob.商品ID
WHERE
  cpm_recomend_data.RecID = ?
  AND cpm_recomend_data.店舗ID = ?
  AND (陳列マスター_eob.売場大ID IN (%s))
;";

        $in_clause = substr(str_repeat(',?', count($large_cat)), 1);
        $stmt = $this->db->prepare(sprintf($sql, $in_clause));
        $stmt->execute(array_merge([$RecID, $store_id], $large_cat));
        $recomend_item_ids = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        // 商品IDでプロシージャをループして商品IDを取得
        $all_item_ids = $this->getProcEobIds($recomend_item_ids, $store_id);

        // 該当商品IDがない場合は空で返す
        if (empty($all_item_ids)) {
            return 0;
        }

        $sql = "
SELECT DISTINCT
  陳列マスター_eob.店舗ID,
  CONCAT(陳列マスター_eob.売場大ID, 陳列マスター_eob.売場中ID, 陳列マスター_eob.売場小ID) AS 売場ID,
  陳列マスター_eob.台,
  陳列マスター_eob.段,
  陳列マスター_eob.位置,
  陳列マスター_eob.商品ID,
  商品標準マスター.商品名,
  商品標準マスター.型番名,
  cpm_recomend_data.RecID,
  cpm_recomend_data.Attr,
  陳列マスター_eob.売場大ID,
  陳列マスター_eob.売場中ID,
  陳列マスター_eob.売場小ID,
  陳列マスター_eob.フェイス数,
  陳列マスター_eob.積上数,
  陳列マスター_eob.奥行陳列数,
  レコメンド対応テーブル.対応フラグ,
  在庫一覧.前日在庫
FROM
    陳列マスター_eob
    INNER JOIN 商品標準マスター
      ON 陳列マスター_eob.商品ID = 商品標準マスター.商品ID
    INNER JOIN 在庫一覧
      ON 在庫一覧.商品ID = 陳列マスター_eob.商品ID
    LEFT OUTER JOIN cpm_recomend_data
      ON cpm_recomend_data.商品ID = 陳列マスター_eob.商品ID
    LEFT OUTER JOIN レコメンド対応テーブル
      ON 売場ID = レコメンド対応テーブル.売場ID AND 陳列マスター_eob.台 = レコメンド対応テーブル.台 AND 陳列マスター_eob.段 = レコメンド対応テーブル.段 AND 陳列マスター_eob.位置 = レコメンド対応テーブル.位置 AND cpm_recomend_data.商品ID = レコメンド対応テーブル.商品ID AND cpm_recomend_data.RecID = レコメンド対応テーブル.RecID
WHERE
    陳列マスター_eob.商品ID IN (%s)
    AND 陳列マスター_eob.店舗ID = ?
  AND (陳列マスター_eob.売場大ID IN (%s))
  AND (cpm_recomend_data.RecID = ? OR cpm_recomend_data.RecID IS NULL)
ORDER BY
  売場ID ASC,
  台 ASC,
  段 ASC,
  位置 ASC 
;";

        $in_clause1 = substr(str_repeat(',?', count($all_item_ids)), 1);
        $in_clause2 = substr(str_repeat(',?', count($large_cat)), 1);
        $stmt = $this->db->prepare(sprintf($sql, $in_clause1, $in_clause2));
        $stmt->execute(array_merge($all_item_ids, [$store_id], $large_cat, [$RecID]));
        return count($stmt->fetchAll(PDO::FETCH_ASSOC));

    }

    /**
     * @param $recommend_list
     * @return mixed
     */
    public function convertRecommendJson($recommend_list)
    {
        $sql = "CALL ui_レコメンドsku(?, '', '', '', ?, ?)";
        foreach ($recommend_list as $groupKey => $groupValue) {
            foreach($groupValue as $key => $value) {
                if (empty($value['RecID'])) {
                    continue;
                }
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$value['RecID'], $value['商品ID'], $value['店舗ID']]);
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
                $recommend_list[$groupKey][$key]["レコメンド"] = $res['レコメンド'];
            }
        }

        return $recommend_list;
    }

    /**
     * @return int
     */
    public function getRecommendCount(): int
    {
        return count($this->rows);
    }

    /**
     * @return array
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * 売場小名称の配列を作る
     * @return array
     */
    public function getBaishoNames(): array
    {
        $baisho_names = [];
        foreach ($this->rows as $row) {
            if (!isset($baisho_names[$row["売場小ID"]])) {
                // 売場小マスターから売場小名称を取得するSQL文を準備する
                $sql2 = "SELECT 売場小名 FROM 売場小マスター WHERE 売場大ID = ? AND 売場中ID = ? AND 売場小ID = ?";

                // SQL文を実行する
                $stmt2 = $this->db->prepare($sql2);
                $stmt2->execute([$row["売場大ID"], $row["売場中ID"], $row["売場小ID"]]);

                // 結果セットから売場小名称を取得する
                $result2 = $stmt2->fetch(PDO::FETCH_ASSOC);
                $baisho_name = $result2["売場小名"];

                // 売場小名称の配列に追加する
                $baisho_names[sprintf("%s-%s-%s",$row["売場大ID"], $row["売場中ID"], $row["売場小ID"])] = $baisho_name;
            }
        }
        return $baisho_names;
    }

    /**
     * 売場中名称の配列を作る
     * @return array
     */
    public function getBaichuNames(): array
    {
        $baiba_chu_names = [];
        foreach ($this->rows as $row) {
            if (!isset($baiba_chu_names[$row["売場中ID"]])) {
                // 売場中マスターから売場中名称を取得するSQL文を準備する
                $sql3 = "SELECT 売場中名 FROM 売場中マスター WHERE 売場大ID = ? AND 売場中ID = ?";

                // SQL文を実行する
                $stmt3 = $this->db->prepare($sql3);
                $stmt3->execute([$row["売場大ID"], $row["売場中ID"]]);

                // 結果セットから売場中名称を取得する
                $result3 = $stmt3->fetch(PDO::FETCH_ASSOC);
                $baiba_chu_name = $result3["売場中名"];

                // 売場中名称の配列に追加する
                $baiba_chu_names[sprintf("%s-%s", $row["売場大ID"], $row["売場中ID"])] = $baiba_chu_name;
            }
        }
        return $baiba_chu_names;
    }

    /**
     * 売場大名称の配列を作る
     * @return array
     */
    public function getBaidaiNames(): array
    {
        $baiba_dai_names = [];
        foreach ($this->rows as $row) {
            if (!isset($baiba_dai_names[$row["売場大ID"]])) {
                // 売場大マスターから売場大名称を取得するSQL文を準備する
                $sql4 = "SELECT 売場大名 FROM 売場大マスター WHERE 売場大ID = ?";

                // SQL文を実行する
                $stmt4 = $this->db->prepare($sql4);
                $stmt4->execute([$row["売場大ID"]]);

                // 結果セットから売場大名称を取得する
                $result4 = $stmt4->fetch(PDO::FETCH_ASSOC);
                $baiba_dai_name = $result4["売場大名"];

                // 売場大名称の配列に追加する
                $baiba_dai_names[$row["売場大ID"]] = $baiba_dai_name;
            }
        }
        return $baiba_dai_names;
    }

    /**
     * 対応件数の取得
     * @param $store_id
     * @param $RecID
     * @param $large_cat
     * @return int
     */
    public function getSupportedNumber($store_id, $RecID, $large_cat): int
    {
        $sql = 'SELECT count(*) as count FROM レコメンド対応テーブル 
                         WHERE 店舗ID = ? 
                             AND RecID = ? AND (%s)';

        $conditions = [];
        foreach($large_cat as $index  => $keyword) {
            $conditions[] = "売場大ID = ?";
        }
        $largeWhere = implode(' OR ', $conditions);

        $stmt = $this->db->prepare(sprintf($sql, $largeWhere));
        $stmt->execute(array_merge([$store_id, $RecID], $large_cat));
        $result = $stmt->fetch();

        return $result['count'] ?? 0;
    }

    /**
     * @param $RecID
     * @param $store_id
     * @param $large_cat
     * @param $now_page_num
     * @return array
     */
    private function getRecommendData($RecID, $store_id, $large_cat, $now_page_num = 1): array
    {
        $limit = self::PAGE_VIEW;
        $offset = ($now_page_num - 1) * self::PAGE_VIEW;

        $sql = "
SELECT DISTINCT
  cpm_recomend_data.商品ID
FROM
  cpm_recomend_data
    INNER JOIN 陳列マスター_eob
      ON cpm_recomend_data.店舗ID = 陳列マスター_eob.店舗ID AND cpm_recomend_data.商品ID = 陳列マスター_eob.商品ID
WHERE
  cpm_recomend_data.RecID = ?
  AND cpm_recomend_data.店舗ID = ?
  AND (陳列マスター_eob.売場大ID IN (%s))
LIMIT {$limit}
OFFSET {$offset};";

        $in_clause = substr(str_repeat(',?', count($large_cat)), 1);
        $stmt = $this->db->prepare(sprintf($sql, $in_clause));
        $stmt->execute(array_merge([$RecID, $store_id], $large_cat));
        $recomend_item_ids = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        // 商品IDでプロシージャをループして商品IDを取得
        $all_item_ids = $this->getProcEobIds($recomend_item_ids, $store_id);

        // 該当商品IDがない場合は空で返す
        if (empty($all_item_ids)) {
            return [];
        }

        $sql = "
SELECT DISTINCT
  陳列マスター_eob.店舗ID,
  CONCAT(陳列マスター_eob.売場大ID, 陳列マスター_eob.売場中ID, 陳列マスター_eob.売場小ID) AS 売場ID,
  陳列マスター_eob.台,
  陳列マスター_eob.段,
  陳列マスター_eob.位置,
  陳列マスター_eob.商品ID,
  商品標準マスター.商品名,
  商品標準マスター.型番名,
  cpm_recomend_data.RecID,
  cpm_recomend_data.Attr,
  陳列マスター_eob.売場大ID,
  陳列マスター_eob.売場中ID,
  陳列マスター_eob.売場小ID,
  陳列マスター_eob.フェイス数,
  陳列マスター_eob.積上数,
  陳列マスター_eob.奥行陳列数,
  レコメンド対応テーブル.対応フラグ,
  在庫一覧.前日在庫
FROM
    陳列マスター_eob
    INNER JOIN 商品標準マスター
      ON 陳列マスター_eob.商品ID = 商品標準マスター.商品ID
    INNER JOIN 在庫一覧
      ON 在庫一覧.商品ID = 陳列マスター_eob.商品ID
    LEFT OUTER JOIN cpm_recomend_data
      ON cpm_recomend_data.商品ID = 陳列マスター_eob.商品ID
    LEFT OUTER JOIN レコメンド対応テーブル
      ON 売場ID = レコメンド対応テーブル.売場ID AND 陳列マスター_eob.台 = レコメンド対応テーブル.台 AND 陳列マスター_eob.段 = レコメンド対応テーブル.段 AND 陳列マスター_eob.位置 = レコメンド対応テーブル.位置 AND 陳列マスター_eob.商品ID = レコメンド対応テーブル.商品ID
WHERE
    陳列マスター_eob.商品ID IN (%s)
    AND 陳列マスター_eob.店舗ID = ?
  AND (陳列マスター_eob.売場大ID IN (%s))
  AND (cpm_recomend_data.RecID = ? OR cpm_recomend_data.RecID IS NULL)
ORDER BY
  売場ID ASC,
  台 ASC,
  段 ASC,
  位置 ASC 
;";

        $in_clause1 = substr(str_repeat(',?', count($all_item_ids)), 1);
        $in_clause2 = substr(str_repeat(',?', count($large_cat)), 1);
        $stmt = $this->db->prepare(sprintf($sql, $in_clause1, $in_clause2));
        $stmt->execute(array_merge($all_item_ids, [$store_id], $large_cat, [$RecID]));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 結果セットを売場IDでグループ化する関数
     * @return array
     */
    private function groupByBaibaId(): array
    {
        $groups = [];
        foreach ($this->rows as $row) {
            // 売場大ID、売場中ID、売場小IDを連結して売場IDを作る
            $baiba_id = $row["売場ID"];
            if (!isset($groups[$baiba_id])) {
                $groups[$baiba_id] = [];
            }
            $groups[$baiba_id][] = $row;
        }
        return $groups;
    }

    /**
     * 結果セットを売場IDでグループ化してから台、段、位置でソート
     * @param $group
     * @return mixed
     */
    private function sortByDaiDanIchi($group): array
    {
        usort($group, static function ($a, $b) {
            if ($a["台"] !== $b["台"]) {
                return $a["台"] <=> $b["台"];
            }
            if ($a["段"] === $b["段"]) {
                return $a["位置"] <=> $b["位置"];
            }
            return $a["段"] <=> $b["段"];
        });
        return $group;
    }


    private function getProcEobIds($recomend_item_ids, $store_id)
    {
        $all_item_ids = [];
        foreach ($recomend_item_ids as $r_key => $recommend_item_id) {
            $tmp_eob = $this->getProcEobStock($store_id, $recommend_item_id['商品ID']);
            foreach ($tmp_eob as $t_key => $eob_item) {
                // 配列に該当商品IDが存在しなければ追加
                if (!in_array($eob_item['商品ID'], $all_item_ids)) {
                    $all_item_ids[] = $eob_item['商品ID'];
                }
            }
        }
        return $all_item_ids;
    }

    private function getProcEobStock($store_id, $item_id)
    {
        $sql = "CALL eob_在庫算出(?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$store_id, $item_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
