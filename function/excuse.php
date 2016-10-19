<?php
function ApplyExcuse() {
	//link with phase.php, substitute.php
	global $xoopsDB, $xoopsUser, $xoopsModuleConfig, $excsErrorMessage;
	$uid = is_object($xoopsUser) ? $xoopsUser -> getVar('uid') : 0;
	$groupids = GetUserGroupsid();
	$phases = ListProcedures($groupids);
	GetProcedureCheckers($phases);
	foreach ($phases as $phase) {
		if (empty($_POST['phase_checker'][$phase['odr']]) || empty($phase['checkers'][$_POST['phase_checker'][$phase['odr']]])) {
			$excsErrorMessage = 'No phase checker.';
			return false;
		}
	}
	if (empty($_POST['date_count'][0]) && empty($_POST['date_count'][1])) {
		$excsErrorMessage = 'No date count.';
		return false;
	}
	escape_string_arr($_POST);
	//new document
	if (!$_POST['document_son']) {
		if (ltrim($_POST['title']) == '') $_POST['title'] = 'document';
		$tmp_description = $_POST['description'];
		$_POST['description'] = $_POST['title'];
		$_POST['date_excuse_Month'] = date('m');
		$_POST['date_excuse_Day'] = date('d');
		$_POST['date_excuse_Year'] = date('Y');
		if ($_POST['dcmt_id'] = AddDocument()) AddFiles($_POST['dcmt_id']);
		$_POST['description'] = $tmp_description;
	}
	//none document
	if ($_POST['document_son'] == 2) $_POST['dcmt_id'] = 0;
	$hour = ($_POST['date_count'][0] - floor($_POST['date_count'][0])) * $xoopsModuleConfig['to_day'];
	$_POST['date_count'][0] = floor($_POST['date_count'][0]);
	$_POST['date_count'][1] = ceil((($_POST['date_count'][1] + $hour) * 60) / $xoopsModuleConfig['mini_hour']) * $xoopsModuleConfig['mini_hour'] / 60;
	$date_bgn = strtotime($_POST['date_bgn']);//mktime($_POST['date_bgn_Hour'], $_POST['date_bgn_Minute'], 0, $_POST['date_bgn_Month'], $_POST['date_bgn_Day'], $_POST['date_bgn_Year']);
	$date_end = strtotime($_POST['date_end']);//mktime($_POST['date_end_Hour'], $_POST['date_end_Minute'], 0, $_POST['date_end_Month'], $_POST['date_end_Day'], $_POST['date_end_Year']);
	if (empty($date_bgn)) $date_bgn = mktime(7, 45, 0, date('n'), date('j'), date('Y'));
	if (empty($date_end)) $date_end = mktime(16, 15, 0, date('n'), date('j'), date('Y'));
	$sql = 'Insert Into ' . $xoopsDB -> prefix('mexcs_excuse') . ' (uid, appointment, excuse_type, description, date_bgn, date_end, date_count_day, date_count_hour, dcmt_id, date_time, state) Values (\'' . $uid . '\', \'' . $_POST['appointment'] . '\', \'' . $_POST['excuse_type'] . '\', \'' . $_POST['description'] . '\', \'' . $date_bgn . '\', \'' . $date_end . '\', \'' . $_POST['date_count'][0] . '\', \'' . $_POST['date_count'][1] . '\', \'' . intval($_POST['dcmt_id']) . '\', \'' . time() . '\', \'1\')';
	if (!$xoopsDB -> query($sql)) {
		$excsErrorMessage = 'MySQL insert fail.';
		return false;
	}
	if (!$id = $xoopsDB -> getInsertId()) return false;
	if (!AddPhases($id)) return false;
	SetPhaseState($id, 'begin');
	if ($xoopsModuleConfig['substitute']) if (!EditSubstitute($id)) return false;
	return true;
}
function UpdateExcuse($excs_id) {
	global $xoopsDB, $xoopsUser, $xoopsModuleConfig, $excsErrorMessage;
	$uid = is_object($xoopsUser) ? $xoopsUser -> getVar('uid') : 0;
	if (empty($_POST['date_count'][0]) && empty($_POST['date_count'][1])) {
		$excsErrorMessage = 'No date count.';
		return false;
	}
	escape_string_arr($_POST);
	if (!$_POST['document_son']) {
		if (ltrim($_POST['title']) == '') $_POST['title'] = 'document';
		$tmp_description = $_POST['description'];
		$_POST['description'] = $_POST['title'];
		$_POST['date_excuse_Month'] = date('m');
		$_POST['date_excuse_Day'] = date('d');
		$_POST['date_excuse_Year'] = date('Y');
		if ($_POST['dcmt_id'] = AddDocument()) AddFiles($_POST['dcmt_id']);
		$_POST['description'] = $tmp_description;
	}
	if ($_POST['document_son'] == 2) $_POST['dcmt_id'] = 0;
	$hour = ($_POST['date_count'][0] - floor($_POST['date_count'][0])) * $xoopsModuleConfig['to_day'];
	$_POST['date_count'][0] = floor($_POST['date_count'][0]);
	$_POST['date_count'][1] = ceil((($_POST['date_count'][1] + $hour) * 60) / $xoopsModuleConfig['mini_hour']) * $xoopsModuleConfig['mini_hour'] / 60;
	$date_bgn = strtotime($_POST['date_bgn']);//mktime($_POST['date_bgn_Hour'], $_POST['date_bgn_Minute'], 0, $_POST['date_bgn_Month'], $_POST['date_bgn_Day'], $_POST['date_bgn_Year']);
	$date_end = strtotime($_POST['date_end']);//mktime($_POST['date_end_Hour'], $_POST['date_end_Minute'], 0, $_POST['date_end_Month'], $_POST['date_end_Day'], $_POST['date_end_Year']);
	if (empty($date_bgn)) $date_bgn = mktime(7, 45, 0, date('n'), date('j'), date('Y'));
	if (empty($date_end)) $date_end = mktime(16, 15, 0, date('n'), date('j'), date('Y'));
	$sql = 'Update ' . $xoopsDB -> prefix('mexcs_excuse') . ' Set appointment = \'' . $_POST['appointment'] . '\', excuse_type = \'' . $_POST['excuse_type'] . '\', description = \'' . $_POST['description'] . '\', date_bgn = \'' . $date_bgn . '\', date_end = \'' . $date_end . '\', date_count_day = \'' . $_POST['date_count'][0] . '\', date_count_hour = \'' . $_POST['date_count'][1] . '\', dcmt_id = \'' . intval($_POST['dcmt_id']) . '\' Where id = \'' . $excs_id . '\'';
	if (!$xoopsDB -> query($sql)) {
		$excsErrorMessage = 'MySQL update fail.';
		return false;
	}
	//reset phase state to contuine, but won't impact the procedure
	//SetPhaseState($excs_id, 'continue');
	if ($xoopsModuleConfig['chang_checker']) UpdatePhaseCheckers($excs_id);
	if ($xoopsModuleConfig['substitute']) if (!EditSubstitute($excs_id)) return false;
	return true;
}
function GetExcuse($excs_id) {
	global $xoopsDB, $excsErrorMessage;
	$sql = 'Select * From ' . $xoopsDB -> prefix('mexcs_excuse') . ' Where id = \'' . $excs_id . '\'';
	if (!$record = $xoopsDB -> fetchArray($xoopsDB -> query($sql))) {
		$excsErrorMessage = 'No excuse.';
		return false;
	}
	return $record;
}
function GetExcuses($year = 0, $page = 0, $type = '') {
	global $xoopsDB, $xoopsUser, $xoopsModuleConfig;
	$uid = is_object($xoopsUser) ? $xoopsUser -> getVar('uid') : 0;
	$recordset = array();
	$years = get_excuse_years(GetYearbgnByUid($uid));
	$year = ' And date_bgn < \'' . $years[$year][1] . '\' And date_bgn > \'' . $years[$year][2] . '\'';
	$page = ($page < 0) ? '' : ' Limit ' . ($page * $xoopsModuleConfig['per_page']) . ', ' . $xoopsModuleConfig['per_page'];
	$type = (empty($type) || !in_array($type, explode('|', $xoopsModuleConfig['excuse_type']))) ? '' : ' And excuse_type = \'' . escape_string($type) . '\'';
	$sql = 'Select * From ' . $xoopsDB -> prefix('mexcs_excuse') . ' Where uid = \'' . $uid . '\'' . $year . $type . ' Order By date_bgn DESC, id DESC' . $page;
	if (!$result = $xoopsDB -> query($sql)) return false;
	while ($record = $xoopsDB -> fetchArray($result)) $recordset[$record['id']] = $record;
	return $recordset;
}
function GetExcusesPersonnel($uid = 0, $year = 0, $page = 0, $type = '', $yearbgn = 0) {
	global $xoopsDB, $xoopsModuleConfig;
	$recordset = array();
	$yearbgn = (empty($yearbgn)) ? GetDefaultYearbgn() : $yearbgn;
	$years = get_excuse_years($yearbgn);
	$year = ' date_bgn < \'' . $years[$year][1] . '\' And date_bgn > \'' . $years[$year][2] . '\'';
	$uid = ($uid) ? ' And uid = \'' . $uid . '\'' : ' And ( uid = \'' . implode('\' Or uid = \'', ListUidByGidOnlyUid(GetYearbgnGroupids($yearbgn))) . '\')';
	$page = ($page < 0) ? '' : ' Limit ' . ($page * $xoopsModuleConfig['per_page']) . ', ' . $xoopsModuleConfig['per_page'];
	$type = (empty($type) || !in_array($type, explode('|', $xoopsModuleConfig['excuse_type']))) ? '' : ' And excuse_type = \'' . escape_string($type) . '\'';
	$sql = 'Select * From ' . $xoopsDB -> prefix('mexcs_excuse') . ' Where' . $year . $uid . $type . ' Order By date_bgn DESC, id DESC' . $page;
	if (!$result = $xoopsDB -> query($sql)) return false;
	while ($record = $xoopsDB -> fetchArray($result)) $recordset[$record['id']] = $record;
	return $recordset;
}
function GetProgressExcuses() {
	global $xoopsDB, $xoopsUser, $excsErrorMessage;
	$uid = is_object($xoopsUser) ? $xoopsUser -> getVar('uid') : 0;
	$recordset = array();
	$sql = 'Select * From ' . $xoopsDB -> prefix('mexcs_excuse') . ' Where uid = \'' . $uid . '\' And state = 1 Order By date_bgn DESC, id DESC';
	if (!$result = $xoopsDB -> query($sql)) return false;
	while ($record = $xoopsDB -> fetchArray($result)) $recordset[$record['id']] = $record;
	return $recordset;
}
function GetCheckExcuse() {
	global $xoopsDB, $xoopsUser, $excsErrorMessage;
	$uid = is_object($xoopsUser) ? $xoopsUser -> getVar('uid') : 0;
	$recordset = array();
	$sql = 'Select excs_id From ' . $xoopsDB -> prefix('mexcs_phase') . ' Where (state = 1 Or state = 2) And checker = \'' . $uid . '\'';
	if (!$result = $xoopsDB -> query($sql)) return false;
	while (list($id) = $xoopsDB -> fetchRow($result)) array_push($recordset, $id);
	if (empty($recordset)) return $recordset;
	$sql = implode('\' Or id = \'', $recordset);
	$sql = 'Select * From ' . $xoopsDB -> prefix('mexcs_excuse') . ' Where (id = \'' . $sql . '\') And state = 1 Order By date_bgn ASC, id DESC';
	$recordset = array();
	if (!$result = $xoopsDB -> query($sql)) return false;
	while ($record = $xoopsDB -> fetchArray($result)) $recordset[$record['id']] = $record;
	return $recordset;
}
function CountExcuses($year = 0, $type = '') {
	global $xoopsDB, $xoopsUser, $xoopsModuleConfig;
	$uid = is_object($xoopsUser) ? $xoopsUser -> getVar('uid') : 0;
	$years = get_excuse_years(GetYearbgnByUid($uid));
	$year = ' And date_bgn < \'' . $years[$year][1] . '\' And date_bgn > \'' . $years[$year][2] . '\'';
	$type = (empty($type) || !in_array($type, explode('|', $xoopsModuleConfig['excuse_type']))) ? '' : ' And excuse_type = \'' . escape_string($type) . '\'';
	$sql = 'Select count(1) From ' . $xoopsDB -> prefix('mexcs_excuse') . ' Where uid = \'' . $uid . '\'' . $year . $type;
	if (!list($count) = $xoopsDB -> fetchRow($xoopsDB -> query($sql))) return false;
	return $count;
}
function CountExcusesPersonnel($uid = 0, $year = 0, $type = '', $yearbgn = 0) {
	global $xoopsDB, $xoopsUser, $xoopsModuleConfig;
	$yearbgn = (empty($yearbgn)) ? GetDefaultYearbgn() : $yearbgn;
	$years = get_excuse_years($yearbgn);
	$year = ' date_bgn < \'' . $years[$year][1] . '\' And date_bgn > \'' . $years[$year][2] . '\'';
	$uid = ($uid) ? ' And uid = \'' . $uid . '\'' : ' And ( uid = \'' . implode('\' Or uid = \'', ListUidByGidOnlyUid(GetYearbgnGroupids($yearbgn))) . '\')';
	$type = (empty($type) || !in_array($type, explode('|', $xoopsModuleConfig['excuse_type']))) ? '' : ' And excuse_type = \'' . escape_string($type) . '\'';
	$sql = 'Select count(1) From ' . $xoopsDB -> prefix('mexcs_excuse') . ' Where' . $year . $uid . $type;
	if (!list($count) = $xoopsDB -> fetchRow($xoopsDB -> query($sql))) return false;
	return $count;
}
function CountExcuseDate($uid, $year, $yearbgn = 0) {
	global $xoopsDB, $xoopsModuleConfig;
	$count = array();
	$yearbgn = (empty($yearbgn)) ? GetDefaultYearbgn() : $yearbgn;
	$years = get_excuse_years($yearbgn);
	$year = ' And date_bgn < \'' . $years[$year][1] . '\' And date_bgn > \'' . $years[$year][2] . '\'';
	$uid = ($uid) ? ' And uid = \'' . $uid . '\'' : ' And ( uid = \'' . implode('\' Or uid = \'', ListUidByGidOnlyUid(GetYearbgnGroupids($yearbgn))) . '\')';
	$str = 'Select sum(date_count_day), sum(date_count_hour) From ' . $xoopsDB -> prefix('mexcs_excuse') . ' Where state = 3' . $uid . $year;
	$excuse_type = explode('|', $xoopsModuleConfig['excuse_type']);
	foreach ($excuse_type as $val) {
		$sql = $str . ' And excuse_type = \'' . escape_string($val) . '\'';
		list($count[$val][0], $count[$val][1]) = $xoopsDB -> fetchRow($xoopsDB -> query($sql));
		$flr = floor($count[$val][1] / $xoopsModuleConfig['to_day']);
		$count[$val][0] = $count[$val][0] + $flr;
		$count[$val][1] = $count[$val][1] - ($flr * $xoopsModuleConfig['to_day']);
	}
	list($count['total'][0], $count['total'][1]) = $xoopsDB -> fetchRow($xoopsDB -> query($str));
	$flr = floor($count['total'][1] / $xoopsModuleConfig['to_day']);
	$count['total'][0] = $count['total'][0] + $flr;
	$count['total'][1] = $count['total'][1] - ($flr * $xoopsModuleConfig['to_day']);
	return $count;
}
function CountExcuseDateUsers($year, $yearbgn = 0) {
	global $xoopsDB, $xoopsModuleConfig;
	$count = array();
	$yearbgn = (empty($yearbgn)) ? GetDefaultYearbgn() : $yearbgn;
	$years = get_excuse_years($yearbgn);
	$year = ' And date_bgn < \'' . $years[$year][1] . '\' And date_bgn > \'' . $years[$year][2] . '\'';
	$sql = 'Select uid, excuse_type, sum(date_count_day) as date_count_day, sum(date_count_hour) as date_count_hour From ' . $xoopsDB -> prefix('mexcs_excuse') . ' Where state = 3'. $year . ' And ( uid = \'' . implode('\' Or uid = \'', ListUidByGidOnlyUid(GetYearbgnGroupids($yearbgn))) . '\') group by uid, excuse_type';
	if (!$result = $xoopsDB -> query($sql)) return false;
	while (list($uid, $excuse_type, $date_count_day, $date_count_hour) = $xoopsDB -> fetchRow($result)) {
		$flr = floor($date_count_hour / $xoopsModuleConfig['to_day']);
		$date_count_day = $date_count_day + $flr;
		$date_count_hour = $date_count_hour - ($flr * $xoopsModuleConfig['to_day']);
		$count[$uid][$excuse_type] = array($date_count_day, $date_count_hour);
		if (empty($count[$uid]['total'][0])) $count[$uid]['total'][0] = 0;
		if (empty($count[$uid]['total'][1])) $count[$uid]['total'][1] = 0;
		$count[$uid]['total'][0] = $count[$uid]['total'][0] + $date_count_day;
		$count[$uid]['total'][1] = $count[$uid]['total'][1] + $date_count_hour;
		$flr = floor($count[$uid]['total'][1] / $xoopsModuleConfig['to_day']);
		$count[$uid]['total'][0] = $count[$uid]['total'][0] + $flr;
		$count[$uid]['total'][1] = $count[$uid]['total'][1] - ($flr * $xoopsModuleConfig['to_day']);
	}
	return $count;
}
function DeleteExcuse($excs_id) {
	//link with comment.php, phase.php, substitute.php
	global $xoopsDB;
	DeleteComments($excs_id);
	DeletePhases($excs_id);
	DeleteSubstitute($excs_id);
	$sql = 'Delete From ' . $xoopsDB -> prefix('mexcs_excuse') . ' Where id = \'' . $excs_id . '\'';
	if (!$xoopsDB -> queryF($sql)) return false;
	return true;
}
function SetDeleteExcuse($excs_id) {
	//link with comment.php
	global $xoopsDB;
	$sql = 'Select id From ' . $xoopsDB -> prefix('mexcs_phase') . ' Where excs_id = \'' . $excs_id . '\' Order By odr DESC Limit 0, 1';
	if (!list($phse_id) = $xoopsDB -> fetchRow($xoopsDB -> query($sql))) return false;
	AddComment($phse_id);
	$sql = 'Update ' . $xoopsDB -> prefix('mexcs_excuse') . ' Set state = 4 Where id = \'' . $excs_id . '\'';
	if (!$xoopsDB -> queryF($sql)) return false;
	return true;
}
function GetDeletComment($excs_id) {
	global $xoopsDB;
	$sql = 'Select id From ' . $xoopsDB -> prefix('mexcs_excuse') . ' Where id = \'' . $excs_id . '\' And state = 4';
	if (!list($id) = $xoopsDB -> fetchRow($xoopsDB -> query($sql))) return false;
	$sql = 'Select id From ' . $xoopsDB -> prefix('mexcs_phase') . ' Where excs_id = \'' . $excs_id . '\' Order By odr DESC Limit 0, 1';
	if (!list($id) = $xoopsDB -> fetchRow($xoopsDB -> query($sql))) return false;
	$sql = 'Select * From ' . $xoopsDB -> prefix('mexcs_comment') . ' Where phse_id = \'' . $id . '\' Order by id DESC Limit 0, 1';
	if (!$record = $xoopsDB -> fetchArray($xoopsDB -> query($sql))) return false;
	return $record;
}
?>