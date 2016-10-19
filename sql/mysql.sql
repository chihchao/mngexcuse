CREATE TABLE `mexcs_procedure` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `groupids` int(10) unsigned NOT NULL default '0',
  `odr` tinyint(3) unsigned NOT NULL default '0',
  `phase` varchar(255) NOT NULL default '',
  `gou` tinyint(1) unsigned NOT NULL default '0',
  `checkers` text NOT NULL,
  PRIMARY KEY  (`id`)
) AUTO_INCREMENT=1 ;

CREATE TABLE `mexcs_yearbgn` (
  `yearbgn` tinyint(2) NOT NULL default '8',
  `groupids` text,
  PRIMARY KEY  (`yearbgn`)
) ;

CREATE TABLE `mexcs_document` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `uid` int(10) unsigned NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `description` text,
  `uids` text,
  `date_excuse` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`id`)
) AUTO_INCREMENT=1 ;

CREATE TABLE `mexcs_file` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `dcmt_id` int(10) unsigned NOT NULL,
  `file_name` varchar(255) NOT NULL default '',
  `file_type` varchar(20) NOT NULL default '',
  `file_realname` varchar(255) NOT NULL default '',
  `img` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) AUTO_INCREMENT=1 ;

CREATE TABLE `mexcs_excuse` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `uid` int(10) unsigned NOT NULL default '0',
  `appointment` varchar(40) default NULL,
  `excuse_type` varchar(40) default NULL,
  `description` text,
  `date_bgn` varchar(20) NOT NULL default '',
  `date_end` varchar(20) NOT NULL default '',
  `date_count_day` varchar(10) default '0',
  `date_count_hour` varchar(10) default '0',
  `dcmt_id` int(10) unsigned NOT NULL default '0',
  `date_time` varchar(20) NOT NULL default '',
  `state` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) AUTO_INCREMENT=1 ;

CREATE TABLE `mexcs_phase` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `excs_id` int(10) unsigned NOT NULL,
  `odr` tinyint(3) unsigned NOT NULL,
  `phase` varchar(255) NOT NULL default '',
  `checker` int(10) unsigned NOT NULL,
  `state` tinyint(3) unsigned NOT NULL default '0',
  `date_time` varchar(20) default NULL,
  PRIMARY KEY  (`id`)
) AUTO_INCREMENT=1 ;

CREATE TABLE `mexcs_substitute` (
  `excs_id` int(10) unsigned NOT NULL,
  `description` text,
  `comment` text,
  `state` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`excs_id`)
);

CREATE TABLE `mexcs_comment` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `phse_id` int(10) unsigned NOT NULL default '0',
  `uid` int(10) unsigned NOT NULL default '0',
  `comment` text,
  `date_time` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`id`)
) AUTO_INCREMENT=1 ;

CREATE TABLE `mexcs_leave` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `year_bgn` varchar(20) NOT NULL default '',
  `year_end` varchar(20) NOT NULL default '',
  `uid` int(10) unsigned NOT NULL default '0',
  `excuse_type` varchar(40) default NULL,
  `leave_day` varchar(10) default '0',
  `leave_hour` varchar(10) default '0',
  PRIMARY KEY  (`id`)
) AUTO_INCREMENT=1 ;