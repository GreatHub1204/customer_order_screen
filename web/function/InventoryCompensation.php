<?php

require_once(__DIR__.'/../config/config.php');

class InventoryCompensation
{

    const MAX_INVENTORY_CONTROL_ON = 1;
    const MAX_INVENTORY_CONTROL_OFF = 0;
    const AUTO_ORDER_ON = 1;
    const AUTO_ORDER_OFF = 0;

    private $db;

    public function __construct()
    {
        $dns = sprintf("mysql:host=%s;dbname=%s", DB_HOST, DB_NAME);
        $this->db = new PDO($dns, DB_USER, DB_PASSWORD);
    }

    /**
     * @param $store_id
     * @param $item_id
     * @return array
     */
    public function getAllInventoryData($store_id, $item_id, $date)
    {
        $result = [
            'message' => '',
            'success' => 'false',
            'result' => []
        ];

        try {
            // 在庫算出のプロシージャコール
            $stock_calculate = $this->getProcEobStock($store_id, $item_id);

            // 基準商品IDの判定、取得
            $base_item_key = array_search( '1', array_column( $stock_calculate, '換算数'));
            $base_item_id = $stock_calculate[$base_item_key]['商品ID'];

            $back_room = [];
            foreach ($stock_calculate as $key => $value) {
                $stock_calculate[$key]['基準商品ID'] = $base_item_id;

                $back_room[$key] = $value;
                $back_room[$key]['基準商品ID'] = $base_item_id;
                $back_room[$key]["商品在庫数"] = '';
                $back_room[$key]["バラ在庫数"] = '';
                // 入力済みなら値を取得して設定
                $stock_result = $this->getOneCorrectionInventory($store_id, $value['商品ID'], $base_item_id, $date, 0, 0, 0);
                if (!empty($stock_result)) {
                    $back_room[$key]["商品在庫数"] = $stock_result["数"];
                    $back_room[$key]["バラ在庫数"] = $stock_result["バラ在庫数"];
                }
            }

            // 更新_最大最低在庫から取得
            $max_min = $this->getMaxMin($store_id, $item_id);

            // 該当商品IDの最大在庫抑制tと自動発注の取得
            $target_item_key = array_search( $item_id, array_column( $stock_calculate, '商品ID'));

            $result['stock_calculate']['最大在庫抑制'] = $stock_calculate[$target_item_key]['最大在庫抑制'];
            $result['stock_calculate']['自動発注'] = $stock_calculate[$target_item_key]['自動発注'];
            $result['item_back'] = $back_room;
            $result['item_bara'] = $this->getBaraData($stock_calculate, $store_id, $date); // 個別
            // 基準商品IDの判定、取得
            $result['auto_order'] = $this->getAutoOrder($store_id, $base_item_id); // 自動発注データ_eobから最低在庫、最大在庫の現在値を取得
            $result['estimated_count'] = array_sum(array_column($stock_calculate, '納品予定数'));
            if ($max_min !== false) {
                $result['max_min'] = $max_min;
            }
            $result['correction_inventory'] = $this->getCorrectionInventory($store_id, $base_item_id);

            $result['success'] = true;
        } catch(\Exception $e) {
            error_log($e->getMessage());
            $result['message'] = "データ取得でエラーが発生しました。再読み込みを試してください。";
            $result['success'] = false;
        }

        // 全部まとめてレス
        return $result;
    }


    /*
    public function insertInventoryCompensation($post)
    {
        try {
            $this->db->beginTransaction();
            $createDate = (new Datetime())->format('Y-m-d H:i:s');
            foreach ($post['itemRowData'] as $key => $value) {
                // 更新_最大最低在庫
                $this->insertInventoryMaxMin($value);

                // 更新_在庫補正
                $this->insertInventoryCompensationDetail($value, $createDate);
            }
            $this->db->commit();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->db->rollBack();
            return false;
        }

        return true;
    }
    */

    private function insertInventoryCompensationDetail($value, $createDate)
    {
        $sql = "INSERT INTO 更新_在庫補正データ
                (店舗ID, 基準商品ID, 日付, 商品ID, 台, 段, 位置, 数, 換算数, バラ在庫数, 作成日, 担当者ID)
                VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ";
        $stmt = $this->db->prepare($sql);
        $res = $stmt->execute([
            $value["storeId"],
            $value["baseItemId"],
            $value["date"],
            $value['itemId'],
            empty($value["dai"]) ? 0 : $value["dai"],
            empty($value["dan"]) ? 0 : $value["dan"],
            empty($value["ichi"]) ? 0 : $value["ichi"],
            $value["stock"],
            $value["bara"],
            $value["stockSum"],
            $createDate,
            $value["userId"]
        ]);
        $stmt->closeCursor();
    }

