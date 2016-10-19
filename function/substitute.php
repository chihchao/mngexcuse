<?php
function EditSubstitute($excs_id) {
	global $xoopsDB, $excsErrorMessage;
	$sql = 'Select count(1) From ' . $xoopsDB -> prefix('mexcs_substitute') . ' Where excs_id = \'' . $excs_id . '\'';
	if (!list($count) = $xoopsDB -> fetchRow($xoopsDB -> query($sql))) return false;
	//have excaped string at apply excuse
	//escape_string_arr($_POST);
	if ($_POST['substitute'] && $count) {
		$sql = 'Update ' . $xoopsDB -> prefix('mexcs_substitute') . ' Set description = \'' . $_POST['substitute_desc'] . '\' Where excs_id = \'' . $excs_id . '\'';
	} elseif ($_POST['substitute'] && !$count) {
		$sql = 'Insert Into ' . $xoopsDB -> prefix('mexcs_substitute') . ' (excs_id, description, state) Values (\'' . $excs_id . '\', \'' . $_POST['substitute_desc'] . '\', \'0\')';
	} elseif (!$_POST['substitute'] && $count) {
		$sql = 'Delete From ' . $xoopsDB -> prefix('mexcs_substitute') . ' Where excs_id = \'' . $excs_id . '\'';
	} else {
		return true;
	}
	if (!$xoopsDB -> queryF($sql)) {
		$excsErrorMessage = 'MySQL insert or update fail.';
		return false;
	}
	return true;
}
function EditSubstituteComment($excs_id) {
	global $xoopsDB;
	$sql = 'Select count(1) From ' . $xoopsDB -> prefix('mexcs_substitute') . ' Where excs_id = \'' . $excs_id . '\'';
	if (!list($count) = $xoopsDB -> fetchRow($xoopsDB -> query($sql))) return false;
	escape_string_arr($_POST);
	if ($count) {
		$sql = 'Update ' . $xoopsDB -> prefix('mexcs_substitute') . ' Set comment = \'' . $_POST['comment'] . '\' Where excs_id = \'' . $excs_id . '\'';
	} else {
		$sql = 'Insert Into ' . $xoopsDB -> prefix('mexcs_substitute') . ' (excs_id, description, comment, state) Values (\'' . $excs_id . '\', \'\', \'' . $_POST['comment'] . '\', \'0\')';
	}
	if (!$xoopsDB -> queryF($sql)) {
		$excsErrorMessage = 'MySQL insert fail.';
		return false;
	}
	return true;
}
function GetSubstitute($excs_id) {
	global $xoopsDB;
	$sql = 'Select * From ' . $xoopsDB -> prefix('mexcs_substitute') . ' Where excs_id = \'' . $excs_id . '\'';
	if (!$record = $xoopsDB -> fetchArray($xoopsDB -> query($sql))) return false;
	return $record;
}
function GetSubstitutesJoinExcuses($uid = 0, $year = 0, $page = 0, $type = '', $yearbgn = 0) {
	global $xoopsDB, $xoopsModuleConfig;
	$recordset = array();
	$tb_excs = $xoopsDB -> prefix('mexcs_excuse');
	$tb_sbtt = $xoopsDB -> prefix('mexcs_substitute');
	$yearbgn = (empty($yearbgn)) ? GetDefaultYearbgn() : $yearbgn;
	$years = get_excuse_years($yearbgn);
	$year = ' '.$tb_excs.'.date_bgn < \'' . $years[$year][1] . '\' And '.$tb_excs.'.date_bgn > \'' . $years[$year][2] . '\'';
	$uid = ($uid) ? ' And '.$tb_excs.'.uid = \'' . $uid . '\'' : ' And ( '.$tb_excs.'.uid = \'' . implode('\' Or '.$tb_excs.'.uid = \'', ListUidByGidOnlyUid(GetYearbgnGroupids($yearbgn))) . '\')';
	$page = ($page < 0) ? '' : ' Limit ' . ($page * $xoopsModuleConfig['per_page']) . ', ' . $xoopsModuleConfig['per_page'];
	$type = (empty($type) || !in_array($type, explode('|', $xoopsModuleConfig['excuse_type']))) ? '' : ' And '.$tb_excs.'.excuse_type = \'' . escape_string($type) . '\'';
	$sql = ' '.$tb_sbtt.'.excs_id, '.$tb_sbtt.'.description as sbtt_description, '.$tb_sbtt.'.comment, '.$tb_sbtt.'.state as sbtt_state, '.$tb_excs.'.id, '.$tb_excs.'.uid, '.$tb_excs.'.appointment, '.$tb_excs.'.excuse_type, '.$tb_excs.'.description, '.$tb_excs.'.date_bgn, '.$tb_excs.'.date_end, '.$tb_excs.'.date_count_day, '.$tb_excs.'.date_count_hour, '.$tb_excs.'.dcmt_id, '.$tb_excs.'.date_time, '.$tb_excs.'.state';
	$sql = 'Select ' . $sql . ' From '.$tb_sbtt.' Left Join '.$tb_excs.' On '.$tb_sbtt.'.excs_id = '.$tb_excs.'.id Where' . $year . $uid . $type . ' Order By '.$tb_excs.'.date_bgn DESC, '.$tb_excs.'.id DESC' . $page;
	if (!$result = $xoopsDB -> query($sql)) return false;
	while ($record = $xoopsDB -> fetchArray($result)) $recordset[$record['id']] = $record;
	return $recordset;
}
function CountSubstitutesJoinExcuses($uid = 0, $year = 0, $type = '', $yearbgn = 0) {
	global $xoopsDB, $xoopsModuleConfig;
	$tb_excs = $xoopsDB -> prefix('mexcs_excuse');
	$tb_sbtt = $xoopsDB -> prefix('mexcs_substitute');
	$yearbgn = (empty($yearbgn)) ? GetDefaultYearbgn() : $yearbgn;
	$years = get_excuse_years($yearbgn);
	$year = ' '.$tb_excs.'.date_bgn < \'' . $years[$year][1] . '\' And '.$tb_excs.'.date_bgn > \'' . $years[$year][2] . '\'';
	$uid = ($uid) ? ' And '.$tb_excs.'.uid = \'' . $uid . '\'' : ' And ( '.$tb_excs.'.uid = \'' . implode('\' Or '.$tb_excs.'.uid = \'', ListUidByGidOnlyUid(GetYearbgnGroupids($yearbgn))) . '\')';
	$type = (empty($type) || !in_array($type, explode('|', $xoopsModuleConfig['excuse_type']))) ? '' : ' And '.$tb_excs.'.excuse_type = \'' . escape_string($type) . '\'';
	$sql = ' '.$tb_sbtt.'.excs_id, '.$tb_sbtt.'.description as sbtt_description, '.$tb_sbtt.'.comment, '.$tb_sbtt.'.state as sbtt_state, '.$tb_excs.'.id, '.$tb_excs.'.uid, '.$tb_excs.'.appointment, '.$tb_excs.'.excuse_type, '.$tb_excs.'.description, '.$tb_excs.'.date_bgn, '.$tb_excs.'.date_end, '.$tb_excs.'.date_count_day, '.$tb_excs.'.date_count_hour, '.$tb_excs.'.dcmt_id, '.$tb_excs.'.date_time, '.$tb_excs.'.state';
	$sql = 'Select count(1) From '.$tb_sbtt.' Left Join '.$tb_excs.' On '.$tb_sbtt.'.excs_id = '.$tb_excs.'.id Where' . $year . $uid . $type;
	if (!list($count) = $xoopsDB -> fetchRow($xoopsDB -> query($sql))) return false;
	return $count;
}
function DeleteSubstitute($excs_id) {
	global $xoopsDB;
	$sql = 'Delete From ' . $xoopsDB -> prefix('mexcs_substitute') . ' Where excs_id = \'' . $excs_id . '\'';
	if (!$xoopsDB -> queryF($sql)) return false;
	return true;
}
?>