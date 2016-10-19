<?php
//include
include_once('include.php');
include_once('function/excuse.php');
include_once('function/substitute.php');
include_once('function/yearbgn.php');

//parameter
$oprt = (empty($_GET['oprt']) || $_GET['oprt'] != 'cmt') ? 'lst' : 'cmt';
$user_id = empty($_GET['user_id']) ? 0 : intval($_GET['user_id']);
$yearbgn = empty($_GET['yearbgn']) ? GetDefaultYearbgn() : intval($_GET['yearbgn']);
$year = empty($_GET['year']) ? 0 : intval($_GET['year']);
$page = empty($_GET['page']) ? 0 : intval($_GET['page']);
$type = (empty($_GET['type']) || !in_array($_GET['type'], explode('|', $xoopsModuleConfig['excuse_type']))) ? '' : $_GET['type'];
$excs_id = empty($_GET['excs_id']) ? 0 : intval($_GET['excs_id']);
$submitted = (empty($_POST['submitted'])) ? false : true;

//main
$uid = is_object($xoopsUser) ? $xoopsUser -> getVar('uid') : 0;
//authority
if (!CheckAuthority('substitute_mng')) redirect_header(XOOPS_URL, 5, _MD_MEXCS_NOAUTHORITY);
//set template values
$tplvar = array();
$tplvar['page'] = array(
	'module_name' => $xoopsModule -> getVar('name'),
	'main_tpl' => 'substitute',
	'oprt' => $oprt,
);
$tplvar['user'] = array(
	'uid' => $uid,
	'uname' => XoopsUser::getUnameFromId($uid,$xoopsModuleConfig['user_name']),
	'excuse_user' => CheckAuthority('excuse_user'),
	'personnel' => CheckAuthority('personnel'),
	'substitute_mng' => CheckAuthority('substitute_mng'),
);
$tplvar['page']['title'] =  _MD_MEXCS_HEAD_SUBSTITUTEMNG;

$tplvar['page']['yearbgns'] = ListYearbgns();
$tplvar['page']['yearbgn'] = $yearbgn;

switch ($oprt) {
	case 'cmt':
		if (!$excuse = GetExcuse($excs_id)) redirect_header('index.php', 5, get_error_message());
		if ($submitted) { EditSubstituteComment($excs_id) ?  redirect_header('excuse.php?oprt=viw&excs_id=' . $excs_id, 5, _MD_MEXCS_SUBSTITUTE_CMTSUCCESS) : redirect_header('substitute.php.php?oprt=cmt&excs_id=' . $excs_id, 5, _MD_MEXCS_SUBSTITUTE_CMTFAIL . get_error_message()); }
		$substitute  = GetSubstitute($excs_id);
		$tplvar['excuse'] = $excuse;
		$tplvar['excuse']['substitute'] = $substitute;
		$tplvar['excuse']['uname'] = XoopsUser::getUnameFromId($excuse['uid'],$xoopsModuleConfig['user_name']);
	break;
	default:
		$excuses = GetSubstitutesJoinExcuses($user_id, $year, $page, $type, $yearbgn);
		foreach ($excuses as $key => $val) {
			$excuses[$key]['state_string'] = get_excuse_state_string($excuses[$key]['state']);
			$excuses[$key]['uname'] = XoopsUser::getUnameFromId($excuses[$key]['uid'],$xoopsModuleConfig['user_name']);
		}
		$excuse_years = get_excuse_years($yearbgn);
		$excuse_users = ListUidByGid(GetYearbgnGroupids($yearbgn), true);
		foreach ($excuse_users as $key => $val) $excuse_users[$key] = XoopsUser::getUnameFromId($key,$xoopsModuleConfig['user_name']);
		$tplvar['page']['page'] = $page;
		$tplvar['page']['pages'] = ceil(CountSubstitutesJoinExcuses($user_id, $year, $type, $yearbgn) / $xoopsModuleConfig['per_page']);
		$tplvar['page']['year'] = $year;
		$tplvar['page']['year_string'] = $excuse_years[$year][0];
		$tplvar['page']['excuse_years'] = $excuse_years;
		$tplvar['page']['type'] = $type;
		$tplvar['page']['type_url'] = urlencode($type);
		$tplvar['page']['excuse_types'] = explode('|', $xoopsModuleConfig['excuse_type']);
		$tplvar['page']['user_id'] = $user_id;
		$tplvar['page']['user_name'] = XoopsUser::getUnameFromId($user_id,$xoopsModuleConfig['user_name']);
		$tplvar['page']['excuse_users'] = $excuse_users;

		$tplvar['excuses'] = $excuses;
	break;
}

//template
$xoopsOption['template_main'] = 'mngexcuse.htm';
include(XOOPS_ROOT_PATH . '/header.php');
$xoopsTpl -> assign('xoops_module_header', $xoops_module_header);
$xoopsTpl -> assign('tplvar', $tplvar);
include(XOOPS_ROOT_PATH . '/footer.php');
?>