<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content>
	<meta name="author" content>
	<title>認証ログ閲覧</title>
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
		<h1>認証ログ閲覧</h1>
		<p>
			同意鍵被りが発生した場合、該当同意鍵の環境ファイルを削除すると再認証時に新しい鍵が発行されます。
		</p>
			<?php
        $ITEMS_PER_PAGE = 1000;
				if (isset($_POST['page'])) {
				    $page = (int) $_POST['page'];
				} else {
				    $page = 1;
				}

				require_once './utils/safe-file.php';

				// ログデータ取得
				$LOGFILE = './HAP/log.cgi';
				$n = 0;
				if (!is_file($LOGFILE)) {
				    exit('<p class="fw-bold">ログファイルがありません。</p></div></body></html>');
				}
				$logs = safe_file($LOGFILE);
				if ($logs === false) {
				    exit('<p class="fw-bold">ログファイルの取得に失敗しました。</p></div></body></html>');
				}
				$logs = array_reverse($logs);
				$maxPage = ceil(count($logs) / $ITEMS_PER_PAGE);
				$offset = ($page - 1) * $ITEMS_PER_PAGE;
				$logs = array_slice($logs, $offset, $ITEMS_PER_PAGE);
				$logs = array_map(function ($line) {
				    $data = explode('<>', rtrim($line));
				    $data = array_pad($data, 11, '');
				    list($date, $status, $wrtAgreementKey, $IP, $HOST, $isp, $asname, $UA, $CH_UA, $clientId, $enFile) = $data;
				    $accountId = hash('sha256', hash('sha256', md5($wrtAgreementKey) . preg_replace('/[^0-9]/', '', md5($wrtAgreementKey))));
				    return [
				        'date' => $date,
				        'status' => $status,
				        'key' => $wrtAgreementKey,
				        'ip' => $IP,
				        'host' => $HOST,
				        'isp' => $isp,
				        'asname' => $asname,
				        'ua' => $UA,
				        'ch_ua' => $CH_UA,
				        'client_id' => $clientId,
				        'en_file' => $enFile,
				        'account_id' => $accountId,
				    ];
				}, $logs);

				$prevPage = $page - 1;
				if ($prevPage < 1) {
				    $prevPage = 1;
				}
				$nextPage = $page + 1;
				if ($nextPage > $maxPage) {
				    $nextPage = $maxPage;
				}
				?>
		<nav aria-label="Page navigation example">
			<ul class="pagination">
					<li class="page-item">
						<form action="?mode=auth-logs" method="post">
							<input type="hidden" name="code" value="<?=htmlspecialchars($_POST['code'], ENT_QUOTES, 'UTF-8');?>">
							<input type="hidden" name="page" value="<?= $prevPage; ?>">
							<button type="submit" class="page-link<?= ($page <= 1) ? ' disabled' : ''; ?>">前へ</button>
						</form>
					</li>
					<li class="page-item">
						<form action="?mode=auth-logs" method="post">
							<input type="hidden" name="code" value="<?=htmlspecialchars($_POST['code'], ENT_QUOTES, 'UTF-8');?>">
							<input type="hidden" name="page" value="<?= $nextPage; ?>">
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
					<input type="checkbox" class="form-check-input"id="checkbox--status" name="mail" checked>成功判定
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
					<input type="checkbox" class="form-check-input"id="checkbox--isp" name="isp" checked>プロバイダ
				</label>
				<label>
					<input type="checkbox" class="form-check-input"id="checkbox--asname" name="asname" checked>AS名
				</label>
				<label>
					<input type="checkbox" class="form-check-input"id="checkbox--ua" name="ua">UA
				</label>
				<label>
					<input type="checkbox" class="form-check-input"id="checkbox--chua" name="chua">CH-UA
				</label>
				<label>
					<input type="checkbox" class="form-check-input"id="checkbox--clientid" name="clientid" checked>clientID
				</label>
				<label>
					<input type="checkbox" class="form-check-input"id="checkbox--enfile" name="enfile" checked>環境ファイル
				</label>
				<label>
					<input type="checkbox" class="form-check-input"id="checkbox--hap" name="hap" checked>HAP
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
				echo '<th class="cell--status text-nowrap">成功判定</th>';
				echo '<th class="cell--key text-nowrap">同意鍵</th>';
				echo '<th class="cell--ip text-nowrap">IP</th>';
				echo '<th class="cell--host text-nowrap">HOST</th>';
				echo '<th class="cell--isp text-nowrap">プロバイダ</th>';
				echo '<th class="cell--asname text-nowrap">AS名</th>';
				echo '<th class="cell--ua text-nowrap">UA</th>';
				echo '<th class="cell--chua text-nowrap">CH_UA</th>';
				echo '<th class="cell--clientid text-nowrap">clientID</th>';
				echo '<th class="cell--enfile text-nowrap">環境ファイル</th>';
				echo '<th class="cell--hap text-nowrap">HAP</th>';
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
				    echo "<td class=\"cell--status text-nowrap\">{$log['status']}</td>";
				    echo "<td class=\"cell--key text-nowrap\">{$log['key']}</td>";
				    echo "<td class=\"cell--ip text-nowrap\">{$log['ip']}</td>";
				    echo "<td class=\"cell--host text-nowrap\">{$log['host']}</td>";
				    echo "<td class=\"cell--host text-nowrap\">{$log['isp']}</td>";
				    echo "<td class=\"cell--host text-nowrap\">{$log['asname']}</td>";
				    echo "<td class=\"cell--ua text-nowrap\">{$log['ua']}</td>";
				    echo "<td class=\"cell--chua text-nowrap\">{$log['ch_ua']}</td>";
				    echo "<td class=\"cell--clientid text-nowrap\">{$log['client_id']}</td>";
				    echo "<td class=\"cell--enfile text-nowrap\">{$log['en_file']}</td>";
				    echo "<td class=\"cell--hap text-nowrap\">{$log['account_id']}</td>";
				    echo '</tr>';
				}
				echo '</tbody>';
				echo '</table>';
				?>
		</div>
	</div>
<script>
(() => {
  const LS_TOGGLES = 'operate-auth-logs-toggles';
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