<?php
function AddPhases($excs_id) {
	global $xoopsDB, $excsErrorMessage;
	$groupids = GetUserGroupsid();
	$phases = ListProcedures($groupids);
	$sql = '';
	//have excaped string at apply excuse
	//escape_string_arr($_POST);
	foreach ($phases as $phase)  $sql .= '(\'' . $excs_id . '\', \'' . $phase['odr'] . '\', \'' . $phase['phase'] . '\', \'' . intval($_POST['phase_checker'][$phase['odr']]) . '\', \'0\'),';
	$sql = 'Insert Into ' . $xoopsDB -> prefix('mexcs_phase') . ' (excs_id, odr, phase, checker, state) Values ' . substr($sql, 0, -1);
	if (!$xoopsDB -> query($sql)) {
		$excsErrorMessage = 'MySQL insert fail.';
		return false;
	}
	return true;
}
function UpdatePhaseCheckers($excs_id) {
	global $xoopsDB, $excsErrorMessage;
	foreach ($_POST['phase_checker'] as $key => $val) {
		$sql = 'Update ' . $xoopsDB -> prefix('mexcs_phase') . ' Set checker = \'' . intval($_POST['phase_checker'][$key]) . '\' Where excs_id = \'' . $excs_id . '\' And odr = \'' . $key . '\'';
		if (!$xoopsDB -> query($sql)) $excsErrorMessage = 'MySQL update fail.';
	}
	return false;
}
function SetPhaseState($excs_id, $type, $phse_id = 0) {
	global $xoopsDB, $xoopsModuleConfig;
	if ($type == 'begin') {
		if ($xoopsModuleConfig['according_order']) {
			if ($xoopsModuleConfig['mail_info'] && $xoopsModuleConfig['mail_checker']) {
				$emails = array();
				$sql = 'Select checker From ' . $xoopsDB -> prefix('mexcs_phase') . ' Where excs_id = \'' . $excs_id . '\' And odr = 0';
				list($checker) = $xoopsDB -> fetchRow($xoopsDB -> query($sql));
				array_push($emails, $checker);
			}
			$sql = 'Update ' . $xoopsDB -> prefix('mexcs_phase') . ' Set state = 1 Where excs_id = \'' . $excs_id . '\' And odr = 0';
		} else {
			if ($xoopsModuleConfig['mail_info'] && $xoopsModuleConfig['mail_checker']) {
				$emails = array();
				$sql = 'Select checker From ' . $xoopsDB -> prefix('mexcs_phase') . ' Where excs_id = \'' . $excs_id . '\'';
				$result = $xoopsDB -> query($sql);
				while (list($checker) = $xoopsDB -> fetchRow($result)) array_push($emails, $checker);
			}
			$sql = 'Update ' . $xoopsDB -> prefix('mexcs_phase') . ' Set state = 1 Where excs_id = \'' . $excs_id . '\'';
		}
		if (!$xoopsDB -> queryF($sql)) return false;
		if ($xoopsModuleConfig['mail_info'] && $xoopsModuleConfig['mail_checker']) mail_info('checker', array('emails' => $emails, 'excs_id' => $excs_id));
	} elseif ($type == 'pass') {
		if ($xoopsModuleConfig['according_order']) {
			if ($xoopsModuleConfig['mail_info'] && $xoopsModuleConfig['mail_user']) {
				$sql = 'Select checker From ' . $xoopsDB -> prefix('mexcs_phase') . ' Where id = \'' . $phse_id . '\'';
				list($checker) = $xoopsDB -> fetchRow($xoopsDB -> query($sql));
			}
			$sql = 'Update ' . $xoopsDB -> prefix('mexcs_phase') . ' Set state = 3, date_time = \'' . time() . '\' Where id = \'' . $phse_id . '\'';
			if (!$xoopsDB -> queryF($sql)) return false;
			if ($xoopsModuleConfig['mail_info'] && $xoopsModuleConfig['mail_user']) mail_info('user_pass', array('checker' => $checker, 'excs_id' => $excs_id));
			$sql = 'Select odr From ' . $xoopsDB -> prefix('mexcs_phase') . ' Where id = \'' . $phse_id . '\'';
			if (!list($odr) = $xoopsDB -> fetchRow($xoopsDB -> query($sql))) return false;
			$odr = $odr + 1;
			if (GetTotalPhases($excs_id) == $odr) {
				//last phase pass
				$sql = 'Update ' . $xoopsDB -> prefix('mexcs_excuse') . ' Set state = 3 Where id = \'' . $excs_id . '\'';
			} else {
				if ($xoopsModuleConfig['mail_info'] && $xoopsModuleConfig['mail_checker']) {
					$emails = array();
					$sql = 'Select checker From ' . $xoopsDB -> prefix('mexcs_phase') . ' Where excs_id = \'' . $excs_id . '\' And odr = \'' . $odr . '\'';
					list($checker) = $xoopsDB -> fetchRow($xoopsDB -> query($sql));
					array_push($emails, $checker);
				}
				if ($xoopsModuleConfig['mail_info'] && $xoopsModuleConfig['mail_checker']) mail_info('checker', array('emails' => $emails, 'excs_id' => $excs_id));
				$sql = 'Update ' . $xoopsDB -> prefix('mexcs_phase') . ' Set state = 1 Where excs_id = \'' . $excs_id . '\' And odr = \'' . $odr . '\'';
			}
			if (!$xoopsDB -> queryF($sql)) return false;
		} else {
			if ($xoopsModuleConfig['mail_info'] && $xoopsModuleConfig['mail_user']) {
				$sql = 'Select checker From ' . $xoopsDB -> prefix('mexcs_phase') . ' Where id = \'' . $phse_id . '\'';
				list($checker) = $xoopsDB -> fetchRow($xoopsDB -> query($sql));
			}
			$sql = 'Update ' . $xoopsDB -> prefix('mexcs_phase') . ' Set state = 3, date_time = \'' . time() . '\' Where id = \'' . $phse_id . '\'';
			if (!$xoopsDB -> queryF($sql)) return false;
			if ($xoopsModuleConfig['mail_info'] && $xoopsModuleConfig['mail_user']) mail_info('user_pass', array('checker' => $checker, 'excs_id' => $excs_id));
			$sql = 'Select count(1) From ' . $xoopsDB -> prefix('mexcs_phase') . ' Where excs_id = \'' . $excs_id . '\' And state != 3';
			if (!list($count) = $xoopsDB -> fetchRow($xoopsDB -> query($sql))) return false;
			if (empty($count)) {
				$sql = 'Update ' . $xoopsDB -> prefix('mexcs_excuse') . ' Set state = 3 Where id = \'' . $excs_id . '\'';
				if (!$xoopsDB -> queryF($sql)) return false;
			}
		}
	} elseif ($type == 'comment') {
		if ($xoopsModuleConfig['mail_info'] && $xoopsModuleConfig['mail_user']) {
			$sql = 'Select checker From ' . $xoopsDB -> prefix('mexcs_phase') . ' Where id = \'' . $phse_id . '\'';
			list($checker) = $xoopsDB -> fetchRow($xoopsDB -> query($sql));
		}
		$sql = 'Update ' . $xoopsDB -> prefix('mexcs_phase') . ' Set state = 2, date_time = \'' . time() . '\' Where id = \'' . $phse_id . '\'';
		if (!$xoopsDB -> queryF($sql)) return false;
		if ($xoopsModuleConfig['mail_info'] && $xoopsModuleConfig['mail_user']) mail_info('user_comment', array('checker' => $checker, 'excs_id' => $excs_id));
	} elseif ($type == 'continue') {
		if ($xoopsModuleConfig['mail_info'] && $xoopsModuleConfig['mail_checker']) {
			$emails = array();
			$sql = 'Select checker From ' . $xoopsDB -> prefix('mexcs_phase') . ' Where excs_id = \'' . $excs_id . '\' And state = 2';
			$result = $xoopsDB -> query($sql);
			while (list($checker) = $xoopsDB -> fetchRow($result)) array_push($emails, $checker);
		}
		$sql = 'Update ' . $xoopsDB -> prefix('mexcs_phase') . ' Set state = 1, date_time = \'' . time() . '\' Where excs_id = \'' . $excs_id . '\' And state = 2';
		if (!$xoopsDB -> queryF($sql)) return false;
		if ($xoopsModuleConfig['mail_info'] && $xoopsModuleConfig['mail_checker']) mail_info('checker_continue', array('emails' => $emails, 'excs_id' => $excs_id));
	} elseif ($type == 'reject') {
		if ($xoopsModuleConfig['mail_info'] && $xoopsModuleConfig['mail_user']) {
			$sql = 'Select checker From ' . $xoopsDB -> prefix('mexcs_phase') . ' Where id = \'' . $phse_id . '\'';
			list($checker) = $xoopsDB -> fetchRow($xoopsDB -> query($sql));
		}
		$sql = 'Update ' . $xoopsDB -> prefix('mexcs_phase') . ' Set state = 4, date_time = \'' . time() . '\' Where id = \'' . $phse_id . '\'';
		if (!$xoopsDB -> queryF($sql)) return false;
		$sql = 'Update ' . $xoopsDB -> prefix('mexcs_excuse') . ' Set state = 2 Where id = \'' . $excs_id . '\'';
		if (!$xoopsDB -> queryF($sql)) return false;
		if ($xoopsModuleConfig['mail_info'] && $xoopsModuleConfig['mail_user']) mail_info('user_reject', array('checker' => $checker, 'excs_id' => $excs_id));
	} else {
		return false;
	}
	return true;
}
function GetPhases($excs_id) {
	global $xoopsDB;
	$recordset = array();
	$sql = 'Select * From ' . $xoopsDB -> prefix('mexcs_phase') . ' Where excs_id = \'' . $excs_id . '\' Order By odr ASC';
	if (!$result = $xoopsDB -> query($sql)) return false;
	while ($record = $xoopsDB -> fetchArray($result)) $recordset[$record['id']] = $record;
	return $recordset;
}
function GetTotalPhases($excs_id = 0) {
	global $xoopsDB;
	$sql = ($excs_id) ? 'Select count(1) From ' . $xoopsDB -> prefix('mexcs_phase') . ' Where excs_id = \'' . $excs_id . '\'' : 'Select count(1) From ' . $xoopsDB -> prefix('mexcs_procedure');
	if (!list($count) = $xoopsDB -> fetchRow($xoopsDB -> query($sql))) return false;
	return $count;
}
function DeletePhases($excs_id) {
	global $xoopsDB;
	$sql = 'Delete From ' . $xoopsDB -> prefix('mexcs_phase') . ' Where excs_id = \'' . $excs_id . '\'';
	if (!$xoopsDB -> queryF($sql)) return false;
	return true;
}
?>