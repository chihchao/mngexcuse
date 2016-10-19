<?php
//include
include_once('include.php');
include_once('function/document.php');
include_once('function/procedure.php');
include_once('function/excuse.php');
include_once('function/phase.php');
include_once('function/substitute.php');
include_once('function/file.php');
include_once('function/yearbgn.php');
include_once('function/leave.php');

$oprt = (empty($_GET['oprt']) || ($_GET['oprt'] != 'personnel' && $_GET['oprt'] != 'user' && $_GET['oprt'] != 'excuse')) ? '' : $_GET['oprt'];
//for excuse
$excs_id = empty($_GET['excs_id']) ? 0 : intval($_GET['excs_id']);
$substitute = empty($_GET['substitute']) ? false : true;
//for personnel
$user_id = empty($_GET['user_id']) ? 0 : intval($_GET['user_id']);
$yearbgn = empty($_GET['yearbgn']) ? GetDefaultYearbgn() : intval($_GET['yearbgn']);
$year = empty($_GET['year']) ? 0 : intval($_GET['year']);
$page = empty($_GET['page']) ? -1 : intval($_GET['page']);
$type = (empty($_GET['type']) || !in_array($_GET['type'], explode('|', $xoopsModuleConfig['excuse_type']))) ? '' : $_GET['type'];

$uid = is_object($xoopsUser) ? $xoopsUser -> getVar('uid') : 0;

