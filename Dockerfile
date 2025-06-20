# ベースイメージとしてPHPとApacheが同梱されたイメージを使用
FROM php:8.2-apache # あなたのPHPバージョンに合わせてください

# 必要なPHP拡張機能をインストール
# PostgreSQLを使うので pdo_pgsql をインストール
RUN docker-php-ext-install pdo_pgsql \
    && docker-php-ext-enable pdo_pgsql \
    && a2enmod rewrite # Apacheのmod_rewriteを有効化

# アプリケーションのコードをコンテナ内の /var/www/html ディレクトリにコピー
COPY . /var/www/html/

# コンテナの作業ディレクトリをアプリケーションのルートに設定
WORKDIR /var/www/html/

# Composerは使わないので、Composer関連の行は削除またはコメントアウト
# COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
# RUN composer install --no-dev --optimize-autoloader

# --- Tailwind CSS をビルドするためのNode.js関連のコマンド ---
# package.json をコピー
COPY package.json ./

# Node.jsの依存関係をインストール (npm install)
RUN npm install

# Tailwind CSS をビルド (npm run build)
# このコマンドが package.json の "scripts": { "build": "..." } と一致することを確認してください
RUN npm run build
# --- ここまで ---

# コンテナがリッスンするポートを公開 (Apacheは通常80)
EXPOSE 80

# Apacheサーバーを起動するコマンド (php:apache イメージではデフォルトで設定されていることが多い)
# CMD ["apache2-foreground"]