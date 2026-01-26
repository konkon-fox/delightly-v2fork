<?php

require_once dirname(__FILE__, 2) . '/utils/normalize-string.php';
require_once dirname(__FILE__, 2) . '/utils/safe-file-get-contents.php';

class KakologDB
{
    private $db = null;
    private $dbDir;
    private $dbFile;
    private $txtFile;
    private $errorFile;

    public function __construct($bbsPath)
    {
        // DB用のファイルを定義
        $dbDir = $bbsPath . '/db';
        if (!is_dir($dbDir)) {
            @mkdir($dbDir, 0775, true);
        }
        $this->dbDir = $dbDir;
        $this->dbFile = $dbDir . '/kakolog.db';
        $this->txtFile = $bbsPath . '/kakolog-subject.txt';
        $this->errorFile = $dbDir . '/kakolog-error.log';
    }

    private function getDB()
    {
        if ($this->db !== null) {
            return $this->db;
        }

        // SQLiteが使えるかチェック
        if (!extension_loaded('pdo_sqlite')) {
            throw new Exception('SQLiteが利用できません');
        }

        $isNewDB = !is_file($this->dbFile);

        $this->db = new PDO('sqlite:' . $this->dbFile);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 新規DBならテーブル作成
        if ($isNewDB) {
            $this->createTable();
        }

        return $this->db;
    }