    private function updateInventoryCompensationDetail($value, $createDate)
    {
        $sql = "UPDATE 更新_在庫補正データ
                SET 数 = ?, 換算数 = ?, バラ在庫数 = ?, 作成日 = ?, 担当者ID = ?
                WHERE 店舗ID = ? AND 基準商品ID = ? AND 日付 = ? AND 商品ID = ? AND 台 = ? AND 段 = ? AND 位置 = ?
                ";
        $stmt = $this->db->prepare($sql);
        $stmt->closeCursor();
        $stmt->execute([
            $value["stock"],
            $value["bara"],
            $value["stockSum"],
            $createDate,
            $value["userId"],
            $value["storeId"],
            $value["baseItemId"],
            $value["date"],
            $value["itemId"],
            empty($value["dai"]) ? 0 : $value["dai"],
            empty($value["dan"]) ? 0 : $value["dan"],
            empty($value["ichi"]) ? 0 : $value["ichi"],
        ]);
        if ($stmt->rowCount() !== 1) {
            throw new \Exception('更新件数が正しくありません。');
        }
        $stmt->closeCursor();
    }

    private function insertInventoryMaxMin($value)
    {
        $sql = "INSERT INTO 更新_最大最低在庫
                (店舗ID, 商品ID, 台, 段, 位置, 最低在庫数, 最大在庫, 最大在庫抑制, 自動発注, 作成日, 担当者ID)
                VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ";
        $stmt = $this->db->prepare($sql);
        $res = $stmt->execute([
            $value["storeId"],
            $value['itemId'],
            empty($value["dai"]) ? 0 : $value["dai"],
            empty($value["dan"]) ? 0 : $value["dan"],
            empty($value["ichi"]) ? 0 : $value["ichi"],
            empty($value["min"]) ? 0 : $value["min"],
            empty($value["max"]) ? 0 : $value["max"],
            $value["flgMaxInventoryControl"],
            $value["flgAutoOrder"],
            date('Y-m-d H:i:s'),
            $value["userId"]
        ]);
        $stmt->closeCursor();
    }

    private function updateInventoryMaxMin($value)
    {
        $sql = "UPDATE 更新_最大最低在庫
                SET 最低在庫数 = ?, 最大在庫 = ?, 最大在庫抑制 = ?, 自動発注 = ?, 担当者ID = ?
                WHERE 店舗ID = ? AND 商品ID = ? AND 台 = ? AND 段 = ? AND 位置 = ? 
                ";
        $stmt = $this->db->prepare($sql);
        $stmt->closeCursor();
        $stmt->execute([
            empty($value["min"]) ? 0 : $value["min"],
            empty($value["max"]) ? 0 : $value["max"],
            $value["flgMaxInventoryControl"],
            $value["flgAutoOrder"],
            $value["userId"],
            $value["storeId"],
            $value["itemId"],
            empty($value["dai"]) ? 0 : $value["dai"],
            empty($value["dan"]) ? 0 : $value["dan"],
            empty($value["ichi"]) ? 0 : $value["ichi"],
        ]);
        $stmt->closeCursor();
    }

    public function upsertInventoryCompensation($post)
    {
        try {
            $this->db->beginTransaction();
            $createDate = (new Datetime())->format('Y-m-d H:i:s');

            foreach($post['itemRowData'] as $key => $value) {
                // 更新_在庫補正データに該当レコードがない場合は新規追加
                if ($this->getInventoryCompensationDetail($value, $createDate) === false) {
                    $this->insertInventoryCompensationDetail($value, $createDate);
                } else {
                    // 更新_在庫補正データ
                    $this->updateInventoryCompensationDetail($value, $createDate);
                }

                // 更新_最大最低在庫
                if (empty($this->getOneCorrectionMinMax(
                    $value["storeId"],
                    $value["itemId"],
                    empty($value["dai"]) ? 0 : $value["dai"],
                    empty($value["dan"]) ? 0 : $value["dan"],
                    empty($value["ichi"]) ? 0 : $value["ichi"])
                )) {
                    // 既存レコードがなければInsert
                    $this->insertInventoryMaxMin($value);
                } else {
                    // 既存レコードがあればupdate
                    $this->updateInventoryMaxMin($value);
                }
            }

            $this->db->commit();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->db->rollBack();
            return false;
        }

        return true;
    }


