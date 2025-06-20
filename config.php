<?php
//config.php
// define('DB_HOST', 'localhost');
// define('DB_NAME', 'birthday_app'); //作成したデータベース名
// define('DB_USER', 'root');  // あなたのデータベースユーザー名
// define('DB_PASS', 'root');  // あなたのデータベースパスワード

// Renderの環境変数を直接読み込むように修正
// 環境変数が設定されていない場合でもデフォルト値（null）でエラーを防ぐ
$host = $_ENV['DB_HOST'] ?? getenv('DB_HOST');
$dbname = $_ENV['DB_NAME'] ?? getenv('DB_NAME');
$user = $_ENV['DB_USER'] ?? getenv('DB_USER');
$password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD');
$port = $_ENV['DB_PORT'] ?? '5432';


try {
    // PostgreSQL用のDSN (Data Source Name) を構築
    $dsn = "pgsql:host={$host};port=$port;dbname={$dbname}";
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // 必要であれば、ここでテーブルがなければ作成するなどの初期化処理を追加
    // 例: $pdo->exec("CREATE TABLE IF NOT EXISTS messages (id SERIAL PRIMARY KEY, content TEXT)");

    //echo "データベースに接続成功！"; // テスト用: 接続できたらこの行をコメントアウトまたは削除
} catch (PDOException $e) {
    // データベース接続に失敗した場合
    echo "データベース接続エラー:" . $e->getMessage();
    exit('データベース接続に失敗しました。管理者にお問い合わせください。'); // エラー時は処理を中断    
}
