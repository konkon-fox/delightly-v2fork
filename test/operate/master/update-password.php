<?php

include './utils/get-json-file.php';

$error = '';

if (($_POST['edit'] ?? '') === 'true' && isset($_POST['new-password'], $_POST['new-password2'])) {
    if (empty($_POST['new-password'])) {
        $error = '新しいパスワードが空欄です。';
    } elseif ($_POST['new-password'] !== $_POST['new-password2']) {
        $error = '確認用パスワードが一致しません。';
    } else {
        $passHash = password_hash($_POST['new-password'], PASSWORD_DEFAULT);
        file_put_contents($masterPasswordfile, $passHash, LOCK_EX);
        if (is_file($createcodefile)) {
            unlink($createcodefile);
        }
        header('Location: master.php?update-password=true');
    }
}

?><!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content>
	<meta name="author" content>
	<title>本パスワード更新</title>
	<link
		href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
		rel="stylesheet"
		integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM"
		crossorigin="anonymous"
	/>
</head>
<body>
	<div class="container d-flex flex-column row-gap-2">
		<header>
			<form action="master.php" method="post">
				<input type="hidden" name="code" value="<?= htmlspecialchars($_POST['code'], ENT_QUOTES, 'UTF-8'); ?>">
				<button type="submit" class="btn btn-sm btn-secondary">← 管理ページへ戻る</button>
			</form>
		</header>
		<h1>本パスワード更新</h1>
		<main>
			<?php
if (!empty($error)) {
    echo "<div class=\"alert alert-warning\" role=\"alert\">{$error}</div>";
}
?>
			<form action="?mode=update-password" method="post">
				<input
					type="hidden"
					name="code"
					value="<?=htmlspecialchars($_POST['code'], ENT_QUOTES, 'UTF-8');?>"
				/>
				<input
					type="hidden"
					name="edit"
					value="true"
				/>
				<div class="d-flex flex-column row-gap-3 align-items-start">
					<div>
						<label for="new-password" class="form-label">新しいパスワード</label>
						<input type="password" class="form-control" id="new-password" name="new-password" required>
					</div>
					<div>
						<label for="new-password2" class="form-label">新しいパスワード(確認用)</label>
						<input type="password" class="form-control" id="new-password2" name="new-password2" required>
					</div>
					<button
						type="submit"
						class="btn btn-primary"
					>
						適用
					</button>
				</div>
			</div>
		</form>
	</main>
</body>
</html>