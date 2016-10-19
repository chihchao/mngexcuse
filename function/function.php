<?php
//error message
function get_error_message(){
	global $excsErrorMessage;
	switch ($excsErrorMessage) {
		case 'Empty phase.':
			return _AD_MEXCS_ERRMESSAGE_EMPTYPHASE;
		break;
		case 'Empty title.':
			return _MD_MEXCS_ERRMESSAGE_EMPTYTITLE;
		break;
		case 'No document.':
			return _MD_MEXCS_ERRMESSAGE_NODOCUMENT;
		break;
		case 'No upload path.':
			return _MD_MEXCS_ERRMESSAGE_NOUPLOADPATH;
		break;
		case 'Delete file fail.':
			return _MD_MEXCS_ERRMESSAGE_DELETEFILEFAIL;
		break;
		case 'No phase checker.':
			return _MD_MEXCS_ERRMESSAGE_NOPHASECHECKER;
		break;
		case 'No date count.':
			return _MD_MEXCS_ERRMESSAGE_NODATECOUNT;
		break;
		case 'No excuse.':
			return _MD_MEXCS_ERRMESSAGE_NOEXCUSE;
		break;
		default:
			return $excsErrorMessage;
		break;
	}
}
//deal slashes problem, set magic_quotes_gpc off
function setoff_magic_quotes_gpc() {
	if (get_magic_quotes_gpc()) {
		function traverse(&$arr) {
			if (!is_array($arr)) return;
			foreach ($arr as $key => $val) is_array ($arr[$key]) ? traverse($arr[$key]) : ($arr[$key] = stripslashes ($arr[$key]));
		}
		$gpc = array( &$_GET, &$_POST, &$_COOKIE );
		traverse($gpc);
	}
}
//escape string for array data
function escape_string($string) {
	if (function_exists('mysql_real_escape_string')) {
		$string = mysql_real_escape_string($string);
	} elseif (function_exists('mysql_escape_string')) {
		$string = mysql_escape_string($string);
	} else {
		$string = addslashes($string);
	}
	return $string;
}
function escape_string_arr(&$arr) { escape_string_arr_trv($arr); }
function escape_string_arr_trv(&$arr) {
	if (!is_array($arr)) return;
	foreach ($arr as $key => $val) is_array($arr[$key]) ? escape_string_arr_trv($arr[$key]) : ($arr[$key] = escape_string($arr[$key]));
}
//split a id string, string like [*][*][*]...
function explode_idstring($id_string) {
	if (empty($id_string)) {
		$id_string = array();
	} else {
		$id_string = explode('][', substr($id_string, 1, -1));
		foreach($id_string as $key => $val) $id_string[$key] = intval($val);
		$id_string = '[' . implode('][', array_unique($id_string)) . ']';
		$id_string = explode('][', substr($id_string, 1, -1));
	}
	return $id_string;
}
//join a id string
function implode_idstring($id_array) {
	if (!is_array($id_array)) $id_array = array();
	if (empty($id_array)) {
		$id_array = '';
	} else {
		foreach($id_array as $key => $val) $id_array[$key] = intval($val);
		$id_array = '[' . implode('][', array_unique($id_array)) . ']';
	}
	return $id_array;
}
//return phase state string
function get_phase_state_string($state) {
	switch ($state) {
		case '0':
			return _MD_MEXCS_EXCUSE_PHASE_STATE_0;
		break;
		case '1':
			return _MD_MEXCS_EXCUSE_PHASE_STATE_1;
		break;
		case '2':
			return _MD_MEXCS_EXCUSE_PHASE_STATE_2;
		break;
		case '3':
			return _MD_MEXCS_EXCUSE_PHASE_STATE_3;
		break;
		case '4':
			return _MD_MEXCS_EXCUSE_PHASE_STATE_4;
		break;
	}
}
//return phase state string
function get_excuse_state_string($state) {
	switch ($state) {
		case '0':
			return _MD_MEXCS_EXCUSE_STATE_0;
		break;
		case '1':
			return _MD_MEXCS_EXCUSE_STATE_1;
		break;
		case '2':
			return _MD_MEXCS_EXCUSE_STATE_2;
		break;
		case '3':
			return _MD_MEXCS_EXCUSE_STATE_3;
		break;
		case '4':
			return _MD_MEXCS_EXCUSE_STATE_4;
		break;
	}
}
function get_upload_message() {
	global $excsErrorMessage;
	$str = array(
		'No upload file.' => _MD_MEXCS_ERRMESSAGE_NOUPLOADFILE,
		'File type error.' => _MD_MEXCS_ERRMESSAGE_FILETYPEERROR,
		'Upload success.' => _MD_MEXCS_ERRMESSAGE_UPLOADSUCCESS,
	);
	foreach ($str as $key => $val) $excsErrorMessage = str_replace($key, $val, $excsErrorMessage);
	return $excsErrorMessage;
}
function get_count_leave($count, $leave) {
	global $xoopsModuleConfig;
	$count[0] = empty($count[0]) ? 0 : $count[0];
	$count[1] = empty($count[1]) ? 0 : $count[1];
	$leave[0] = empty($leave[0]) ? 0 : $leave[0];
	$leave[1] = empty($leave[1]) ? 0 : $leave[1];
	$count = ($count[0] * $xoopsModuleConfig['to_day']) + $count[1];
	$leave = ($leave[0] * $xoopsModuleConfig['to_day']) + $leave[1];
	$leave = $leave - $count;
	/*
	$count = floor($leave / $xoopsModuleConfig['to_day']);
	$leave = $leave - ($count * $xoopsModuleConfig['to_day']);
	*/
	$count = $leave;
	$leave = $leave % $xoopsModuleConfig['to_day'];
	$count = ($count - $leave) / $xoopsModuleConfig['to_day'];
	return array($count, $leave);
}
function mail_info($type, $var = '') {
	global $xoopsDB, $xoopsConfig, $xoopsModule, $xoopsUser, $xoopsModuleConfig;
	switch ($type) {
		case 'checker':
			$member_handler =& xoops_gethandler('member');
			$emails = array();
			foreach ($var['emails'] as $checker) {
				$checker =& $member_handler -> getUser($checker);
				array_push($emails, $checker -> getVar('email'));
			}
			$excs = GetExcuse($var['excs_id']);
			$subject = '[' . $xoopsModule -> getVar('name') . ']' . _MD_MEXCS_MAIL_CHECKER_TITLE;
			$content = $xoopsUser -> getUnameFromId($excs['uid'],$xoopsModuleConfig['user_name']) . _MD_MEXCS_MAIL_CHECKER_CONTENT . '<a href="' . XOOPS_URL . '/modules/mngexcuse/excuse.php?oprt=viw&excs_id=' . $var['excs_id'] . '">' . XOOPS_URL . '/modules/mngexcuse/excuse.php?oprt=viw&excs_id=' . $var['excs_id'] . '</a>';
		break;
		case 'user_pass':
			$excs = GetExcuse($var['excs_id']);
			$member_handler =& xoops_gethandler('member');
			$user =& $member_handler -> getUser($excs['uid']);
			$emails = array($user -> getVar('email'));
			$subject = '[' . $xoopsModule -> getVar('name') . ']' . _MD_MEXCS_MAIL_USERPASS_TITLE;
			$content = $xoopsUser -> getUnameFromId($var['checker'],$xoopsModuleConfig['user_name']) . _MD_MEXCS_MAIL_USERPASS_CONTENT . '<a href="' . XOOPS_URL . '/modules/mngexcuse/excuse.php?oprt=viw&excs_id=' . $var['excs_id'] . '">' . XOOPS_URL . '/modules/mngexcuse/excuse.php?oprt=viw&excs_id=' . $var['excs_id'] . '</a>';
		break;
		case 'user_comment':
			$excs = GetExcuse($var['excs_id']);
			$member_handler =& xoops_gethandler('member');
			$user =& $member_handler -> getUser($excs['uid']);
			$emails = array($user -> getVar('email'));
			$subject = '[' . $xoopsModule -> getVar('name') . ']' . _MD_MEXCS_MAIL_USERCOMMENT_TITLE;
			$content = $xoopsUser -> getUnameFromId($var['checker'],$xoopsModuleConfig['user_name']) . _MD_MEXCS_MAIL_USERCOMMENT_CONTENT . '<a href="' . XOOPS_URL . '/modules/mngexcuse/excuse.php?oprt=viw&excs_id=' . $var['excs_id'] . '">' . XOOPS_URL . '/modules/mngexcuse/excuse.php?oprt=viw&excs_id=' . $var['excs_id'] . '</a>';
		break;
		case 'checker_continue':
			$member_handler =& xoops_gethandler('member');
			$emails = array();
			foreach ($var['emails'] as $checker) {
				$checker =& $member_handler -> getUser($checker);
				array_push($emails, $checker -> getVar('email'));
			}
			$excs = GetExcuse($var['excs_id']);
			$subject = '[' . $xoopsModule -> getVar('name') . ']' . _MD_MEXCS_MAIL_CHECKERCONTINUE_TITLE;
			$content = $xoopsUser -> getUnameFromId($excs['uid'],$xoopsModuleConfig['user_name']) . _MD_MEXCS_MAIL_CHECKERCONTINUE_CONTENT . '<a href="' . XOOPS_URL . '/modules/mngexcuse/excuse.php?oprt=viw&excs_id=' . $var['excs_id'] . '">' . XOOPS_URL . '/modules/mngexcuse/excuse.php?oprt=viw&excs_id=' . $var['excs_id'] . '</a>';
		break;
		case 'user_reject':
			$excs = GetExcuse($var['excs_id']);
			$member_handler =& xoops_gethandler('member');
			$user =& $member_handler -> getUser($excs['uid']);
			$emails = array($user -> getVar('email'));
			$subject = '[' . $xoopsModule -> getVar('name') . ']' . _MD_MEXCS_MAIL_USERREJECT_TITLE;
			$content = $xoopsUser -> getUnameFromId($var['checker'],$xoopsModuleConfig['user_name']) . _MD_MEXCS_MAIL_USERREJECT_CONTENT . '<a href="' . XOOPS_URL . '/modules/mngexcuse/excuse.php?oprt=viw&excs_id=' . $var['excs_id'] . '">' . XOOPS_URL . '/modules/mngexcuse/excuse.php?oprt=viw&excs_id=' . $var['excs_id'] . '</a>';
		break;
	}
	$xoopsMailer =& getMailer();
	$xoopsMailer -> useMail();
	$xoopsMailer -> multimailer -> isHTML(true);
	$xoopsMailer -> setFromEmail($xoopsConfig['adminmail']);
	$xoopsMailer -> setFromName($xoopsConfig['sitename']);
	$xoopsMailer -> setToEmails($emails);
	$xoopsMailer -> setSubject($subject);
	$xoopsMailer -> setBody($content);
	if ($xoopsMailer -> send()) {
		return true;
	} else {
		return false;
	}
}
function set_module_header($type, $var = '') {
	global $xoops_module_header;
	switch ($type) {
		case 'stylecss':
			$xoops_module_header .= '
<link rel="stylesheet" type="text/css" media="screen" href="default.css" />
			';
		break;
		default:
			$xoops_module_header = '';
		break;
	}
}
?>