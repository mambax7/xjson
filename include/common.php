<?php
/**
 * @param $username
 * @param $password
 * @return bool
 */

use XoopsModules\Xjson;

require_once dirname(__DIR__) . '/preloads/autoloader.php';

$moduleDirName = basename(dirname(__DIR__));
$moduleDirNameUpper   = strtoupper($moduleDirName); //$capsDirName


/** @var \XoopsDatabase $db */
/** @var Xjson\Helper $helper */
/** @var Xjson\Utility $utility */
$db      = \XoopsDatabaseFactory::getDatabaseConnection();
$helper  = Xjson\Helper::getInstance();
$utility = new Xjson\Utility();
//$configurator = new Xjson\Common\Configurator();

$helper->loadLanguage('common');

//handlers
//$categoryHandler     = new Xjson\CategoryHandler($db);
//$downloadHandler     = new Xjson\DownloadHandler($db);

if (!defined($moduleDirNameUpper . '_CONSTANTS_DEFINED')) {
    define($moduleDirNameUpper . '_DIRNAME', basename(dirname(__DIR__)));
    define($moduleDirNameUpper . '_ROOT_PATH', XOOPS_ROOT_PATH . '/modules/' . $moduleDirName . '/');
    define($moduleDirNameUpper . '_PATH', XOOPS_ROOT_PATH . '/modules/' . $moduleDirName . '/');
    define($moduleDirNameUpper . '_URL', XOOPS_URL . '/modules/' . $moduleDirName . '/');
    define($moduleDirNameUpper . '_IMAGE_URL', constant($moduleDirNameUpper . '_URL') . '/assets/images/');
    define($moduleDirNameUpper . '_IMAGE_PATH', constant($moduleDirNameUpper . '_ROOT_PATH') . '/assets/images');
    define($moduleDirNameUpper . '_ADMIN_URL', constant($moduleDirNameUpper . '_URL') . '/admin/');
    define($moduleDirNameUpper . '_ADMIN_PATH', constant($moduleDirNameUpper . '_ROOT_PATH') . '/admin/');
    define($moduleDirNameUpper . '_ADMIN', constant($moduleDirNameUpper . '_URL') . '/admin/index.php');
    define($moduleDirNameUpper . '_AUTHOR_LOGOIMG', constant($moduleDirNameUpper . '_URL') . '/assets/images/logoModule.png');
    define($moduleDirNameUpper . '_UPLOAD_URL', XOOPS_UPLOAD_URL . '/' . $moduleDirName); // WITHOUT Trailing slash
    define($moduleDirNameUpper . '_UPLOAD_PATH', XOOPS_UPLOAD_PATH . '/' . $moduleDirName); // WITHOUT Trailing slash
    define($moduleDirNameUpper . '_CONSTANTS_DEFINED', 1);
}

$pathIcon16    = Xmf\Module\Admin::iconUrl('', 16);
$pathIcon32    = Xmf\Module\Admin::iconUrl('', 32);
//$pathModIcon16 = $helper->getModule()->getInfo('modicons16');
//$pathModIcon32 = $helper->getModule()->getInfo('modicons32');

$icons = [
    'edit'    => "<img src='" . $pathIcon16 . "/edit.png'  alt=" . _EDIT . "' align='middle'>",
    'delete'  => "<img src='" . $pathIcon16 . "/delete.png' alt='" . _DELETE . "' align='middle'>",
    'clone'   => "<img src='" . $pathIcon16 . "/editcopy.png' alt='" . _CLONE . "' align='middle'>",
    'preview' => "<img src='" . $pathIcon16 . "/view.png' alt='" . _PREVIEW . "' align='middle'>",
    'print'   => "<img src='" . $pathIcon16 . "/printer.png' alt='" . _CLONE . "' align='middle'>",
    'pdf'     => "<img src='" . $pathIcon16 . "/pdf.png' alt='" . _CLONE . "' align='middle'>",
    'add'     => "<img src='" . $pathIcon16 . "/add.png' alt='" . _ADD . "' align='middle'>",
    '0'       => "<img src='" . $pathIcon16 . "/0.png' alt='" . 0 . "' align='middle'>",
    '1'       => "<img src='" . $pathIcon16 . "/1.png' alt='" . 1 . "' align='middle'>",
];

