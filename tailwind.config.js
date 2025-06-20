/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./*.{html,js,php}", // プロジェクトルートにあるHTMLファイルをスキャン
    "./src/**/*.{html,js,php}", // srcディレクトリ内のHTMLやJSファイルをスキャン (必要に応じて追加)
    // 他にもTailwindクラスを使うファイルがあればここに追加
  ],
  theme: {
    extend: {
      colors: {
        'cream-bg': '#fff7ef',
      },
    },
  },
  plugins: [],
}

