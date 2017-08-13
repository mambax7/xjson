<?php
/**
 * @return array
 */
function retrievecrc_xsd()
{
    $xsd                                    = [];
    $i                                      = 0;
    $data                                   = [];
    $data[]                                 = ['name' => 'username', 'type' => 'string'];
    $data[]                                 = ['name' => 'password', 'type' => 'string'];
    $data[]                                 = ['name' => 'tablename', 'type' => 'string'];
    $data[]                                 = ['name' => 'clause', 'type' => 'string'];
    $xsd['request'][$i]['items']['data']    = $data;
    $xsd['request'][$i]['items']['objname'] = 'var';

    $i                     = 0;
    $xsd['response'][$i++] = ['name' => 'id', 'type' => 'double'];
    $xsd['response'][$i++] = ['name' => 'crc', 'type' => 'string'];
    $data_b                = [];
    $data_b[]              = ['name' => 'field', 'type' => 'string'];
    $data_b[]              = ['name' => 'crc', 'type' => 'string'];
    $data[]                = ['items' => ['data' => $data_b, 'objname' => 'data']];
    $i++;
    $xsd['response'][$i]['items']['data']    = $data;
    $xsd['response'][$i]['items']['objname'] = 'result';
    return $xsd;
}

function retrievecrc_wsdl()
{
}

function retrievecrc_wsdl_service()
{
}

// Define the method as a PHP function
/**
 * @param $var
 * @return array|bool
 */
function retrievecrc($var)
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
    $sql = 'SELECT * FROM ' . $xoopsDB->prefix('json_fields') . ' WHERE `crc` = 1 ';
    if (strlen($var['tablename']) > 0) {
        $sql    .= 'and tbl_id = ' . get_tableid($var['tablename']);
        $tbl_id = get_tableid($var['tablename']);
    } elseif ($var['id'] > 0) {
        $sql    .= 'and tbl_id = ' . $var['id'];
        $tbl_id = $var['id'];
    } else {
        return ['ErrNum' => 2, 'ErrDesc' => 'Table Name or Table ID not specified'];
    }

    $ret = $xoopsDB->query($sql);
    $sql = 'SELECT ';
    $tmp = [];
    while ($row = $xoopsDB->fetchArray($ret)) {
        $sql   .= '`' . $row['fieldname'] . '`';
        $tmp[] = $row['fieldname'];
        $t++;
        if ($t < $xoopsDB->getRowsNum($ret)) {
            $sql .= ', ';
        }
    }
    if (strlen($var['tablename']) > 0) {
        $sql .= ' FROM ' . $xoopsDB->prefix($var['tablename']);
    } elseif ($var['id'] > 0) {
        $sql .= ' FROM ' . $xoopsDB->prefix(get_tablename($var['id']));
    }
    if ($var['clause'] == 1) {
        if (strpos(' ' . strtolower($var['clause']), 'union') > 0) {
            return ['ErrNum' => 8, 'ErrDesc' => 'Union not accepted'];
        }
        $sql .= ' WHERE `' . get_fieldname($var['fieldid'], $tbl_id) . '` ' . $var['clause'];
    }

    $ret = $xoopsDB->query($sql);
    $rtn = [];

    while ($row = $xoopsDB->fetchArray($ret)) {
        $id++;
        $tmp_b = [];
        $crc   = '';
        foreach ($tmp as $result) {
            $tmp_b[] = ['field' => $result, 'crc' => md5($row[$result])];
            $crc     = md5($crc . $row[$result]);
        }
        $rtn[] = [
            'id'   => $id,
            'crc'  => $crc,
            'data' => $tmp_b
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
