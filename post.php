<?php
// config.php を読み込み、データベース接続を確立
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'config.php';
require 'get_recipients_data.php';

// メッセージの投稿結果をユーザーに伝えるための変数
//session_start(); // PHPスクリプトの開始時に必ずセッションを開始。ヘッダー送信前である必要がある。
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

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
    $recipient_id_val = $_POST['recipient_id'] ?? '';

    if (empty($sender_val) || empty($message_val) || empty($recipient_id_val)) {
        $_SESSION['message_status'] = '※名前とメッセージを入力し、送りたい相手を選択してください。';

        header('Location: post.php'); // ★ エラーの場合もリダイレクト
        exit(); // ここで処理を終了
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO messages (sender_name, message_text, recipient_id) VALUE (:sender_val, :message_val, :recipient_id_val)");

            // プレースホルダに実際の値をバインド（割り当てる）
            $stmt->bindParam(':sender_val', $sender_val, PDO::PARAM_STR); //PDO::PARAM_STRは文字列として扱うことを示す
            $stmt->bindParam(':message_val', $message_val, PDO::PARAM_STR);
            $stmt->bindParam(':recipient_id_val', $recipient_id_val, PDO::PARAM_INT);

            // SQL文を実行
            $stmt->execute();

            $_SESSION['message_status'] = 'メッセージが投稿されました！';

            // 投稿成功後、フォームの入力をクリアするために、ページをリロードする (リダイレクト)
            header('Location: manage.php');
            exit(); // リダイレクト後は必ずexit()でスクリプトの実行を終了する
        } catch (PDOException $e) {
            // データベースへの挿入に失敗した場合
            $message_status = 'メッセージ投稿に失敗しました: ' . $e->getMessage();

            //header('Location: post.php'); // ★ エラーの場合もリダイレクト
            exit(); // ここで処理を終了
        }
    }
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>投稿フォーム</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c&display=swap" rel="stylesheet">
    <link href="./dist/output.css" rel="stylesheet">
</head>

<body>
    <header class="w-full">
        <ul class="flex justify-around mt-5 mb-5 max-w-lg mx-auto border-l-2 border-sky-800">
            <li class="flex-1"><a href="index.php" class="block px-3 py-2 text-center border-r-2 border-sky-800 hover:bg-cyan-700 hover:text-white">寄せ書きTOP</a></li>
            <li class="flex-1"><a href="post.php" class="block px-3 py-2 text-center border-r-2 border-sky-800 hover:bg-cyan-700 hover:text-white">投稿フォーム</a></li>
            <li class="flex-1"><a href="manage.php" class="block px-3 py-2 text-center border-r-2  border-sky-800 hover:bg-cyan-700 hover:text-white">投稿一覧</a></li>
        </ul>
    </header>
    <section>
        <!-- 寄せ書き登録画面エリア start -->
        <div class="max-w-lg mx-auto px-3">
            <h1 class="text-center mt-10 mb-4 font-bold text-lg">メッセージを送ろう！🥳🥳🥳</h1>
            <p class="mb-4">お名前とメッセージを入力し、メッセージを送りたい相手を選択してください。<br>
                内容がよろしければ「送信」ボタンをクリックしてください。</p>
            <p class="text-red-500 mt-3 mb-2 font-bold"><?php echo $message_status; ?></p>
            <form action="post.php" method="post">
                <div class="space-y-6">
                    <label for="senderArea" class="block">
                        <div class="flex flex-col md:grid md:grid-cols-[190px_1fr] gap-x-4 md:items-center">
                            <div class="text-left mb-2 md:mb-0">お名前【※必須】:</div>
                            <input type="text" name="sender" id="senderArea" class="border-2 border-amber-900 border-solid p-2" placeholder="名前を入力してください。">
                        </div>
                    </label>
                    <label for="messageArea" class="block">
                        <div class="flex flex-col md:grid md:grid-cols-[190px_1fr] gap-x-4 md:items-center">
                            <div class="text-left mb-2 md:mb-0">メッセージ【※必須】:</div>
                            <textarea name="message" id="messageArea" class="border-2 border-amber-900 border-solid p-2" cols="30" rows="10" placeholder="メッセージを入力してください。"></textarea>
                        </div>
                    </label>
                    <label for="recipientArea" class="block">
                        <div class="flex flex-col md:grid md:grid-cols-[190px_1fr] gap-x-4 md:items-center">
                            <div class="text-left mb-2 md:mb-0">送りたい相手【※必須】:</div>
                            <select name="recipient_id" id="recipientArea" class="border-2 border-amber-900 border-solid p-2 w-full
    appearance-none          /* ブラウザのデフォルトスタイルを無効化 */
    focus:outline-none       /* フォーカス時のアウトラインを消す（任意） */
    bg-white                 /* 背景色を白にする（必要であれば） */
    pr-8                     /* 右側のパディングでアイコンのためのスペースを作る */">
                                <option value="">選択してください。</option>
                                <?php foreach ($all_recipients as $recipient): ?>
                                    <option value="<?php echo htmlspecialchars($recipient['id'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($recipient['name'], ENT_QUOTES, 'UTF-8'); ?></option>

                                <?php endforeach; ?>
                            </select>
                        </div>
                    </label>
                </div>
                <div class="flex justify-center max-w-sm mx-auto space-x-4 mt-10 mb-20">
                    <button type="submit" class="bg-cyan-600 hover:bg-cyan-800 text-white font-bold py-2 px-4 rounded mr-20">送信</button>
                    <button type="reset" class="bg-transparent hover:bg-gray-800 text-gray-800 font-semibold hover:text-white py-2 px-4 border border-gray-800 hover:border-transparent rounded">キャンセル</button>
                </div>
            </form>
        </div>
        <!-- 寄せ書き登録画面エリア end -->

    </section>
    <footer class="text-center mt-20 bg-cyan-700 text-white pt-2 pb-2">
        © 2025 寄せ書きアプリ
    </footer>
</body>

</html>