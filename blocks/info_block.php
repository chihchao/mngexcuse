<?php
//include

//include_once(XOOPS_ROOT_PATH . '/mainfile.php');
//include_once(XOOPS_ROOT_PATH . '/modules/mngexcuse/function/function.php');
include_once(XOOPS_ROOT_PATH . '/modules/mngexcuse/function/user.php');
include_once(XOOPS_ROOT_PATH . '/modules/mngexcuse/function/excuse.php');
include_once(XOOPS_ROOT_PATH . '/modules/mngexcuse/function/phase.php');


//main
function info_block()
{
	global $xoopsUser;
	$uid = is_object($xoopsUser) ? $xoopsUser -> getVar('uid') : 0;
	$excuses['string'] = array(
		'uid' => $uid,
		'uname' => XoopsUser::getUnameFromId($uid,$xoopsModuleConfig['user_name']),
		'url' => XOOPS_URL . '/modules/mngexcuse/',
		'information' => _MD_MEXCS_BLOCK_INFO,
		'pgsexcuse' => _MD_MEXCS_BLOCK_PGSEXCUSE,
		'chkexcuse' => _MD_MEXCS_BLOCK_CHKEXCUSE,
		'empty' => _MD_MEXCS_BLOCK_EMPTY,
	);
	$excuses['progress'] = GetProgressExcuses();
	foreach ($excuses['progress'] as $key => $val) {
		$excuses['progress'][$key]['phases'] = GetPhases($excuses['progress'][$key]['id']);
		$ttl = count($excuses['progress'][$key]['phases']);
		$pgs = 0;
		foreach ($excuses['progress'][$key]['phases'] as $k => $v) if ($excuses['progress'][$key]['phases'][$k]['state'] == 3) $pgs ++;
		$excuses['progress'][$key]['phases'] = array($pgs, $ttl);
	}
	$excuses['check'] = GetCheckExcuse();
	foreach ($excuses['check'] as $key => $val) $excuses['check'][$key]['uname'] = XoopsUser::getUnameFromId($excuses['check'][$key]['uid'],$xoopsModuleConfig['user_name']);

	return $excuses;
}
?>