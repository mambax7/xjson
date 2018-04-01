<?php

use XoopsModules\Xjson;

include XOOPS_ROOT_PATH . '/modules/xjson/plugins/inc/usercheck.php';
include XOOPS_ROOT_PATH . '/modules/xjson/plugins/inc/authcheck.php';
include XOOPS_ROOT_PATH . '/modules/xjson/plugins/inc/siteinfocheck.php';

/**
 * @return array
 */
function xoops_check_activation_xsd()
{
    $xsd                  = [];
    $i                    = 0;
    $xsd['request'][$i]   = ['name' => 'username', 'type' => 'string'];
    $xsd['request'][$i++] = ['name' => 'password', 'type' => 'string'];
    $data                 = [];
    $data[]               = ['name' => 'uname', 'type' => 'string'];
    $data[]               = ['name' => 'actkey', 'type' => 'string'];
    $data_b               = [];
    $data_b[]             = ['name' => 'sitename', 'type' => 'string'];
    $data_b[]             = ['name' => 'adminmail', 'type' => 'string'];
    $data_b[]             = ['name' => 'xoops_url', 'type' => 'string'];
    $data[]               = ['items' => ['data' => $data_b, 'objname' => 'siteinfo']];
    $i++;
    $xsd['request'][$i]['items']['data']    = $data;
    $xsd['request'][$i]['items']['objname'] = 'user';

    $i                   = 0;
    $xsd['response'][$i] = ['name' => 'ERRNUM', 'type' => 'integer'];
    $data                = [];
    $data[]              = ['name' => 'uname', 'type' => 'integer'];
    $data[]              = ['name' => 'actkey', 'type' => 'string'];
    $i++;
    $xsd['response'][$i]['items']['data']    = $data;
    $xsd['response'][$i]['items']['objname'] = 'RESULT';

    return $xsd;
}

function xoops_check_activation_wsdl()
{
}

function xoops_check_activation_wsdl_service()
{
}

/**
 * @param $username
 * @param $password
 * @param $user
 * @return array
 */
function xoops_check_activation($username, $password, $user)
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

    if ('' !== $user['passhash']) {
        if ($user['passhash'] != sha1(($user['time'] - $user['rand']) . $user['uname'] . $user['actkey'])) {
            return ['ERRNUM' => 4, 'ERRTXT' => 'No Passhash'];
        }
    } else {
        return ['ERRNUM' => 4, 'ERRTXT' => 'No Passhash'];
    }

    foreach ($user as $k => $l) {
        ${$k} = $l;
    }

    $siteinfo = check_siteinfo($siteinfo);

    require_once XOOPS_ROOT_PATH . '/class/auth/authfactory.php';
    require_once XOOPS_ROOT_PATH . '/language/' . $xoopsConfig['language'] . '/auth.php';
    $xoopsAuth =& XoopsAuthFactory::getAuthConnection(addslashes($uname));

    if (true === check_auth_class($xoopsAuth)) {
        $result = $xoopsAuth->check_activation($uname, $actkey, $siteinfo);
        return $result;
    } else {
        global $xoopsConfig, $xoopsConfigUser;

        global $xoopsDB;
        $sql = 'SELECT uid FROM ' . $xoopsDB->prefix('users') . " WHERE uname = '$uname'";
        $ret = $xoopsDB->query($sql);
        $row = $xoopsDB->fetchArray($ret);

        $memberHandler = xoops_getHandler('member');
        $thisuser      = $memberHandler->getUser($row['uid']);
        if (!is_object($thisuser)) {
            exit();
        }
        if ($thisuser->getVar('actkey') != $actkey) {
            $return = ['state' => _US_STATE_ONE, 'action' => 'redirect_header', 'url' => 'index.php', 'opt' => 5, 'text' => _US_ACTKEYNOT];
        } else {
            if ($thisuser->getVar('level') > 0) {
                $return = ['state' => _US_STATE_ONE, 'action' => 'redirect_header', 'url' => 'user.php', 'opt' => 5, 'text' => _US_ACONTACT, 'set' => false];
            } else {
                if (false !== $memberHandler->activateUser($thisuser)) {
                    $configHandler   = xoops_getHandler('config');
                    $xoopsConfigUser = $configHandler->getConfigsByCat(XOOPS_CONF_USER);
                    if (2 == $xoopsConfigUser['activation_type']) {
                        $myts        = \MyTextSanitizer::getInstance();
                        $xoopsMailer =& xoops_getMailer();
                        $xoopsMailer->useMail();
                        $xoopsMailer->setTemplate('activated.tpl');
                        $xoopsMailer->assign('SITENAME', $siteinfo['sitename']);
                        $xoopsMailer->assign('ADMINMAIL', $siteinfo['adminmail']);
                        $xoopsMailer->assign('SITEURL', $siteinfo['xoops_url'] . '/');
                        $xoopsMailer->setToUsers($thisuser);
                        $xoopsMailer->setFromEmail($siteinfo['adminmail']);
                        $xoopsMailer->setFromName($siteinfo['sitename']);
                        $xoopsMailer->setSubject(sprintf(_US_YOURACCOUNT, $siteinfo['sitename']));
                        if (!$xoopsMailer->send()) {
                            $return = ['state' => _US_STATE_TWO, 'text' => sprintf(_US_ACTVMAILNG, $thisuser->getVar('uname'))];
                        } else {
                            $return = ['state' => _US_STATE_TWO, 'text' => sprintf(_US_ACTVMAILOK, $thisuser->getVar('uname'))];
                        }
                    } else {
                        $local = explode(' @ ', $thisuser->getVar('user_intrest'));
                        if (_US_USERREG == $local[0]) {
                            $return = ['state' => _US_STATE_ONE, 'action' => 'redirect_header', 'url' => $local[1] . '/user.php', 'opt' => 5, 'text' => _US_ACTLOGIN, 'set' => false];
                        } else {
                            $return = ['state' => _US_STATE_ONE, 'action' => 'redirect_header', 'url' => 'user.php', 'opt' => 5, 'text' => _US_ACTLOGIN, 'set' => false];
                        }
                    }
                } else {
                    $return = ['state' => _US_STATE_ONE, 'action' => 'redirect_header', 'url' => 'index.php', 'opt' => 5, 'text' => 'Activation failed!'];
                }
            }
        }

        return $return;
    }
}
?>
