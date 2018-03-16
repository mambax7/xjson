<?php
/**
 * @return array
 */
function tableschemer_xsd()
{
    $xsd                                    = [];
    $i                                      = 0;
    $data                                   = [];
    $data[]                                 = ['name' => 'username', 'type' => 'string'];
    $data[]                                 = ['name' => 'password', 'type' => 'string'];
    $data[]                                 = ['name' => 'update', 'type' => 'integer'];
    $data[]                                 = ['name' => 'post', 'type' => 'integer'];
    $data[]                                 = ['name' => 'retrieve', 'type' => 'integer'];
    $data[]                                 = ['name' => 'tablename', 'type' => 'string'];
    $xsd['request'][$i]['items']['data']    = $data;
    $xsd['request'][$i]['items']['objname'] = 'var';

    $data                                    = [];
    $data[]                                  = ['name' => 'table_id', 'type' => 'integer'];
    $data[]                                  = ['name' => 'field', 'type' => 'string'];
    $data[]                                  = ['name' => 'allowpost', 'type' => 'integer'];
    $data[]                                  = ['name' => 'allowretrieve', 'type' => 'integer'];
    $data[]                                  = ['name' => 'allowupdate', 'type' => 'integer'];
    $data[]                                  = ['name' => 'string', 'type' => 'integer'];
    $data[]                                  = ['name' => 'int', 'type' => 'integer'];
    $data[]                                  = ['name' => 'float', 'type' => 'integer'];
    $data[]                                  = ['name' => 'text', 'type' => 'integer'];
    $data[]                                  = ['name' => 'other', 'type' => 'integer'];
    $data[]                                  = ['name' => 'key', 'type' => 'integer'];
    $xsd['response'][$i]['items']['data']    = $data;
    $xsd['response'][$i]['items']['objname'] = 'items';
    return $xsd;
}

function tableschemer_wsdl()
{
}

function tableschemer_wsdl_service()
{
}

// Define the method as a PHP function
/**
 * @param $var
 * @return array|bool
 */
function tableschemer($var)
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
    $sql = 'SELECT * FROM ' . $xoopsDB->prefix('json_fields') . ' WHERE visible = 1 ';
    if ($var['post'] = 1) {
        $sql .= 'and allowpost = 1 ';
    } elseif ($var['retrieve'] = 1) {
        $sql .= 'and allowretrieve = 1 ';
    } elseif ($var['update'] = 1) {
        $sql .= 'and allowupdate = 1 ';
    }
    if (strlen($var['tablename']) > 0) {
        $sql .= 'and tbl_id = ' . get_tableid($var['tablename']);
    } elseif ($var['id'] > 0) {
        $sql .= 'and tbl_id = ' . $var['id'];
    } else {
        return ['ErrNum' => 2, 'ErrDesc' => 'Table Name or Table ID not specified'];
    }

    $ret = $xoopsDB->query($sql);
    $rtn = [];
    while (false !== ($row = $xoopsDB->fetchArray($ret))) {
        $rtn[] = [
            'table_id'      => $row['tbl_id'],
            'field'         => $row['fieldname'],
            'allowpost'     => $row['allowpost'],
            'allowretrieve' => $row['allowretrieve'],
            'allowupdate'   => $row['allowupdate'],
            'string'        => $row['string'],
            'int'           => $row['int'],
            'float'         => $row['float'],
            'text'          => $row['text'],
            'key'           => $row['key'],
            'other'         => $row['other']
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
