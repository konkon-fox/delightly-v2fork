<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content>
	<meta name="author" content>
	<title>同意鍵エラーログ閲覧</title>
	<link
		href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
		rel="stylesheet"
		integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM"
		crossorigin="anonymous"
	/>
	<style>
		td{
			max-width: 300px;
			text-overflow: ellipsis;
			overflow-x: hidden;
		}
		td:hover{
			text-overflow: clip;
			overflow-x: auto;
		}
	</style>
</head>
<body>
	<div class="container d-flex flex-column row-gap-2">
		<header>
			<form action="master.php" method="post">
				<input type="hidden" name="code" value="<?= htmlspecialchars($_POST['code'], ENT_QUOTES, 'UTF-8'); ?>">
				<button type="submit" class="btn btn-sm btn-secondary">← 管理ページへ戻る</button>
			</form>
		</header>
		<main></main>
		<h1>同意鍵エラーログ閲覧</h1>
			<?php
        $ITEMS_PER_PAGE = 1000;
				if (isset($_POST['page'])) {
				    $page = (int) $_POST['page'];
				} else {
				    $page = 1;
				}
				$queryKey = $_POST['key'] ?? '';
				$queryIp = $_POST['ip'] ?? '';

				require_once './utils/safe-file.php';
				require_once './extend/SystemDB.php';

				$db = new SystemDB();

				if ($db->isSQLiteMode()) {
				    // DBからログ取得(v4-)

				    $options = [
				        'page' => $page,
				        'per_page' => $ITEMS_PER_PAGE,
				    ];
				    if ($queryKey !== '') {
				        $options['key'] = $queryKey;
				    }
				    if ($queryIp !== '') {
				        $options['ip'] = $queryIp;
				    }

				    $result = $db->searchFromKeyErrorLog($options);
				    if ($result === false) {
				        exit('ログの検索に失敗しました。');
				    }
				    $logs = $result['logs'];
				    $maxPage = ceil($result['total_count'] / $ITEMS_PER_PAGE);
				} else {
				    exit('SQLiteに対応していません。');
				}

				$prevPage = $page - 1;
				if ($prevPage < 1) {
				    $prevPage = 1;
				}
				$nextPage = $page + 1;
				if ($nextPage > $maxPage) {
				    $nextPage = $maxPage;
				}
				?>
		<nav aria-label="Page navigation example"  class="d-flex flex-column row-gap-2">
			<form action="?mode=key-error-logs" method="post" class="d-flex flex-column row-gap-2 align-items-start">
				<input type="hidden" name="code" value="<?=htmlspecialchars($_POST['code'], ENT_QUOTES, 'UTF-8');?>">
				<div class="w-100">
					<label for="key" class="form-label">同意鍵</label>
					<input type="text" class="form-control" id="key" name="key" value="<?=htmlspecialchars($queryKey, ENT_QUOTES, 'UTF-8'); ?>">
				</div>
				<div class="w-100">
					<label for="ip" class="form-label">IPアドレス</label>
					<input type="text" class="form-control" id="ip" name="ip" value="<?=htmlspecialchars($queryIp, ENT_QUOTES, 'UTF-8'); ?>">
				</div>
				<button class="btn btn-primary">検索</button>
			</form>
			<ul class="pagination">
					<li class="page-item">
						<form action="?mode=key-error-logs" method="post">
							<input type="hidden" name="code" value="<?=htmlspecialchars($_POST['code'], ENT_QUOTES, 'UTF-8');?>">
							<input type="hidden" name="page" value="<?= $prevPage; ?>">
							<input type="hidden" name="key" value="<?=htmlspecialchars($queryKey, ENT_QUOTES, 'UTF-8'); ?>">
							<input type="hidden" name="ip" value="<?=htmlspecialchars($queryIp, ENT_QUOTES, 'UTF-8'); ?>">
							<button type="submit" class="page-link<?= ($page <= 1) ? ' disabled' : ''; ?>">前へ</button>
						</form>
					</li>
					<li class="page-item">
						<form action="?mode=key-error-logs" method="post">
							<input type="hidden" name="code" value="<?=htmlspecialchars($_POST['code'], ENT_QUOTES, 'UTF-8');?>">
							<input type="hidden" name="page" value="<?= $nextPage; ?>">
							<input type="hidden" name="key" value="<?=htmlspecialchars($queryKey, ENT_QUOTES, 'UTF-8'); ?>">
							<input type="hidden" name="ip" value="<?=htmlspecialchars($queryIp, ENT_QUOTES, 'UTF-8'); ?>">
							<button type="submit" class="page-link<?= ($page >= $maxPage) ? ' disabled' : ''; ?>">次へ</button>
						</form>
					</li>
			</ul>
			<div>ページ: <?= $page; ?></div>
		</nav>
		<div>
			<div class="d-flex flex-wrap gap-2">
				<label>
					<input type="checkbox" class="form-check-input"id="checkbox--date" name="date" checked>日付
				</label>
				<label>
					<input type="checkbox" class="form-check-input"id="checkbox--key" name="key" checked>同意鍵
				</label>
				<label>
					<input type="checkbox" class="form-check-input"id="checkbox--ip" name="ip" checked>IP
				</label>
				<label>
					<input type="checkbox" class="form-check-input"id="checkbox--host" name="host" checked>ホスト
				</label>
				<label>
					<input type="checkbox" class="form-check-input"id="checkbox--ua" name="ua">UA
				</label>
				<label>
					<input type="checkbox" class="form-check-input"id="checkbox--message" name="message" checked>エラー文
				</label>
			</div>
		</div>
		<div class="overflow-x-auto overflow-y-auto" style="height:70vh;">
			<?php
				// ログ一覧
				echo '<table class="table table-sm table-bordered table-striped table-hover">';
				// テーブルヘッダー
				echo '<thead class="sticky-top">';
				echo '<tr class="table-primary">';
				echo '<th class="cell--date text-nowrap">日付</th>';
				echo '<th class="cell--key text-nowrap">同意鍵</th>';
				echo '<th class="cell--ip text-nowrap">IP</th>';
				echo '<th class="cell--host text-nowrap">HOST</th>';
				echo '<th class="cell--ua text-nowrap">UA</th>';
				echo '<th class="cell--message text-nowrap">エラー文</th>';
				echo '</tr>';
				echo '</thead>';
				// テーブルボディ
				echo '<tbody>';
				foreach ($logs as $log) {
				    $log = array_map(function ($value) {
				        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
				    }, $log);
				    echo '<tr>';
				    echo "<td class=\"cell--date text-nowrap\">{$log['date']}</td>";
				    echo "<td class=\"cell--key text-nowrap\">{$log['key']}</td>";
				    echo "<td class=\"cell--ip text-nowrap\">{$log['ip']}</td>";
				    echo "<td class=\"cell--host text-nowrap\">{$log['host']}</td>";
				    echo "<td class=\"cell--ua text-nowrap\">{$log['ua']}</td>";
				    echo "<td class=\"cell--message text-nowrap\">{$log['message']}</td>";
				    echo '</tr>';
				}
				echo '</tbody>';
				echo '</table>';
				?>
		</div>
	</div>
