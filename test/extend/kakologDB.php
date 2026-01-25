<?php

require_once dirname(__FILE__, 2) . '/utils/normalize-string.php';
class KakologDB
{
    private $db = null;
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
     * @param int $NOWTIME アーカイブ時間
     *
     * @return bool 成功判定
     */
    public function add($thread, $title, $number, $NOWTIME)
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
                $NOWTIME,
            ]);

            return true;
        } catch (Exception $e) {
            $error = sprintf(
                "%s<>%s<>%d<>%s<>%d\n",
                $e->getMessage(),
                date('Y-m-d H:i:s', $NOWTIME),
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
                archived_at INTEGER NOT NULL
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

}
