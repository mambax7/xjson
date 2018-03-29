<?php

use XoopsModules\Xjson;
/** @var Xjson\Helper $helper */
$helper = Xjson\Helper::getInstance();

/**
 * @return array
 */
function arpmacaddress_xsd()
{
    $xsd                  = [];
    $i                    = 0;
    $xsd['request'][$i]   = ['name' => 'username', 'type' => 'string'];
    $xsd['request'][$i++] = ['name' => 'password', 'type' => 'string'];
    $xsd['request'][$i++] = ['name' => 'remoteaddress', 'type' => 'string'];

    $i                     = 0;
    $xsd['response'][$i]   = ['name' => 'ERRNUM', 'type' => 'integer'];
    $xsd['response'][$i++] = ['name' => 'RESULT', 'type' => 'string'];
    $xsd['response'][$i++] = ['name' => 'MACADDRESS', 'type' => 'string'];

    return $xsd;
}

function arpmacaddress_wsdl()
{
}

function arpmacaddress_wsdl_service()
{
}

$ret = explode(' ', XOOPS_VERSION);
$ver = explode('.', $ret[1]);

if ($ret[0] >= 2 && $ret[1] >= 3) {
    /**
     * @param $username
     * @param $password
     * @param $remoteaddress
     * @return array|bool
     */
    function arpmacaddress($username, $password, $remoteaddress)
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

        error_reporting(0);
        exec('arping -c 1 ' . $remoteaddress, $user_mac);
        $macaddress = substr($user_mac[1], strpos($user_mac[1], ':') - 2, '17');

        return ['MACADDRESS' => $macaddress];
    }
}
?>

