<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content>
	<meta name="author" content>
	<title>投稿ログ閲覧</title>
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
		<h1>投稿ログ閲覧</h1>
			<?php
			$ITEMS_PER_PAGE = 1000;
			if (isset($_POST['page'])) {
					$page = (int) $_POST['page'];
			} else {
					$page = 0;
			}

			include './utils/safe-file.php';

			// ログデータ取得
			$LOGFILE = '../'.$bbs.'/LOG.cgi';
			$n = 0;
			if (!is_file($LOGFILE)) {
					exit('<p class="fw-bold">ログファイルがありません。</p></div></body></html>');
			}
			$logs = safe_file($LOGFILE);
			if ($logs === false) {
					exit('<p class="fw-bold">ログファイルの取得に失敗しました。</p></div></body></html>');
			}
			$logs = array_reverse($logs);
			$maxPage = ceil(count($logs) / $ITEMS_PER_PAGE) - 1;
			$prevPage = $page - 1;
			if ($prevPage < 0) {
					$prevPage = 0;
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
							<button type="submit" class="page-link<?= ($page <= 0) ? ' disabled' : ''; ?>">前へ</button>
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
			<div>ページ: <?= $page + 1; ?></div>
		</nav>
		<div>
			<div class="d-flex flex-wrap gap-2">
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
			</div>
		</div>
		<div class="overflow-x-auto overflow-y-auto" style="height:70vh;">
			<?php
			$targetLogs = array_slice($logs, $page * $ITEMS_PER_PAGE, $ITEMS_PER_PAGE);
			// ログ行の内容
			// $newLog = $_POST['name'].'<>'.$_POST['mail'].'<>'.$DATE.' '.$ID.'<>'.$_POST['comment'].'<>'.$_POST['title'].'<>'.$_POST['thread'].'<>'.$number.'<>'.$HOST.'<>'.$_SERVER['REMOTE_ADDR'].'<>'.$_SERVER['HTTP_USER_AGENT'].'<>'.htmlspecialchars($CH_UA, ENT_NOQUOTES, 'UTF-8').'<>'.htmlspecialchars($ACCEPT, ENT_NOQUOTES, 'UTF-8').'<>'.$WrtAgreementKey.'<>'.$LV.'<>'.$info."\n";
			// $info = $_SERVER['REMOTE_PORT'].'<>'.htmlspecialchars($_SERVER['HTTP_CF_IPCOUNTRY'], ENT_NOQUOTES, 'UTF-8').'<>'.$HAP['REMOTE_ADDR'].'<>'.htmlspecialchars($HAP['HOST'].$HAP['country'].$HAP['region'].' '.$HAP['provider'], ENT_NOQUOTES, 'UTF-8').'<>'.htmlspecialchars($HAP['SLIP_NAME'].' '.$HAP['USER_AGENT'].$HAP['CH_UA'].$HAP['ACCEPT'], ENT_NOQUOTES, 'UTF-8').'<>';
			// ログ一覧
			echo '<table class="table table-sm table-bordered table-striped table-hover">';
			// テーブルヘッダー
			echo '<thead class="sticky-top">';
			echo '<tr class="table-primary">';
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
			echo '</tr>';
			echo '</thead>';
			// テーブルボディ
			echo '<tbody>';
			foreach ($targetLogs as $log) {
					$data = explode('<>', rtrim($log));
					$data = array_pad($data, 19, '');
					list($name, $mail, $dateid, $comment, $title, $thread, $number, $HOST, $IP, $UA, $CH_UA, $ACCEPT, $clientId, $LV, $PORT, $CF_IPCOUNTRY, $_, $area, $slip) = $data;

					$decodedTitle = html_entity_decode($title, ENT_QUOTES);
					$title = htmlspecialchars($decodedTitle, ENT_QUOTES, 'UTF-8');
					$decodedComment = html_entity_decode($comment, ENT_QUOTES);
					$comment = htmlspecialchars($decodedComment, ENT_QUOTES, 'UTF-8');
					$url = "/#{$bbsOfUrl}/{$thread}/{$number}";
					echo '<tr>';
					echo "<td class=\"cell--name text-nowrap\"><b>{$name}</b></td>";
					echo "<td class=\"cell--mail text-nowrap\">{$mail}</td>";
					echo "<td class=\"cell--dateid text-nowrap\">{$dateid}</td>";
					echo "<td class=\"cell--comment text-nowrap\">{$comment}</td>";
					echo "<td class=\"cell--title text-nowrap\">{$title}</td>";
					echo "<td class=\"cell--url text-nowrap\"><a href=\"{$url}\">{$url}</a></td>";
					echo "<td class=\"cell--host text-nowrap\">{$HOST}</td>";
					echo "<td class=\"cell--ip text-nowrap\">{$IP}</td>";
					echo "<td class=\"cell--ua text-nowrap\">{$UA}</td>";
					echo "<td class=\"cell--chua text-nowrap\">{$CH_UA}</td>";
					echo "<td class=\"cell--accept text-nowrap\">{$ACCEPT}</td>";
					echo "<td class=\"cell--clientid text-nowrap\">{$clientId}</td>";
					echo "<td class=\"cell--lv text-nowrap\">{$LV}</td>";
					echo "<td class=\"cell--port text-nowrap\">{$PORT}</td>";
					echo "<td class=\"cell--country text-nowrap\">{$CF_IPCOUNTRY}</td>";
					echo "<td class=\"cell--area text-nowrap\">{$area}</td>";
					echo "<td class=\"cell--slip text-nowrap\">{$slip}</td>";
					echo '</tr>';
			}
			echo '</tbody>';
			echo '</table>';
			?>
		</div>
	</div>
<script>
(() => {
  const LS_TOGGLES = 'operate-log-toggles';
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