    /**
     * 新規データを追加するメソッド
     *
     * @param int $thread スレキー
     * @param string $title スレタイ
     * @param int $number レス数
     * @param int|null $archivedAt アーカイブ時間
     *
     * @return bool 成功判定
     */
    public function add($thread, $title, $number, $archivedAt)
    {
        try {
            $db = $this->getDB();

            $stmt = $db->prepare('
                INSERT OR REPLACE INTO kakolog 
                (thread, title, normalized_title, res, archived_at)
                VALUES (?, ?, ?, ?, ?)
            ');

            $stmt->execute([
                $thread,
                $title,
                normalizeString($title),
                $number,
                $archivedAt,
            ]);

            return true;
        } catch (Exception $e) {
            $date = $archivedAt === null ? 'null' : date('Y-m-d H:i:s', $archivedAt);
            $error = sprintf(
                "%s<>%s<>%d<>%s<>%d\n",
                $e->getMessage(),
                $date,
                $thread,
                $title,
                $number
            );
            @file_put_contents($this->errorFile, $error, LOCK_EX | FILE_APPEND);
            return false;
        }
    }
    /**
     * データを検索するメソッド
     *
     * @param array{
     *   page?: int,
     *   per_page?: int,
     *   keywords?: string,
     *   search_mode?: 'AND'|'OR',
     *   since_time?: int,
     *   until_time?: int,
     *   min_res?: int,
     *   max_res?: int
     * } $options 検索オプション
     *
     * @return array{
     *   logs: array<int, array{thread: int, title: string, res: int}>
     *   total_count: int
     * }|false 成功すれば結果を出力、失敗時はfalse
     */
    public function search($options = [])
    {
        try {
            $db = $this->getDB();

            // デフォルト値
            $page = isset($options['page']) ? max(1, (int) $options['page']) : 1;
            $perPage = isset($options['per_page']) ? (int) $options['per_page'] : 50;
            $offset = ($page - 1) * $perPage;

            // WHERE句とパラメータを構築
            $where = [];
            $params = [];

            // 検索モード
            $searchMode = (($options['search_mode'] ?? 'AND') === 'OR') ? 'OR' : 'AND';

            // スレタイ検索
            if (isset($options['keywords'])) {
                $keywords = preg_split('/[\s　]+/u', trim($options['keywords']), -1, PREG_SPLIT_NO_EMPTY);
                if (!empty($keywords)) {
                    // WHERE, パラメータへ追加
                    $keywordsWhere = [];
                    foreach ($keywords as $word) {
                        // WHERE句を作成
                        $keywordsWhere[] = 'normalized_title LIKE ?';
                        // パラメータへ追加
                        $params[] = '%' . normalizeString($word) . '%';
                    }
                    // WHEREへ追加
                    $where[] = '(' . implode(" {$searchMode} ", $keywordsWhere) . ')';
                }
            }

            // 日付検索(スレキー)
            if (isset($options['since_time'])) {
                $where[] = 'thread >= ?';
                $params[] = $options['since_time'];
            }
            if (isset($options['until_time'])) {
                $where[] = 'thread <= ?';
                $params[] = $options['until_time'];
            }

            // レス数検索
            if (isset($options['min_res'])) {
                $where[] = 'res >= ?';
                $params[] = $options['min_res'];
            }
            if (isset($options['max_res'])) {
                $where[] = 'res <= ?';
                $params[] = $options['max_res'];
            }

            // WHILE句を作成
            $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

            // 検索一致件数取得
            $stmt = $db->prepare("
                SELECT COUNT(*)
                FROM kakolog
                {$whereSQL}
            ");
            $stmt->execute($params);
            $total = $stmt->fetchColumn();

            // データ取得（新しい順）
            $stmt = $db->prepare("
                SELECT thread, title, res
                FROM kakolog
                {$whereSQL}
                ORDER BY thread DESC
                LIMIT ?
                OFFSET ?
            ");
            $stmt->execute(array_merge($params, [$perPage, $offset]));
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'logs' => $logs,
                'total_count' => $total,
            ];
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 新規テーブルを作成するメソッド
     *
     * @return void
     */
    private function createTable()
    {
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS kakolog (
                thread INTEGER PRIMARY KEY,
                title TEXT NOT NULL,
                normalized_title TEXT NOT NULL,
                res INTEGER NOT NULL,
                archived_at INTEGER
            )
        ');

        // インデックス作成
        $this->db->exec('CREATE INDEX IF NOT EXISTS idx_normalized_title ON kakolog(normalized_title)');
        $this->db->exec('CREATE INDEX IF NOT EXISTS idx_res ON kakolog(res)');
        $this->db->exec('CREATE INDEX IF NOT EXISTS idx_archived_at ON kakolog(archived_at)');
    }

    /**
     * SQLiteを使うかどうか判定するメソッド
     *
     * @return bool
     */
    public function isSQLiteMode()
    {
        // DBファイルが存在すればSQLiteモード
        if (is_file($this->dbFile)) {
            return true;
        }
        // kakolog-subject.txtファイルが存在すればファイルモード
        if (is_file($this->txtFile)) {
            return false;
        }
        // どちらもなければ、SQLiteが使えるならSQLiteモード
        return extension_loaded('pdo_sqlite');
    }

    /**
     * 過去ログ一覧をファイル形式からDBへ移行するメソッド
     *
     * @return array{alertType:string, message:string}
     */
    public function migrate3to4()
    {
        // SQLiteが使えるか確認
        if (extension_loaded('pdo_sqlite') === false) {
            return [
                'alertType' => 'danger',
                'message' => 'SQLiteが使えない環境です。',
            ];
        }

        // 進行状況を取得
        $stateFile = $this->dbDir . '/migrate3to4.state';
        if (is_file($stateFile)) {
            $state = safe_file_get_contents($stateFile);
            if ($state === false) {
                return [
                    'alertType' => 'danger',
                    'message' => '進行状況の取得に失敗しました。',
                ];
            }
            if ($state === 'done') {
                return [
                    'alertType' => 'info',
                    'message' => '既に移行完了済みです。',
                ];
            }
            $offset = (int) $state;
        } else {
            $offset = 0;
        }

        // 過去ログファイルが無い場合
        if (!is_file($this->txtFile)) {
            return [
                'alertType' => 'info',
                'message' => '移行処理は不要です。',
            ];
        }

        // 移行処理

        // ファイルを開く
        $CHUNK_SIZE = 1000;
        $subjectHandle = @fopen($this->txtFile, 'r');
        if ($subjectHandle === false) {
            return [
                'alertType' => 'danger',
                'message' => '過去ログファイルが開けませんでした。',
            ];
        }
        if (!flock($subjectHandle, LOCK_SH)) {
            fclose($subjectHandle);
            return [
                'alertType' => 'danger',
                'message' => '過去ログファイルへのロック取得に失敗しました。',
            ];
        }

        // ポインタ移動
        if ($offset > 0) {
            fseek($subjectHandle, $offset, SEEK_SET);
        }

        // チャンク処理
        $processedLines = 0;
        $logs = [];
        while (!feof($subjectHandle) && $processedLines < $CHUNK_SIZE) {
            // 1行読み込む
            $line = fgets($subjectHandle);
            // utf-8に変換
            $line = mb_convert_encoding($line, 'UTF-8', 'SJIS-win');

            // 終端の場合
            if ($line === false) {
                break;
            }

            // 正しいデータなら配列に追加
            if (preg_match('/^([0-9]+)\.dat<>(.+)\s\(([0-9]+)\)$/', trim($line), $matches)) {
                $logs[] = [
                    'thread' => (int) $matches[1],
                    'title' => $matches[2],
                    'res' => (int) $matches[3],
                ];
            }

            // 次の行へ
            $processedLines++;
        }

        // ポインタ位置を進行状態ファイルへ記録
        $nextOffset = ftell($subjectHandle);
        // ファイル閉じる
        flock($subjectHandle, LOCK_UN);
        fclose($subjectHandle);

        // 取得したデータをDBへ追加
        try {
            $db = $this->getDB();
            $db->beginTransaction();

            $stmt = $db->prepare('
                INSERT OR REPLACE INTO kakolog 
                (thread, title, normalized_title, res, archived_at)
                VALUES (?, ?, ?, ?, ?)
            ');

            $insertedCount = 0;
            foreach ($logs as $log) {
                $stmt->execute([
                    $log['thread'],
                    $log['title'],
                    normalizeString($log['title']),
                    $log['res'],
                    null,
                ]);
                $insertedCount++;
            }
            $db->commit();
        } catch (Exception $e) {
            // エラー時はロールバック
            if (isset($db)) {
                $db->rollBack();
            }
            return [
                'alertType' => 'danger',
                'message' => '移行中にエラーが発生しました: ' . $e->getMessage(),
            ];
        }

        // ポインタ位置がファイルサイズ以上なら完了
        $subjectSize = filesize($this->txtFile);
        if ($nextOffset >= $subjectSize) {
            $nextOffset = 'done';
            rename($this->txtFile, $this->txtFile . '.old');
        }
        // 進行状況を記録
        if ($processedLines > 0) {
            file_put_contents($stateFile, $nextOffset);
        }
        // 完了メッセージ
        if ($nextOffset === 'done') {
            return [
                'alertType' => 'success',
                'message' => "{$insertedCount}件移行。<br>DBへの移行が完了しました。",
            ];
        } else {
            $percent = round(($nextOffset / $subjectSize) * 100, 2);
            return [
                'alertType' => 'success',
                'message' => "{$insertedCount}件移行。<br>進捗: {$percent}% 完了 ({$nextOffset} / {$subjectSize} バイト)",
            ];
        }
    }

    /**
     * DBの更新日時を取得するメソッド
     *
     * @return int|false
     */
    public function getFileTime()
    {
        // DBファイルが存在しなければfalse
        if (!is_file($this->dbFile)) {
            return false;
        }

        return filemtime($this->dbFile);
    }
}
