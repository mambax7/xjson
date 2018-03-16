<?php
/**
 * @return array
 */
function tablesforupdate_xsd()
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

function tablesforupdate_wsdl()
{
}

function tablesforupdate_wsdl_service()
{
}

/**
 * @param $var
 * @return array|bool|void
 */
function tablesforupdate($var)
{
    global $xoopsModuleConfig;
    if (1 == $xoopsModuleConfig['site_user_auth']) {
        if ($ret = check_for_lock(basename(__FILE__), $username, $password)) {
            return $ret;
        }
        if (!checkright(basename(__FILE__), $username, $password)) {
            mark_for_lock(basename(__FILE__), $username, $password);
            return ['ErrNum' => 9, 'ErrDesc' => 'No Permission for plug-in'];
        }
    }
    global $xoopsDB;
    $sql = 'SELECT * FROM ' . $xoopsDB->prefix('json_tables') . ' WHERE allowupdate = 1 AND visible = 1';
    $ret = $xoopsDB->query($sql);
    $rtn = [];
    while (false !== ($row = $xoopsDB->fetchArray($ret))) {
        $t++;
        $rtn[$t] = [
            'id'    => $row['tbl_id'],
            'table' => $row['tablename']
        ];
    }

    global $xoopsModuleConfig;
    if (1 == $xoopsModuleConfig['site_user_auth']) {
        if (!validateuser($var['username'], $var['password'])) {
            return false;
        }
    }
    return $rtn;
}
?>
