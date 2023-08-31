<?php

require_once(__DIR__.'/../config/config.php');

class Baiba
{

    private $db;

    public function __construct()
    {
        $dns = sprintf("mysql:host=%s;dbname=%s", DB_HOST, DB_NAME);
        $this->db = new PDO($dns, DB_USER, DB_PASSWORD);
    }

    public function getBaibaAll()
    {
        $sql1 = 'SELECT 売場大ID, 売場大名 FROM 売場大マスター';
        $sql2 = 'SELECT 売場大ID, 売場中ID, 売場中名 FROM 売場中マスター';
        $sql3 = 'SELECT 売場大ID, 売場中ID, 売場小ID, 売場小名 FROM 売場小マスター';

        $stmt1 = $this->db->query($sql1);
        $results['uridai'] = $stmt1->fetchAll(PDO::FETCH_ASSOC);
        $stmt1 = $this->db->query($sql2);
        $results['urichu'] = $stmt1->fetchAll(PDO::FETCH_ASSOC);
        $stmt1 = $this->db->query($sql3);
        $results['urisho'] = $stmt1->fetchAll(PDO::FETCH_ASSOC);

        return $results;
    }

    /**
     * 売場小名称の配列を作る
     * @param $rows
     * @return array
     */
    public function getBaishoNames($rows): array
    {
        $baisho_names = [];
        foreach ($rows as $row) {
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
     * @param $rows
     * @return array
     */
    public function getBaichuNames($rows): array
    {
        $baiba_chu_names = [];
        foreach ($rows as $row) {
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
     * @param $rows
     * @return array
     */
    public function getBaidaiNames($rows): array
    {
        $baiba_dai_names = [];
        foreach ($rows as $row) {
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

}
