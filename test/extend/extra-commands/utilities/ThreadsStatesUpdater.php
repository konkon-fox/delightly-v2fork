
<?php
/**
 * スレ状態ファイル`/{$bbs}/threads-states/{スレ番号}.json`の更新を行うためのクラスです。
 * get()でファイルをロックしput()でロックを解除するので必ずセットで使ってください。※get()でfalseを返した場合put()は不要です。
 * 内容の取得のみを使いたい場合はこのクラスではなくgetThreadsStates関数を使用してください。
 */
class ThreadsStatesUpdater
{
    private $path;
    private $file;

    /**
     * @param string $path `/{$bbs}/threads-states/{スレ番号}.json`へのパス
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * `/{$bbs}/threads-states/{スレ番号}.json`の内容を取得するメソッド
     *
     * @return array|false
     */
    public function get()
    {
        if (!is_file($this->path)) {
            return [];
        }
        $this->file = fopen($this->path, 'c+');
        if (!flock($this->file, LOCK_EX)) {
            fclose($this->file);
            unset($this->file);
            return false;
        }
        clearstatcache();
        $text = stream_get_contents($this->file);
        $data = json_decode($text, true);
        if ($data === null) {
            return [];
        }
        return  $data;
    }

    /**
     * `/{$bbs}/threads-states/{スレ番号}.json`に新しい内容を書き込むメソッド
     *
     * @param array $threadsStates スレ状態
     *
     * @return boolean 成功判定
     */
    public function put($threadsStates)
    {
        if (!is_file($this->path)) {
            $this->file = fopen($this->path, 'w+');
            if (!flock($this->file, LOCK_EX)) {
                fclose($this->file);
                unset($this->file);
                return false;
            }
        }
        if (!isset($this->file)) {
            return false;
        }
        $data = json_encode($threadsStates, JSON_UNESCAPED_UNICODE);
        ftruncate($this->file, 0);
        rewind($this->file);
        fwrite($this->file, $data);
        flock($this->file, LOCK_UN);
        fclose($this->file);
        unset($this->file);
        return true;
    }
}
