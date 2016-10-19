<?php
//include
include_once('include.php');
include_once('function/excuse.php');
include_once('function/leave.php');
include_once('function/yearbgn.php');

//parameter
$oprt = (empty($_GET['oprt']) || ($_GET['oprt'] != 'cnt' && $_GET['oprt'] != 'lev')) ? 'lst' : $_GET['oprt'];
$yearbgn = empty($_GET['yearbgn']) ? GetDefaultYearbgn() : intval($_GET['yearbgn']);
$user_id = empty($_GET['user_id']) ? 0 : intval($_GET['user_id']);
$year = empty($_GET['year']) ? (($_GET['year'] == null) ? 1 : 0) : intval($_GET['year']);
$page = empty($_GET['page']) ? 0 : intval($_GET['page']);
$type = (empty($_GET['type']) || !in_array($_GET['type'], explode('|', $xoopsModuleConfig['excuse_type']))) ? '' : $_GET['type'];
$submitted = (empty($_POST['submitted'])) ? false : true;

//main
$uid = is_object($xoopsUser) ? $xoopsUser -> getVar('uid') : 0;
//authority
if (!CheckAuthority('personnel')) redirect_header(XOOPS_URL, 5, _MD_MEXCS_NOAUTHORITY);
//set template values
$tplvar = array();
$tplvar['page'] = array(
	'module_name' => $xoopsModule -> getVar('name'),
	'main_tpl' => 'personnel',
	'oprt' => $oprt,
);
$tplvar['user'] = array(
	'uid' => $uid,
	'uname' => XoopsUser::getUnameFromId($uid,$xoopsModuleConfig['user_name']),
	'excuse_user' => CheckAuthority('excuse_user'),
	'personnel' => CheckAuthority('personnel'),
	'substitute_mng' => CheckAuthority('substitute_mng'),
);
$tplvar['page']['title'] =  _MD_MEXCS_HEAD_PERSONNEL;

$tplvar['page']['yearbgns'] = ListYearbgns();
$tplvar['page']['yearbgn'] = $yearbgn;

