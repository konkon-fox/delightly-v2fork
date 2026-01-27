<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>システム移行</title>
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
    <h1>システム移行</h1>
    <main>
      <div class="list-group d-flex align-items-start">
        <form action="?mode=migration3to4" method="POST" class="list-group-item list-group-item-action">
          <input type="hidden" name="code" value="<?= htmlspecialchars($_POST['code'], ENT_QUOTES, 'UTF-8'); ?>">
          <button class="btn stretched-link">過去ログ一覧ファイルをDB化(v3 => v4)</button>
        </form>
        <form action="?mode=migration2to4" method="POST" class="list-group-item list-group-item-action">
          <input type="hidden" name="code" value="<?= htmlspecialchars($_POST['code'], ENT_QUOTES, 'UTF-8'); ?>">
          <button class="btn stretched-link">過去ログ一覧ファイルをDB化(v2 => v4)</button>
        </form>
      </div>
    </main>
  </div>
</body>
</html>