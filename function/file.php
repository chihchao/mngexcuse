<?php
function AddFiles($dcmt_id) {
	global $xoopsDB, $xoopsModuleConfig, $excsErrorMessage;
	$filetype_image = array('image/gif', 'image/jpeg', 'image/jpg', 'image/pjpeg', 'image/png');
	$thumbnail_size = 100;
	$upload_path = GetFilePath('upload');
	if (!is_dir($upload_path) || !is_writeable($upload_path)) {
		if (!is_dir(GetFilePath('upload')) && !mkdir(GetFilePath('upload'))) {
			$excsErrorMessage = 'No upload path.';
			return false;
		}
	}
	//test document exist or not
	$sql = 'Select id From ' . $xoopsDB -> prefix('mexcs_document') . ' Where id = \'' . $dcmt_id . '\'';
	if (!list($dcmt_id) = $xoopsDB -> fetchRow($xoopsDB -> query($sql))) {
		$excsErrorMessage = 'No document.';
		return false;
	}

	foreach($_FILES['file']['tmp_name'] as $key => $val) {
		if (is_uploaded_file($_FILES['file']['tmp_name'][$key])) {
		//file type
		$filetype_ok = empty($xoopsModuleConfig['filetype_ok']) ? array() : explode('|', strtolower($xoopsModuleConfig['filetype_ok']));
		$filetype = strtolower(substr(strrchr($_FILES['file']['name'][$key], '.'), 1));
		if (empty($filetype_ok) || in_array($filetype, $filetype_ok)) {
		//file values
		$file = array();
		$file['file_name'] = escape_string($_FILES['file']['name'][$key]);
		$file['file_type'] = escape_string($_FILES['file']['type'][$key]);
		//the real name of file saved in server
		$file['file_realname'] = function_exists('microtime') ? str_replace('0.', '', str_replace(' ', '_', microtime())) : time();
		$file['file_realname'] = $dcmt_id . '_' . $key . '_' . $file['file_realname'];
		$file['img'] = 0;
		//creat thumbnail and put file to upload path
		if (in_array($file['file_type'], $filetype_image) && ResizeImage($_FILES['file']['tmp_name'][$key], GetFilePath('file', $file['file_realname']), $xoopsModuleConfig['image_wth'], $xoopsModuleConfig['image_hgt'])) {
			ResizeImage($_FILES['file']['tmp_name'][$key], GetFilePath('thumbnail', $file['file_realname']), $thumbnail_size, $thumbnail_size);
			unlink($_FILES['file']['tmp_name'][$key]);
			$file['img'] = 1;
		} elseif (!move_uploaded_file($_FILES['file']['tmp_name'][$key], GetFilePath('file', $file['file_realname']))) {
			$excsErrorMessage .= '[' . $_FILES['file']['name'][$key] . '] Can not move tmp_file to upload path.' . chr(13) . chr(10);
			continue;
		}
		$sql = 'Insert Into ' . $xoopsDB -> prefix('mexcs_file') . ' (dcmt_id, file_name, file_type, file_realname, img) Values (\'' . $dcmt_id . '\', \'' . $file['file_name'] . '\', \'' . $file['file_type'] . '\', \'' . $file['file_realname'] . '\', \'' . $file['img'] . '\')';
		if ($xoopsDB -> query($sql)) {
			$excsErrorMessage .= '<li>[' . $_FILES['file']['name'][$key] . '] Upload success.</li>' . chr(13) . chr(10);
		} else {
			$excsErrorMessage .= '<li>[' . $_FILES['file']['name'][$key] . '] MySQL insert fail.</li>' . chr(13) . chr(10);
		}
		//if filetype_ok else
		} else {
			$excsErrorMessage .= '<li>[' . $_FILES['file']['name'][$key] . '] File type error.</li>' . chr(13) . chr(10);
		}
		//if is_uploaded_file else
		} else {
			$excsErrorMessage .= '<li>[file ' . $key . '] No upload file.</li>' . chr(13) . chr(10);
		}
	}
	$excsErrorMessage = '<ul>' . $excsErrorMessage . '</ul>';
	return true;
}
function GetFilePath($case = 'file', $file_name = '') {
	global $xoopsModuleConfig;
	$path = '';
	switch ($case) {;
		case 'upload_default':
			$path = XOOPS_ROOT_PATH . '/uploads/mngexcuse';
		break;
		case 'upload':
			$path = XOOPS_ROOT_PATH . '/' . $xoopsModuleConfig['upload_path'];
		break;
		case 'thumbnail':
			$path = XOOPS_ROOT_PATH . '/' . $xoopsModuleConfig['upload_path'] . '/' . $file_name . '_tn';
		break;
		default:
			$path = XOOPS_ROOT_PATH . '/' . $xoopsModuleConfig['upload_path'] . '/' . $file_name;
		break;
	}
	return $path;
}
function ResizeImage($source, $thumbnail, $max_width, $max_height){
	if (file_exists($source) && !empty($thumbnail)){
		$source_size = getimagesize($source); //圖檔大小
		$source_ratio = $source_size[0] / $source_size[1]; // 計算寬/高
		$thumbnail_ratio = $max_width / $max_height;
		if ($thumbnail_ratio > $source_ratio) {
			$thumbnail_size[1] = $max_height;
			$thumbnail_size[0] = $max_height * $source_ratio;
		}else{
			$thumbnail_size[0] = $max_width;
			$thumbnail_size[1] = $max_width / $source_ratio;
		}
		if (function_exists('imagecreatetruecolor')) {
			$thumbnail_img = imagecreatetruecolor($thumbnail_size[0], $thumbnail_size[1]);
		} else {
			$thumbnail_img = imagecreate($thumbnail_size[0], $thumbnail_size[1]);
		}
		switch ($source_size[2]) {
			case 1:
				$source_img = imagecreatefromgif($source);
				break;
			case 2:
				$source_img = imagecreatefromjpeg($source);
				break;
			case 3:
				$source_img = imagecreatefrompng($source);
				break; 
			default:
				return false;
				break; 
		} 
		imagecopyresized($thumbnail_img, $source_img, 0, 0, 0, 0, $thumbnail_size[0], $thumbnail_size[1], $source_size[0], $source_size[1]);
		imagejpeg($thumbnail_img, $thumbnail, 100);
		imagedestroy($source_img);
		imagedestroy($thumbnail_img);
		return true;
	}else{
		return false;
	}
}
function DeleteFile($file_id) {
	global $xoopsDB, $excsErrorMessage;
	$sql = 'Select file_realname From ' . $xoopsDB -> prefix('mexcs_file') . ' Where id = \'' . $file_id . '\'';
	if (!list($file_realname) = $xoopsDB -> fetchRow($xoopsDB -> query($sql))) {
		$excsErrorMessage = 'No file.';
		return false;
	}
	if (file_exists(GetFilePath('file', $file_realname)) && unlink(GetFilePath('file', $file_realname))) {
		if (file_exists(GetFilePath('thumbnail', $file_realname))) unlink(GetFilePath('thumbnail', $file_realname));
		$sql = 'Delete From ' . $xoopsDB -> prefix('mexcs_file') . ' Where id = \'' . $file_id . '\'';
		if ($xoopsDB -> queryF($sql)) {
			return true;
		} else {
			$excsErrorMessage = 'MySQL delete fail.';
			return false;
		}
	} else {
		$excsErrorMessage = 'Delete file fail.';
		return false;
	}
}
function Getfile($file_id) {
	global $xoopsDB, $excsErrorMessage;
	$sql = 'Select * From ' . $xoopsDB -> prefix('mexcs_file') . ' Where id = \'' . $file_id . '\'';
	if (!$record = $xoopsDB -> fetchArray($xoopsDB -> query($sql))) {
		$excsErrorMessage = 'No file.';
		return false;
	}
	return $record;
}
function GetFiles($dcmt_id, $idk = false) {
	global $xoopsDB, $xoopsModuleConfig;
	$recordset = array();
	$sql = 'Select * From ' . $xoopsDB -> prefix('mexcs_file') . ' Where dcmt_id = \'' . $dcmt_id . '\'';
	if (!$result = $xoopsDB -> query($sql)) return false;
	if ($idk) {
		while ($record = $xoopsDB -> fetchArray($result)) $recordset[$record['id']] = $record;
	} else {
		while ($record = $xoopsDB -> fetchArray($result)) array_push($recordset, $record);
	}
	return $recordset;
}
?>