<?php

$all_recipients = [];

try {
    $stmt = $pdo->prepare("SELECT id, name FROM recipients ORDER BY name ASC");
    $stmt->execute();

    $all_recipients = $stmt->fetchAll(PDO::FETCH_ASSOC); //今回はプレースホルダー設定をしていないので、バインドしなくてOK
} catch (PDOException $e) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['message_status'] = '宛先リストの読み込みに失敗しました: ' . $e->getMessage();
}