header('Content-Type: text/html; charset='._CHARSET); 
echo('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
echo('<html><head>');
echo('<title>' . $xoopsConfig['sitename'] . '</title>');
echo('<meta http-equiv="Content-Type" content="text/html; charset=' . _CHARSET . '" />');
echo('<meta name="AUTHOR" content="' . $xoopsConfig['sitename'] . '" />');
echo('<meta name="COPYRIGHT" content="Copyright (c) '.date('Y').' by ' . $xoopsConfig['sitename'] . '" />');
echo('<meta name="DESCRIPTION" content="' . $xoopsConfig['slogan'] . '" />');
echo('<meta name="GENERATOR" content="' . XOOPS_VERSION . '" />');
echo('<link rel="stylesheet" type="text/css" media="all" href="print.css" />');
echo('</head><body bgcolor="#ffffff" text="#000000" onload="window.print()">');
switch ($oprt) {
	case 'personnel':
		//authority
		if (!CheckAuthority('personnel')) redirect_header(XOOPS_URL, 5, _MD_MEXCS_NOAUTHORITY);
		$excuses = GetExcusesPersonnel($user_id, $year, $page, $type, $yearbgn);
		foreach ($excuses as $key => $val) {
			$excuses[$key]['state_string'] = get_excuse_state_string($excuses[$key]['state']);
			$excuses[$key]['uname'] = XoopsUser::getUnameFromId($excuses[$key]['uid'],$xoopsModuleConfig['user_name']);
		}
		$excuse_years = get_excuse_years($yearbgn);
		?>
		<table>
		<caption><?php echo($excuse_years[$year][0] . _MD_MEXCS_HEAD_EXCUSE); ?></caption>
		<thead>
			<tr>

			<th><?php echo(_MD_MEXCS_EXCUSE_DATEBGN); ?></th>
			<th><?php echo(_MD_MEXCS_EXCUSE_DATEEND); ?></th>
			<th><?php echo(_MD_MEXCS_EXCUSE_EXCUSETYPE); ?></th>
			<th><?php echo(_MD_MEXCS_EXCUSE_APPLICANT); ?></th>
			<th><?php echo(_MD_MEXCS_EXCUSE_DATECOUNT . ' - ' . _MD_MEXCS_EXCUSE_DATECOUNT_DAY); ?></th>
			<th><?php echo(_MD_MEXCS_EXCUSE_DATECOUNT . ' - ' . _MD_MEXCS_EXCUSE_DATECOUNT_HOUR); ?></th>
			<th><?php echo(_MD_MEXCS_EXCUSE_PHASE_STATE); ?></th>

			</tr>
		</thead>
		<tbody>
			<?php foreach($excuses as $key => $val) { ?>
			<tr class="MEXCSExcuseState<?php echo($val['state']); ?>">
			<td><?php echo(date('Y-m-d H:i', $val['date_bgn'])); ?></td>
			<td><?php echo(date('Y-m-d H:i', $val['date_end'])); ?></td>
			<td><?php echo($val['excuse_type']); ?></td>
			<td><?php echo($val['uname']); ?></td>
			<td><?php echo($val['date_count_day']); ?></td>
			<td><?php echo($val['date_count_hour']); ?></td>
			<td><?php echo($val['state_string']); ?></td>
			</tr>
			<?php } ?>
		</tbody>
		</table>
		<?php
	break;
	case 'user':
		if (!CheckAuthority('excuse_user')) redirect_header(XOOPS_URL, 5, _MD_MEXCS_NOAUTHORITY);
		$excuses = GetExcuses($year, $page, $type);
		foreach ($excuses as $key => $val) $excuses[$key]['state_string'] = get_excuse_state_string($excuses[$key]['state']);
		$excuse_years = get_excuse_years(GetYearbgnByUid($uid));
		$leave = GetLeave($year, $uid);
		$count = CountExcuseDate($uid, $year, GetYearbgnByUid($uid));
		foreach ($count as $key => $val) {
			if (!empty($leave[$key][0]) || !empty($leave[$key][1])) {
				$tmp = get_count_leave($count[$key], $leave[$key]);
				$count[$key][2] = $tmp[0];
				$count[$key][3] = $tmp[1];
			}
		}
		/*
		*/
		?>
		<table>
		<caption><?php echo($excuse_years[$year][0] . ' ' . XoopsUser::getUnameFromId($uid,$xoopsModuleConfig['user_name']) . _MD_MEXCS_HEAD_EXCUSE); ?> ( <?php echo count($excuses); ?> )</caption>
		<thead>
			<tr>
			<th><?php echo(_MD_MEXCS_EXCUSE_DATEBGN); ?></th>
			<th><?php echo(_MD_MEXCS_EXCUSE_DATEEND); ?></th>
			<th><?php echo(_MD_MEXCS_EXCUSE_EXCUSETYPE); ?></th>
			<th><?php echo(_MD_MEXCS_EXCUSE_DATECOUNT . ' - ' . _MD_MEXCS_EXCUSE_DATECOUNT_DAY); ?></th>
			<th><?php echo(_MD_MEXCS_EXCUSE_DATECOUNT . ' - ' . _MD_MEXCS_EXCUSE_DATECOUNT_HOUR); ?></th>
			<th><?php echo(_MD_MEXCS_EXCUSE_PHASE_STATE); ?></th>
			</tr>
		</thead>
		<tfoot><tr><td colspan="6">
		<ul>
		<?php foreach ($count as $cnt_id=>$cnt) { ?>
			<li>
			<?php echo (($cnt_id == 'total') ? _MD_MEXCS_EXCUSE_STATISTICS_TOTAL : $cnt_id).': '.$cnt[0]._MD_MEXCS_EXCUSE_DATECOUNT_DAY.' '.$cnt[1]._MD_MEXCS_EXCUSE_DATECOUNT_HOUR; ?>
			<?php if ($cnt[2]!='' || $cnt[3]!='') echo '('._MD_MEXCS_EXCUSE_STATISTICS_LEAVE.' '.$cnt[2]._MD_MEXCS_EXCUSE_DATECOUNT_DAY.$cnt[3].' '._MD_MEXCS_EXCUSE_DATECOUNT_HOUR.')'; ?>
			</li>
		<?php } ?>
		</ul>
		</td></tr></tfoot>
		<tbody>
			<?php foreach($excuses as $key => $val) { ?>
			<tr class="MEXCSExcuseState<?php echo($val['state']); ?>">
			<td><?php echo(date('Y-m-d H:i', $val['date_bgn'])); ?></td>
			<td><?php echo(date('Y-m-d H:i', $val['date_end'])); ?></td>
			<td><?php echo($val['excuse_type']); ?></td>
			<td><?php echo($val['date_count_day']); ?></td>
			<td><?php echo($val['date_count_hour']); ?></td>
			<td><?php echo($val['state_string']); ?></td>
			</tr>
			<?php } ?>
		</tbody>
		</table>
		<?php
	break;
	case 'excuse':
		if (!$excuse = GetExcuse($excs_id)) redirect_header('index.php', 5, get_error_message());
		//authority
		if ($excuse['uid'] != $uid && !CheckAuthority('personnel') && !CheckAuthority('substitute_mng')) redirect_header(XOOPS_URL, 5, _MD_MEXCS_NOAUTHORITY);
		$excuse['phases'] = GetPhases($excs_id);
		$excuse['uname'] = XoopsUser::getUnameFromId($excuse['uid'],$xoopsModuleConfig['user_name']);
		foreach ($excuse['phases'] as $key => $val) {
			$excuse['phases'][$key]['checker_uname'] = XoopsUser::getUnameFromId($excuse['phases'][$key]['checker'],$xoopsModuleConfig['user_name']);
			$excuse['phases'][$key]['state_string'] = get_phase_state_string($excuse['phases'][$key]['state']);
		}
		$excuse['substitute'] = GetSubstitute($excs_id);
		$excuse['document'] = GetDocument($excuse['dcmt_id']);
		if ($excuse['document']) $excuse['document']['files'] = GetFiles($excuse['dcmt_id']);
		?>
		<table>
		<tr>
			<td class="PGTitle" colspan="4"><?php echo($xoopsModuleConfig['print_title']); ?><?php if ($substitute) echo(_MD_MEXCS_EXCUSE_SUBSTITUTE_TITLE); ?></td>
		</tr>
		<tr>
			<td class="TDTitle"><?php echo(_MD_MEXCS_EXCUSE_APPLICANT); ?></td>
			<td colspan="3"><?php echo($excuse['uname']); ?></td>
		</tr>
		<tr>
			<td class="TDTitle"><?php echo(_MD_MEXCS_EXCUSE_APPOINTMENT); ?></td>
			<td><?php echo($excuse['appointment']); ?></td>
			<td class="TDTitle"><?php echo(_MD_MEXCS_EXCUSE_EXCUSETYPE); ?></td>
			<td><?php echo($excuse['excuse_type']); ?></td>
		</tr>
		<tr>
			<td class="TDTitle"><?php echo(_MD_MEXCS_EXCUSE_DATEBGN); ?></td>
			<td><?php echo(date ("Y-m-d H:i", $excuse['date_bgn'])); ?></td>
			<td class="TDTitle"><?php echo(_MD_MEXCS_EXCUSE_DATEEND); ?></td>
			<td><?php echo(date ("Y-m-d H:i", $excuse['date_end'])); ?></td>
		</tr>
		<tr>
			<td class="TDTitle"><?php echo(_MD_MEXCS_EXCUSE_DATECOUNT); ?></td>
			<td><?php echo($excuse['date_count_day']._MD_MEXCS_EXCUSE_DATECOUNT_DAY.$excuse['date_count_hour']._MD_MEXCS_EXCUSE_DATECOUNT_HOUR); ?></td>
			<td class="TDTitle"><?php echo(_MD_MEXCS_EXCUSE_DATETIME); ?></td>
			<td><?php echo(date ("Y-m-d", $excuse['date_time'])); ?></td>
		</tr>
		<tr>
			<td class="TDTitle"><?php echo(_MD_MEXCS_EXCUSE_PHASE); ?></td>
			<td colspan="3">
			<ul>
				<?php foreach ($excuse['phases'] as $val) {?>
				<li>[<?php echo($val['phase']); ?>] <?php echo($val['checker_uname']); ?> / <?php echo($val['state_string']); ?></li>
				<?php } ?>
			</ul>
			</td>
		</tr>
		<tr>
			<td class="TDTitle"><?php echo(_MD_MEXCS_EXCUSE_DESCRIPTION); ?></td>
			<td colspan="3"><?php echo(nl2br($excuse['description'])); ?></td>
		</tr>
		<?php if (!empty($excuse['substitute']['description'])) {?>
		<tr>
			<td class="TDTitle"><?php echo(_MD_MEXCS_EXCUSE_SUBSTITUTE); ?></td>
			<td colspan="3"><?php echo($excuse['substitute']['description']); ?></td>
		</tr>
		<?php } ?>
		<?php if ($excuse['substitute']['comment']) {?>
		<tr>
			<td class="TDTitle"><?php echo(_MD_MEXCS_EXCUSE_SUBSTITUTE_COMMENT); ?></td>
			<td colspan="3"><?php echo(nl2br($excuse['substitute']['comment'])); ?></td>
		</tr>
		<?php } ?>
		<?php if (!empty($excuse['substitute']['description'])) {?>
		<tr>
			<td class="TDTitle"><?php echo(_MD_MEXCS_EXCUSE_SUBSTITUTE_REVIEW); ?></td>
			<td colspan="3">
			<ul>
			<li><?php echo(_MD_MEXCS_EXCUSE_SUBSTITUTE_REVIEW_1); ?></li>
			<li><?php echo(_MD_MEXCS_EXCUSE_SUBSTITUTE_REVIEW_2); ?></li>
			<li><?php echo(_MD_MEXCS_EXCUSE_SUBSTITUTE_REVIEW_3); ?></li>
			<li><?php echo(_MD_MEXCS_EXCUSE_SUBSTITUTE_REVIEW_4); ?></li>
			<li><?php echo(_MD_MEXCS_EXCUSE_SUBSTITUTE_REVIEW_5); ?></li>
			<li><?php echo(_MD_MEXCS_EXCUSE_SUBSTITUTE_REVIEW_6); ?></li>
			</ul>
			</td>
		</tr>
		<?php } ?>
		<tr>
			<td class="TDTitle"><?php echo(_MD_MEXCS_EXCUSE_DOCUMENT); ?></td>
			<td colspan="3">
				<h3><?php echo($excuse['document']['title']); ?></h3>
				<p><?php echo(nl2br($excuse['document']['description'])); ?></p>
			</td>
		</tr>
		</table>
		<ul>
		<?php foreach ($excuse['document']['files'] as $val) {?>
			<li>
			<?php if ($val['img']) {?>
					<img src="file.php?file_id=<?php echo($val['id']); ?>" />
			<?php } else {?>
					<a href="file.php?file_id=<?php echo($val['id']); ?>" title="<?php echo($val['file_name']); ?>"><?php echo($val['file_name']); ?></a>
			<?php } ?>
			</li>
		<?php } ?>
		</ul>
		<?php
	break;
}
echo('</body></html>');
?>