<?php

use XoopsModules\Xjson;
/** @var Xjson\Helper $helper */
$helper = Xjson\Helper::getInstance();

/**
 * @return array
 */
function story_xsd()
{
    $xsd     = [];
    $i       = 0;
    $data    = [];
    $data[]  = ['name' => 'username', 'type' => 'string'];
    $data[]  = ['name' => 'password', 'type' => 'string'];
    $datab   = [];
    $datab[] = ['name' => 'topicid', 'type' => 'integer'];
    $datab[] = ['name' => 'uid', 'type' => 'integer'];
    $datab[] = ['name' => 'title', 'type' => 'string'];
    $datab[] = ['name' => 'hometext', 'type' => 'string'];
    $datab[] = ['name' => 'bodytext', 'type' => 'string'];
    $datab[] = ['name' => 'created', 'type' => 'integer'];
    $datab[] = ['name' => 'published', 'type' => 'integer'];
    $datab[] = ['name' => 'expires', 'type' => 'integer'];
    $datab[] = ['name' => 'robot-purpose', 'type' => 'string'];
    $datab[] = ['name' => 'nohtml', 'type' => 'integer'];
    $datab[] = ['name' => 'nosmiley', 'type' => 'integer'];
    $datab[] = ['name' => 'approved', 'type' => 'integer'];
    $datab[] = ['name' => 'description', 'type' => 'string'];
    $datab[] = ['name' => 'keywords', 'type' => 'string'];
    $datab[] = ['name' => 'picture', 'type' => 'string'];
    $datab[] = ['name' => 'tags', 'type' => 'string'];
    $data[]  = ['items' => ['data' => $datab, 'objname' => 'story']];

    $xsd['request'][$i]['items']['data']    = $data;
    $xsd['request'][$i]['items']['objname'] = 'var';

    $xsd['response'][] = ['name' => 'ErrDesc', 'type' => 'string'];
    $xsd['response'][] = ['name' => 'made', 'type' => 'integer'];
    $xsd['response'][] = ['name' => 'stored', 'type' => 'integer'];

    return $xsd;
}

function story_wsdl()
{
}

function story_wsdl_service()
{
}

// Define the method as a PHP function
/**
 * @param $username
 * @param $password
 * @param $story
 * @return array
 */
function story($username, $password, $story)
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

    define('NW_MODULE_PATH', $GLOBALS['xoops']->path('/modules/xnews/'));
    require_once $GLOBALS['xoops']->path('/modules/xnews/class/class.newsstory.php');

    $newstory = new NewsStory(0);

    $newstory->setUid($story['uid']);

    $newstory->setTitle($story['title']);

    $newstory->setHometext(urldecode($story['hometext']));

    $newstory->setBodytext(urldecode($story['bodytext']));

    $newstory->setTopicId((int)$story['topicid']);

    $newstory->setNohtml($story['nohtml']);

    $nosmiley = isset($story['nosmiley']) ? (int)$story['nosmiley'] : 0;

    $newstory->setNosmiley($nosmiley);

    $newstory->setPublished($story['published']);

    $newstory->setExpired($story['expired']);

    $newstory->Setdescription(urldecode($story['description']));

    $newstory->Setkeywords(urldecode($story['keywords']));

    $newstory->setApproved($story['approve']);

    $newstory->Settags($story['tags']);

    if ($result = $newstory->store()) {
        return ['stored' => true, 'made' => time()];
    }

    return ['stored' => false, 'made' => time()];
}
?>
