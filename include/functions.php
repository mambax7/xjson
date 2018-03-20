<?php
/**
 * @param $object
 * @return mixed
 */

use XoopsModules\Xjson;

/**
 * @param $object
 * @return mixed
 */
function object2array($object)
{
    if (is_object($object)) {
        foreach ($object as $key => $value) {
            if (is_object($value)) {
                $array[$key] = object2array($value);
            } else {
                $array[$key] = $value;
            }
        }
    } else {
        $array = $object;
    }

    return $array;
}

    function footer_adminMenu()
    {
        echo '</div></td></tr>';
        echo '</table>';
    }
