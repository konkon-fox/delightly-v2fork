<?php

require_once './utils/get-json-file.php';
require_once './extend/kakologDB.php';

$rootPath = dirname(__FILE__, 4);
$dirList = glob($rootPath . '/*', GLOB_ONLYDIR);
$excludeDir = [
    'admin',
    'api',
    'images',
    'static',
    'test',
];
$bbsList = [];
foreach ($dirList as $path) {
    $dir = basename($path);
    if (in_array($dir, $excludeDir, true)) {
        continue;
    }
    $bbsList[] = [
				'bbs' => $dir,
		];
}

if ($_POST['edit'] === 'true' && isset($_POST['bbs'])) {
	  $bbs = basename($_POST['bbs']);
		$bbsPath = $rootPath. '/'. $bbs;
		$db = new KakologDB($bbsPath);
		$result = $db->migrate2to4();
}

?><!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content>
	<meta name="author" content>
	<title>過去ログ一覧ファイルをDB化(v2 => v4)</title>
	<link
		href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
		rel="stylesheet"
		integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM"
		crossorigin="anonymous"
	/>
</head>
<body>
	<div class="container d-flex flex-column row-gap-2">
    <header class="d-flex flex-column row-gap-1">
			<form action="master.php" method="post">
				<input type="hidden" name="code" value="<?= htmlspecialchars($_POST['code'], ENT_QUOTES, 'UTF-8'); ?>">
				<button type="submit" class="btn btn-sm btn-secondary">← システム総管理ページへ戻る</button>
			</form>
			<form action="?mode=migration-list" method="post">
				<input type="hidden" name="code" value="<?= htmlspecialchars($_POST['code'], ENT_QUOTES, 'UTF-8'); ?>">
				<button type="submit" class="btn btn-sm btn-secondary">← システム移行へ戻る</button>
			</form>
		</header>
		<h1>過去ログ一覧ファイルをDB化(v2 => v4)</h1>
		<main class="d-flex flex-column row-gap-2">
			<p class="text-danger">
				【重要】既にv3系やv4系に移行済みの場合はこの処理は不要です。
			</p>
			<p>
				この処理は専ブラ用の過去ログが削除されたv2系のためのものです。<br>
				通常ブラウザ用のdatから専ブラ用のdatを生成し、kakoフォルダへ格納。及び、DBへ過去ログ情報を格納します。
			</p>
			<?php
					foreach ($bbsList as $bbsData) {
							?>
							<div class="card">
								<form action="?mode=migration2to4" method="post">
									<input
										type="hidden"
										name="code"
										value="<?= htmlspecialchars($_POST['code'], ENT_QUOTES, 'UTF-8'); ?>"
									/>
									<input
										type="hidden"
										name="edit"
										value="true"
									/>
									<input
										type="hidden"
										name="bbs"
										value="<?= htmlspecialchars($bbsData['bbs'], ENT_QUOTES, 'UTF-8'); ?>"
									/>
									<div class="card-header">板: <?= htmlspecialchars($bbsData['bbs'], ENT_QUOTES, 'UTF-8'); ?></div>
  								<div class="card-body">
										<p class="card-text">
											<button class="btn btn-primary">移行する</button>
										</p>
										<?php
												if(isset($bbs,$result) && $bbs === $bbsData['bbs']){
													  ?>
															<div class="alert alert-<?= htmlspecialchars($result['alertType'], ENT_QUOTES, 'UTF-8'); ?>" role="alert">
																<?= $result['message']; ?>
															</div>
														<?php
												}
										?>
									</div>
								</form>
							</div>
							<?php
					}
			?>
		</main>
</body>
</html>