<?php
function AddProcedure() {
	global $xoopsDB, $excsErrorMessage;
	$sql = 'Select count(id) From ' . $xoopsDB -> prefix('mexcs_procedure') . ' Where groupids = ' . intval($_POST['groupids']) . '';
	if (!list($count) = $xoopsDB -> fetchRow($xoopsDB -> query($sql))) $count = 0;
	if (ltrim($_POST['phase']) == '') {
		$excsErrorMessage = 'Empty phase.';
		return false;
	}
	$_POST['phase'] = htmlspecialchars($_POST['phase']);
	escape_string_arr($_POST);
	$checkers = (intval($_POST['gou'])) ? implode_idstring($_POST['gid']) : implode_idstring($_POST['uid']);
	$sql = 'Insert Into ' . $xoopsDB -> prefix('mexcs_procedure') . ' (groupids, odr, phase, gou, checkers) Values (\'' . intval($_POST['groupids']) . '\', \'' . $count . '\', \'' . $_POST['phase'] . '\', \'' . intval($_POST['gou']) . '\', \'' . $checkers . '\')';
	if (!$xoopsDB -> query($sql)) {
		$excsErrorMessage = 'MySQL insert fail.';
		return false;
	}
	return true;
}
function UpdateProcedure($prcd_id) {
	global $xoopsDB, $excsErrorMessage;
	if (ltrim($_POST['phase']) == '') {
		$excsErrorMessage = 'Empty phase.';
		return false;
	}
	$_POST['phase'] = htmlspecialchars($_POST['phase']);
	escape_string_arr($_POST);
	$checkers = (intval($_POST['gou'])) ? implode_idstring($_POST['gid']) : implode_idstring($_POST['uid']);
	$sql = 'Update ' . $xoopsDB -> prefix('mexcs_procedure') . ' Set phase = \'' . $_POST['phase'] . '\', gou = \'' . $_POST['gou'] . '\', checkers = \'' . $checkers . '\' Where id = \'' . $prcd_id . '\'';
	if (!$xoopsDB -> query($sql)) {
		$excsErrorMessage = 'MySQL update fail.';
		return false;
	}
	return true;
}
function DeleteProcedure($prcd_id) {
	global $xoopsDB;
	$sql = 'Select groupids, odr From ' . $xoopsDB -> prefix('mexcs_procedure') . ' Where id = \'' . $prcd_id . '\'';
	if (!list($groupids, $odr) = $xoopsDB -> fetchRow($xoopsDB -> query($sql))) return false;
	$sql = 'Update ' . $xoopsDB -> prefix('mexcs_procedure') . ' Set odr = odr - 1 Where odr > ' . $odr . ' And groupids = ' . $groupids;
	if (!$xoopsDB -> queryF($sql)) return false;
	$sql = 'Delete From ' . $xoopsDB -> prefix('mexcs_procedure') . ' Where id = \'' . $prcd_id . '\'';
	if (!$xoopsDB -> query($sql)) return false;
	return true;
}
function UpdateProcedureOrder($prcd_id, $fob) {
	global $xoopsDB, $excsErrorMessage;
	$fob = ($fob == 'frw') ? -1 : 1;
	$sql = 'Select groupids, odr From ' . $xoopsDB -> prefix('mexcs_procedure') . ' Where id = \'' . $prcd_id . '\'';
	if (!list($groupids, $odr) = $xoopsDB -> fetchRow($xoopsDB -> query($sql))) return false;
	$fob = $odr + $fob;
	$sql = 'Select id From ' . $xoopsDB -> prefix('mexcs_procedure') . ' Where odr = \'' . $fob . '\' And groupids = ' . $groupids;
	if (!list($id) = $xoopsDB -> fetchRow($xoopsDB -> query($sql))) return false;
	$sql = 'Update ' . $xoopsDB -> prefix('mexcs_procedure') . ' Set odr = \'' . $fob . '\' Where id = \'' . $prcd_id . '\'';
	if (!$xoopsDB -> queryF($sql)) return false;
	$sql = 'Update ' . $xoopsDB -> prefix('mexcs_procedure') . ' Set odr = \'' . $odr . '\' Where id = \'' . $id . '\'';
	if (!$xoopsDB -> queryF($sql)) return false;
	return true;
}
function GetProcedure($prcd_id) {
	global $xoopsDB;
	$sql = 'Select * From ' . $xoopsDB -> prefix('mexcs_procedure') . ' Where id = \'' . $prcd_id . '\'';
	if (!$record = $xoopsDB -> fetchArray($xoopsDB -> query($sql))) return false;
	$record['checkers'] = explode_idstring($record['checkers']);
	return $record;
}
function GetGroupids() {
	global $xoopsDB;
	$recordset = array();
	$sql = 'Select groupids From ' . $xoopsDB -> prefix('mexcs_procedure') . ' Group By groupids Order By groupids';
	if (!$result = $xoopsDB -> query($sql)) return false;
	while (list($record) = $xoopsDB -> fetchRow($result)) array_push($recordset, $record);
	return $recordset;
}
function ListProcedures($groupids = 0, $idk = false) {
	global $xoopsDB;
	$recordset = array();
	if (empty($groupids)) {
		$sql = 'Select * From ' . $xoopsDB -> prefix('mexcs_procedure') . ' Order By groupids, odr, id';
	} else {
		$sql = 'Select * From ' . $xoopsDB -> prefix('mexcs_procedure') . ' Where groupids = ' . $groupids . ' Order By odr, id';
	}
	if (!$result = $xoopsDB -> query($sql)) return false;
	if ($idk) {
		while ($record = $xoopsDB -> fetchArray($result)) {
			$record['checkers'] = explode_idstring($record['checkers']);
			$recordset[$record['id']] = $record;
		}
	} else {
		while ($record = $xoopsDB -> fetchArray($result)) {
			$record['checkers'] = explode_idstring($record['checkers']);
			array_push($recordset, $record);
		}
	}
	return $recordset;
}
function GetProcedureCheckers(&$phases) {
	global $xoopsModuleConfig;
	foreach ($phases as $key => $val) {
		$phases[$key]['checkers'] = array(0 => '');
		if ($val['gou']) {
			$val['checkers'] = ListUidByGid($val['checkers'], true);
			foreach ($val['checkers'] as $k => $v) $phases[$key]['checkers'][$k] = XoopsUser::getUnameFromId($k,$xoopsModuleConfig['user_name']);
		} else {
			foreach ($val['checkers'] as $k => $v) $phases[$key]['checkers'][$v] = XoopsUser::getUnameFromId($v,$xoopsModuleConfig['user_name']);
		}
	}
}
?>