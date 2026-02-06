<?php

if (!isset($_GET['bbs']) || empty($_GET['bbs'])) {
    echo 'bbsを指定してください。';
    exit;
}
if (!preg_match('/^[a-zA-Z0-9_-]+$/', $_GET['bbs'])) {
    echo 'bbsが不正です。';
    exit;
}
$settingPath = "../{$_GET['bbs']}/setting.json";
$bbsTitle = '';
if (is_file($settingPath)) {
    $settingHandle = fopen($settingPath, 'r');
    if ($settingHandle) {
        if (flock($settingHandle, LOCK_SH)) {
            $settingJson = stream_get_contents($settingHandle);
            flock($settingHandle, LOCK_UN);
            $setting = json_decode($settingJson, true);
            if (isset($setting['BBS_TITLE'])) {
                $bbsTitle = $setting['BBS_TITLE'];
            }
        }
        fclose($settingHandle);
    }
}
$safeBbs = htmlspecialchars($_GET['bbs'], ENT_QUOTES, 'UTF-8');

?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $bbsTitle ?> 過去ログ</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM"
      crossorigin="anonymous"
    />
    <style>
      a {
        text-decoration: none;
      }
      a:hover {
        text-decoration: underline;
      }
      .hidden-input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
      }
      #loading {
        pointer-events: none;
        background-color: rgb(0 0 0 / 0.5);
        transition: opacity 100ms ease-in 500ms;
      }
      .my-spinner {
        width: 64px;
        height: 64px;
      }
    </style>
  </head>
  <body class="bg-body-secondary">
    <div class="container d-flex flex-column row-gap-2 p-2">
      <h1 class="fs-1 m-0">
        <?= $bbsTitle ?>
        過去ログ
      </h1>

      <div class="bg-white p-2">
        <form
          id="search-form"
          hx-get="/api/get-kakolog.php"
          hx-trigger="submit, auto-search"
          hx-target="#result"
          hx-swap="innerHTML"
          hx-indicator="#loading"
          class="d-flex flex-column row-gap-3 align-items-start"
        >
          <!-- AND・OR指定, リセットボタン-->
          <div class="d-flex column-gap-1 w-100">
            <input
              type="radio"
              class="btn-check"
              name="and-or"
              id="option-and"
              autocomplete="off"
              value="and"
              checked
            />
            <label class="btn btn-sm btn-outline-success" for="option-and"
              >AND</label
            >
            <input
              type="radio"
              class="btn-check"
              name="and-or"
              id="option-or"
              autocomplete="off"
              value="or"
            />
            <label class="btn btn-sm btn-outline-success" for="option-or"
              >OR</label
            >
            <button type="reset" class="btn btn-sm btn-secondary ms-auto">
              クリア
            </button>
          </div>

          <!-- キーワード指定 -->
          <input
            type="text"
            name="keywords"
            placeholder="スレタイ検索"
            class="form-control"
          />

          <!-- 日付指定-->
          <details>
            <summary>日付指定</summary>
            <div>
              <input type="date" name="since-date" class="form-control" />
              ～
              <input type="date" name="until-date" class="form-control" />
            </div>
          </details>

          <!-- レス数-->
          <details>
            <summary>レス数指定</summary>
            <div>
              <input type="number" name="min-res" class="form-control" />
              ～
              <input type="number" name="max-res" class="form-control" />
            </div>
          </details>

          <!-- bbs指定 -->
          <input type="hidden" name="bbs" value="<?= $safeBbs ?>" />

          <!-- submit-->
          <button type="submit" class="btn btn-primary">検索</button>
        </form>
      </div>

      <div class="bg-white p-2">
        <div id="result"></div>
      </div>
    </div>

    <!-- ローディング -->
    <div
      id="loading"
      class="htmx-indicator position-fixed top-50 start-50 translate-middle d-flex p-3 rounded"
    >
      <div
        class="spinner-border spinner-border-lg text-white my-spinner"
        role="status"
      >
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <!------------------------------------------------------------------------------------------------>
    <script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.8/dist/htmx.min.js"></script>
    <script>
      const searchForm = document.getElementById('search-form');
      // 自動検索 ---------------------------------------------------------------
      document.addEventListener('DOMContentLoaded', autoSearch);

      function autoSearch() {
        // クエリパラメータからinputに入力
        const params = new URLSearchParams(location.search);
        // and検索 or検索
        if (params.get('and-or') === 'or') {
          searchForm['and-or'].value = 'or';
        }
        // キーワード検索
        searchForm.keywords.value = decodeURIComponent(
          params.get('keywords') || ''
        );
        // 日付指定
        searchForm['since-date'].value = params.get('since-date') || '';
        searchForm['until-date'].value = params.get('until-date') || '';
        // レス数指定
        searchForm['min-res'].value = params.get('min-res') || '';
        searchForm['max-res'].value = params.get('max-res') || '';

        // 検索実行
        htmx.trigger(searchForm, 'auto-search');
      }

      // 検索パラメータによるurlの変更 --------------------------------------------
      searchForm.addEventListener('htmx:configRequest', updatePushUrl);

      /**
       * htmxのリクエスト設定を傍受し、パラメータを動的に設定する関数
       * @param {Event} event htmxのconfigRequestイベント
       */
      function updatePushUrl(event) {
        // 1. リクエストパラメータを取得
        // event.detail.parameters にはフォームの入力値が入っている
        const params = new URLSearchParams(event.detail.parameters);
        params.set(
          'keywords',
          encodeURIComponent(params.get('keywords') || '')
        );

        const newUrl = '?' + params.toString();
        history.pushState({ newUrl }, '', newUrl);
      }

      // DOM置換後 ---------------------------------------------------------------
      document
        .getElementById('result')
        .addEventListener('htmx:afterSwap', applyCopyBtns);

      function applyCopyBtns() {
        const btns = document.querySelectorAll('.copy-button');
        btns.forEach((btn, i) => {
          btn.addEventListener('click', () => {
            const targetIndex = btn.dataset.index;
            if (targetIndex === null) return;
            const targetInput = document.querySelector(
              `#hidden-input-${targetIndex}`
            );
            if (targetInput === null) return;
            targetInput.select();
            document.execCommand('copy');
          });
        });
      }
      // クリアボタン ---------------------------------------------------------------
      searchForm.addEventListener('reset', resetUrl);

      function resetUrl() {
        const params = new URLSearchParams(location.search);
        const bbs = params.get('bbs') || '';
        const newUrl = '?bbs=' + bbs;
        history.pushState({ newUrl }, '', newUrl);
      }
    </script>
  </body>
</html>
