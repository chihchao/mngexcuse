<?php
//modules basic
$modversion['name'] = _MI_MEXCS_NAME;
$modversion['version'] = 1.22;
$modversion['description'] = _MI_MEXCS_DESC;
$modversion['credits'] = 'atlas(ch.ch.hsu@gmail.com)';
$modversion['author'] = 'atlas(ch.ch.hsu@gmail.com)';
$modversion['license'] = 'GPL see LICENSE';
$modversion['image'] = 'images/logo.png';
$modversion['dirname'] = 'mngexcuse';

//css
$modversion['css'] = 'default.css';

//database
$modversion['sqlfile']['mysql'] = 'sql/mysql.sql';
$modversion['tables'] = array(
	'mexcs_procedure',
	'mexcs_yearbgn',
	'mexcs_document',
	'mexcs_file',
	'mexcs_excuse',
	'mexcs_phase',
	'mexcs_substitute',
	'mexcs_comment',
	'mexcs_leave',
);

//admin
$modversion['hasAdmin'] = 1;
$modversion['adminindex'] = 'admin/index.php';
$modversion['adminmenu'] = 'admin/menu.php';

//templates
$modversion['templates'] = array(
	array('file' => 'head.htm', 'description' => ''),
	array('file' => 'idx.htm', 'description' => ''),
	array('file' => 'document_form.htm', 'description' => ''),
	array('file' => 'document_list.htm', 'description' => ''),
	array('file' => 'document_view.htm', 'description' => ''),
	array('file' => 'excuse_form.htm', 'description' => ''),
	array('file' => 'excuse_view.htm', 'description' => ''),
	array('file' => 'excuse_list.htm', 'description' => ''),
	array('file' => 'excuse_sdel.htm', 'description' => ''),
	array('file' => 'personnel_list.htm', 'description' => ''),
	array('file' => 'personnel_user.htm', 'description' => ''),
	array('file' => 'personnel_lvfm.htm', 'description' => ''),
	array('file' => 'substitute_list.htm', 'description' => ''),
	array('file' => 'substitute_form.htm', 'description' => ''),
	array('file' => 'mngexcuse.htm', 'description' => ''),
);



//mainmenu
$modversion['hasMain'] = 1;

