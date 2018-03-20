<?php
include XOOPS_ROOT_PATH . '/modules/xcurl/plugins/inc/authcheck.php';

/**
 * @return array
 */
function xoops_network_disclaimer_xsd()
{
    $xsd    = [];
    $i      = 0;
    $data   = [];
    $data[] = ['name' => 'username', 'type' => 'string'];
    $data[] = ['name' => 'password', 'type' => 'string'];
    $i++;
    $xsd['request'][$i]['items']['data']    = $data;
    $xsd['request'][$i]['items']['objname'] = 'var';

    $i                   = 0;
    $xsd['response'][$i] = ['name' => 'ERRNUM', 'type' => 'integer'];
    $xsd['response'][$i] = ['name' => 'RESULT', 'type' => 'string'];

    return $xsd;
}

function xoops_network_disclaimer_wsdl()
{
}

function xoops_network_disclaimer_wsdl_service()
{
}

/**
 * @param $username
 * @param $password
 * @return array|bool
 */
function xoops_network_disclaimer($username, $password)
{
    global $xoopsModuleConfig, $xoopsConfig;

    if (1 == $xoopsModuleConfig['site_user_auth']) {
        if ($ret = check_for_lock(basename(__FILE__), $username, $password)) {
            return $ret;
        }
        if (!checkright(basename(__FILE__), $username, $password)) {
            mark_for_lock(basename(__FILE__), $username, $password);
            return ['ErrNum' => 9, 'ErrDesc' => 'No Permission for plug-in'];
        }
    }

    require_once XOOPS_ROOT_PATH . '/class/auth/authfactory.php';
    require_once XOOPS_ROOT_PATH . '/language/' . $xoopsConfig['language'] . '/auth.php';
    $xoopsAuth = XoopsAuthFactory::getAuthConnection();

    if (true === check_auth_class($xoopsAuth)) {
        $result = $xoopsAuth->network_disclaimer();
        return $result;
    } else {
        $configHandler   = xoops_getHandler('config');
        $xoopsConfigUser = $configHandler->getConfigsByCat(XOOPS_CONF_USER);

        return ['ERRNUM' => 1, 'RESULT' => $xoopsConfigUser['reg_disclaimer']];
    }
}
?>
