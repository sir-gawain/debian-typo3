#
# Table structure for table 'sys_workspace'
#
CREATE TABLE sys_workspace (
  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,
  tstamp int(11) DEFAULT '0' NOT NULL,
  deleted tinyint(1) DEFAULT '0' NOT NULL,
  title varchar(30) DEFAULT '' NOT NULL,
  description varchar(255) DEFAULT '' NOT NULL,
  adminusers text,
  members text,
  reviewers text,
  db_mountpoints varchar(255) DEFAULT '' NOT NULL,
  file_mountpoints varchar(255) DEFAULT '' NOT NULL,
  publish_time int(11) DEFAULT '0' NOT NULL,
  unpublish_time int(11) DEFAULT '0' NOT NULL,
  freeze tinyint(3) DEFAULT '0' NOT NULL,
  live_edit tinyint(3) DEFAULT '0' NOT NULL,
  vtypes tinyint(3) DEFAULT '0' NOT NULL,
  disable_autocreate tinyint(1) DEFAULT '0' NOT NULL,
  swap_modes tinyint(3) DEFAULT '0' NOT NULL,
  publish_access tinyint(3) DEFAULT '0' NOT NULL,
  custom_stages int(11) DEFAULT '0' NOT NULL,
  stagechg_notification tinyint(3) DEFAULT '0' NOT NULL,

  PRIMARY KEY (uid),
  KEY parent (pid)
);


#
# Table structure for table 'sys_workspace_stage'
#
CREATE TABLE sys_workspace_stage (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(1) DEFAULT '0' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	title varchar(30) DEFAULT '' NOT NULL,
	responsible_persons varchar(255) DEFAULT '' NOT NULL,
	default_mailcomment text,
	parentid int(11) DEFAULT '0' NOT NULL,
	parenttable tinytext NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'sys_workspace_cache'
#
CREATE TABLE sys_workspace_cache (
    id int(11) unsigned NOT NULL auto_increment,
    identifier varchar(32) DEFAULT '' NOT NULL,
    content mediumblob NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    lifetime int(11) DEFAULT '0' NOT NULL,    
      PRIMARY KEY (id),
      KEY cache_id (identifier)
) ENGINE=InnoDB;


#
# Table structure for table 'sys_workspace_cache_tags'
#
CREATE TABLE sys_workspace_cache_tags (
  id int(11) unsigned NOT NULL auto_increment,
  identifier varchar(128) DEFAULT '' NOT NULL,
  tag varchar(128) DEFAULT '' NOT NULL,
  PRIMARY KEY (id),
  KEY cache_id (identifier),
  KEY cache_tag (tag)
) ENGINE=InnoDB;
