
<?php
/**
 * スレ情報ファイルthreads-states.cgiの更新を行うためのクラスです。
 * get()でファイルをロックしput()でロックを解除するので必ずセットで使ってください。※get()でfalseを返した場合put()は不要です。
 * 内容の取得のみを使いたい場合はこのクラスではなくfile_get_contentsを使用してください。
 */
class ThreadsStatesUpdater
{
    private $path;
    private $file;

    /**
     * @param string $path threads-states.cgiへのパス
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * threads-states.cgiの内容を取得するメソッド
     *
     * @return array|false
     */
    public function get()
    {
        if(!is_file($this->path)) {
            return [];
        }
        $this->file = fopen($this->path, 'r+');
        if(!flock($this->file, LOCK_EX)) {
            fclose($this->file);
            unset($this->file);
            return false;
        }
        clearstatcache();
        $text = fread($this->file, filesize($this->path));
        return json_decode($text, true);
    }

    /**
     * threads-states.cgiに新しい内容を書き込むメソッド
     *
     * @param array $threadsStates スレ状態
     * @return boolean 成功判定
     */
    public function put($threadsStates)
    {
        if(!is_file($this->path)) {
            $this->file = fopen($this->path, 'w+');
            if(!flock($this->file, LOCK_EX)) {
                fclose($this->file);
                unset($this->file);
                return false;
            }
        }
        if(!isset($this->file)) {
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
