<?php
//include
include_once('include.php');
include('function/document.php');
include('function/phase.php');
include('function/file.php');

//parameter
$oprt = (empty($_GET['oprt']) || ($_GET['oprt'] != 'tn' && $_GET['oprt'] != 'df')) ? 'fl' : $_GET['oprt'];
$file_id = empty($_GET['file_id']) ? 0 : intval($_GET['file_id']);
$excs_id= empty($_GET['excs_id']) ? 0 : intval($_GET['excs_id']);
$submitted = (empty($_POST['submitted'])) ? false : true;

//main
$uid = is_object($xoopsUser) ? $xoopsUser -> getVar('uid') : 0;
//authority
if (!CheckAuthority('excuse_info')) redirect_header('index.php', 5, _MD_MEXCS_NOAUTHORITY);
if (!$file = GetFile($file_id)) redirect_header('index.php', 5, get_error_message());
if (!$document = GetDocument($file['dcmt_id'])) redirect_header('document.php', 5, get_error_message());

if ($oprt =='df') {
	//authority
	if ($document['uid'] != $uid && !CheckAuthority('admin')) redirect_header('document.php', 5, _MD_MEXCS_NOAUTHORITY);
	if ($submitted) { DeleteFile($file_id) ?  redirect_header('document.php?oprt=viw&dcmt_id=' . $file['dcmt_id'], 5, _MD_MEXCS_FILE_DELSUCCESS) : redirect_header('document.php?oprt=viw&dcmt_id=' . $file['dcmt_id'], 5, _MD_MEXCS_FILE_DELFAIL . get_error_message()); }

	include(XOOPS_ROOT_PATH . "/header.php");
	xoops_confirm(array('submitted' => 'true'), $_SERVER['REQUEST_URI'], '[' . $file['file_name'] . '] ' . _MD_MEXCS_FILE_DELCONFIRM);
	include(XOOPS_ROOT_PATH . "/footer.php");
} else {
	//authority
	if ($excs_id) {
		$excuse['phases'] = GetPhases($excs_id);
		$checker = false;
		foreach ($excuse['phases'] as $val) if ($val['checker'] == $uid) $checker = true;
	} else {
		$checker = false;
	}
	if (!checker && $document['uid'] != $uid && !CheckAuthority('admin') && !CheckAuthority('personnel') && !CheckAuthority('substitute_mng') && !in_array($uid, $document['uids'])) redirect_header('document.php', 5, _MD_MEXCS_NOAUTHORITY);

	$path = ($oprt == 'tn') ? 'thumbnail' : 'file';
	$path = GetFilePath($path, $file['file_realname']);

	header('Pragma: public');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Cache-Control: private', false);
	header('Content-Description: File Transfer');
	header('Content-type: ' . $file['file_type']);
	header('Content-Disposition: inline; filename=' . $file['file_name']);
	//header('Content-Disposition: attachment; filename=' . $file['name']);
	header('Content-Transfer-Encoding: binary');
	//header('Content-Length: ' . $file['file_size']);
	readfile($path);
}
exit();
?>