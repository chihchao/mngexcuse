<?php
function CheckAuthority($type = 'admin', $authority_users = array()) {
	global $xoopsUser, $xoopsModuleConfig, $xoopsModule;
	//if admin, return true
	//if (is_object($xoopsUser) && $xoopsUser -> isAdmin()) return true;
	$groups = is_object($xoopsUser) ? $xoopsUser -> getGroups() : array(XOOPS_GROUP_ANONYMOUS);
	$uid = is_object($xoopsUser) ? $xoopsUser -> getVar('uid') : 0;
	if ($type == 'excuse_user') {
		foreach ($groups as $val) if (in_array($val, $xoopsModuleConfig['excuse_user'])) return true;
	}
	if ($type == 'excuse_info') {
		if (CheckAuthority('excuse_user')) return true;
		if (CheckAuthority('personnel')) return true;
		if (CheckAuthority('substitute_mng')) return true;
		//if ($xoopsModule -> checkAccess()) return true;
		$moduleperm_handler =& xoops_gethandler( 'groupperm' );
		if ( $moduleperm_handler -> checkRight('module_read', $xoopsModule -> getVar('mid'), $groups) ) return true;
	}
	if ($type == 'personnel') if (in_array($uid, $xoopsModuleConfig['personnel'])) return true;
	if ($type == 'substitute_mng') foreach ($groups as $val) if (in_array($val, $xoopsModuleConfig['substitute_mng'])) return true;
	return false;
}
function ListUidByGid($gid = array(), $idk = false) {
	global $xoopsDB;
	$recordset = array();
	if (empty($gid)) return false;
	$sql = implode('\' Or ' . $xoopsDB -> prefix('groups_users_link') . '.groupid = \'', $gid);
	$sql = 'Select * From ' . $xoopsDB -> prefix('groups_users_link') . ' Left Join ' . $xoopsDB -> prefix('users') . ' On ' . $xoopsDB -> prefix('groups_users_link') . '.uid = ' . $xoopsDB -> prefix('users') . '.uid Where ' . $xoopsDB -> prefix('users') . '.level > 0 And (' . $xoopsDB -> prefix('groups_users_link') . '.groupid = \'' . $sql . '\') Group By ' . $xoopsDB -> prefix('groups_users_link') . '.uid Order by ' . $xoopsDB -> prefix('users') . '.uname';
	if (!$result = $xoopsDB -> query($sql)) return false;
	if ($idk) {
		while ($record = $xoopsDB -> fetchArray($result)) $recordset[$record['uid']] = $record;
	} else {
		while ($record = $xoopsDB -> fetchArray($result)) array_push($recordset, $record);
	}
	return $recordset;
}
function ListUidByGidOnlyUid($gid = array()) {
	global $xoopsDB;
	$recordset = array();
	if (empty($gid)) return array();
	$sql = implode('\' Or ' . $xoopsDB -> prefix('groups_users_link') . '.groupid = \'', $gid);
	$sql = 'Select ' . $xoopsDB -> prefix('groups_users_link') . '.uid as uid From ' . $xoopsDB -> prefix('groups_users_link') . ' Left Join ' . $xoopsDB -> prefix('users') . ' On ' . $xoopsDB -> prefix('groups_users_link') . '.uid = ' . $xoopsDB -> prefix('users') . '.uid Where ' . $xoopsDB -> prefix('users') . '.level > 0 And (' . $xoopsDB -> prefix('groups_users_link') . '.groupid = \'' . $sql . '\') Group By ' . $xoopsDB -> prefix('groups_users_link') . '.uid Order by ' . $xoopsDB -> prefix('users') . '.uname';
	if (!$result = $xoopsDB -> query($sql)) return false;
	while ($record = $xoopsDB -> fetchArray($result)) $recordset[$record['uid']] = $record['uid'];
	return $recordset;
}

function ListGidsByUid($uid) {
	global $xoopsDB;
	$recordset = array();
	$sql = 'Select groupid From ' . $xoopsDB -> prefix('groups_users_link') . ' Where uid = ' . $uid;
	if (!$result = $xoopsDB -> query($sql)) return false;
	while ($record = $xoopsDB -> fetchArray($result)) array_push($recordset, $record['groupid']);
	return $recordset;
}
function ListGroups($idk = false) {
	global $xoopsDB;
	$recordset = array();
	$sql = 'Select * From ' . $xoopsDB -> prefix('groups');
	if (!$result = $xoopsDB -> query($sql)) return false;
	if ($idk) {
		while ($record = $xoopsDB -> fetchArray($result)) $recordset[$record['groupid']] = $record;
	} else {
		while ($record = $xoopsDB -> fetchArray($result)) array_push($recordset, $record);
	}
	return $recordset;
}
function GetUserGroupsid($exuid = 0) {
	global $xoopsUser;
	$groupids = GetGroupids();
	if (empty($exuid)) {
		$groups = is_object($xoopsUser) ? $xoopsUser -> getGroups() : array(XOOPS_GROUP_ANONYMOUS);
	} else {
		$groups = ListGidsByUid($exuid);
	}
	foreach ($groups as $val) if (in_array($val, $groupids)) return $val;
	return false;
}
function GetNameByUid($uid) {
    global $xoopsDB;
    $sql = 'Select * From ' . $xoopsDB -> prefix('users') . ' Where uid = \'' . $uid . '\'';
    if (!$record = $xoopsDB -> fetchArray($xoopsDB -> query($sql))) return '';
    return $record['name'];
}
?>