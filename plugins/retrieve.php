<?php

use XoopsModules\Xjson;

/**
 * @return array
 */
function retrieve_xsd()
{
    $xsd     = [];
    $i       = 0;
    $data    = [];
    $data[]  = ['name' => 'username', 'type' => 'string'];
    $data[]  = ['name' => 'password', 'type' => 'string'];
    $data[]  = ['name' => 'tablename', 'type' => 'string'];
    $data[]  = ['name' => 'clause', 'type' => 'string'];
    $datab   = [];
    $datab[] = ['name' => 'field', 'type' => 'string'];
    $data[]  = ['items' => ['data' => $datab, 'objname' => 'data']];

    $xsd['request'][$i]['items']['data']    = $data;
    $xsd['request'][$i]['items']['objname'] = 'var';

    $xsd['response'][] = ['name' => 'total_records', 'type' => 'double'];
    $data              = [];
    $data[]            = ['name' => 'field', 'type' => 'string'];
    $data[]            = ['name' => 'value', 'type' => 'string'];
    $i++;
    $xsd['response'][$i]['items']['data']    = $data;
    $xsd['response'][$i]['items']['objname'] = 'data';
    return $xsd;
}

function retrieve_wsdl()
{
}

function retrieve_wsdl_service()
{
}

// Define the method as a PHP function
/**
 * @param $var
 * @return array|bool
 */
function retrieve($var)
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

    if (!validate($tbl_id, $var['data'], 'allowretrieve')) {
        return ['ErrNum' => 4, 'ErrDesc' => 'Not all fields are allowed retrieve'];
    } else {
        $sql = 'SELECT ';
        foreach ($var['data'] as $data) {
            if ('*' === $data['field']) {
                return ['ErrNum' => 7, 'ErrDesc' => 'Wildcard not accepted'];
            }

            $sql_b .= '`' . $data['field'] . '`,';
        }
        if (strlen($var['clause']) > 0) {
            if (strpos(' ' . strtolower($var['clause']), 'union') > 0) {
                return ['ErrNum' => 8, 'ErrDesc' => 'Union not accepted'];
            }
            $sql_c .= 'WHERE ' . $var['clause'] . '';
        }

//        global $xoopsModuleConfig;
        if (1 == $helper->getConfig('site_user_auth')) {
            if (!validateuser($var['username'], $var['password'])) {
                return false;
            }
        }
        //echo $sql." ".substr($sql_b,0,strlen($str_b)-1)." FROM ".$xoopsDB->prefix(get_tablename($tbl_id))." ".$sql_c;

        $rt = $xoopsDB->queryF($sql . ' ' . substr($sql_b, 0, strlen($str_b) - 1) . ' FROM ' . $xoopsDB->prefix(get_tablename($tbl_id)) . ' ' . $sql_c);

        if (!$xoopsDB->getRowsNum($rt)) {
            return ['ErrNum' => 3, 'ErrDesc' => 'No Records Returned from Query'];
        } else {
            $rtn = [];
            while (false !== ($row = $xoopsDB->fetchArray($rt))) {
                $rdata = [];
                foreach ($var['data'] as $data) {
                    $rdata[] = ['fieldname' => $data['field'], 'value' => $row[$data['field']]];
                }
                $rtn[] = $rdata;
            }
        }

        return ['total_records' => $xoopsDB->getRowsNum($rt), 'items' => $rtn];
    }
}
?>
