<?php

use XoopsModules\Xjson;

/**
 * @return array
 */
function xoops_authentication_xsd()
{
    $xsd                                    = [];
    $i                                      = 0;
    $xsd['request'][$i]                     = ['name' => 'username', 'type' => 'string'];
    $xsd['request'][$i++]                   = ['name' => 'password', 'type' => 'string'];
    $data                                   = [];
    $data[]                                 = ['name' => 'username', 'type' => 'string'];
    $data[]                                 = ['name' => 'password', 'type' => 'string'];
    $xsd['request'][$i++]['items']['data']  = $data;
    $xsd['request'][$i]['items']['objname'] = 'auth';

    $i                   = 0;
    $xsd['response'][$i] = ['name' => 'ERRNUM', 'type' => 'integer'];
    $data                = [];
    $data[]              = ['name' => 'uid', 'type' => 'integer'];
    $data[]              = ['name' => 'uname', 'type' => 'string'];
    $data[]              = ['name' => 'email', 'type' => 'string'];
    $data[]              = ['name' => 'user_from', 'type' => 'string'];
    $data[]              = ['name' => 'name', 'type' => 'integer'];
    $data[]              = ['name' => 'url', 'type' => 'string'];
    $data[]              = ['name' => 'user_icq', 'type' => 'string'];
    $data[]              = ['name' => 'user_sig', 'type' => 'string'];
    $data[]              = ['name' => 'user_viewemail', 'type' => 'integer'];
    $data[]              = ['name' => 'user_aim', 'type' => 'string'];
    $data[]              = ['name' => 'user_yim', 'type' => 'string'];
    $data[]              = ['name' => 'user_msnm', 'type' => 'string'];
    $data[]              = ['name' => 'attachsig', 'type' => 'integer'];
    $data[]              = ['name' => 'timezone_offset', 'type' => 'string'];
    $data[]              = ['name' => 'notify_method', 'type' => 'integer'];
    $data[]              = ['name' => 'user_occ', 'type' => 'string'];
    $data[]              = ['name' => 'bio', 'type' => 'string'];
    $data[]              = ['name' => 'user_intrest', 'type' => 'string'];
    $data[]              = ['name' => 'user_mailok', 'type' => 'integer'];
    $i++;
    $xsd['response'][$i]['items']['data']    = $data;
    $xsd['response'][$i]['items']['objname'] = 'RESULT';

    return $xsd;
}

function xoops_authentication_wsdl()
{
}

function xoops_authentication_wsdl_service()
{
}

/**
 * @param $username
 * @param $password
 * @param $auth
 * @return array|bool
 */
function xoops_authentication($username, $password, $auth)
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

    if ('' !== $auth['passhash']) {
        if ($auth['passhash'] != sha1(($auth['time'] - $auth['rand']) . $auth['username'] . $auth['password'])) {
            return ['ERRNUM' => 4, 'ERRTXT' => 'No Passhash'];
        }
    } else {
        return ['ERRNUM' => 4, 'ERRTXT' => 'No Passhash'];
    }

    require_once XOOPS_ROOT_PATH . '/class/auth/authfactory.php';
    require_once XOOPS_ROOT_PATH . '/language/' . $xoopsConfig['language'] . '/auth.php';
    $xoopsAuth =& XoopsAuthFactory::getAuthConnection(addslashes($auth['username']));
    $user      = $xoopsAuth->authenticate(addslashes($auth['username']), addslashes($auth['password']));

    if (is_object($user)) {
        $row = [
            'uid'             => $user->getVar('uid'),
            'uname'           => $user->getVar('uname'),
            'email'           => $user->getVar('email'),
            'user_from'       => $user->getVar('user_from'),
            'name'            => $user->getVar('name'),
            'url'             => $user->getVar('url'),
            'user_icq'        => $user->getVar('user_icq'),
            'user_sig'        => $user->getVar('user_sig'),
            'user_viewemail'  => $user->getVar('user_viewemail'),
            'user_aim'        => $user->getVar('user_aim'),
            'user_yim'        => $user->getVar('user_yim'),
            'user_msnm'       => $user->getVar('user_msnm'),
            'attachsig'       => $user->getVar('attachsig'),
            'timezone_offset' => $user->getVar('timezone_offset'),
            'notify_method'   => $user->getVar('notify_method'),
            'user_occ'        => $user->getVar('user_occ'),
            'bio'             => $user->getVar('bio'),
            'user_intrest'    => $user->getVar('user_intrest'),
            'user_mailok'     => $user->getVar('user_mailok')
        ];
    }

    if (!empty($row)) {
        return ['ERRNUM' => 1, 'RESULT' => $row];
    } else {
        return ['ERRNUM' => 3, 'ERRTXT' => _ERR_FUNCTION_FAIL];
    }
}
?>
