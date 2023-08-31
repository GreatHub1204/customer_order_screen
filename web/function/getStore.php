<?php

function getStore(PDO $pdo, string $store_id)
{
    $query = 'SELECT * FROM `店舗マスター` WHERE `店舗マスター`.`店舗ID` = :id ';
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $store_id]);

    $store = $stmt->fetch(PDO::FETCH_ASSOC);

    return $store;
}
