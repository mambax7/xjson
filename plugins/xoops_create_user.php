<?php

use XoopsModules\Xjson;

require_once XOOPS_ROOT_PATH . '/modules/xcurl/plugins/inc/usercheck.php';
require_once XOOPS_ROOT_PATH . '/modules/xcurl/plugins/inc/authcheck.php';
require_once XOOPS_ROOT_PATH . '/modules/xcurl/plugins/inc/siteinfocheck.php';
require_once XOOPS_ROOT_PATH . '/class/xoopsmailer.php';
require_once XOOPS_ROOT_PATH . '/class/xoopsuser.php';
//require_once XOOPS_ROOT_PATH . '/kernel/user.php';

/** @var Xjson\Helper $helper */
$helper = Xjson\Helper::getInstance();

/**
 * @return array
 */
function xoops_create_user_xsd()
{
    $xsd      = [];
    $i        = 0;
    $data     = [];
    $data[]   = ['name' => 'username', 'type' => 'string'];
    $data[]   = ['name' => 'password', 'type' => 'string'];
    $datab    = [];
    $datab[]  = ['name' => 'user_viewemail', 'type' => 'integer'];
    $datab[]  = ['name' => 'uname', 'type' => 'string'];
    $datab[]  = ['name' => 'email', 'type' => 'string'];
    $datab[]  = ['name' => 'url', 'type' => 'string'];
    $datab[]  = ['name' => 'actkey', 'type' => 'string'];
    $datab[]  = ['name' => 'pass', 'type' => 'string'];
    $datab[]  = ['name' => 'timezone_offset', 'type' => 'string'];
    $datab[]  = ['name' => 'user_mailok', 'type' => 'integer'];
    $datab[]  = ['name' => 'passhash', 'type' => 'string'];
    $datab[]  = ['name' => 'rand', 'type' => 'integer'];
    $data[]   = ['items' => ['data' => $datab, 'objname' => 'user']];
    $data_c   = [];
    $data_c[] = ['name' => 'sitename', 'type' => 'string'];
    $data_c[] = ['name' => 'adminmail', 'type' => 'string'];
    $data_c[] = ['name' => 'xoops_url', 'type' => 'string'];
    $data[]   = ['items' => ['data' => $datab, 'objname' => 'siteinfo']];

    $i++;
    $xsd['request'][$i]['items']['data']    = $data;
    $xsd['request'][$i]['items']['objname'] = 'var';
    $i                                      = 0;
    $xsd['response'][$i]                    = ['name' => 'ERRNUM', 'type' => 'integer'];
    $data                                   = [];
    $data[]                                 = ['name' => 'id', 'type' => 'integer'];
    $data[]                                 = ['name' => 'user', 'type' => 'string'];
    $data[]                                 = ['name' => 'text', 'type' => 'string'];
    $i++;
    $xsd['response'][$i]['items']['data']    = $data;
    $xsd['response'][$i]['items']['objname'] = 'RESULT';

    return $xsd;
}

function xoops_create_user_wsdl()
{
}

function xoops_create_user_wsdl_service()
{
}

/**
 * @param $username
 * @param $password
 * @param $user
 * @param $siteinfo
 * @return array|mixed
 */
