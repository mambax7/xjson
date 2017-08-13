<?php
/**
 * @return array
 */
function tablesforpost_xsd()
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

function tablesforpost_wsdl()
{
}

function tablesforpost_wsdl_service()
{
}

// Define the method as a PHP function
/**
 * @param $var
 * @return array|bool|void
 */
function tablesforpost($var)
{
    global $xoopsModuleConfig;
    if ($xoopsModuleConfig['site_user_auth'] == 1) {
        if ($ret = check_for_lock(basename(__FILE__), $username, $password)) {
            return $ret;
        }
        if (!checkright(basename(__FILE__), $username, $password)) {
            mark_for_lock(basename(__FILE__), $username, $password);
            return ['ErrNum' => 9, 'ErrDesc' => 'No Permission for plug-in'];
        }
    }
    global $xoopsDB;
    $sql = 'SELECT * FROM ' . $xoopsDB->prefix('json_tables') . ' WHERE allowpost = 1 AND visible = 1';
    $ret = $xoopsDB->query($sql);
    $rtn = [];
    while ($row = $xoopsDB->fetchArray($ret)) {
        $t++;
        $rtn[$t] = [
            'id'    => $row['tbl_id'],
            'table' => $row['tablename']
        ];
    }

    global $xoopsModuleConfig;
    if ($xoopsModuleConfig['site_user_auth'] == 1) {
        if (!validateuser($var['username'], $var['password'])) {
            return false;
        }
    }
    return $rtn;
}
