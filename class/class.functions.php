<?php

/**
 * Class FunctionsHandler
 */
class FunctionsHandler
{
    public $functions = [];

    /**
     * FunctionsHandler constructor.
     * @param $wsdl
     */
    public function __construct($wsdl)
    {
    }

    /**
     * @return array
     */
    public function GetServerExtensions()
    {
        $files = [];
        $f     = [];
        $files = $this->getFileListAsArray(XOOPS_ROOT_PATH . '/modules/xjson/plugins/');
        static $f_count;
        static $f_buffer;

        if ($f_count != count($files)) {
            $f_count = count($files);
            foreach ($files as $k => $l) {
                if (strpos($k, '.php', 1) == (strlen($k) - 4)) {
                    $f[] = $k;
                }
            }
            $f_buffer = $f;
        }

        return $f_buffer;
    }

    /**
     * @param $dirname
     * @return array
     */
    public function getDirListAsArray($dirname)
    {
        $ignored = [];
        $list    = [];
        if (substr($dirname, -1) !== '/') {
            $dirname .= '/';
        }
        if ($handle = opendir($dirname)) {
            while ($file = readdir($handle)) {
                if (0 === strpos($file, '.') || in_array(strtolower($file), $ignored)) {
                    continue;
                }
                if (is_dir($dirname . $file)) {
                    $list[$file] = $file;
                }
            }
            closedir($handle);
            asort($list);
            reset($list);
        }
        //print_r($list);
        return $list;
    }

    /*
     *  gets list of all files in a directory
     */
    /**
     * @param        $dirname
     * @param string $prefix
     * @return array
     */
    public function getFileListAsArray($dirname, $prefix = '')
    {
        $filelist = [];
        if (substr($dirname, -1) === '/') {
            $dirname = substr($dirname, 0, -1);
        }
        if (is_dir($dirname) && $handle = opendir($dirname)) {
            while (false !== ($file = readdir($handle))) {
                if (!preg_match("/^[\.]{1,2}$/", $file) && is_file($dirname . '/' . $file)) {
                    $file            = $prefix . $file;
                    $filelist[$file] = $file;
                }
            }
            closedir($handle);
            asort($filelist);
            reset($filelist);
        }
        return $filelist;
    }

    public function __destruct()
    {
    }
}