    private function getInventoryCompensationDetail($value, $createDate)
    {
        $sql = "SELECT * FROM 更新_在庫補正データ 
         WHERE 店舗ID = ? AND 基準商品ID = ? AND 日付 = ? AND 商品ID = ? AND 台 = ? AND 段 = ? AND 位置 = ?;";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $value["storeId"],
            $value["baseItemId"],
            $value["date"],
            $value["itemId"],
            empty($value["dai"]) ? 0 : $value["dai"],
            empty($value["dan"]) ? 0 : $value["dan"],
            empty($value["ichi"]) ? 0 : $value["ichi"],
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    private function getBaraData($stock_calculate, $store_id, $date)
    {
        $bara_data = $stock_calculate;
        $result = [];
        $cnt = 0;
        foreach ($bara_data as $key => $value) {
            $sql = "SELECT c.*, ud.売場大名, uc.売場中名, us.売場小名 FROM 陳列マスター_eob as c
                        INNER JOIN 売場大マスター as ud ON c.売場大ID = ud.売場大ID
                        INNER JOIN 売場中マスター as uc ON c.売場大ID = uc.売場大ID AND c.売場中ID = uc.売場中ID
                        INNER JOIN 売場小マスター as us ON c.売場大ID = us.売場大ID AND c.売場中ID = us.売場中ID AND c.売場小ID = us.売場小ID
                        WHERE 店舗ID = ? AND 商品ID = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$store_id, $value['商品ID']]);
            $tmp_display = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            // ※陳列マスターは複数行HITする可能性があるのでループ
            foreach ($tmp_display as $ekey => $dvalue) {
                $result[$cnt] = $value;
                $result[$cnt]['売場大名'] = $dvalue['売場大名'];
                $result[$cnt]['売場中名'] = $dvalue['売場中名'];
                $result[$cnt]['売場小名'] = $dvalue['売場小名'];
                $result[$cnt]['台'] = $dvalue['台'];
                $result[$cnt]['段'] = $dvalue['段'];
                $result[$cnt]['位置'] = $dvalue['位置'];
                $result[$cnt]['フェイス数'] = $dvalue['フェイス数'];
                $result[$cnt]['積上数'] = $dvalue['積上数'];
                $result[$cnt]['奥行陳列数'] = $dvalue['奥行陳列数'];
                $result[$cnt]["商品在庫数"] = '';
                $result[$cnt]["バラ在庫数"] = '';

                // 入力済みなら値を取得して設定
                // 更新_在庫補正データ
                $stock_result = $this->getOneCorrectionInventory($store_id, $dvalue['商品ID'], $value['基準商品ID'], $date, $dvalue['台'], $dvalue['段'], $dvalue['位置']);
                if (!empty($stock_result)) {
                    $result[$cnt]["商品在庫数"] = $stock_result["数"];
                    $result[$cnt]["バラ在庫数"] = $stock_result["バラ在庫数"];
                }
                // 更新_最低最大在庫 TODO
                $max_min_result = $this->getOneCorrectionMinMax($store_id, $dvalue['商品ID'], $dvalue['台'], $dvalue['段'], $dvalue['位置']);
                $result[$cnt]["min"] = !empty($max_min_result) ? $max_min_result["最低在庫数"] : "";
                $result[$cnt]["max"] = !empty($max_min_result) ? $max_min_result["最大在庫"] : "";

                $cnt++;
            }
        }

        // 売場　台、段、位置　を昇順にソート
        foreach ($result as $key => $row) {
            $tmp_uriba_dai[$key] = $row["売場大名"];
            $tmp_uriba_chu[$key] = $row["売場中名"];
            $tmp_uriba_sho[$key] = $row["売場小名"];
            $tmp_dai[$key] = $row["台"];
            $tmp_dan[$key] = $row["段"];
            $tmp_ichi[$key] = $row["位置"];
        }
        array_multisort($tmp_uriba_dai, SORT_ASC,
            $tmp_uriba_chu, SORT_ASC,
            $tmp_uriba_sho, SORT_ASC,
            $tmp_dai, SORT_ASC,
            $tmp_dan, SORT_ASC,
            $tmp_ichi, SORT_ASC,
            $result);

        return $result;
    }

    private function getProcEobStock($store_id, $item_id)
    {
        $sql = "CALL eob_在庫算出(?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$store_id, $item_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getAutoOrder($store_id, $item_id)
    {
        $sql = "SELECT 最大在庫, 最低在庫 FROM 自動発注データ_eob 
                    WHERE 店舗ID = ? and 商品ID = ? 
                    ORDER BY 納品予定日 DESC LIMIT 1;";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$store_id, $item_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getMaxMin($store_id, $item_id)
    {
        $sql = "SELECT * FROM 更新_最大最低在庫 WHERE 店舗ID = ? and 商品ID = ?;";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$store_id, $item_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getCorrectionInventory($store_id, $base_item_id)
    {
        $sql = "SELECT SUM(バラ在庫数) as 補正値 FROM 更新_在庫補正データ WHERE 店舗ID = ? and 基準商品ID = ?;";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$store_id, $base_item_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (is_numeric($result['補正値']) && $result['補正値'] > 0) ? $result['補正値'] : 0;
    }

    private function getOneCorrectionInventory($store_id, $item_id, $base_item_id, $date, $dai, $dan, $ichi)
    {
        $sql = "SELECT * FROM 更新_在庫補正データ
                        WHERE 店舗ID = ? AND 基準商品ID = ? AND 日付 = ? AND 商品ID = ? AND 台 = ? AND 段 = ? AND 位置 = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$store_id, $base_item_id, $date, $item_id, $dai, $dan, $ichi]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $result === false ? [] : $result;
    }

    private function getOneCorrectionMinMax($store_id, $item_id, $dai, $dan, $ichi)
    {
        $sql = "SELECT * FROM 更新_最大最低在庫
                        WHERE 店舗ID = ? AND 商品ID = ? AND 台 = ? AND 段 = ? AND 位置 = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$store_id, $item_id, $dai, $dan, $ichi]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $result === false ? [] : $result;
    }

}
