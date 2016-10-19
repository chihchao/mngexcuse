<?php
function AddDocument() {
	global $xoopsDB, $xoopsUser, $excsErrorMessage;
	$uid = is_object($xoopsUser) ? $xoopsUser -> getVar('uid') : 0;
	if (ltrim($_POST['title']) == '') {
		$excsErrorMessage = 'Empty title.';
		return false;
	}
	$_POST['title'] = htmlspecialchars($_POST['title']);
	escape_string_arr($_POST);
	$_POST['uids'] = empty($_POST['uids']) ? array() : $_POST['uids'];
	$sql = 'Insert Into ' . $xoopsDB -> prefix('mexcs_document') . ' (uid, title, description, uids, date_excuse) Values (\'' . $uid . '\', \'' . $_POST['title'] . '\', \'' . $_POST['description'] . '\', \'' . implode_idstring($_POST['uids']) . '\', \'' . mktime (0, 0, 0, $_POST['date_excuse_Month'], $_POST['date_excuse_Day'], $_POST['date_excuse_Year']) . '\')';
	if (!$xoopsDB -> query($sql)) {
		$excsErrorMessage = 'MySQL insert fail.';
		return false;
	}
	if (!$id = $xoopsDB -> getInsertId()) return false;
	return $id;
}
function UpdateDocument($dcmt_id) {
	global $xoopsDB, $excsErrorMessage;
	if (ltrim($_POST['title']) == '') {
		$excsErrorMessage = 'Empty title.';
		return false;
	}
	$_POST['title'] = htmlspecialchars($_POST['title']);
	escape_string_arr($_POST);
	$sql = 'Update ' . $xoopsDB -> prefix('mexcs_document') . ' Set title = \'' . $_POST['title'] . '\', description = \'' . $_POST['description'] . '\', uids = \'' . implode_idstring($_POST['uids']) . '\', date_excuse = \'' . mktime (0, 0, 0, $_POST['date_excuse_Month'], $_POST['date_excuse_Day'], $_POST['date_excuse_Year']) . '\' Where id = \'' . intval($dcmt_id) . '\'';
	if (!$xoopsDB -> query($sql)) {
		$excsErrorMessage = 'MySQL update fail.';
		return false;
	}
	return true;
}
function DeleteDocument($dcmt_id) {
	global $xoopsDB, $excsErrorMessage;
	$files = GetFiles($dcmt_id);
	foreach ($files as $file) DeleteFile($file['id']);
	$sql = 'Delete From ' . $xoopsDB -> prefix('mexcs_document') . ' Where id = \'' . $dcmt_id . '\'';
	if (!$xoopsDB -> query($sql)) {
		$excsErrorMessage = 'MySQL delete fail.';
		return false;
	}
	$sql = 'Update ' . $xoopsDB -> prefix('mexcs_excuse') . ' Set dcmt_id = \'0\' Where dcmt_id = \'' . $dcmt_id . '\'';
	if (!$xoopsDB -> query($sql)) {
		$excsErrorMessage = 'MySQL update fail.';
		return false;
	}
	return true;
}
function GetDocument($dcmt_id) {
	global $xoopsDB, $excsErrorMessage;
	$sql = 'Select * From ' . $xoopsDB -> prefix('mexcs_document') . ' Where id = \'' . intval($dcmt_id) . '\'';
	if (!$record = $xoopsDB -> fetchArray($xoopsDB -> query($sql))) {
		$excsErrorMessage = 'No document.';
		return false;
	}
	$record['uids'] = explode_idstring($record['uids']);
	return $record;
}
function ListDocuments($page = 0, $idk = false, $uid = -1) {
	global $xoopsDB, $xoopsUser, $xoopsModuleConfig;
	$recordset = array();
	$uid = ($uid < 0) ? (is_object($xoopsUser) ? $xoopsUser -> getVar('uid') : 0) : $uid;
	if ($page >= 0) {
		$page = $page * $xoopsModuleConfig['per_page'];
		$sql = 'Select * From ' . $xoopsDB -> prefix('mexcs_document') . ' Where uid = \'' . $uid . '\' Or uids like \'%[' . $uid . ']%\' Order By date_excuse DESC Limit ' . $page . ', ' . $xoopsModuleConfig['per_page'];
	} else {
		$period = time() - ($xoopsModuleConfig['document_period'] * 86400);
		$sql = 'Select * From ' . $xoopsDB -> prefix('mexcs_document') . ' Where (uid = \'' . $uid . '\' Or uids like \'%[' . $uid . ']%\') And date_excuse > \'' . $period . '\'Order By date_excuse DESC';
	}
	if (!$result = $xoopsDB -> query($sql)) return false;
	if ($idk) {
		while ($record = $xoopsDB -> fetchArray($result)) {
			$record['uids'] = explode_idstring($record['uids']);
			$recordset[$record['id']] = $record;
		}
	} else {
		while ($record = $xoopsDB -> fetchArray($result)) {
			$record['uids'] = explode_idstring($record['uids']);
			array_push($recordset, $record);
		}
	}
	return $recordset;
}
function CountDocuments() {
	global $xoopsDB, $xoopsUser;
	$uid = is_object($xoopsUser) ? $xoopsUser -> getVar('uid') : 0;
	$sql = 'Select count(1) From ' . $xoopsDB -> prefix('mexcs_document') . ' Where uid = \'' . $uid . '\' Or uids like \'%[' . $uid . ']%\'';
	if (!list($count) = $xoopsDB -> fetchRow($xoopsDB -> query($sql))) return false;
	return $count;
}
?>