<?php
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 xoops.org                           //
//                       <https://xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
// ------------------------------------------------------------------------- //

require_once __DIR__ . '/preloads/autoloader.php';

$modversion['version']       = 1.54;
$modversion['module_status'] = 'Beta 1';
$modversion['release_date']  = '2018/03/12';
$modversion['name']          = 'X-JSON API';
$modversion['releasedate']   = 'Thusday: 21 April 2011';
$modversion['status']        = 'Stable';
$modversion['author']        = 'Chronolabs Australia';
$modversion['credits']       = 'Simon Roberts';
$modversion['teammembers']   = 'Wishcraft';
$modversion['license']       = 'GPL';
$modversion['official']      = 1;
$modversion['description']   = 'JSON API Server to exchange JSON Packages with external server.';
$modversion['help']          = '';
$modversion['image']         = 'images/xjson_slogo.png';
$modversion['dirname']       = 'xjson';

// All tables should not have any prefix!
$modversion['sqlfile']['mysql'] = 'sql/mysql.sql';

$modversion['author_realname']        = 'Simon Roberts';
$modversion['author_website_url']     = 'http://www.chronolabs.org.au';
$modversion['author_website_name']    = 'Chronolabs International';
$modversion['author_email']           = 'simon@chronolabs.org.au';
$modversion['demo_site_url']          = '';
$modversion['demo_site_name']         = '';
$modversion['support_site_url']       = 'http://www.chronolabs.org.au/forums/x-json/0,10,0,0,100,0,DESC,0';
$modversion['support_site_name']      = 'x-json';
$modversion['submit_bug']             = 'http://www.chronolabs.org.au/forums/x-json/0,10,0,0,100,0,DESC,0';
$modversion['submit_feature']         = 'http://www.chronolabs.org.au/forums/x-json/0,10,0,0,100,0,DESC,0';
$modversion['usenet_group']           = 'sci.chronolabs';
$modversion['maillist_announcements'] = '';
$modversion['maillist_bugs']          = '';
$modversion['maillist_features']      = '';

// Tables created by sql file (without prefix!)
$modversion['tables'][0] = 'json_tables';
$modversion['tables'][1] = 'json_fields';
$modversion['tables'][2] = 'json_plugins';

// Admin things
$modversion['hasAdmin']    = 1;
$modversion['system_menu'] = 1;
$modversion['adminindex']  = 'admin/index.php';
$modversion['adminmenu']   = 'admin/menu.php';

// Menu
$modversion['hasMain'] = 1;

// Smarty
$modversion['use_smarty'] = 0;

$modversion['config'][] = [
    'name'        => 'site_user_auth',
    'title'       => '_XJSON_USERAUTH',
    'description' => '_XJSON_USERAUTHDESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
];
$modversion['config'][] = [
    'name'        => 'lock_seconds',
    'title'       => '_XJSON_SECONDS',
    'description' => '_XJSON_SECONDS_DESC',
    'formtype'    => 'select',
    'valuetype'   => 'int',
    'default'     => 180,
    'options'     => [
        _XJSON_SECONDS_3600 => 3600,
        _XJSON_SECONDS_1800 => 1800,
        _XJSON_SECONDS_1200 => 1200,
        _XJSON_SECONDS_600  => 600,
        _XJSON_SECONDS_300  => 300,
        _XJSON_SECONDS_180  => 180,
        _XJSON_SECONDS_60   => 60,
        _XJSON_SECONDS_30   => 30
    ],
];

$modversion['config'][] = [
    'name'        => 'function_cache',
    'title'       => '_XJSON_FUNCTIONCACHE',
    'description' => '_XJSON_FUNCTIONCACHE_DESC',
    'formtype'    => 'select',
    'valuetype'   => 'int',
    'default'     => 180,
    'options'     => [
        _XJSON_SECONDS_3600 => 3600,
        _XJSON_SECONDS_1800 => 1800,
        _XJSON_SECONDS_1200 => 1200,
        _XJSON_SECONDS_600  => 600,
        _XJSON_SECONDS_300  => 300,
        _XJSON_SECONDS_180  => 180,
        _XJSON_SECONDS_60   => 60,
        _XJSON_SECONDS_30   => 30
    ],
];

mt_srand(((float)('0' . substr(microtime(), strpos(microtime(), ' ') + 1, strlen(microtime()) - strpos(microtime(), ' ') + 1))) * mt_rand(30, 99999));
mt_srand(((float)('0' . substr(microtime(), strpos(microtime(), ' ') + 1, strlen(microtime()) - strpos(microtime(), ' ') + 1))) * mt_rand(30, 99999));

$modversion['config'][] = [
    'name'        => 'lock_random_seed',
    'title'       => '_XJSON_USERANDOMLOCK',
    'description' => '_XJSON_USERANDOMLOCK_DESC',
    'formtype'    => 'text',
    'valuetype'   => 'int',
    'default'     => mt_rand(30, 170),
];
$modversion['config'][] = [
    'name'        => 'cache_seconds',
    'title'       => '_XJSON_SECONDSCACHE',
    'description' => '_XJSON_SECONDSCACHE_DESC',
    'formtype'    => 'select',
    'valuetype'   => 'int',
    'default'     => 3600,
    'options'     => [
        _XJSON_SECONDS_3600 => 3600,
        _XJSON_SECONDS_1800 => 1800,
        _XJSON_SECONDS_1200 => 1200,
        _XJSON_SECONDS_600  => 600,
        _XJSON_SECONDS_300  => 300,
        _XJSON_SECONDS_180  => 180,
        _XJSON_SECONDS_60   => 60,
        _XJSON_SECONDS_30   => 30
    ],
];
