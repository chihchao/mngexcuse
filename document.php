<?php
//include
include_once('include.php');
include('function/document.php');
include('function/file.php');

//parameter
$oprt = (empty($_GET['oprt']) || ($_GET['oprt'] != 'add' && $_GET['oprt'] != 'edt' && $_GET['oprt'] != 'del' && $_GET['oprt'] != 'viw')) ? 'lst' : $_GET['oprt'];
$dcmt_id = empty($_GET['dcmt_id']) ? 0 : intval($_GET['dcmt_id']);
$page = empty($_GET['page']) ? 0 : intval($_GET['page']);
$submitted = (empty($_POST['submitted'])) ? false : true;

//main
$uid = is_object($xoopsUser) ? $xoopsUser -> getVar('uid') : 0;
//authority
if (!CheckAuthority('excuse_info')) redirect_header('index.php', 5, _MD_MEXCS_NOAUTHORITY);
//set template values
$tplvar = array();
$tplvar['page'] = array(
	'module_name' => $xoopsModule -> getVar('name'),
	'main_tpl' => 'document',
	'oprt' => $oprt
);
$tplvar['user'] = array(
	'uid' => $uid,
	'uname' => XoopsUser::getUnameFromId($uid,$xoopsModuleConfig['user_name']),
	'excuse_user' => CheckAuthority('excuse_user'),
	'personnel' => CheckAuthority('personnel'),
	'substitute_mng' => CheckAuthority('substitute_mng'),
);
//switch by action
switch ($oprt) {
	case 'add':
		if ($submitted) {
			if ($dcmt_id = AddDocument()) {
				AddFiles($dcmt_id);
				redirect_header('document.php?oprt=viw&dcmt_id=' . $dcmt_id, 5, _MD_MEXCS_DOCUMENT_ADDSUCCESS . get_upload_message());
			} else {
				redirect_header('document.php?oprt=add', 5, _MD_MEXCS_DOCUMENT_ADDFAIL . get_error_message());
			}
		}
		$users = ListUidByGid($xoopsModuleConfig['excuse_user'], true);
		foreach ($users as $key => $val) $users[$key] = XoopsUser::getUnameFromId($key,$xoopsModuleConfig['user_name']);
		$tplvar['page']['title'] = _MD_MEXCS_DOCUMENT_ADDDOCUMENT;
		$tplvar['users'] = $users;
	break;
	case 'edt':
		if (!$document = GetDocument($dcmt_id)) redirect_header('document.php', 5, get_error_message());
		if ($document['uid'] != $uid && !CheckAuthority('admin')) redirect_header('document.php', 5, _MD_MEXCS_NOAUTHORITY);
		if ($submitted) { UpdateDocument($dcmt_id) ?  redirect_header('document.php?oprt=viw&dcmt_id=' . $dcmt_id, 5, _MD_MEXCS_DOCUMENT_UDTSUCCESS) : redirect_header($_SERVER['REQUEST_URI'], 5, _MD_MEXCS_DOCUMENT_UDTFAIL . get_error_message()); }
		$users = ListUidByGid($xoopsModuleConfig['excuse_user'], true);
		foreach ($users as $key => $val) $users[$key] = XoopsUser::getUnameFromId($key,$xoopsModuleConfig['user_name']);
		$tplvar['page']['title'] = _MD_MEXCS_DOCUMENT_EDTDOCUMENT;
		$tplvar['document'] = $document;
		$tplvar['users'] = $users;
	break;
	case 'del':
		if (!$document = GetDocument($dcmt_id)) redirect_header('document.php', 5, get_error_message());
		if ($document['uid'] != $uid && !CheckAuthority('admin')) redirect_header('document.php', 5, _MD_MEXCS_NOAUTHORITY);
		if ($submitted) { DeleteDocument($dcmt_id) ?  redirect_header('document.php', 5, _MD_MEXCS_DOCUMENT_DELSUCCESS) : redirect_header('document.php?oprt=viw&dcmt_id=' . $dcmt_id, 5, _MD_MEXCS_DOCUMENT_DELFAIL . get_error_message()); }
		include(XOOPS_ROOT_PATH . "/header.php");
		xoops_confirm(array('submitted' => 'true'), $_SERVER['REQUEST_URI'], _MD_MEXCS_DOCUMENT_DELCONFIRM);
		include(XOOPS_ROOT_PATH . "/footer.php");
	break;
	case 'viw':
		if (!$document = GetDocument($dcmt_id)) redirect_header('document.php', 5, get_error_message());
		//authority
		if ($document['uid'] == $uid || CheckAuthority('admin')) {
			$tplvar['user']['write'] = true;
		} else {
			$tplvar['user']['write'] = false;
			if (!in_array($uid, $document['uids'])) redirect_header('document.php', 5, _MD_MEXCS_NOAUTHORITY);
		}
		if ($submitted) {
			if (!$tplvar['user']['write']) redirect_header('document.php', 5, _MD_MEXCS_NOAUTHORITY);
			if (AddFiles($dcmt_id)) redirect_header('document.php?oprt=viw&dcmt_id=' . $dcmt_id, 5, get_upload_message());
		}
		$document['uname'] =  XoopsUser::getUnameFromId($document['uid'],$xoopsModuleConfig['user_name']);
		$document['users'] = array();
		foreach ($document['uids'] as $key => $val) $document['users'][$key] = array('uid' => $val, 'uname' => XoopsUser::getUnameFromId($val,$xoopsModuleConfig['user_name']));
		$files = GetFiles($dcmt_id);
		$tplvar['page']['title'] =  _MD_MEXCS_DOCUMENT_VIWDOCUMENT;
		$tplvar['document'] = $document;
		$tplvar['files'] = $files;
	break;
	default:
		$documents = ListDocuments($page);
		$pages = ceil(CountDocuments() / $xoopsModuleConfig['per_page']);
		$tplvar['page']['title'] =  _MD_MEXCS_HEAD_DOCUMENT;
		$tplvar['page']['page'] =  $page;
		$tplvar['page']['pages'] =  $pages;
		$tplvar['documents'] = $documents;
	break;
}

//template
$xoopsOption['template_main'] = 'mngexcuse.htm';
include(XOOPS_ROOT_PATH . '/header.php');
$xoopsTpl -> assign('xoops_module_header', $xoops_module_header);
$xoopsTpl -> assign('tplvar', $tplvar);
include(XOOPS_ROOT_PATH . '/footer.php');
?>