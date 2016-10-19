<?php
//include
include_once('include.php');
include_once('function/document.php');
include_once('function/procedure.php');
include_once('function/excuse.php');
include_once('function/phase.php');
include_once('function/substitute.php');
include_once('function/file.php');
include_once('function/comment.php');
include_once('function/leave.php');
include_once('function/yearbgn.php');

//parameter
$oprt = (empty($_GET['oprt']) || ($_GET['oprt'] != 'apl' && $_GET['oprt'] != 'edt' && $_GET['oprt'] != 'del' && $_GET['oprt'] != 'viw')) ? 'lst' : $_GET['oprt'];
$excs_id = empty($_GET['excs_id']) ? 0 : intval($_GET['excs_id']);
$year = empty($_GET['year']) ? 0 : intval($_GET['year']);
$page = empty($_GET['page']) ? 0 : intval($_GET['page']);
$type = (empty($_GET['type']) || !in_array($_GET['type'], explode('|', $xoopsModuleConfig['excuse_type']))) ? '' : $_GET['type'];
$submitted = (empty($_POST['submitted'])) ? false : true;

//main
$uid = is_object($xoopsUser) ? $xoopsUser -> getVar('uid') : 0;
//authority
if (!CheckAuthority('excuse_info')) redirect_header(XOOPS_URL, 5, _MD_MEXCS_NOAUTHORITY);
//set template values
$tplvar = array();
$tplvar['page'] = array(
	'module_name' => $xoopsModule -> getVar('name'),
	'main_tpl' => 'excuse',
	'oprt' => $oprt,
);
$tplvar['user'] = array(
	'uid' => $uid,
	'uname' => XoopsUser::getUnameFromId($uid,$xoopsModuleConfig['user_name']),
	'excuse_user' => CheckAuthority('excuse_user'),
	'personnel' => CheckAuthority('personnel'),
	'substitute_mng' => CheckAuthority('substitute_mng'),
);
switch ($oprt) {
	case 'apl':
		if (!CheckAuthority('excuse_user')) redirect_header(XOOPS_URL, 5, _MD_MEXCS_NOAUTHORITY);
		if ($submitted) { (ApplyExcuse()) ?  redirect_header('index.php', 5, _MD_MEXCS_EXCUSE_APLSUCCESS) : redirect_header('excuse.php?oprt=apl', 5, _MD_MEXCS_EXCUSE_APLFAIL . get_error_message()); }
		$dcmts = array('0' => '');
		$page = $page - 1;
		$documents = ListDocuments($page, true);
		foreach($documents as $key => $val) $dcmts[$key] = '[' . date ('Y-m-d', $val['date_excuse']) . '] ' . $val['title'];
		if (!($groupids = GetUserGroupsid())) redirect_header(XOOPS_URL, 5, _MD_MEXCS_NOAUTHORITY);
		$phases = ListProcedures($groupids);
		GetProcedureCheckers($phases);
		$tplvar['page']['title'] =  _MD_MEXCS_HEAD_APLEXCUSE;
		$tplvar['phases'] = $phases;
		$tplvar['form']['excuse_statement'] = $xoopsModuleConfig['excuse_statement'];
		$tplvar['form']['appointment'] = explode('|', $xoopsModuleConfig['appointment_type']);
		$tplvar['form']['excuse'] = explode('|', $xoopsModuleConfig['excuse_type']);
		$tplvar['form']['date_bgn'] = mktime(7, 45, 0, date('n'), date('j'), date('Y'));
		$tplvar['form']['date_end'] = mktime(16, 15, 0, date('n'), date('j'), date('Y'));
		$tplvar['form']['substitute'] = $xoopsModuleConfig['substitute'];
		$tplvar['form']['substitute_stm'] = $xoopsModuleConfig['substitute_stm'];
		$tplvar['form']['substitute_desc'] = $xoopsModuleConfig['substitute_desc'];
		$tplvar['form']['dcmts'] = $dcmts;
		$tplvar['form']['dcmts_more'] = str_replace('%document_period%', $xoopsModuleConfig['document_period'], _MD_MEXCS_EXCUSE_DOCUMENT_MORE_DESC);
		$tplvar['form']['dcmts_pages'] = ceil(CountDocuments() / $xoopsModuleConfig['per_page']);
		$tplvar['users'] = ListUidByGid($xoopsModuleConfig['excuse_user'], true);
		foreach ($tplvar['users'] as $key => $val) $tplvar['users'][$key] = XoopsUser::getUnameFromId($key,$xoopsModuleConfig['user_name']);
	break;
	case 'edt':
		if (!$excuse = GetExcuse($excs_id)) redirect_header('index.php', 5, get_error_message());
		if (!CheckAuthority('personnel') && ($excuse['state'] > 1 || $excuse['uid'] != $uid)) redirect_header(XOOPS_URL, 5, _MD_MEXCS_NOAUTHORITY);
		if ($submitted) { (UpdateExcuse($excs_id)) ?  redirect_header('excuse.php?oprt=viw&excs_id=' . $excs_id, 5, _MD_MEXCS_EXCUSE_EDTSUCCESS) : redirect_header('excuse.php?oprt=edt&excs_id=' . $excs_id, 5, _MD_MEXCS_EXCUSE_EDTFAIL . get_error_message()); }
		$dcmts = array('0' => '');
		//document
		$dcmts = array('0' => '');
		$page = $page - 1;
		$documents = ListDocuments($page, true, $excuse['uid']);
		foreach($documents as $key => $val) $dcmts[$key] = '[' . date ('Y-m-d', $val['date_excuse']) . '] ' . $val['title'];
		//substitute
		$excuse['substitute'] = GetSubstitute($excs_id);
		//phases
		if (!($groupids = GetUserGroupsid($excuse['uid']))) redirect_header(XOOPS_URL, 5, _MD_MEXCS_NOAUTHORITY);
		$phases = ListProcedures($groupids);
		GetProcedureCheckers($phases);
		$excuse['phases'] = GetPhases($excs_id);
		$phases_pass = array();
		foreach ($excuse['phases'] as $key => $val) $phases_pass[$excuse['phases'][$key]['odr']] = (($excuse['phases'][$key]['state'] == 3) || !$xoopsModuleConfig['chang_checker']) ? array(1, XoopsUser::getUnameFromId($excuse['phases'][$key]['checker'],$xoopsModuleConfig['user_name']), get_phase_state_string($excuse['phases'][$key]['state'])) : array(0, $excuse['phases'][$key]['checker']);
		foreach ($phases as $key => $val) $phases[$key]['pass'] = $phases_pass[$phases[$key]['odr']];
		//template var
		$tplvar['page']['title'] = _MD_MEXCS_EXCUSE_EDTEXCUSE;
		$tplvar['page']['edit_excuse'] = 1;
		$tplvar['page']['delete_excuse'] = $xoopsModuleConfig['unsubmit'] ? 1 : 0;
		$tplvar['excuse'] = $excuse;
		$tplvar['phases'] = $phases;
		$tplvar['form']['excuse_statement'] = $xoopsModuleConfig['excuse_statement'];
		$tplvar['form']['appointment'] = explode('|', $xoopsModuleConfig['appointment_type']);
		$tplvar['form']['excuse'] = explode('|', $xoopsModuleConfig['excuse_type']);
		$tplvar['form']['date_bgn'] = (empty($excuse['date_bgn'])) ? mktime(7, 45, 0, date('n'), date('j'), date('Y')) : $excuse['date_bgn'];
		$tplvar['form']['date_end'] = (empty($excuse['date_end'])) ? mktime(16, 15, 0, date('n'), date('j'), date('Y')) : $excuse['date_end'];
		$tplvar['form']['substitute'] = $xoopsModuleConfig['substitute'];
		$tplvar['form']['substitute_stm'] = $xoopsModuleConfig['substitute_stm'];
		$tplvar['form']['substitute_desc'] = (empty($excuse['substitute']['description'])) ? $xoopsModuleConfig['substitute_desc'] : $excuse['substitute']['description'];
		$tplvar['form']['dcmts'] = $dcmts;
		$tplvar['form']['dcmts_more'] = str_replace('%document_period%', $xoopsModuleConfig['document_period'], _MD_MEXCS_EXCUSE_DOCUMENT_MORE_DESC);
		$tplvar['form']['dcmts_pages'] = ceil(CountDocuments() / $xoopsModuleConfig['per_page']);
		$tplvar['users'] = ListUidByGid($xoopsModuleConfig['excuse_user'], true);
		foreach ($tplvar['users'] as $key => $val) $tplvar['users'][$key] = XoopsUser::getUnameFromId($key,$xoopsModuleConfig['user_name']);
	break;
	case 'del':
		if (!$excuse = GetExcuse($excs_id)) redirect_header('index.php', 5, get_error_message());
		if (!CheckAuthority('personnel') && ($excuse['state'] > 1 || !$xoopsModuleConfig['unsubmit'] || $excuse['uid'] != $uid)) redirect_header(XOOPS_URL, 5, _MD_MEXCS_NOAUTHORITY);
		if ($excuse['state'] > 1) {
			if ($submitted) { SetDeleteExcuse($excs_id) ?  redirect_header('index.php', 5, _MD_MEXCS_EXCUSE_DELSUCCESS) : redirect_header('excuse.php?oprt=viw&excs_id=' . $excs_id, 5, _MD_MEXCS_EXCUSE_DELFAIL . get_error_message()); }
			$tplvar['page']['title'] = _MD_MEXCS_EXCUSE_EDTEXCUSE;
			$excuse['uname'] = XoopsUser::getUnameFromId($excuse['uid'],$xoopsModuleConfig['user_name']);
			$tplvar['excuse'] = $excuse;
		} else {
			if ($submitted) { DeleteExcuse($excs_id) ?  redirect_header('index.php', 5, _MD_MEXCS_EXCUSE_DELSUCCESS) : redirect_header('excuse.php?oprt=viw&excs_id=' . $excs_id, 5, _MD_MEXCS_EXCUSE_DELFAIL . get_error_message()); }
			include(XOOPS_ROOT_PATH . "/header.php");
			xoops_confirm(array('submitted' => 'true'), $_SERVER['REQUEST_URI'], _MD_MEXCS_EXCUSE_DELCONFIRM);
			include(XOOPS_ROOT_PATH . "/footer.php");
		}
	break;
	case 'viw':
		if (!$excuse = GetExcuse($excs_id)) redirect_header('index.php', 5, get_error_message());
		//authority
		$excuse['phases'] = GetPhases($excs_id);
		$checker = false;
		foreach ($excuse['phases'] as $val) if ($val['checker'] == $uid) $checker = true;
		if ($excuse['uid'] != $uid && !CheckAuthority('personnel') && !CheckAuthority('substitute_mng') && !$checker) redirect_header(XOOPS_URL, 5, _MD_MEXCS_NOAUTHORITY);
		$phases_total = count($excuse['phases']);
		if ($submitted) {
			$phase_check = (empty($_POST['phase_check'])) ? false : true;
			$phase_id = (empty($_POST['phse_id'])) ? 0 : intval($_POST['phse_id']);
			$redirect_string = '';
			if ($phase_check) {
				//authority
				if ($excuse['phases'][$phase_id]['checker'] != $uid) redirect_header(XOOPS_URL, 5, _MD_MEXCS_NOAUTHORITY);
				if ($_POST['state'] == '3') if (!SetPhaseState($excs_id, 'pass', $phase_id)) redirect_header($_SERVER['REQUEST_URI'], 5, _MD_MEXCS_EXCUSE_PHASE_STATE_UPDFAIL . get_error_message());
				if ($_POST['state'] == '2') if (!SetPhaseState($excs_id, 'comment', $phase_id)) redirect_header($_SERVER['REQUEST_URI'], 5, _MD_MEXCS_EXCUSE_PHASE_STATE_UPDFAIL . get_error_message());
				if ($_POST['state'] == '4' && $xoopsModuleConfig['reject'] && ($excuse['phases'][$phase_id]['odr'] + 1) == $phases_total) if (!SetPhaseState($excs_id, 'reject', $phase_id)) redirect_header($_SERVER['REQUEST_URI'], 5, _MD_MEXCS_EXCUSE_PHASE_STATE_UPDFAIL . get_error_message());
				$redirect_string = _MD_MEXCS_EXCUSE_PHASE_STATE_UPDSUCCESS;
			} else {
				if ($excuse['uid'] != $uid) redirect_header(XOOPS_URL, 5, _MD_MEXCS_NOAUTHORITY);
			}
			if (AddComment($phase_id)) $redirect_string .= '<br />' . _MD_MEXCS_EXCUSE_PHASE_STATE_CMTSUCCESS;
			redirect_header('index.php', 5, $redirect_string);
		}
		if ($excuse['state'] == 4) {
			$excuse['del_comment'] = GetDeletComment($excs_id);
			$excuse['del_comment']['uname'] = XoopsUser::getUnameFromId($excuse['del_comment']['uid'],$xoopsModuleConfig['user_name']);
		}
		$excuse['state_string'] = get_excuse_state_string($excuse['state']);
		$excuse['uname'] = XoopsUser::getUnameFromId($excuse['uid'],$xoopsModuleConfig['user_name']);
		$excuse['comments'] = GetComments($excs_id);
		foreach ($excuse['phases'] as $key => $val) {
			$excuse['phases'][$key]['checker_uname'] = XoopsUser::getUnameFromId($excuse['phases'][$key]['checker'],$xoopsModuleConfig['user_name']);
			$excuse['phases'][$key]['state_string'] = get_phase_state_string($excuse['phases'][$key]['state']);
			$excuse['phases'][$key]['reject'] = (($excuse['phases'][$key]['odr'] + 1) == $phases_total && $xoopsModuleConfig['reject']) ? 1 : 0;
			$excuse['phases'][$key]['comments'] = empty($excuse['comments'][$excuse['phases'][$key]['id']]) ? array() : $excuse['comments'][$excuse['phases'][$key]['id']];
			foreach ($excuse['phases'][$key]['comments'] as $k => $v) $excuse['phases'][$key]['comments'][$k]['uname'] = XoopsUser::getUnameFromId($excuse['phases'][$key]['comments'][$k]['uid'],$xoopsModuleConfig['user_name']);
		}
		$excuse['substitute'] = GetSubstitute($excs_id);
		$excuse['document'] = GetDocument($excuse['dcmt_id']);
		if ($excuse['document']) $excuse['document']['files'] = GetFiles($excuse['dcmt_id']);
		$tplvar['page']['title'] =  _MD_MEXCS_EXCUSE_VIWEXCUSE;
		$tplvar['page']['edit_excuse'] = ($excuse['state'] > 3) ? 0 : ((CheckAuthority('personnel')) ? 1 : (($excuse['state'] > 1) ? 0 : ($excuse['uid'] == $uid) ? 1 : 0));
		$tplvar['page']['delete_excuse'] = ($excuse['state'] > 3) ? 0 : ((CheckAuthority('personnel')) ? 1 : (($excuse['state'] > 1) ? 0 : ($xoopsModuleConfig['unsubmit'] && $excuse['uid'] == $uid) ? 1 : 0));
		$tplvar['page']['according_order'] = $xoopsModuleConfig['according_order'];
		$tplvar['excuse'] = $excuse;
	break;
	default:
		if (!CheckAuthority('excuse_user')) redirect_header(XOOPS_URL, 5, _MD_MEXCS_NOAUTHORITY);
		$excuses = GetExcuses($year, $page, $type);
		foreach ($excuses as $key => $val) $excuses[$key]['state_string'] = get_excuse_state_string($excuses[$key]['state']);
		$excuse_years = get_excuse_years(GetYearbgnByUid($uid));
		$leave = GetLeave($year, $uid);
		$count = CountExcuseDate($uid, $year, GetYearbgnByUid($uid));
		foreach ($count as $key => $val) {
			if (!empty($leave[$key][0]) || !empty($leave[$key][1])) {
				$tmp = get_count_leave($count[$key], $leave[$key]);
				$count[$key][2] = $tmp[0];
				$count[$key][3] = $tmp[1];
			}
		}
		$tplvar['page']['title'] = _MD_MEXCS_HEAD_EXCUSE;
		$tplvar['page']['page'] = $page;
		$tplvar['page']['pages'] = ceil(CountExcuses($year, $type) / $xoopsModuleConfig['per_page']);
		$tplvar['page']['year'] = $year;
		$tplvar['page']['year_string'] = $excuse_years[$year][0];
		$tplvar['page']['type'] = $type;
		$tplvar['page']['type_url'] = urlencode($type);
		$tplvar['page']['excuse_types'] = explode('|', $xoopsModuleConfig['excuse_type']);
		$tplvar['excuses'] = $excuses;
		$tplvar['count'] = $count;
		$tplvar['page']['excuse_years'] = $excuse_years;
	break;
}

//template
$xoopsOption['template_main'] = 'mngexcuse.htm';
include(XOOPS_ROOT_PATH . '/header.php');
$xoopsTpl -> assign('xoops_module_header', $xoops_module_header);
$xoopsTpl -> assign('tplvar', $tplvar);
include(XOOPS_ROOT_PATH . '/footer.php');
?>