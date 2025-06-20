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
    $stmt = $pdo->query("
    SELECT
        m.id,
        m.sender_name,
        m.message_text,
        m.created_at,
        r.name AS recipient_name
    FROM
        messages AS m
    JOIN
        recipients AS r ON m.recipient_id = r.id
    ORDER BY
        m.created_at DESC
    ");
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c:wght@400;700&display=swap" rel="stylesheet">
    <link href="./dist/output.css" rel="stylesheet">

</head>

<body>
    <header class="w-full">
        <ul class="flex justify-around mt-5 mb-5 max-w-lg mx-auto border-l-2 border-sky-800">
            <li class="flex-1"><a href="index.php" class="block px-3 py-2 text-center border-r-2 border-sky-800 hover:bg-blue-800 hover:text-white">寄せ書きTOP</a></li>
            <li class="flex-1"><a href="post.php" class="block px-3 py-2 text-center border-r-2 border-sky-800 hover:bg-blue-800 hover:text-white">投稿フォーム</a></li>
            <li class="flex-1"><a href="manage.php" class="block px-3 py-2 text-center border-r-2  border-sky-800 hover:bg-blue-800 hover:text-white">投稿一覧</a></li>
        </ul>
    </header>
    <!-- 投稿メッセージ一覧エリア start -->
    <section>
        <div class="max-w-lg mx-auto px-3">
            <p><?php echo $message_status; ?></p>
            <h1 class="text-center mt-10 mb-4 font-bold text-lg">投稿メッセージ一覧</h1>
            <?php if (!empty($messages)): ?>
                <?php foreach ($messages as $message): ?>
                    <div class="border-2 border-sky-500 p-5 mb-6">
                        <p class="pb-1"><span class="font-black">名前:</span><?php echo htmlspecialchars($message['sender_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="pb-1"><span class="font-black">メッセージ: </span><?php echo htmlspecialchars($message['message_text'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="pb-1"><span class="font-black">投稿日時: </span><?php echo htmlspecialchars($message['created_at'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="pb-1"><span class="font-black">宛先: </span><?php echo htmlspecialchars($message['recipient_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <div class="flex justify-center max-w-sm mx-auto space-x-4 mt-6">
                            <a href="edit.php?id=<?php echo htmlspecialchars($message['id'], ENT_QUOTES, 'UTF-8'); ?>" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-20">編集する</a>
                            <form action="delete.php" method="post">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($message['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" onclick="return confirm('このメッセージを削除しますか？')" class="bg-transparent hover:bg-gray-800 text-gray-800 font-semibold hover:text-white py-2 px-4 border border-gray-800 hover:border-transparent rounded">削除</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>まだメッセージはありません。最初のメッセージを投稿してみましょう！</p>
            <?php endif ?>
        </div>
        <!-- 投稿メッセージ一覧エリア end -->
    </section>
</body>

</html>