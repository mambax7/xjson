<?php

use XoopsModules\Xjson;


/**
 * @return array
 */
function update_xsd()
{
    $xsd          = [];
    $i            = 0;
    $data_a       = [];
    $data_a[$i]   = ['name' => 'username', 'type' => 'string'];
    $data_a[$i++] = ['name' => 'password', 'type' => 'string'];
    $data_a[$i++] = ['name' => 'tablename', 'type' => 'string'];
    $data         = [];
    $data[]       = ['name' => 'field', 'type' => 'string'];
    $data[]       = ['name' => 'value', 'type' => 'string'];
    $i++;
    $data_a[$i]['items']['data']            = $data;
    $data_a[$i]['items']['objname']         = 'data';
    $i                                      = 0;
    $xsd['request'][$i]['items']['data']    = $data;
    $xsd['request'][$i]['items']['objname'] = 'var';
    $xsd['response'][]                      = ['name' => 'result', 'type' => 'double'];

    return $xsd;
}

function update_wsdl()
{
}

function update_wsdl_service()
{
}

// Define the method as a PHP function
/**
 * @param $var
 * @return array|bool|\mysqli_result
 */
function update($var)
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
    if (strlen($var['tablename']) > 0) {
        $tbl_id = get_tableid($var['tablename']);
    } elseif ($var['id'] > 0) {
        $tbl_id = $var['id'];
    } else {
        return ['ErrNum' => 2, 'ErrDesc' => 'Table Name or Table ID not specified'];
    }

    if (!validate($tbl_id, $var['data'], 'allowupdate')) {
        return ['ErrNum' => 5, 'ErrDesc' => 'Not all fields are allowed update'];
    } else {
        $sql = 'UPDATE ' . $xoopsDB->prefix(get_tablename($tbl_id)) . ' SET ';
        foreach ($var['data'] as $data) {
            if (!is_fieldkey($data['field'], $tbl_id)) {
                $sql_b .= '`' . $data['field'] . "` = '" . addslashes($data['value']) . "',";
            } else {
                if (strpos(' ' . $data['value'], '%') > 0 || strpos(' ' . $data['value'], '_') > 0) {
                    return ['ErrNum' => 7, 'ErrDesc' => 'Wildcard not accepted'];
                }
                if (strpos(' ' . strtolower($data['value']), 'union') > 0) {
                    return ['ErrNum' => 8, 'ErrDesc' => 'Union not accepted'];
                }
                $sql_c .= ' WHERE `' . $data['field'] . "` = '" . addslashes($data['value']) . "'";
            }
        }
        if (0 == strlen($sql_c)) {
            return ['ErrNum' => 6, 'ErrDesc' => 'No primary key set'];
        }

//        global $xoopsModuleConfig;
        if (1 == $helper->getConfig('site_user_auth')) {
            if (!validateuser($var['username'], $var['password'])) {
                return false;
            }
        }
        return $xoopsDB->queryF($sql . substr($sql_b, 0, strlen($sql_b) - 1) . $sql_c);
    }
}
?>
