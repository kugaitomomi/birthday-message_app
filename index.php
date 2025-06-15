<?php
// config.php を読み込み、データベース接続を確立
require 'config.php';

// メッセージの投稿結果をユーザーに伝えるための変数
session_start(); // PHPスクリプトの開始時に必ずセッションを開始。ヘッダー送信前である必要がある。

$message_status = ''; // メッセージの投稿結果をユーザーに伝えるための変数

// セッションにメッセージがある場合は取得し、すぐに削除する
if (isset($_SESSION['message_status'])) {
    $message_status = $_SESSION['message_status'];
    unset($_SESSION['message_status']); //都度セッション判定をしたいので、unsetで削除する必要がある。
}

// ===========================================
// メッセージ投稿処理 (POSTリクエストがあった場合)
// ===========================================
// HTTPメソッドがPOSTかどうかを確認
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // フォームから送信された値を取得
    // $_POSTはフォームのname属性に対応。

    $sender_val = $_POST['sender'] ?? '';
    $message_val = $_POST['message'] ?? '';

    if (empty($sender_val) || empty($message_val)) {
        $_SESSION['message_status'] = '名前とメッセージを両方入力してください。';

        header('Location: index.php'); // ★ エラーの場合もリダイレクト
        exit(); // ここで処理を終了
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO messages (sender_name, message_text) VALUE (:sender_val, :message_val)");

            // プレースホルダに実際の値をバインド（割り当てる）
            $stmt->bindParam(':sender_val', $sender_val, PDO::PARAM_STR); //PDO::PARAM_STRは文字列として扱うことを示す
            $stmt->bindParam(':message_val', $message_val, PDO::PARAM_STR);

            // SQL文を実行
            $stmt->execute();

            $_SESSION['message_status'] = 'メッセージが投稿されました！';

            // 投稿成功後、フォームの入力をクリアするために、ページをリロードする (リダイレクト)
            header('Location: index.php');
            exit(); // リダイレクト後は必ずexit()でスクリプトの実行を終了する
        } catch (PDOException $e) {
            // データベースへの挿入に失敗した場合
            $message_status = 'メッセージ投稿に失敗しました: ' . $e->getMessage();

            header('Location: index.php'); // ★ エラーの場合もリダイレクト
            exit(); // ここで処理を終了
        }
    }
}

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
    var_dump($messages); //配列を直接echoすると "Array" と表示されるだけで内容が見えないので注意。デバッグにはvar_dump()が便利です。
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
    <!-- 寄せ書き登録画面エリア start -->
    <h1>寄せ書きアプリへようこそ！</h1>
    <div>
        <h1>メッセージ画面入力フォーム</h1>
        <p>お名前とメッセージを入力し、内容が良ければ「送信」ボタンをクリックしてください。</p>
        <form action="index.php" method="post">
            <label for="senderArea">
                お名前【※必須】:
                <input type="text" name="sender" id="senderArea" placeholder="名前を入力してください。">
            </label>
            <label for="messageArea">
                メッセージ【※必須】:
                <textarea name="message" id="messageArea" cols="30" rows="10" placeholder="メッセージを入力してください。"></textarea>
            </label>
            <p><?php echo $message_status; ?></p>
            <button type="submit">送信</button>
            <button type="reset">キャンセル</button>
        </form>
    </div>
    <!-- 寄せ書き登録画面エリア end -->
    <!-- 寄せ書き表示画面エリア start -->
    <div>
        <h1>理一くん誕生日、おめでとう！！🥳🥳🥳</h1>
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