$debug = false;

// MyTextSanitizer object
$myts = \MyTextSanitizer::getInstance();

if (!isset($GLOBALS['xoopsTpl']) || !($GLOBALS['xoopsTpl'] instanceof \XoopsTpl)) {
    require_once $GLOBALS['xoops']->path('class/template.php');
    $GLOBALS['xoopsTpl'] = new \XoopsTpl();
}

$GLOBALS['xoopsTpl']->assign('mod_url', XOOPS_URL . '/modules/' . $moduleDirName);
// Local icons path
if (is_object($helper->getModule())) {
    $pathModIcon16 = $helper->getModule()->getInfo('modicons16');
    $pathModIcon32 = $helper->getModule()->getInfo('modicons32');

    $GLOBALS['xoopsTpl']->assign('pathModIcon16', XOOPS_URL . '/modules/' . $moduleDirName . '/' . $pathModIcon16);
    $GLOBALS['xoopsTpl']->assign('pathModIcon32', $pathModIcon32);
}

    //============================================================

function validateuser($username, $password)
{
    global $xoopsDB;
    $sql = 'select * from ' . $xoopsDB->prefix('users') . " WHERE uname = '$username' and pass = " . (32 == strlen($password) && strtolower($password) == $password ? "'$password'" : "md5('$password')");
    $ret = $xoopsDB->query($sql);
    if (!$xoopsDB->getRowsNum($ret)) {
        return false;
    } else {
        return true;
    }
}

/**
 * @param $username
 * @param $password
 * @return bool
 */
function user_uid($username, $password)
{
    global $xoopsDB;
    $sql = 'select uid from ' . $xoopsDB->prefix('users') . " WHERE uname = '$username' and pass = " . (32 == strlen($password) && strtolower($password) == $password ? "'$password'" : "md5('$password')");
    $ret = $xoopsDB->query($sql);
    if (!$xoopsDB->getRowsNum($ret)) {
        return false;
    } else {
        $row = $xoopsDB->fetchArray($ret);
        return $row['uid'];
    }
}

/**
 * @param $tbl_id
 * @param $data
 * @param $function
 * @return bool
 */
function validate($tbl_id, $data, $function)
{
    global $xoopsDB;
    $sql  = 'select * from ' . $xoopsDB->prefix('json_tables') . " WHERE tablename = '" . get_tablename($tbl_id) . "' and $function = 1";
    $ret  = $xoopsDB->query($sql);
    $pass = true;
    if (!$xoopsDB->getRowsNum($ret)) {
        $pass = false;
    } else {
        foreach ($data as $row) {
            $sql = 'select * from ' . $xoopsDB->prefix('json_fields') . " WHERE tbl_id = '$tbl_id' and $function = 1 and fieldname = '" . $row['field'] . "'";
            $ret = $xoopsDB->query($sql);
            if (!$xoopsDB->getRowsNum($ret) && !is_fieldkey($row['field'], $tbl_id)) {
                $pass = false;
            }
        }
    }

    return $pass;
}

/**
 * @param $function_file
 * @param $username
 * @param $password
 * @return mixed
 */
