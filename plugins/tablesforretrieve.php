<?php

use XoopsModules\Xjson;


/**
 * @return array
 */
function tablesforretrieve_xsd()
{
    $xsd                                    = [];
    $i                                      = 0;
    $data                                   = [];
    $data[]                                 = ['name' => 'username', 'type' => 'string'];
    $data[]                                 = ['name' => 'password', 'type' => 'string'];
    $xsd['request'][$i]['items']['data']    = $data;
    $xsd['request'][$i]['items']['objname'] = 'var';

    $data                                    = [];
    $data[]                                  = ['name' => 'id', 'type' => 'integer'];
    $data[]                                  = ['name' => 'table', 'type' => 'string'];
    $xsd['response'][$i]['items']['data']    = $data;
    $xsd['response'][$i]['items']['objname'] = 'items';

    return $xsd;
}

function tablesforretrieve_wsdl()
{
}

function tablesforretrieve_wsdl_service()
{
}

// Define the method as a PHP function
/**
 * @param $var
 * @return array|bool|void
 */
function tablesforretrieve($var)
{
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
    global $xoopsDB;
    $sql = 'SELECT * FROM ' . $xoopsDB->prefix('json_tables') . ' WHERE allowretrieve = 1 AND visible = 1';
    $ret = $xoopsDB->query($sql);
    $rtn = [];
    while (false !== ($row = $xoopsDB->fetchArray($ret))) {
        $t++;
        $rtn[$t] = [
            'id'    => $row['tbl_id'],
            'table' => $row['tablename']
        ];
    }

//    global $xoopsModuleConfig;
    if (1 == $helper->getConfig('site_user_auth')) {
        if (!validateuser($var['username'], $var['password'])) {
            return false;
        }
    }
    return $rtn;
}
?>
