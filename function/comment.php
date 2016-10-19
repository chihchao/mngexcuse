<?php
function AddComment($phse_id) {
	global $xoopsDB, $xoopsUser;
	$uid = is_object($xoopsUser) ? $xoopsUser -> getVar('uid') : 0;
	if (empty($phse_id)) return false;
	if (ltrim($_POST['comment']) == '') return false;
	escape_string_arr($_POST);
	$sql = 'Insert Into ' . $xoopsDB -> prefix('mexcs_comment') . ' (phse_id, uid, comment, date_time) Values (\'' . $phse_id . '\', \'' . $uid . '\', \'' . $_POST['comment'] . '\', \'' . time() . '\')';
	if (!$xoopsDB -> query($sql)) return false;
	return true;
}
function GetComments($excs_id) {
	global $xoopsDB;
	$recordset = array();
	$phse_id = array();
	$sql = 'Select id From ' . $xoopsDB -> prefix('mexcs_phase') . ' Where excs_id = \'' . $excs_id . '\' Order By odr';
	if (!$result = $xoopsDB -> query($sql)) return false;
	while (list($id) = $xoopsDB -> fetchRow($result)) array_push($phse_id, $id);
	if (empty($phse_id)) return false;
	$sql = implode('\' Or phse_id = \'', $phse_id);
	$sql = 'Select * From ' . $xoopsDB -> prefix('mexcs_comment') . ' Where phse_id = \'' . $sql . '\' Order by phse_id, id ASC';
	if (!$result = $xoopsDB -> query($sql)) return false;
	while ($record = $xoopsDB -> fetchArray($result)) $recordset[$record['phse_id']][$record['id']] = $record;
	return $recordset;
}
function DeleteComments($excs_id) {
	global $xoopsDB;
	$cmnts = GetComments($excs_id);
	$cmnts_id = array();
	foreach ($cmnts as $val) foreach ($val as $v) array_push($cmnts_id, $v['id']);
	if (empty($cmnts_id)) return true;
	$sql = implode('\' Or id = \'', $cmnts_id);
	$sql = 'Delete From ' . $xoopsDB -> prefix('mexcs_comment') . ' Where id = \'' . $sql . '\'';
	if (!$xoopsDB -> queryF($sql)) return false;
	return true;
}
?>