function checkright($function_file, $username, $password)
{
    global $xoopsConfig;
    $uid           = user_uid($username, $password);
    $moduleHandler = xoops_getHandler('module');
    $xoModule      = $moduleHandler->getByDirname('xjson');
    if (0 <> $uid) {
        global $xoopsDB, $xoopsModule;
        $rUser         = new \XoopsUser($uid);
        $grouppermHandler  = xoops_getHandler('groupperm');
        $groups        = is_object($rUser) ? $rUser->getGroups() : [XOOPS_GROUP_ANONYMOUS];
        $sql           = 'SELECT plugin_id FROM ' . $xoopsDB->prefix('json_plugins') . " WHERE plugin_file = '" . addslashes($function_file) . "'";
        $ret           = $xoopsDB->queryF($sql);
        $row           = $xoopsDB->fetchArray($ret);
        $item_id       = $row['plugin_id'];
        $modid         = $xoModule->getVar('mid');
        $onlineHandler = xoops_getHandler('online');
        $onlineHandler->write($uid, $username, time(), $modid, (string)$_SERVER['REMOTE_ADDR']);
        $memberHandler = xoops_getHandler('member');
        @ini_set('session.gc_maxlifetime', $xoopsConfig['session_expire'] * 60);
        session_set_saveHandler([&$sess_handler, 'open'], [&$sess_handler, 'close'], [&$sess_handler, 'read'], [&$sess_handler, 'write'], [&$sess_handler, 'destroy'], [&$sess_handler, 'gc']);
        session_start();
        $_SESSION['xoopsUserId']     = $uid;
        $GLOBALS['xoopsUser']        = $memberHandler->getUser($uid);
        $_SESSION['xoopsUserGroups'] = $GLOBALS['xoopsUser']->getGroups();
        $GLOBALS['sess_handler']->update_cookie();

        return $grouppermHandler->checkRight('plugin_call', $item_id, $groups, $modid);
    } else {
        global $xoopsDB, $xoopsModule;
        $grouppermHandler = xoops_getHandler('groupperm');
        $groups       = [XOOPS_GROUP_ANONYMOUS];
        $sql          = 'SELECT plugin_id FROM ' . $xoopsDB->prefix('json_plugins') . " WHERE plugin_file = '" . addslashes($function_file) . "'";
        $ret          = $xoopsDB->queryF($sql);
        $row          = $xoopsDB->fetchArray($ret);
        $item_id      = $row['plugin_id'];
        $modid        = $xoModule->getVar('mid');
        return $grouppermHandler->checkRight('plugin_call', $item_id, $groups, $modid);
    }
}

/**
 * @param $tablename
 * @return mixed
 */
function get_tableid($tablename)
{
    global $xoopsDB;
    $sql = 'SELECT * FROM ' . $xoopsDB->prefix('json_tables') . " WHERE tablename = '$tablename'";
    $ret = $xoopsDB->query($sql);
    $row = $xoopsDB->fetchArray($ret);
    return $row['tbl_id'];
}

/**
 * @param $tableid
 * @return mixed
 */
function get_tablename($tableid)
{
    global $xoopsDB;
    $sql = 'SELECT * FROM ' . $xoopsDB->prefix('json_tables') . " WHERE tbl_id = '$tableid'";
    $ret = $xoopsDB->query($sql);
    $row = $xoopsDB->fetchArray($ret);
    return $row['tablename'];
}

/**
 * @param $fld_id
 * @param $tbl_id
 * @return mixed
 */
function get_fieldname($fld_id, $tbl_id)
{
    global $xoopsDB;
    $sql = 'SELECT * FROM ' . $xoopsDB->prefix('json_fields') . " WHERE tbl_id = '$tbl_id' and fld_id = '$fld_id'";
    $ret = $xoopsDB->query($sql);
    $row = $xoopsDB->fetchArray($ret);
    return $row['fieldname'];
}

/**
 * @param $fieldname
 * @param $tbl_id
 * @return bool
 */
function is_fieldkey($fieldname, $tbl_id)
{
    global $xoopsDB;
    $sql = 'SELECT * FROM ' . $xoopsDB->prefix('json_fields') . " WHERE tbl_id = '$tbl_id' and fieldname = '$fieldname' and `key` = 1";
    //echo $sql."\n";
    $ret = $xoopsDB->query($sql);
    if (!$xoopsDB->getRowsNum($ret)) {
        return false;
    } else {
        return true;
    }
}

