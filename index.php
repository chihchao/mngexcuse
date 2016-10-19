<?php
//include
include_once('include.php');
include('function/excuse.php');
include('function/phase.php');

//main
$uid = is_object($xoopsUser) ? $xoopsUser -> getVar('uid') : 0;
//authority
if (!CheckAuthority('excuse_info')) redirect_header(XOOPS_URL, 5, _MD_MEXCS_NOAUTHORITY);
//set template values
$tplvar = array();
$tplvar['page'] = array(
	'module_name' => $xoopsModule -> getVar('name'),
	'main_tpl' => 'index',
);
$tplvar['user'] = array(
	'uid' => $uid,
	'uname' => XoopsUser::getUnameFromId($uid,$xoopsModuleConfig['user_name']),
	'excuse_user' => CheckAuthority('excuse_user'),
	'personnel' => CheckAuthority('personnel'),
	'substitute_mng' => CheckAuthority('substitute_mng'),
);
$tplvar['page']['title'] = _MD_MEXCS_HEAD_HOMEPAGE;

$excuses['progress'] = GetProgressExcuses();
foreach ($excuses['progress'] as $key => $val) {
	$excuses['progress'][$key]['phases'] = GetPhases($excuses['progress'][$key]['id']);
	foreach ($excuses['progress'][$key]['phases'] as $k => $v) {
		$excuses['progress'][$key]['phases'][$k]['checker_uname'] = XoopsUser::getUnameFromId($excuses['progress'][$key]['phases'][$k]['checker'],$xoopsModuleConfig['user_name']);
		$excuses['progress'][$key]['phases'][$k]['state_string'] = get_phase_state_string($excuses['progress'][$key]['phases'][$k]['state']);
	}
}

$excuses['check'] = GetCheckExcuse();
foreach ($excuses['check'] as $key => $val) $excuses['check'][$key]['uname'] = XoopsUser::getUnameFromId($excuses['check'][$key]['uid'],$xoopsModuleConfig['user_name']);
$tplvar['excuses'] = $excuses;

//template
$xoopsOption['template_main'] = 'mngexcuse.htm';
include(XOOPS_ROOT_PATH . '/header.php');
$xoopsTpl -> assign('xoops_module_header', $xoops_module_header);
$xoopsTpl -> assign('tplvar', $tplvar);
include(XOOPS_ROOT_PATH . '/footer.php');
?>