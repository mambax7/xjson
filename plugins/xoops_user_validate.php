<?php

use XoopsModules\Xjson;

include XOOPS_ROOT_PATH . '/modules/xcurl/plugins/inc/usercheck.php';
include XOOPS_ROOT_PATH . '/modules/xcurl/plugins/inc/authcheck.php';

/**
 * @return array
 */
function xoops_user_validate_xsd()
{
    $xsd     = [];
    $i       = 0;
    $data    = [];
    $data[]  = ['name' => 'username', 'type' => 'string'];
    $data[]  = ['name' => 'password', 'type' => 'string'];
    $datab   = [];
    $datab[] = ['name' => 'uname', 'type' => 'string'];
    $datab[] = ['name' => 'pass', 'type' => 'string'];
    $datab[] = ['name' => 'vpass', 'type' => 'string'];
    $datab[] = ['name' => 'email', 'type' => 'string'];
    $data[]  = ['items' => ['data' => $datab, 'objname' => 'validate']];
    $i++;
    $xsd['request'][$i]['items']['data']    = $data;
    $xsd['request'][$i]['items']['objname'] = 'var';
    $i                                      = 0;
    $xsd['response'][$i]                    = ['name' => 'ERRNUM', 'type' => 'integer'];
    $xsd['response'][$i++]                  = ['name' => 'RESULT', 'type' => 'string'];

    return $xsd;
}

function xoops_user_validate_wsdl()
{
}

function xoops_user_validate_wsdl_service()
{
}

$ret = explode(' ', XOOPS_VERSION);
$ver = explode('.', $ret[1]);

if ($ret[0] >= 2 && $ret[1] >= 3) {
    xoops_load('userUtility');

    /**
     * @param $username
     * @param $password
     * @param $validate
     * @return array|bool
     */
    function xoops_user_validate($username, $password, $validate)
    {
        global  $xoopsConfig;
        /** @var Xjson\Helper $helper */
        $helper = Xjson\Helper::getInstance();

        if (1 == $helper->getConfig('site_user_auth')) {
            if ($ret = check_for_lock(basename(__FILE__), $username, $password)) {
                return $ret;
            }
            if (!checkright(basename(__FILE__), $username, $password)) {
                mark_for_lock(basename(__FILE__), $username, $password);
                return ['ErrNum' => 9, 'ErrDesc' => 'No Permission for plug-in'];
            }
        }

        if ('' !== $validate['passhash']) {
            if ($validate['passhash'] != sha1(($validate['time'] - $validate['rand']) . $validate['uname'] . $validate['pass'])) {
                return ['ERRNUM' => 4, 'ERRTXT' => 'No Passhash'];
            }
        } else {
            return ['ERRNUM' => 4, 'ERRTXT' => 'No Passhash'];
        }

        require_once XOOPS_ROOT_PATH . '/class/auth/authfactory.php';
        require_once XOOPS_ROOT_PATH . '/language/' . $xoopsConfig['language'] . '/auth.php';
        $xoopsAuth =& XoopsAuthFactory::getAuthConnection($myts->addSlashes($validate['uname']));

        if (true === check_auth_class($xoopsAuth)) {
            $result = $xoopsAuth->validate($validate['uname'], $validate['email'], $validate['pass'], $validate['vpass']);
            return $result;
        } else {
            return ['ERRNUM' => 1, 'RESULT' => \XoopsUserUtility::validate($validate['uname'], $validate['email'], $validate['pass'], $validate['vpass'])];
        }
    }
} else { // LEGACY SUPPORT

    /**
     * @param $username
     * @param $password
     * @param $validate
     * @return array|bool
     */
    function xoops_user_validate($username, $password, $validate)
    {
        global  $xoopsConfig;
        /** @var Xjson\Helper $helper */
        $helper = Xjson\Helper::getInstance();

        if (1 == $helper->getConfig('site_user_auth')) {
            if ($ret = check_for_lock(basename(__FILE__), $username, $password)) {
                return $ret;
            }
            if (!checkright(basename(__FILE__), $username, $password)) {
                mark_for_lock(basename(__FILE__), $username, $password);
                return ['ErrNum' => 9, 'ErrDesc' => 'No Permission for plug-in'];
            }
        }

        if ('' !== $validate['passhash']) {
            if ($validate['passhash'] != sha1(($validate['time'] - $validate['rand']) . $validate['uname'] . $validate['pass'])) {
                return ['ERRNUM' => 4, 'ERRTXT' => 'No Passhash'];
            }
        } else {
            return ['ERRNUM' => 4, 'ERRTXT' => 'No Passhash'];
        }

        return ['ERRNUM' => 1, 'RESULT' => userCheck($validate['uname'], $validate['email'], $validate['pass'], $validate['vpass'])];
    }
}
?>
