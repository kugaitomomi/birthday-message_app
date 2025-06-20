# ベースイメージとしてPHPとApacheが同梱されたイメージを使用
FROM php:8.2-apache

# Composerをインストール
# 最新のComposerバイナリを取得し、/usr/local/bin/composer に配置
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# アプリケーションのコードをコンテナ内の /var/www/html ディレクトリにコピー
COPY . /var/www/html/

# Apacheのmod_rewriteを有効化（必要であれば）
RUN a2enmod rewrite

# 必要なPHP拡張機能をインストール（例: mysqli, pdo_mysql, gd など）
# YOUR_EXTENSION_NAME を必要な拡張機能の名前に置き換えてください
RUN docker-php-ext-install mysqli pdo_mysql # もしMySQLを使うなら
# RUN docker-php-ext-install pdo_pgsql       # もしPostgreSQLを使うなら
# RUN docker-php-ext-install gd              # 画像処理が必要なら

# Composerを使用する場合（オプション）
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN composer install --no-dev --optimize-autoloader # 依存関係をインストール

# コンテナがリッスンするポートを公開 (Apacheは通常80)
EXPOSE 80

# Apacheサーバーを起動するコマンド (php:apache イメージではデフォルトで設定されていることが多い)
# CMD ["apache2-foreground"]