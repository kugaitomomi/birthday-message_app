<?php
session_start(); // セッション開始

require 'config.php'; // DB接続

// ===========================================
// メッセージ表示処理 (GETリクエスト時に実行)
// ===========================================
$messages = []; // 取得したメッセージを格納する配列を初期化

try {
    // 全メッセージを取得するSQLクエリ
    // 新しい投稿が上に来るように、idを降順に並べ替える (ORDER BY id DESC)
    // テーブル名が 'messages' であることを確認してください
    $stmt = $pdo->query("SELECT id, sender_name, message_text, created_at FROM messages ORDER BY created_at DESC"); //結果セット（問い合わせた行とカラムの集合）を取得しているだけ。値の取得ではない。
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC); // 連想配列として結果を取得
    //var_dump($messages); //配列を直接echoすると "Array" と表示されるだけで内容が見えないので注意。デバッグにはvar_dump()が便利です。
} catch (PDOException $e) {
    $message_status = 'メッセージの読み込みに失敗しました: ' . $e->getMessage();
    // ここはGETリクエスト時のエラーなので、セッションには保存しない（直接表示）
}

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>寄せ書きアプリ</title>
</head>

<body>
    <!-- 寄せ書き表示画面エリア start -->
    <div>
        <h1>🥳🥳🥳理一くん誕生日、おめでとう！！🥳🥳🥳</h1>
        <?php if (!empty($messages)): ?>
            <?php foreach ($messages as $message): ?>
                <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
                    <p><strong>名前: </strong><?php echo htmlspecialchars($message['sender_name'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>メッセージ: </strong><?php echo htmlspecialchars($message['message_text'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>投稿日時: </strong><?php echo htmlspecialchars($message['created_at'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>まだメッセージはありません。最初のメッセージを投稿してみましょう！</p>
        <?php endif ?>
    </div>
    <!-- 寄せ書き表示画面エリア end -->
</body>

</html>