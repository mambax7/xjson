<?php
/**
 * $Id$
 * Module: WF-Downloads
 * Version: v2.0.5a
 * Release Date: 26 july 2004
 * Author: WF-Sections
 * Licence: GNU
 */

use XoopsModules\Xjson;

error_reporting(E_ALL);
//include __DIR__ . '/../../../mainfile.php';
//include __DIR__ . '/../../../include/cp_header.php';
//include __DIR__ . '/../include/functions.php';
require_once __DIR__ . '/../../../include/cp_header.php';

include_once XOOPS_ROOT_PATH . '/class/xoopstree.php';
include_once XOOPS_ROOT_PATH . '/class/xoopslists.php';
include_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';

/** @var Xjson\Helper $helper */
$helper = Xjson\Helper::getInstance();

/** @var Xmf\Module\Admin $adminObject */
$adminObject = \Xmf\Module\Admin::getInstance();

if (is_object($xoopsUser)) {
    $xoopsModule = XoopsModule::getByDirname('xjson');
    if (!$xoopsUser->isAdmin($xoopsModule->mid())) {
        redirect_header(XOOPS_URL . '/', 3, _NOPERM);
        exit();
    }
} else {
    redirect_header(XOOPS_URL . '/', 1, _NOPERM);
    exit();
}
$myts = \MyTextSanitizer::getInstance();

// Load language files
$helper->loadLanguage('admin');
$helper->loadLanguage('modinfo');
$helper->loadLanguage('main');

error_reporting(E_ALL);
