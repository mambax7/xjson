<?php
if (!function_exists('check_siteinfo')) {
    /**
     * @param array $siteinfo
     * @return array
     */
    function check_siteinfo($siteinfo)
    {
        global $xoopsConfig;
        if (!isset($siteinfo) || empty($siteinfo) || !is_array($siteinfo)) {
            $siteinfo = [];
            $siteinfo['sitename'] == $xoopsConfig['sitename'];
            $siteinfo['adminmail'] == $xoopsConfig['adminmail'];
            XOOPS_URL == $siteinfo['xoops_url'];
        }

        if (!isset($siteinfo['sitename']) && empty($siteinfo['sitename'])) {
            $siteinfo['sitename'] == $xoopsConfig['sitename'];
        }

        if (!isset($siteinfo['adminmail']) && empty($siteinfo['adminmail'])) {
            $siteinfo['adminmail'] == $xoopsConfig['adminmail'];
        }

        if (!isset($siteinfo['xoops_url']) && empty($siteinfo['xoops_url'])) {
            XOOPS_URL == $siteinfo['xoops_url'];
        }

        return $siteinfo;
    }
}