//config
$modversion['config'][] = array(
	'name' => 'mngexcuse_begin',
	'title' => '_MI_MEXCS_CFG_MNGEXCUSEBEGIN',
	'description' => '_MI_MEXCS_CFG_MNGEXCUSEBEGIN_DESC',
	'formtype' => 'textbox',
	'valuetype' => 'text',
	'default' => time()
);
$modversion['config'][] = array(
	'name' => 'personnel',
	'title' => '_MI_MEXCS_CFG_PERSONNEL',
	'description' => '_MI_MEXCS_CFG_PERSONNEL_DESC',
	'formtype' => 'user_multi',
	'valuetype' => 'array',
	'default' => ''
);
$modversion['config'][] = array(
	'name' => 'excuse_user',
	'title' => '_MI_MEXCS_CFG_EXCUSEUSER',
	'description' => '_MI_MEXCS_CFG_EXCUSEUSER_DESC',
	'formtype' => 'group_multi',
	'valuetype' => 'array',
	'default' => ''
);
$modversion['config'][] = array(
	'name' => 'year_bgn',
	'title' => '_MI_MEXCS_CFG_YEARBGN',
	'description' => '_MI_MEXCS_CFG_YEARBGN_DESC',
	'formtype' => 'select',
	'options' => array('1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9, '10' => 10, '11' => 11, '12' => 12),
	'valuetype' => 'int',
	'default' => 8
);
$modversion['config'][] = array(
	'name' => 'to_day',
	'title' => '_MI_MEXCS_CFG_TODAY',
	'description' => '_MI_MEXCS_CFG_TODAY_DESC',
	'formtype' => 'select',
	'options' => array('1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9, '10' => 10, '11' => 11, '12' => 12, '13' => 13, '14' => 14, '15' => 15, '16' => 16, '17' => 17, '18' => 18, '19' => 19, '20' => 20, '21' => 21, '22' => 22, '23' => 23, '24' => 24),
	'valuetype' => 'int',
	'default' => 8
);
$modversion['config'][] = array(
	'name' => 'mini_hour',
	'title' => '_MI_MEXCS_CFG_MINIHOUR',
	'description' => '_MI_MEXCS_CFG_MINIHOUR_DESC',
	'formtype' => 'select',
	'options' => array('1' => 1, '10' => 10, '15' => 15, '30' => 30, '60' => 60),
	'valuetype' => 'int',
	'default' => 60
);
$modversion['config'][] = array(
	'name' => 'document_period',
	'title' => '_MI_MEXCS_CFG_DOCUMENTPERIOD',
	'description' => '_MI_MEXCS_CFG_DOCUMENTPERIOD_DESC',
	'formtype' => 'select',
	'options' => array('15' => 15, '30' => 30, '60' => 60, '90' => 90, '180' => 180, '365' => 365),
	'valuetype' => 'int',
	'default' => 30
);
$modversion['config'][] = array(
	'name' => 'reject',
	'title' => '_MI_MEXCS_CFG_REJECT',
	'description' => '_MI_MEXCS_CFG_REJECT_DESC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' => 1
);
$modversion['config'][] = array(
	'name' => 'unsubmit',
	'title' => '_MI_MEXCS_CFG_UNSUBMIT',
	'description' => '_MI_MEXCS_CFG_UNSUBMIT_DESC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' => 1
);
$modversion['config'][] = array(
	'name' => 'chang_checker',
	'title' => '_MI_MEXCS_CFG_CHANGCHECKER',
	'description' => '_MI_MEXCS_CFG_CHANGCHECKER_DESC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' => 1
);
$modversion['config'][] = array(
	'name' => 'according_order',
	'title' => '_MI_MEXCS_CFG_ACCORDINGORDER',
	'description' => '_MI_MEXCS_CFG_ACCORDINGORDER_DESC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' => 1
);
$modversion['config'][] = array(
	'name' => 'excuse_statement',
	'title' => '_MI_MEXCS_CFG_EXCUSESTATEMENT',
	'description' => '_MI_MEXCS_CFG_EXCUSESTATEMENT_DESC',
	'formtype' => 'textarea',
	'valuetype' => 'text',
	'default' => ''
);
$modversion['config'][] = array(
	'name' => 'excuse_type',
	'title' => '_MI_MEXCS_CFG_EXCUSETYPE',
	'description' => '_MI_MEXCS_CFG_EXCUSETYPE_DESC',
	'formtype' => 'textbox',
	'valuetype' => 'text',
	'default' => _MI_MEXCS_CFG_EXCUSETYPE_DEFAULT
);
$modversion['config'][] = array(
	'name' => 'appointment_type',
	'title' => '_MI_MEXCS_CFG_APPOINTMENTTYPE',
	'description' => '_MI_MEXCS_CFG_APPOINTMENTTYPE_DESC',
	'formtype' => 'textbox',
	'valuetype' => 'text',
	'default' => _MI_MEXCS_CFG_APPOINTMENTTYPE_DEFAULT
);
$modversion['config'][] = array(
	'name' => 'per_page',
	'title' => '_MI_MEXCS_CFG_PERPAGE',
	'description' => '_MI_MEXCS_CFG_PERPAGE_DESC',
	'formtype' => 'textbox',
	'valuetype' => 'text',
	'default' => '25'
);
$modversion['config'][] = array(
	'name' => 'filetype_ok',
	'title' => '_MI_MEXCS_CFG_FILETYPEOK',
	'description' => '_MI_MEXCS_CFG_FILETYPEOK_DESC',
	'formtype' => 'textbox',
	'valuetype' => 'text',
	'default' => 'jpg|jpeg|png|gif|doc|odt'
);
$modversion['config'][] = array(
	'name' => 'upload_path',
	'title' => '_MI_MEXCS_CFG_UPLOADPATH',
	'description' => '_MI_MEXCS_CFG_UPLOADPATH_DESC',
	'formtype' => 'textbox',
	'valuetype' => 'text',
	'default' => 'uploads/mngexcuse'
);
$modversion['config'][] = array(
	'name' => 'image_wth',
	'title' => '_MI_MEXCS_CFG_IMAGEWTH',
	'description' => '_MI_MEXCS_CFG_IMAGEWTH_DESC',
	'formtype' => 'textbox',
	'valuetype' => 'text',
	'default' => '800'
);
$modversion['config'][] = array(
	'name' => 'image_hgt',
	'title' => '_MI_MEXCS_CFG_IMAGEHGT',
	'description' => '_MI_MEXCS_CFG_IMAGEHGT_DESC',
	'formtype' => 'textbox',
	'valuetype' => 'text',
	'default' => '800'
);
$modversion['config'][] = array(
	'name' => 'substitute',
	'title' => '_MI_MEXCS_CFG_SUBSTITUTE',
	'description' => '_MI_MEXCS_CFG_SUBSTITUTE_DESC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' => 1
);
$modversion['config'][] = array(
	'name' => 'substitute_mng',
	'title' => '_MI_MEXCS_CFG_SUBSTITUTEMNG',
	'description' => '_MI_MEXCS_CFG_SUBSTITUTEMNG_DESC',
	'formtype' => 'group_multi',
	'valuetype' => 'array',
	'default' => ''
);
$modversion['config'][] = array(
	'name' => 'substitute_stm',
	'title' => '_MI_MEXCS_CFG_SUBSTITUTESTM',
	'description' => '_MI_MEXCS_CFG_SUBSTITUTESTM_DESC',
	'formtype' => 'textarea',
	'valuetype' => 'text',
	'default' => ''
);
$modversion['config'][] = array(
	'name' => 'substitute_desc',
	'title' => '_MI_MEXCS_CFG_SUBSTITUTEDESC',
	'description' => '_MI_MEXCS_CFG_SUBSTITUTEDESC_DESC',
	'formtype' => 'textarea',
	'valuetype' => 'text',
	'default' => _MI_MEXCS_CFG_SUBSTITUTEDESC_DEFAULT
);
$modversion['config'][] = array(
	'name' => 'print_title',
	'title' => '_MI_MEXCS_CFG_PRINTTITLE',
	'description' => '_MI_MEXCS_CFG_PRINTTITLE_DESC',
	'formtype' => 'textbox',
	'valuetype' => 'text',
	'default' => _MI_MEXCS_CFG_PRINTTITLE_DEFAULT
);
$modversion['config'][] = array(
	'name' => 'mail_info',
	'title' => '_MI_MEXCS_CFG_MAILINFO',
	'description' => '_MI_MEXCS_CFG_MAILINFO_DESC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' => 0
);
$modversion['config'][] = array(
	'name' => 'mail_checker',
	'title' => '_MI_MEXCS_CFG_MAILCHECKER',
	'description' => '_MI_MEXCS_CFG_MAILCHECKER_DESC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' => 0
);
$modversion['config'][] = array(
	'name' => 'mail_user',
	'title' => '_MI_MEXCS_CFG_MAILUSER',
	'description' => '_MI_MEXCS_CFG_MAILUSER_DESC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' => 0
);
$modversion['config'][] = array(
	'name' => 'user_name',
	'title' => '_MI_MEXCS_CFG_USERNAME',
	'description' => '_MI_MEXCS_CFG_USERNAME_DESC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' => 0
);

//blocks
$modversion['blocks'] = array(
	array(
		'file' => 'info_block.php',
		'name' => _MI_MEXCS_BLK_INFORMATION_NAME,
		'description' => _MI_MEXCS_BLK_INFORMATION_DESC,
		'show_func' => 'info_block',
		'template' => 'info_block.htm',
	),
);
?>