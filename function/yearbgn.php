<?php
//year bgn
function get_excuse_years($yearbgn = 0) {
	global $xoopsDB, $xoopsModuleConfig;
	$excuse_years = array();
	if (empty($xoopsModuleConfig['mngexcuse_begin'])) $xoopsModuleConfig['mngexcuse_begin'] = mktime(0, 0, 0, 1, 1, 2008);
	$yearbgn = (empty($yearbgn)) ? $xoopsModuleConfig['year_bgn'] : $yearbgn;
	//$now_year = (mktime(0, 0, 0, $yearbgn, 1, date('Y')) > time()+2592000) ? mktime(0, 0, 0, $yearbgn, 1, date('Y')) : mktime(0, 0, 0, $yearbgn, 1, (date('Y') + 1));//提前一個月2592000看到下一年度
	$now_year = (mktime(0, 0, 0, $yearbgn, 1, date('Y')) > time()) ? mktime(0, 0, 0, $yearbgn, 1, (date('Y') + 1)) : mktime(0, 0, 0, $yearbgn, 1, (date('Y') + 2));
	while ($now_year > $xoopsModuleConfig['mngexcuse_begin']) {
		$bgn_year = date('Y', $now_year) - 1;
		$bgn_year = mktime(0, 0, 0, $yearbgn, 1, $bgn_year);
		$year = date('Y', $bgn_year) . '.' . date('n', $bgn_year) . '-' . date('Y', ($now_year-1)) . '.' . date('n', ($now_year-1));
		array_push($excuse_years, array($year, $now_year, $bgn_year));
		$now_year = $bgn_year;
	}
	return $excuse_years;
}
function GetYearbgnByUid($uid = 0) {
	global $xoopsUser;
	$uid = (empty($uid)) ? $xoopsUser -> getVar('uid') : $uid;
	$gids = ListGidsByUid($uid);
	$yearbgns = ListYearbgns();
	foreach ($gids as $val) foreach ($yearbgns as $v) if (in_array($val, $v['groupids'])) return $v['yearbgn'];
	return 0;
}
function AddYearbgn() {
	global $xoopsDB;
	escape_string_arr($_POST);
	$groupids = implode_idstring($_POST['groupids']);
	$sql = 'Insert Into ' . $xoopsDB -> prefix('mexcs_yearbgn') . ' (yearbgn, groupids) Values (\'' . intval($_POST['yearbgn']) . '\', \'' . $groupids . '\')';
	if (!$xoopsDB -> query($sql)) {
		$excsErrorMessage = 'MySQL insert fail.';
		return false;
	}
	return true;
}
function UpdateYearbgn($yearbgn) {
	global $xoopsDB;
	escape_string_arr($_POST);
	$groupids = implode_idstring($_POST['groupids']);
	$sql = 'Update ' . $xoopsDB -> prefix('mexcs_yearbgn') . ' Set yearbgn = \'' . intval($_POST['yearbgn']) . '\', groupids = \'' . $groupids . '\' Where yearbgn = \'' . $yearbgn . '\'';
	if (!$xoopsDB -> query($sql)) {
		$excsErrorMessage = 'MySQL update fail.';
		return false;
	}
	return true;
}
function DeleteYearbgn($yearbgn) {
	global $xoopsDB;
	$sql = 'Delete From ' . $xoopsDB -> prefix('mexcs_yearbgn') . ' Where yearbgn = \'' . $yearbgn . '\'';
	if (!$xoopsDB -> query($sql)) return false;
	return true;
}
function GetYearbgn($yearbgn) {
	global $xoopsDB;
	$sql = 'Select * From ' . $xoopsDB -> prefix('mexcs_yearbgn') . ' Where yearbgn = \'' . $yearbgn . '\'';
	if (!$record = $xoopsDB -> fetchArray($xoopsDB -> query($sql))) return false;
	$record['groupids'] = explode_idstring($record['groupids']);
	return $record;
}
function GetYearbgnGroupids($yearbgn) {
	global $xoopsDB, $xoopsModuleConfig;
	$sql = 'Select groupids From ' . $xoopsDB -> prefix('mexcs_yearbgn') . ' Where yearbgn = \'' . $yearbgn . '\'';
	if (!$record = $xoopsDB -> fetchArray($xoopsDB -> query($sql))) {
		$yearbgn = GetDefaultYearbgn();
		$sql = 'Select groupids From ' . $xoopsDB -> prefix('mexcs_yearbgn') . ' Where yearbgn = \'' . $yearbgn . '\'';
		if (!$record = $xoopsDB -> fetchArray($xoopsDB -> query($sql))) {
			return $xoopsModuleConfig['excuse_user'];
		} else {
			return explode_idstring($record['groupids']);
		}
	} else {
		return explode_idstring($record['groupids']);
	}
}
function GetDefaultYearbgn() {
	global $xoopsDB, $xoopsModuleConfig;
	$sql = 'Select yearbgn From ' . $xoopsDB -> prefix('mexcs_yearbgn') . ' Limit 0, 1';
	if (!$record = $xoopsDB -> fetchArray($xoopsDB -> query($sql))) {
		return $xoopsModuleConfig['year_bgn'];
	} else {
		return $record['yearbgn'];
	}
}
function ListYearbgns($listdb = false) {
	global $xoopsDB, $xoopsModuleConfig;
	$recordset = array();
	$sql = 'Select * From ' . $xoopsDB -> prefix('mexcs_yearbgn');
	if (!$result = $xoopsDB -> query($sql)) {
		if ($listdb) {
			return false;
		} else {
			return array($xoopsModuleConfig['year_bgn'] => array('yearbgn' => $xoopsModuleConfig['year_bgn'], 'groupids' => GetYearbgnGroupids(0)));
		}
	}
	while ($record = $xoopsDB -> fetchArray($result)) {
		$record['groupids'] = explode_idstring($record['groupids']);
		$recordset[$record['yearbgn']] = $record;
	}
	if (empty($recordset)) {
		if ($listdb) {
			return false;
		} else {
			return array($xoopsModuleConfig['year_bgn'] => array('yearbgn' => $xoopsModuleConfig['year_bgn'], 'groupids' => GetYearbgnGroupids(0)));
		}
	}
	return $recordset;
}
?>