<script>
(() => {
  const LS_TOGGLES = 'operate-key-error-logs-toggles';
  const formCheckInputs = document.querySelectorAll('.form-check-input');

  const firstToggles = (() => {
    if (!window.localStorage) return {};
    const rawLoadData = window.localStorage.getItem(LS_TOGGLES);
    if (rawLoadData === null) return {};
    return JSON.parse(rawLoadData);
  })();

  // 初期表示
  formCheckInputs.forEach((cb) => {
    const tds = document.querySelectorAll(`.cell--${cb.name}`);

    if (firstToggles[cb.name] === true) {
      // lsでtrueの場合
      cb.checked = true;
      tds.forEach((td) => {
        td.style.display = '';
      });
    } else if (firstToggles[cb.name] === false) {
      // lsでfalseの場合
      cb.checked = false;
      tds.forEach((td) => {
        td.style.display = 'none';
      });
    } else {
      // lsでundefinedの場合
      // デフォルトでチェックなしの項目を非表示
      if (cb.checked === false) {
        tds.forEach((td) => {
          td.style.display = 'none';
        });
      }
    }
  });

  formCheckInputs.forEach((el) => {
    el.addEventListener('change', () => {
      // カラムの表示非表示を切り替え
      const tds = document.querySelectorAll(`.cell--${el.name}`);
      if (el.checked === true) {
        tds.forEach((td) => {
          td.style.display = '';
        });
      } else {
        tds.forEach((td) => {
          td.style.display = 'none';
        });
      }
      // localStorageへセーブ
      if (window.localStorage) {
        const toggles = {};
        formCheckInputs.forEach((cb) => {
          if (cb.checked === true) {
            toggles[cb.name] = true;
          } else {
            toggles[cb.name] = false;
          }
        });
        const saveData = JSON.stringify(toggles);
        window.localStorage.setItem(LS_TOGGLES, saveData);
      }
    });
  });
})();

</script>
</body>
</html>