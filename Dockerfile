# ベースイメージとしてPHPとApacheが同梱されたイメージを使用
FROM php:8.2-apache

# 必要なPHP拡張機能をインストール
# libpq-dev をインストールするために apt-get update と apt-get install を追加
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo_pgsql \
    && docker-php-ext-enable pdo_pgsql \
    && a2enmod rewrite

# --- ここに Node.js (npm) のインストールを追加します ---
# Node.js公式のDebianリポジトリを追加
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs build-essential

# npmキャッシュのクリーンアップとパーミッション調整（推奨）
RUN npm cache clean --force && npm config set unsafe-perm true
# --- Node.js (npm) のインストール終わり ---


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