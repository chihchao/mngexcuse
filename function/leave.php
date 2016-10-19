<?php
function DealLeave($year, $uid) {
	global $xoopsDB, $xoopsModuleConfig, $excsErrorMessage;
	$excuse_years = get_excuse_years(GetYearbgnByUid($uid));
	$excuse_types = explode('|', $xoopsModuleConfig['excuse_type']);
	escape_string_arr($_POST);
	foreach ($excuse_types as $key => $val) {
		$_POST['day'][$key] = empty($_POST['day'][$key]) ? 0 : $_POST['day'][$key];
		$_POST['hour'][$key] = empty($_POST['hour'][$key]) ? 0 : $_POST['hour'][$key];
		$sql = 'Select count(1) From ' . $xoopsDB -> prefix('mexcs_leave') . ' Where year_bgn = \'' . $excuse_years[$year][1] . '\' And year_end =\'' . $excuse_years[$year][2] . '\' And uid = \'' . $uid . '\' And excuse_type = \'' . $val . '\'';
		if (!list($count) = $xoopsDB -> fetchRow($xoopsDB -> query($sql))) return false;
		if (empty($count)) {
			$sql = 'Insert Into ' . $xoopsDB -> prefix('mexcs_leave') . ' (year_bgn, year_end, uid, excuse_type, leave_day, leave_hour) Values (\'' . $excuse_years[$year][1] . '\', \'' . $excuse_years[$year][2] . '\', \'' . $uid . '\', \'' . $val . '\', \'' . $_POST['day'][$key] . '\', \'' . $_POST['hour'][$key] . '\')';
			if (!$xoopsDB -> query($sql)) $excsErrorMessage = 'MySQL insert fail.';
		} else {
			$sql = 'Update ' . $xoopsDB -> prefix('mexcs_leave') . ' Set leave_day = \'' . $_POST['day'][$key] . '\', leave_hour = \'' . $_POST['hour'][$key] . '\' Where year_bgn = \'' . $excuse_years[$year][1] . '\' And year_end =\'' . $excuse_years[$year][2] . '\' And uid = \'' . $uid . '\' And excuse_type = \'' . $val . '\'';
			if (!$xoopsDB -> queryF($sql)) $excsErrorMessage = 'MySQL update fail.';
		}
	}
	if (empty($excsErrorMessage)) {
		return true;
	} else {
		return false;
	}
}
function GetLeave($year, $uid) {
	global $xoopsDB;
	$recordset = array();
	$excuse_years = get_excuse_years(GetYearbgnByUid($uid));
	$sql = 'Select * From ' . $xoopsDB -> prefix('mexcs_leave') . ' Where year_bgn = \'' . $excuse_years[$year][1] . '\' And year_end =\'' . $excuse_years[$year][2] . '\' And uid = \'' . $uid . '\'';
	if (!$result = $xoopsDB -> query($sql)) return false;
	while ($record = $xoopsDB -> fetchArray($result)) $recordset[$record['excuse_type']] = array($record['leave_day'], $record['leave_hour']);
	return $recordset;
}
function GetLeaves($year, $yearbgn = 0) {
	global $xoopsDB;
	$recordset = array();
	$yearbgn = (empty($yearbgn)) ? GetDefaultYearbgn() : $yearbgn;
	$excuse_years = get_excuse_years($yearbgn);
	$sql = 'Select * From ' . $xoopsDB -> prefix('mexcs_leave') . ' Where year_bgn = \'' . $excuse_years[$year][1] . '\' And year_end =\'' . $excuse_years[$year][2] . '\'';
	if (!$result = $xoopsDB -> query($sql)) return false;
	while ($record = $xoopsDB -> fetchArray($result)) $recordset[$record['uid']][$record['excuse_type']] = array($record['leave_day'], $record['leave_hour']);
	return $recordset;
}
?>