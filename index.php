<?php
session_start(); // セッション開始

require 'config.php'; // DB接続
require 'get_recipients_data.php';

// ===========================================
// メッセージ表示処理 (GETリクエスト時に実行)
// ===========================================
$messages = []; // 取得したメッセージを格納する配列を初期化
$selected_recipient_id = $_GET['filter_recipient_id'] ?? ''; // 選択された宛先IDを取得
$display_h1_name = '理一';

if (!empty($selected_recipient_id)) {
    foreach ($all_recipients as $recipient) {
        if ($recipient['id'] == $selected_recipient_id) {
            $display_h1_name = $recipient['name'];
            break;
        }
    }
}

try {
    // 全メッセージを取得するSQLクエリ
    // 新しい投稿が上に来るように、idを降順に並べ替える (ORDER BY id DESC)
    // テーブル名が 'messages' であることを確認してください
    $sql = "
    SELECT
        m.id,
        m.sender_name,
        m.message_text,
        m.created_at,
        r.name AS recipient_name -- recipientsテーブルのnameカラムをrecipient_nameとして取得
    FROM
        messages AS m
    JOIN
        recipients AS r ON m.recipient_id = r.id -- recipientsテーブルを外部参照する
   ";
    // 宛先が選択されている場合、WHERE句を追加
    if (!empty($selected_recipient_id)) {
        $sql .= " WHERE m.recipient_id = :filter_recipient_id";
    }

    $sql .= " ORDER BY m.created_at DESC";

    if (!empty($selected_recipient_id)) {
        //プレースホルダーがある場合
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':filter_recipient_id', $selected_recipient_id, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        //プレースホルダーがない場合(全件表示)
        $stmt = $pdo->query($sql);
    }

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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c&display=swap" rel="stylesheet">
    <link href="./dist/output.css" rel="stylesheet">
</head>

<body id="top" class="bg-cream-bg">
    <!-- 寄せ書き表示画面エリア start -->
    <div class="max-w-lg mx-auto px-3">
        <h1 class="text-2xl font-bold underline text-blue-600 mb-10 mt-10 text-center">🥳<?php echo htmlspecialchars($display_h1_name, ENT_QUOTES,); ?>くん誕生日、おめでとう！！🥳</h1>
        <?php if (isset($message_status) && !empty($message_status)): ?>
            <p class="text-red-500 text-center mb-4"><?php echo htmlspecialchars($message_status, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <div><img src="./img/main.jpg" alt=""></div>
        <?php if (!empty($messages)): ?>
            <?php foreach ($messages as $message): ?>
                <div class="border border-gray-300 p-5 bg-white mt-10">
                    <p><strong>名前: </strong><?php echo htmlspecialchars($message['sender_name'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>メッセージ: </strong><?php echo htmlspecialchars($message['message_text'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>宛先：</strong><?php echo htmlspecialchars($message['recipient_name'], ENT_QUOTES, 'UTF-8')  ?></p>
                    <p><strong>投稿日時: </strong><?php echo htmlspecialchars($message['created_at'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>まだメッセージはありません。最初のメッセージを投稿してみましょう！</p>
        <?php endif ?>
    </div>
    <!-- 寄せ書き表示画面エリア end -->
    <div class="max-w-lg mx-auto px-3 mt-10">
        <form action="index.php" method="GET">
            <div>
                <label for="filterRecipient" class="">宛先で絞り込む</label>
                <select name="filter_recipient_id" id="filterRecipient" class="border-2 border-amber-900 border-solid p-2 w-full">
                    <option value="">全ての宛先</option>
                    <?php foreach ($all_recipients as $recipient): ?>
                        <option value="<?php echo htmlspecialchars($recipient['id'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($recipient['name'], ENT_QUOTES, 'UTF-8'); ?></option>

                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex justify-center mt-8 mb-10">
                <button type="submit" class="bg-cyan-600 hover:bg-cyan-800 text-white font-bold py-2 px-4 rounded">絞り込む</button>
            </div>
        </form>
    </div>
    <footer class="text-center mt-20 bg-cyan-700 text-white pt-2 pb-2">
        © 2025 寄せ書きアプリ
    </footer>
</body>

</html>