<?php
session_start();
require('config.php');

$message_status = '';
if (isset($_SESSION['message_status'])) {
    $message_status = $_SESSION['message_status'];
    unset($_SESSION['message_status']);
}

$id = $_GET['id'] ?? null; // GETでidを取得
$message_data = []; //編集内容を配列形式で取得

// ===========================================
// メッセージデータ読み込み処理 (GETリクエスト時)
// ===========================================

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!$id) {
        $_SESSION['message_status'] = '編集対象のメッセージが指定されていません。';

        header('Location: manage.php');
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT id, sender_name, message_text FROM messages WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $message_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$message_data) {
            $_SESSION['message_status'] = '指定されたメッセージが見つかりませんでした。';

            header('Location: manage.php');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['message_status'] = 'メッセージの読み込みに失敗しました:' . $e->getMessage();

        header('Location: manage.php');
        exit();
    }
}
// ===========================================
// メッセージ更新処理 (POSTリクエスト時)
// ===========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $sender_name = $_POST['sender_name'] ?? null;
    $message_text = $_POST['message_text'] ?? null;

    $message_data = [
        'id' => $id,
        'sender_name' => $sender_name,
        'message_text' => $message_text,
    ];

    if ($id) {
        try {
            $stmt = $pdo->prepare("SELECT id, sender_name, message_text FROM messages WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $message_data = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $message_data = [];
        }
    }

    if (!$id || empty($sender_name) || empty($message_text)) {
        $_SESSION['message_status'] = '名前とメッセージを両方入力してください。';

        //リダイレクトする際は、対処の個々のページに飛ばしたいからid指定を行う。
        header('Location: edit.php?id=' . htmlspecialchars($id));
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE messages SET sender_name= :sender_name, message_text = :message_text WHERE id = :id");
        $stmt->bindParam(':sender_name', $sender_name, PDO::PARAM_STR);
        $stmt->bindParam(':message_text', $message_text, PDO::PARAM_STR);
        $stmt->bindParam('id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $_SESSION['message_status'] = 'メッセージが更新されました。';
        header('Location: manage.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['message_status'] = 'メッセージの更新に失敗しました: ' . $e->getMessage();

        header('Location: edit.php?id=' . htmlspecialchars($id));
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>編集ページ</title>
</head>

<body>
    <!-- 寄せ書き編集画面エリア start -->
    <div>
        <h1>メッセージ画面編集フォーム</h1>
        <p>お名前とメッセージを入力し、内容が良ければ「送信」ボタンをクリックしてください。</p>

        <?php if (!empty($message_data)): ?>
            <form action="edit.php" method="post">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($message_data['id'] ?? $id, ENT_QUOTES, 'UTF-8') ?>">
                <label for="senderArea">
                    お名前【※必須】:
                    <input type="text" name="sender_name" id="senderArea" value="<?php echo htmlspecialchars($message_data['sender_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="お名前を入力してください">
                </label>
                <label for="messageArea">
                    メッセージ【※必須】:
                    <textarea name="message_text" id="messageArea" cols="30" rows="10" placeholder="メッセージを入力してください。"><?php echo htmlspecialchars($message_data['message_text'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </label>
                <p><?php echo $message_status; ?></p>
                <button type="submit">更新</button>
                <button type="reset">キャンセル</button>
            </form>
        <?php else: ?>
            <p>編集対象のメッセージが見つからないか、不正なアクセスです。</p>
        <?php endif; ?>
        <p><a href="manage.php">メッセージ一覧に戻る</a></p>

    </div>
    <!-- 寄せ書き編集画面エリア end -->
</body>

</html>