function xoops_create_user($username, $password, $user, $siteinfo)
{
    xoops_load('userUtility');

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
        if ($user['passhash'] != sha1(($user['time'] - $user['rand']) . $user['uname'] . $user['pass'])) {
            return ['ERRNUM' => 4, 'ERRTXT' => 'No Passhash'];
        }
    } else {
        return ['ERRNUM' => 4, 'ERRTXT' => 'No Passhash'];
    }

    foreach ($user as $k => $l) {
        ${$k} = $l;
    }

    require_once XOOPS_ROOT_PATH . '/class/auth/authfactory.php';
    require_once XOOPS_ROOT_PATH . '/language/' . $xoopsConfig['language'] . '/auth.php';
    $xoopsAuth =& XoopsAuthFactory::getAuthConnection($uname);

    if (true === check_auth_class($xoopsAuth)) {
        $result = $xoopsAuth->create_user($user_viewemail, $uname, $email, $url, $actkey, $pass, $timezone_offset, $user_mailok, $siteinfo);
        return $result;
    } else {
        if (0 == strlen(userCheck($uname, $email, $pass, $pass))) {
            global $xoopsConfig;
            $configHandler   = xoops_getHandler('config');
            $xoopsConfigUser = $configHandler->getConfigsByCat(XOOPS_CONF_USER);

            $memberHandler = xoops_getHandler('member');
            $newuser       = $memberHandler->createUser();
            $newuser->setVar('user_viewemail', $user_viewemail, true);
            $newuser->setVar('uname', $uname, true);
            $newuser->setVar('email', $email, true);
            if ('' !== $url) {
                $newuser->setVar('url', formatURL($url), true);
            }
            $newuser->setVar('user_avatar', 'blank.gif', true);

            if (empty($actkey)) {
                $actkey = substr(md5(uniqid(mt_rand(), 1)), 0, 8);
            }

            $newuser->setVar('actkey', $actkey, true);
            $newuser->setVar('pass', md5($pass), true);
            $newuser->setVar('timezone_offset', $timezone_offset, true);
            $newuser->setVar('user_regdate', time(), true);
            $newuser->setVar('uorder', $xoopsConfig['com_order'], true);
            $newuser->setVar('umode', $xoopsConfig['com_mode'], true);
            $newuser->setVar('user_mailok', $user_mailok, true);
            $newuser->setVar('user_intrest', _US_USERREG . ' @ ' . $xoops_url, true);
            if (1 == $xoopsConfigUser['activation_type']) {
                $newuser->setVar('level', 1, true);
            }

            if (!$memberHandler->insertUser($newuser, true)) {
                $return = ['state' => 1, 'text' => _US_REGISTERNG];
            } else {
                $newid = $newuser->getVar('uid');
                if (!$memberHandler->addUserToGroup(XOOPS_GROUP_USERS, $newid)) {
                    $return = ['state' => 1, 'text' => _US_REGISTERNG];
                }
                if (1 == $xoopsConfigUser['activation_type']) {
                    $return = ['state' => 2, 'user' => $uname];
                }
                // Sending notification email to user for self activation
                if (0 == $xoopsConfigUser['activation_type']) {
                    $xoopsMailer = xoops_getMailer();
                    $xoopsMailer->useMail();
                    $xoopsMailer->setTemplate('register.tpl');
                    $xoopsMailer->assign('SITENAME', $siteinfo['sitename']);
                    $xoopsMailer->assign('ADMINMAIL', $siteinfo['adminmail']);
                    $xoopsMailer->assign('SITEURL', XOOPS_URL . '/');
                    $xoopsMailer->setToUsers(new \XoopsUser($newid));
                    $xoopsMailer->setFromEmail($siteinfo['adminmail']);
                    $xoopsMailer->setFromName($siteinfo['sitename']);
                    $xoopsMailer->setSubject(sprintf(_US_USERKEYFOR, $uname));
                    if (!$xoopsMailer->send()) {
                        $return = ['state' => 1, 'text' => _US_YOURREGMAILNG];
                    } else {
                        $return = ['state' => 1, 'text' => _US_YOURREGISTERED];
                    }
                    // Sending notification email to administrator for activation
                } elseif (2 == $xoopsConfigUser['activation_type']) {
                    $xoopsMailer = xoops_getMailer();
                    $xoopsMailer->useMail();
                    $xoopsMailer->setTemplate('adminactivate.tpl');
                    $xoopsMailer->assign('USERNAME', $uname);
                    $xoopsMailer->assign('USEREMAIL', $email);
                    if (XOOPS_URL == $siteinfo['xoops_url']) {
                        $xoopsMailer->assign('USERACTLINK', $siteinfo['xoops_url'] . '/register.php?op=actv&id=' . $newid . '&actkey=' . $actkey);
                    }
                } else {
                    $xoopsMailer->assign('USERACTLINK', $siteinfo['xoops_url'] . '/register.php?op=actv&uname=' . $uname . '&actkey=' . $actkey);
                }
                $xoopsMailer->assign('SITENAME', $siteinfo['sitename']);
                $xoopsMailer->assign('ADMINMAIL', $siteinfo['adminmail']);
                $xoopsMailer->assign('SITEURL', $siteinfo['xoops_url'] . '/');
                $memberHandler = xoops_getHandler('member');
                $xoopsMailer->setToGroups($memberHandler->getGroup($xoopsConfigUser['activation_group']));
                $xoopsMailer->setFromEmail($siteinfo['adminmail']);
                $xoopsMailer->setFromName($siteinfo['sitename']);
                $xoopsMailer->setSubject(sprintf(_US_USERKEYFOR, $uname));
                if (!$xoopsMailer->send()) {
                    $return = ['state' => 1, 'text' => _US_YOURREGMAILNG];
                } else {
                    $return = ['state' => 1, 'text' => _US_YOURREGISTERED2];
                }
            }
            if (1 == $xoopsConfigUser['new_user_notify'] && !empty($xoopsConfigUser['new_user_notify_group'])) {
                $xoopsMailer = xoops_getMailer();
                $xoopsMailer->useMail();
                $memberHandler = xoops_getHandler('member');
                $xoopsMailer->setToGroups($memberHandler->getGroup($xoopsConfigUser['new_user_notify_group']));
                $xoopsMailer->setFromEmail($siteinfo['adminmail']);
                $xoopsMailer->setFromName($siteinfo['sitename']);
                $xoopsMailer->setSubject(sprintf(_US_NEWUSERREGAT, $xoopsConfig['sitename']));
                $xoopsMailer->setBody(sprintf(_US_HASJUSTREG, $uname));
                $xoopsMailer->send();
            }

            if (stripos($_SERVER['HTTP_HOST'], 'xortify.com')) {
                define('XORTIFY_API_URI', 'http://xortify.chronolabs.coop/soap/');
            } else {
                define('XORTIFY_API_URI', 'http://xortify.com/soap/');
            }

            define('XORTIFY_USER_AGENT', 'Mozilla/5.0 (X11; U; Linux i686; pl-PL; rv:1.9.0.2) XOOPS/20100101 XoopsAuth/1.xx (php)');

            if (!$ch = curl_init(str_replace('soap', 'ban', XORTIFY_API_URI))) {
                trigger_error('Could not intialise CURLSERIAL file: ' . XORTIFY_API_URI);
                return ['ERRNUM' => 1, 'RESULT' => $return];
            }
            $cookies = XOOPS_VAR_PATH . '/cache/xoops_cache/authcurl_' . md5(XORTIFY_API_URI) . '.cookie';

            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, XORTIFY_USER_AGENT);

            $data = curl_exec($ch);
            curl_close($ch);

            if (stripos($data, 'solve puzzel') > 0) {
                $sc     = new soapclient(null, ['location' => XORTIFY_API_URI, 'uri' => XORTIFY_API_URI]);
                $result = $sc->__soapCall('xoops_create_user', [
                    'username' => $username,
                    'password' => $password,
                    'user'     => $user,
                    'siteinfo' => $siteinfo
                ]);
            }

            return ['ERRNUM' => 1, 'RESULT' => $return];
        } else {
            return ['ERRNUM' => 1, 'RESULT' => ['state' => 1, 'text' => userCheck($uname, $email, $pass, $pass)]];
        }
    }
}
?>
