<?php
//include
include_once('../../../include/cp_header.php');
include_once(XOOPS_ROOT_PATH . '/class/xoopsformloader.php');
include_once('../../../mainfile.php');
include_once('../function/function.php');
include_once('../function/user.php');
include_once('../function/procedure.php');
include_once('../function/yearbgn.php');

//parameter
$oprt = (empty($_GET['oprt']) || ($_GET['oprt'] != 'edt' && $_GET['oprt'] != 'del' && $_GET['oprt'] != 'frw' && $_GET['oprt'] != 'bkw' && $_GET['oprt'] != 'ayb' && $_GET['oprt'] != 'dyb')) ? 'lst' : $_GET['oprt'];
$submitted = empty($_POST['submitted']) ? false : true;
$prcd_id = empty($_GET['prcd_id']) ? 0 : intval($_GET['prcd_id']);
$yearbgn = empty($_GET['yearbgn']) ? 0 : intval($_GET['yearbgn']);

xoops_cp_header();
switch ($oprt) {
	case 'ayb':
		if ($submitted) {
			if (empty($yearbgn)) {
				if (AddYearbgn()) {
					redirect_header('index.php', 5, _AD_MEXCS_YEARBGN_ADDSUCCESS);
				}else{
					redirect_header('index.php?oprt=ayb', 5, get_error_message());
				}
			} else {
				if (UpdateYearbgn($yearbgn)) {
					redirect_header('index.php', 5, _AD_MEXCS_YEARBGN_UDTSUCCESS);
				}else{
					redirect_header('index.php?oprt=ayb&yearbgn=' . $yearbgn, 5, get_error_message());
				}
			}
		}
		if ($yb = GetYearbgn($yearbgn)) {
			$form_yearbgn = $yb['yearbgn'];
			$form_groupids = $yb['groupids'];
		} else {
			$form_yearbgn = 8;
			$form_groupids = array();
		}
		$form_title = (empty($yearbgn)) ? _AD_MEXCS_YEARBGN_ADDYEARBGN : _AD_MEXCS_YEARBGN_EDTYEARBGN;
		$form = new XoopsThemeForm($form_title, 'FormYearbgn', xoops_getenv('REQUEST_URI'));
		$yearbgn_select = new XoopsFormSelect(_AD_MEXCS_YEARBGN_SELECT . ': ' . _AD_MEXCS_YEARBGN_SELECT_DESC, "yearbgn", $form_yearbgn, 1, false);
		for($i = 1; $i <= 12; $i ++) $yearbgn_select -> addOption($i, $i);
		$form -> addElement($yearbgn_select);
	   	$form -> addElement(new XoopsFormSelectGroup(_AD_MEXCS_YEARBGN_GROUP . ': ' . _AD_MEXCS_YEARBGN_GROUP_DESC, 'groupids', false, $form_groupids, 5, true));
		$form -> addElement(new XoopsFormHidden( 'submitted', '1'));
		$form -> addElement(new XoopsFormButton('', 'submit', _SEND, 'submit'));
		echo('<a href="index.php" title="' . _AD_MEXCS_ADM_PROCEDURE . '">' . _AD_MEXCS_ADM_PROCEDURE . '</a>');
		$form -> display();
	break;
	case 'dyb':
		if ($submitted) (DeleteYearbgn($yearbgn)) ?  redirect_header('index.php', 5, _AD_MEXCS_YEARBGN_DELSUCCESS) : redirect_header('index.php', 5, _AD_MEXCS_YEARBGN_DELFAIL);
		xoops_confirm(array('submitted' => 'true'), $_SERVER['REQUEST_URI'], _AD_MEXCS_YEARBGN_DELCONFIRM);
	break;
	case 'frw':
	case 'bkw':
		if (empty($prcd_id)) redirect_header('index.php', 5, _AD_MEXCS_PHASE_NOPHASE);
		(UpdateProcedureOrder($prcd_id, $oprt)) ?  redirect_header('index.php', 5, _AD_MEXCS_PHASE_UDTORDERSUCCESS) : redirect_header('index.php', 5, _AD_MEXCS_PHASE_UDTORDERFAIL);
	break;
	case 'del':
		if ($submitted) (DeleteProcedure($prcd_id)) ?  redirect_header('index.php', 5, _AD_MEXCS_PHASE_DELSUCCESS) : redirect_header('index.php', 5, _AD_MEXCS_PHASE_DELFAIL);
		xoops_confirm(array('submitted' => 'true'), $_SERVER['REQUEST_URI'], _AD_MEXCS_PHASE_DELCONFIRM);
	break;
	case 'edt':
		if (!empty($prcd_id)) {
			if ($submitted) { UpdateProcedure($prcd_id) ?  redirect_header('index.php', 5, _AD_MEXCS_PHASE_UDTSUCCESS) : redirect_header('index.php?oprt=edt&prcd_id=' . $prcd_id, 5, get_error_message()); }
			if (!$phase = GetProcedure($prcd_id)) redirect_header('index.php?oprt=edt', 5, _AD_MEXCS_PHASE_NOPHASE);
			if ($phase['gou']) {
				$phase['gid'] = $phase['checkers'];
				$phase['uid'] = '';
			} else {
				$phase['gid'] = '';
				$phase['uid'] = $phase['checkers'];
			}
		}
		if ($submitted) {
			if (AddProcedure()) {
				redirect_header('index.php', 5, _AD_MEXCS_PHASE_ADDSUCCESS);
			}else{
				redirect_header('index.php?oprt=edt', 5, get_error_message());
			}
		}
		$phase = empty($phase) ? array('groupids' => '','phase' => '', 'gou' => '', 'gid' => '', 'uid' => '') : $phase;
		$form_title = (empty($prcd_id)) ? _AD_MEXCS_PHASE_ADDPHASE : _AD_MEXCS_PHASE_EDTPHASE;
		$form = new XoopsThemeForm($form_title, 'FormPhase', xoops_getenv('REQUEST_URI'));
	   	if (empty($prcd_id)) {
			$form -> addElement(new XoopsFormSelectGroup(_AD_MEXCS_PHASE_GROUPIDS . ': ' . _AD_MEXCS_PHASE_GROUPIDS_DESC, 'groupids', false, $phase['groupids'], 1, false));
		} else {
			$groups = ListGroups(true);
			$form -> addElement(new XoopsFormLabel(_AD_MEXCS_PHASE_GROUPIDS,  $groups[$phase['groupids']]['name']));
		}
	   	$form -> addElement(new XoopsFormText(_AD_MEXCS_PHASE_PHASE, 'phase', 10, 255, $phase['phase']));
		$gou = new XoopsFormRadio(_AD_MEXCS_PHASE_GOU, 'gou', $phase['gou']);
		$gou -> addOptionArray(array(1 => _AD_MEXCS_PHASE_GROUP, 0 => _AD_MEXCS_PHASE_USER));
	   	$form -> addElement($gou);
	   	$form -> addElement(new XoopsFormSelectGroup(_AD_MEXCS_PHASE_CHECKERS . ': ' . _AD_MEXCS_PHASE_GROUP . _AD_MEXCS_PHASE_GROUP_DESC, 'gid', false, $phase['gid'], 5, true));
	   	$form -> addElement(new XoopsFormSelectUser(_AD_MEXCS_PHASE_CHECKERS . ': ' . _AD_MEXCS_PHASE_USER . _AD_MEXCS_PHASE_USER_DESC, 'uid', false, $phase['uid'], 5, true));
		$form -> addElement(new XoopsFormHidden( 'submitted', '1'));
		$form -> addElement(new XoopsFormButton('', 'submit', _SEND, 'submit'));
		echo('<a href="index.php" title="' . _AD_MEXCS_ADM_PROCEDURE . '">' . _AD_MEXCS_ADM_PROCEDURE . '</a>');
		$form -> display();
	break;

	default:
		$page_string = '';
		$phases = ListProcedures();
		$groups = ListGroups(true);
		foreach ($phases as $phase) {
			$page_string .= '<tr>
			<td>' . $groups[$phase['groupids']]['name'] . '</td>
			<td>' . $phase['odr'] . '</td>
			<td>' . $phase['phase'] . '</td>
			<td>';
			if ($phase['gou']) {
				$page_string .= _AD_MEXCS_PHASE_GROUP . ': ';
				foreach ($phase['checkers'] as $checker) $page_string .= $groups[$checker]['name'] . '; ';
			} else {
				$page_string .= _AD_MEXCS_PHASE_USER . ': ';
				foreach ($phase['checkers'] as $checker) $page_string .= XoopsUser::getUnameFromId($checker,$xoopsModuleConfig['user_name']) . '; ';
			}
			$page_string .= '</td><td><ul>
			<li><a href="index.php?oprt=edt&prcd_id=' . $phase['id'] . '">' . _AD_MEXCS_PHASE_EDIT . '</a></li>
			<li><a href="index.php?oprt=del&prcd_id=' . $phase['id'] . '">' . _AD_MEXCS_PHASE_DELETE . '</a></li>
			<li><a href="index.php?oprt=frw&prcd_id=' . $phase['id'] . '">' . _AD_MEXCS_PHASE_FORWARD . '</a></li>
			<li><a href="index.php?oprt=bkw&prcd_id=' . $phase['id'] . '">' . _AD_MEXCS_PHASE_BACKWARD . '</a></li>
			<ul></td>
			</tr>';
		}
		$page_string = '<a href="index.php?oprt=edt" title="' . _AD_MEXCS_PHASE_ADDPHASE . '">' . _AD_MEXCS_PHASE_ADDPHASE . '</a>
		<table><thead>
		<tr><th>' . _AD_MEXCS_PHASE_GROUPIDS . '</th><th>' . _AD_MEXCS_PHASE_ORDER . '</th><th>' . _AD_MEXCS_PHASE_PHASE . '</th><th>' . _AD_MEXCS_PHASE_CHECKERS . '</th><th>' . _AD_MEXCS_PHASE_OPERATION . '</th></tr>
		</thead><tbody>' . $page_string . '</tbody></table>';
		$page_string .= '<a href="index.php?oprt=ayb" title="' . _AD_MEXCS_YEARBGN_ADDYEARBGN . '">' . _AD_MEXCS_YEARBGN_ADDYEARBGN . '</a><table><thead><tr><th>' . _AD_MEXCS_YEARBGN_SELECT . '</th><th>' . _AD_MEXCS_PHASE_GROUPIDS . '</th><th>' . _AD_MEXCS_PHASE_OPERATION . '</th></tr></thead><tbody>';
		$yearbgns = ListYearbgns(true);
		if (is_array($yearbgns)) {
		foreach ($yearbgns as $yearbgn) {
			$page_string .= '<tr><td>' . $yearbgn['yearbgn'] . '</td><td>';
			foreach ($yearbgn['groupids'] as $gid) $page_string .= $groups[$gid]['name'] . '; ';
			$page_string .= '</td><td><ul>
			<li><a href="index.php?oprt=ayb&yearbgn=' . $yearbgn['yearbgn'] . '">' . _AD_MEXCS_PHASE_EDIT . '</a></li>
			<li><a href="index.php?oprt=dyb&yearbgn=' . $yearbgn['yearbgn'] . '">' . _AD_MEXCS_PHASE_DELETE . '</a></li>
			<ul></td>
			</tr>';
		}
		}
		$page_string .= '</tbody></table>';
		echo($page_string);
	break;
}
xoops_cp_footer();
?>