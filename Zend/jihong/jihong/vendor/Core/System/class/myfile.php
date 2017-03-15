<?php

class myfile
{

    private $file = '';

    private $filename = '';

    function __construct()
    {
        $this->init();
    }

    function __destruct()
    {
        unset($this->file);
    }

    private function init()
    {
        $this->filename = LOG_FILENAME;
    }

    private function openfile()
    {
        $this->file = fopen(__DIR__ . "/../" . $this->filename, "ab");
    }

    private function closefile()
    {
        fclose($this->file);
    }

    /**
     * 记录错误日志
     * 
     * @param unknown $content  日志内容
     * @param unknown $level 错误等级 0致命错误；1错误；2警告
     */
    public function put($content, $level)
    {
        if (LOG_LEVEL >= $level) {
            $this->openfile();
            $temp = date('Y-m-d H:i:s') . " " . $content . "\r\n";
            fwrite($this->file, $temp);
            $this->closefile();
        }
    }

    /**
     * 回头车专用记录到日志的方法
     * 
     * @param string $content
     *            正文
     * @return void
     * @author WZ
     * @version 1.0.20140429 WZ
     *         
     */
    public function savePublicFile($content)
    {
        if (APP_PATH) {
            if (! is_dir(APP_PATH . '/public/push_log')) {
                mkdir(APP_PATH . '/public/push_log', 0777);
            }
            $push_file = APP_PATH . '/public/push_log/log_' . date('Ymd') . '.txt';
            if (is_file($push_file)) {} else {
                touch($push_file);
                chmod($push_file, 0777);
            }
            $temp = file_get_contents($push_file);
            $temp = date('Y-m-d H:i:s') . " $content" . "\r\n" . $temp;
            file_put_contents($push_file, $temp);
        }
    }
}
?>