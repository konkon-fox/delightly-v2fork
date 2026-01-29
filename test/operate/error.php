<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content>
	<meta name="author" content>
	<title>エラーログ閲覧</title>
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
<?php
$bbs = basename($_REQUEST['bbs']);
$safeBbs = htmlspecialchars($bbs, ENT_QUOTES, 'UTF-8');
$bbsOfUrl = urlencode($bbs);
?>
	<div class="container d-flex flex-column row-gap-2">
		<header>
			<form action="?bbs=<?= $safeBbs; ?>" method="post">
				<input type="hidden" name="password" value="<?=htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8');?>">
				<button type="submit" class="btn btn-sm btn-secondary">← 管理ページへ戻る</button>
			</form>
		</header>
		<h1>エラーログ閲覧</h1>
			<?php
            $ITEMS_PER_PAGE = 1000;
if (isset($_POST['page'])) {
    $page = (int) $_POST['page'];
} else {
    $page = 1;
}

require_once './utils/safe-file.php';

// ログデータ取得
$LOGFILE = '../' . $bbs . '/errors.cgi';
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
$logs = array_map(function($line){
		$data = explode('<>', rtrim($line));
		$data = array_pad($data, 20, '');
		list($error, $name, $mail, $dateid, $comment, $title, $thread, $number, $HOST, $IP, $UA, $CH_UA, $ACCEPT, $clientId, $LV, $PORT, $CF_IPCOUNTRY, $hapIp, $area, $slip) = $data;
		return [
			'error' =>$error,
			'name' =>$name,
			'mail' =>$mail,
			'date_id' =>$dateid,
			'comment' =>$comment,
			'title' =>$title,
			'thread' =>$thread,
			'number' =>$number,
			'host' =>$HOST,
			'ip' =>$IP,
			'ua' =>$UA,
			'ch_ua' =>$CH_UA,
			'accept' =>$ACCEPT,
			'client_id' =>$clientId,
			'lv' =>$LV,
			'port' =>$PORT,
			'cf_ipcountry' =>$CF_IPCOUNTRY,
			'hap_ip' =>$hapIp,
			'hap_area' =>$area,
			'hap_slip' =>$slip,
			'account_id' =>'unknown',
			'posted_at' =>'unknown'
		];
}, $logs);

$prevPage = $page;
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
						<form action="?bbs=<?= $safeBbs; ?>&mode=log" method="post">
							<input type="hidden" name="password" value="<?=htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8');?>">
							<input type="hidden" name="page" value="<?= $prevPage; ?>">
							<button type="submit" class="page-link<?= ($page <= 1) ? ' disabled' : ''; ?>">前へ</button>
						</form>
					</li>
					<li class="page-item">
						<form action="?bbs=<?= $safeBbs; ?>&mode=log" method="post">
							<input type="hidden" name="password" value="<?=htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8');?>">
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
					<input type="checkbox" class="form-check-input" id="checkbox--error" name="error" checked>エラー内容
				</label>
				<label>
					<input type="checkbox" class="form-check-input" id="checkbox--name" name="name" checked>名前
				</label>
				<label>
					<input type="checkbox" class="form-check-input"id="checkbox--mail" name="mail">メール欄
				</label>
				<label>
					<input type="checkbox" class="form-check-input"id="checkbox--dateid" name="dateid" checked>日付・ID
				</label>
				<label>
					<input type="checkbox" class="form-check-input"id="checkbox--comment" name="comment" checked>本文
				</label>
				<label>
					<input type="checkbox" class="form-check-input"id="checkbox--title" name="title">スレタイ
				</label>
				<label>
					<input type="checkbox" class="form-check-input"id="checkbox--url" name="url" checked>URL
				</label>
				<label>
					<input type="checkbox" class="form-check-input"id="checkbox--host" name="host" checked>ホスト
				</label>
				<label>
					<input type="checkbox" class="form-check-input"id="checkbox--ip" name="ip" checked>IP
				</label>
				<label>
					<input type="checkbox" class="form-check-input"id="checkbox--ua" name="ua">UA
				</label>
				<label>
					<input type="checkbox" class="form-check-input"id="checkbox--chua" name="chua">CH-UA
				</label>
				<label>
					<input type="checkbox" class="form-check-input"id="checkbox--accept" name="accept">ACCEPT
				</label>
				<label>
					<input type="checkbox" class="form-check-input"id="checkbox--clientid" name="clientid" checked>clientID
				</label>
				<label>
					<input type="checkbox" class="form-check-input"id="checkbox--lv" name="lv">LV
				</label>
				<label>
					<input type="checkbox" class="form-check-input"id="checkbox--port" name="port">ポート番号
				</label>
				<label>
					<input type="checkbox" class="form-check-input"id="checkbox--country" name="country">国
				</label>
				<label>
					<input type="checkbox" class="form-check-input"id="checkbox--area" name="area">地域(認証時)
				</label>
				<label>
					<input type="checkbox" class="form-check-input"id="checkbox--slip" name="slip">SLIP(認証時)
				</label>
				<label>
					<input type="checkbox" class="form-check-input"id="checkbox--hap" name="hap">HAP
				</label>
			</div>
		</div>
		<div class="overflow-x-auto overflow-y-auto" style="height:70vh;">
			<?php
