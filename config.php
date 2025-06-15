<?php
//config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'birthday_app'); //作成したデータベース名
define('DB_USER', 'root');  // あなたのデータベースユーザー名
define('DB_PASS', 'root');  // あなたのデータベースパスワード

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // エラー発生時に例外をスロー
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // 取得結果を連想配列にする
            PDO::ATTR_EMULATE_PREPARES => false, // プリペアドステートメントのエミュレーションを無効にする
        ]
    );
    //echo "データベースに接続成功！"; // テスト用: 接続できたらこの行をコメントアウトまたは削除
} catch (PDOException $e) {
    // データベース接続に失敗した場合
    echo "データベース接続エラー:" . $e->getMessage();
    exit(); // エラー時は処理を中断    
}
