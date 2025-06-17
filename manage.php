<?php
session_start();

require_once 'config.php';

$message_status = '';

if (isset($_SESSION['message_status'])) {
    $message_status = $_SESSION['message_status'];
    unset($_SESSION['message_status']);
}

// ===========================================
// メッセージ表示処理 (GETリクエスト時に実行)
// ===========================================
$messages = []; // 取得したメッセージを格納する配列を初期化

try {
    $stmt = $pdo->query("SELECT id, sender_name, message_text, created_at FROM messages ORDER BY created_at DESC");
    $messages = $stmt->fetchALL(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message_status = 'メッセージの読み込みに失敗しました: ' . $e->getMessage();
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
    <!-- 投稿メッセージ一覧エリア start -->
    <div>
        <p><?php echo $message_status; ?></p>
        <h1>投稿メッセージ一覧</h1>
        <?php if (!empty($messages)): ?>
            <?php foreach ($messages as $message): ?>
                <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
                    <p><strong>名前: </strong><?php echo htmlspecialchars($message['sender_name'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>メッセージ: </strong><?php echo htmlspecialchars($message['message_text'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>投稿日時: </strong><?php echo htmlspecialchars($message['created_at'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p>
                        <a href="edit.php?id=<?php echo htmlspecialchars($message['id'], ENT_QUOTES, 'UTF-8'); ?>">編集</a>
                    <form action="delete.php" method="post">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($message['id'], ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" onclick="return confirm('このメッセージを削除しますか？')">削除</button>
                    </form>
                    </p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>まだメッセージはありません。最初のメッセージを投稿してみましょう！</p>
        <?php endif ?>
        <p><a href="post.php">投稿フォームへ</a></p>
        <p><a href="index.php">TOPページへ</a></p>
    </div>
    <!-- 投稿メッセージ一覧エリア end -->
</body>

</html>