$targetLogs = array_slice($logs, $page * $ITEMS_PER_PAGE, $ITEMS_PER_PAGE);
// ログ一覧
echo '<table class="table table-sm table-bordered table-striped table-hover">';
// テーブルヘッダー
echo '<thead class="sticky-top">';
echo '<tr class="table-primary">';
echo '<th class="cell--error text-nowrap">エラー内容</th>';
echo '<th class="cell--name text-nowrap">名前</th>';
echo '<th class="cell--mail text-nowrap">メール欄</th>';
echo '<th class="cell--dateid text-nowrap">日付・ID</th>';
echo '<th class="cell--comment text-nowrap">本文</th>';
echo '<th class="cell--title text-nowrap">スレタイ</th>';
echo '<th class="cell--url text-nowrap">URL</th>';
echo '<th class="cell--host text-nowrap">ホスト</th>';
echo '<th class="cell--ip text-nowrap">IP</th>';
echo '<th class="cell--ua text-nowrap">UA</th>';
echo '<th class="cell--chua text-nowrap">CH-UA</th>';
echo '<th class="cell--accept text-nowrap">ACCEPT</th>';
echo '<th class="cell--clientid text-nowrap">clientID</th>';
echo '<th class="cell--lv text-nowrap">LV</th>';
echo '<th class="cell--port text-nowrap">ポート番号</th>';
echo '<th class="cell--country text-nowrap">国</th>';
echo '<th class="cell--area text-nowrap">地域(認証時)</th>';
echo '<th class="cell--slip text-nowrap">SLIP(認証時)</th>';
echo "<th class=\"cell--hap text-nowrap\">HAP</th>";
echo '</tr>';
echo '</thead>';
// テーブルボディ
echo '<tbody>';
foreach ($logs as $log) {
	$log['title'] = html_entity_decode($log['title'], ENT_QUOTES);
	$log['comment'] = html_entity_decode($log['comment'], ENT_QUOTES);
	$log = array_map(function ($value) {
		return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
	},$log);
	$url = "/#{$bbsOfUrl}/{$log['thread']}/{$log['number']}";
	$accountId = $log['account_id'] ?? 'unknown';

	echo '<tr>';
	echo "<td class=\"cell--error text-nowrap\">{$log['error']}</td>";
	echo "<td class=\"cell--name text-nowrap\"><b>{$log['name']}</b></td>";
	echo "<td class=\"cell--mail text-nowrap\">{$log['mail']}</td>";
	echo "<td class=\"cell--dateid text-nowrap\">{$log['date_id']}</td>";
	echo "<td class=\"cell--comment text-nowrap\">{$log['comment']}</td>";
	echo "<td class=\"cell--title text-nowrap\">{$log['title']}</td>";
	echo "<td class=\"cell--url text-nowrap\"><a href=\"{$url}\">{$url}</a></td>";
	echo "<td class=\"cell--host text-nowrap\">{$log['host']}</td>";
	echo "<td class=\"cell--ip text-nowrap\">{$log['ip']}</td>";
	echo "<td class=\"cell--ua text-nowrap\">{$log['ua']}</td>";
	echo "<td class=\"cell--chua text-nowrap\">{$log['ch_ua']}</td>";
	echo "<td class=\"cell--accept text-nowrap\">{$log['accept']}</td>";
	echo "<td class=\"cell--clientid text-nowrap\">{$log['client_id']}</td>";
	echo "<td class=\"cell--lv text-nowrap\">{$log['lv']}</td>";
	echo "<td class=\"cell--port text-nowrap\">{$log['port']}</td>";
	echo "<td class=\"cell--country text-nowrap\">{$log['cf_ipcountry']}</td>";
	echo "<td class=\"cell--area text-nowrap\">{$log['hap_area']}</td>";
	echo "<td class=\"cell--slip text-nowrap\">{$log['hap_slip']}</td>";
	echo "<td class=\"cell--hap text-nowrap\">{$accountId}</td>";
	echo '</tr>';
}
echo '</tbody>';
echo '</table>';
?>
		</div>
	</div>
<script>
(() => {
  const LS_TOGGLES = 'operate-error-log-toggles';
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