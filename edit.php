<?php
session_start();
require('config.php');
require('get_recipients_data.php');

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
        $stmt = $pdo->prepare(
            "
        SELECT 
            m.id, m.sender_name,
            m.message_text,
            m.recipient_id,
            r.name AS recipient_name
        FROM messages AS m
        JOIN
            recipients AS r ON m.recipient_id = r.id
        WHERE
            m.id = :id"
        );
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
    $recipient_id = $_POST['recipient_id'] ?? null;

    $errors = [];

    if (empty($sender_name)) {
        $errors[] = 'お名前は必須項目です。';
    }
    if (empty($message_text)) {
        $errors[] = 'メッセージは必須項目です。';
    }
    if (empty($message_text)) {
        $errors[] = '送りたい相手は必須項目です。';
    }

    if (!empty($errors)) {
        $_SESSION['message_status'] = implode('<br>', $errors);

        $_SESSION['old_input'] = [
            'id' => $id,
            'message_text' => $message_text,
            'recipient_id' => $recipient_id,
        ];

        header('Location: edit.php?id=' . htmlspecialchars($id));
        exit();
    }

    try {
        $stmt = $pdo->prepare("
        UPDATE 
            messages
        SET 
            sender_name= :sender_name,
            message_text = :message_text,
            recipient_id = :recipient_id
        WHERE id = :id
        ");
        $stmt->bindParam(':sender_name', $sender_name, PDO::PARAM_STR);
        $stmt->bindParam(':message_text', $message_text, PDO::PARAM_STR);
        $stmt->bindParam(':recipient_id', $recipient_id, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c&display=swap" rel="stylesheet">
    <link href="./dist/output.css" rel="stylesheet">
</head>
</head>

<body>
    <header class="w-full">
        <ul class="flex justify-around mt-5 mb-5 max-w-lg mx-auto border-l-2 border-sky-800">
            <li class="flex-1"><a href="index.php" class="block px-3 py-2 text-center border-r-2 border-sky-800 hover:bg-blue-800 hover:text-white">寄せ書きTOP</a></li>
            <li class="flex-1"><a href="post.php" class="block px-3 py-2 text-center border-r-2 border-sky-800 hover:bg-blue-800 hover:text-white">投稿フォーム</a></li>
            <li class="flex-1"><a href="manage.php" class="block px-3 py-2 text-center border-r-2  border-sky-800 hover:bg-blue-800 hover:text-white">投稿一覧</a></li>
        </ul>
    </header>
    <section>
        <!-- 寄せ書き編集画面エリア start -->
        <div class="max-w-lg mx-auto px-3">
            <h1 class="text-center mt-10 mb-4 font-bold text-lg">メッセージ画面編集フォーム</h1>
            <p class="mb-4">お名前とメッセージを入力し、内容が良ければ「送信」ボタンをクリックしてください。</p>
            <p class="text-red-500 mt-3 mb-2 font-bold"><?php echo $message_status; ?></p>
            <?php if (!empty($message_data)): ?>
                <div class="space-y-6">
                    <form action="edit.php" method="post">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($message_data['id'] ?? $id, ENT_QUOTES, 'UTF-8') ?>">
                        <label for="senderArea" class="block mb-5">
                            <div class="flex flex-col md:grid md:grid-cols-[190px_1fr] gap-x-4 md:items-center">
                                <div class="text-left mb-2 md:mb-0">お名前【※必須】:</div>
                                <input type="text" name="sender_name" id="senderArea" class="border-2 border-amber-900 border-solid p-2" value="<?php echo htmlspecialchars($message_data['sender_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="お名前を入力してください">
                            </div>
                        </label>
                        <label for="messageArea" class="block mb-5">
                            <div class="flex flex-col md:grid md:grid-cols-[190px_1fr] gap-x-4 md:items-center">
                                <div class="text-left mb-2 md:mb-0">メッセージ【※必須】:</div>
                                <textarea name="message_text" id="messageArea" class="border-2 border-amber-900 border-solid p-2" cols="30" rows="10" placeholder="メッセージを入力してください。"><?php echo htmlspecialchars($message_data['message_text'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>
                        </label>
                        <label for="messageArea" class="mb-5">
                            <div class="flex flex-col md:grid md:grid-cols-[190px_1fr] gap-x-4 md:items-center">
                                <div class="text-left mb-2 md:mb-0">送りたい相手【※必須】:</div>
                                <select name="recipient_id" id="recipientArea" class="border-2 border-amber-900 border-solid p-2 w-full
    appearance-none          /* ブラウザのデフォルトスタイルを無効化 */
    focus:outline-none       /* フォーカス時のアウトラインを消す（任意） */
    bg-white                 /* 背景色を白にする（必要であれば） */
    pr-8                     /* 右側のパディングでアイコンのためのスペースを作る */">
                                    <option value="">選択してください</option>
                                    <?php foreach ($all_recipients as $recipient): ?>
                                        <option value="<?php echo htmlspecialchars($recipient['id'], ENT_QUOTES, 'UTF-8'); ?>" <?php if (($message_data['recipient_id'] ?? '') == $recipient['id']): ?> selected<?php endif; ?>>
                                            <?php echo htmlspecialchars($recipient['name'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </label>
                        <div class="flex justify-center max-w-sm mx-auto space-x-4 mt-10 mb-20">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-20">更新</button>
                            <button type="reset" class="bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-2 px-4 border border-blue-500 hover:border-transparent rounded">キャンセル</button>
                        </div>
                    </form>
                <?php else: ?>
                    <p>編集対象のメッセージが見つからないか、不正なアクセスです。</p>
                <?php endif; ?>
                </div>
        </div>
        <!-- 寄せ書き編集画面エリア end -->
    </section>
</body>

</html>