$excuse_years = get_excuse_years($yearbgn);
$tplvar['page']['excuse_years'] = $excuse_years;
$tplvar['page']['year'] = $year;
$tplvar['page']['year_string'] = $excuse_years[$year][0];
$tplvar['page']['excuse_types'] = explode('|', $xoopsModuleConfig['excuse_type']);
switch ($oprt) {
	case 'cnt':
		$counts = CountExcuseDateUsers($year, $yearbgn);
		$leaves = GetLeaves($year, $yearbgn);
		$users = ListUidByGid(GetYearbgnGroupids($yearbgn), true);
		foreach ($users as $key => $val) {
			$users[$key]['uname'] = XoopsUser::getUnameFromId($key,$xoopsModuleConfig['user_name']);
			foreach ($tplvar['page']['excuse_types'] as $v) {
				$users[$key]['counts'][$v][0] = empty($counts[$key][$v][0]) ? 0 : $counts[$key][$v][0];
				$users[$key]['counts'][$v][1] = empty($counts[$key][$v][1]) ? 0 : $counts[$key][$v][1];
				if (!empty($leaves[$key][$v][0]) || !empty($leaves[$key][$v][1])) {
					$tmp = get_count_leave($users[$key]['counts'][$v], $leaves[$key][$v]);
					$users[$key]['counts'][$v][2] = $tmp[0];
					$users[$key]['counts'][$v][3] = $tmp[1];
				}
			}
			$users[$key]['counts']['total'][0] = empty($counts[$key]['total'][0]) ? 0 : $counts[$key]['total'][0];
			$users[$key]['counts']['total'][1] = empty($counts[$key]['total'][1]) ? 0 : $counts[$key]['total'][1];
		}
		$tplvar['users'] = $users;
		$tplvar['page']['title'] = _MD_MEXCS_EXCUSE_STATISTICS;
	break;
	case 'lev':
		if (empty($user_id)) {
			$leaves = GetLeaves($year, $yearbgn);
			$users = ListUidByGid(GetYearbgnGroupids($yearbgn), true);
			foreach ($users as $key => $val) {
				$users[$key]['uname'] = XoopsUser::getUnameFromId($key,$xoopsModuleConfig['user_name']);
				foreach ($tplvar['page']['excuse_types'] as $v) {
					$users[$key]['leaves'][$v][0] = empty($leaves[$key][$v][0]) ? 0 : $leaves[$key][$v][0];
					$users[$key]['leaves'][$v][1] = empty($leaves[$key][$v][1]) ? 0 : $leaves[$key][$v][1];
				}
			}
			$tplvar['users'] = $users;
			$tplvar['page']['title'] = _MD_MEXCS_PERSONNEL_LEAVE_DAYS;
		} else {
			if ($submitted) { DealLeave($year, $user_id) ?  redirect_header('personnel.php?oprt=lev', 5, _MD_MEXCS_PERSONNEL_LEVSUCCESS) : redirect_header('personnel.php.php?oprt=lev&year=' . $year . '&user_id=' . $user_id, 5, _MD_MEXCS_PERSONNEL_LEVFAIL . get_error_message()); }
			$tplvar['page']['user_id'] = $user_id;
			$tplvar['page']['user_name'] = XoopsUser::getUnameFromId($user_id,$xoopsModuleConfig['user_name']);
			$leave = GetLeave($year, $user_id);
			$excuse_types = array();
			foreach ($tplvar['page']['excuse_types'] as $key => $val)  $excuse_types[$val] = empty($leave[$val]) ? array() : $leave[$val];
			$tplvar['excuse_types'] = $excuse_types;
			$tplvar['page']['title'] = _MD_MEXCS_PERSONNEL_LEAVE;
		}
	break;
	default:
		$excuses = GetExcusesPersonnel($user_id, $year, $page, $type, $yearbgn);
		foreach ($excuses as $key => $val) {
			$excuses[$key]['state_string'] = get_excuse_state_string($excuses[$key]['state']);
			$excuses[$key]['uname'] = XoopsUser::getUnameFromId($excuses[$key]['uid'],$xoopsModuleConfig['user_name']);
		}
		$excuse_users = ListUidByGid(GetYearbgnGroupids($yearbgn), true);
		foreach ($excuse_users as $key => $val) $excuse_users[$key] = XoopsUser::getUnameFromId($key,$xoopsModuleConfig['user_name']);
		$count = CountExcuseDate($user_id, $year, $yearbgn);
		if (!empty($user_id)) {
			$leave = GetLeave($year, $user_id);
			foreach ($count as $key => $val) {
				if (!empty($leave[$key][0]) || !empty($leave[$key][1])) {
					$tmp = get_count_leave($count[$key], $leave[$key]);
					$count[$key][2] = $tmp[0];
					$count[$key][3] = $tmp[1];
				}
			}
		}
		$tplvar['page']['page'] = $page;
		$tplvar['page']['pages'] = ceil(CountExcusesPersonnel($user_id, $year, $type, $yearbgn) / $xoopsModuleConfig['per_page']);
		$tplvar['page']['type'] = $type;
		$tplvar['page']['type_url'] = urlencode($type);
		$tplvar['page']['user_id'] = $user_id;
		$tplvar['page']['user_name'] = XoopsUser::getUnameFromId($user_id,$xoopsModuleConfig['user_name']);
		$tplvar['page']['excuse_users'] = $excuse_users;

		$tplvar['excuses'] = $excuses;
		$tplvar['count'] = $count;
	break;
}

//template
$xoopsOption['template_main'] = 'mngexcuse.htm';
include(XOOPS_ROOT_PATH . '/header.php');
$xoopsTpl -> assign('xoops_module_header', $xoops_module_header);
$xoopsTpl -> assign('tplvar', $tplvar);
include(XOOPS_ROOT_PATH . '/footer.php');
?>