<?php

require_once './utils/get-json-file.php';
require_once './extend/SystemDB.php';

if ($_POST['edit'] === 'true') {
    $db = new SystemDB();
    $result = $db->migrateAuthLog();
}

?><!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content>
	<meta name="author" content>
	<title>認証ログをDB化(v3 => v4)</title>
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
		<h1>認証ログをDB化(v3 => v4)</h1>
		<main class="d-flex flex-column row-gap-2">
			<div class="card">
				<form action="?mode=migration-auth-log" method="post">
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
					<div class="card-header">認証ログ</div>
					<div class="card-body">
						<p class="card-text">
							<button class="btn btn-primary">移行する</button>
						</p>
						<?php
						if (isset($result)) {
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
		</main>
</body>
</html>