if (!function_exists('xoops_isIPv6')) {
    /**
     * @param string $ip
     * @return bool
     */
    function xoops_isIPv6($ip = '')
    {
        if ('' === $ip) {
            return false;
        }

        if (substr_count($ip, ':') > 0) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('xoops_getUserIP')) {
    /**
     * @param bool $ip
     * @return array
     */
    function xoops_getUserIP($ip = false)
    {
        $ret = [];
        if (is_object($GLOBALS['xoopsUser'])) {
            $ret['uid']   = $GLOBALS['xoopsUser']->getVar('uid');
            $ret['uname'] = $GLOBALS['xoopsUser']->getVar('uname');
        } else {
            $ret['uid']   = 0;
            $ret['uname'] = $GLOBALS['xoopsConfig']['anonymous'];
        }
        $ret['sessionid'] = session_id();
        if (!$ip) {
            if ('' !== $_SERVER['HTTP_X_FORWARDED_FOR']) {
                $ip                  = (string)$_SERVER['HTTP_X_FORWARDED_FOR'];
                $ret['is_proxied']   = true;
                $proxy_ip            = $_SERVER['REMOTE_ADDR'];
                $ret['network-addy'] = @gethostbyaddr($ip);
                $ret['long']         = @ip2long($ip);
                if (xoops_isIPv6($ip)) {
                    $ret['ip6']       = $ip;
                    $ret['proxy-ip6'] = $proxy_ip;
                } else {
                    $ret['ip4']       = $ip;
                    $ret['proxy-ip4'] = $proxy_ip;
                }
            } else {
                $ret['is_proxied']   = false;
                $ip                  = (string)$_SERVER['REMOTE_ADDR'];
                $ret['network-addy'] = @gethostbyaddr($ip);
                $ret['long']         = @ip2long($ip);
                if (xoops_isIPv6($ip)) {
                    $ret['ip6'] = $ip;
                } else {
                    $ret['ip4'] = $ip;
                }
            }
        } else {
            $ret['is_proxied']   = false;
            $ret['network-addy'] = @gethostbyaddr($ip);
            $ret['long']         = @ip2long($ip);
            if (xoops_isIPv6($ip)) {
                $ret['ip6'] = $ip;
            } else {
                $ret['ip4'] = $ip;
            }
        }
        $ret['md5']  = md5($ip . $ret['long'] . $ret['network-addy'] . $ret['is_proxied']);
        $ret['sha1'] = sha1($ip . $ret['long'] . $ret['network-addy'] . $ret['is_proxied'] . $ret['uid'] . $ret['uname']);
        $ret['made'] = time();
        return $ret;
    }
}

/**
 * @param $function_file
 * @param $username
 * @param $password
 * @return array|bool
 */
function check_for_lock($function_file, $username, $password)
{
    xoops_load('xoopscache');
    $userip = xoops_getUserIP();
    $retn   = false;
    if ($result = \XoopsCache::read('lock_' . $function_file . '_' . $username)) {
        foreach ($result as $id => $ret) {
            if ($ret['made'] < time() - $GLOBALS['xoopsModuleConfig']['lock_seconds']
                || $ret['made'] < ((time() - $GLOBALS['xoopsModuleConfig']['lock_seconds']) + mt_rand(1, $GLOBALS['xoopsModuleConfig']['lock_random_seed']))) {
                unset($result[$id]);
            } elseif ($ret['md5'] == $userip['md5']) {
                $retn = ['ErrNum' => 9, 'ErrDesc' => 'No Permission for plug-in'];
            }
        }
        \XoopsCache::delete('lock_' . $function_file . '_' . $username);
        \XoopsCache::write('lock_' . $function_file . '_' . $username, $result, $GLOBALS['cache_seconds']);
        return $retn;
    }
}

/**
 * @param $function_file
 * @param $username
 * @param $password
 * @return array
 */
function mark_for_lock($function_file, $username, $password)
{
    xoops_load('xoopscache');
    $userip = xoops_getUserIP();
    $result = [];
    if ($result = XoopsCache::read('lock_' . $function_file . '_' . $username)) {
        $result[] = $userip;
        XoopsCache::delete('lock_' . $function_file . '_' . $username);
        XoopsCache::write('lock_' . $function_file . '_' . $username, $result, $GLOBALS['cache_seconds']);
        return ['ErrNum' => 9, 'ErrDesc' => 'No Permission for plug-in'];
    } else {
        $result[] = $userip;
        XoopsCache::delete('lock_' . $function_file . '_' . $username);
        XoopsCache::write('lock_' . $function_file . '_' . $username, $result, $GLOBALS['cache_seconds']);
        return ['ErrNum' => 9, 'ErrDesc' => 'No Permission for plug-in'];
    }
}
