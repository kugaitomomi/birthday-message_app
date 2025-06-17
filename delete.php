<?php
session_start();
require('config.php');

// ===========================================
// メッセージ削除処理 (POSTリクエスト時のみ実行)
// ===========================================

// HTTPメソッドがPOSTかどうかを確認
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // フォームから送信されたメッセージIDを取得
    $id = $_POST['id'] ?? null;

    // IDが指定されているかどうかのバリデーション
    if (!$id) {
        $_SESSION['message_status'] = '削除対象のメッセージが指定されていません。';
        header('Location: manage.php');
        exit();
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM messages WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT); // IDは整数型として扱う
        $stmt->execute();

        $_SESSION['message_status'] = 'メッセージを削除しました。';
        header('Location: manage.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['message_status'] = '不正なアクセスです。削除はPOSTメソッドで行ってください。';
        header('Location: manage.php');
        exit();
    }
}
