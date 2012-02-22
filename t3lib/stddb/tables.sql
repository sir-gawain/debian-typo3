#
# TYPO3 SVN ID: $Id$
#

#
# Table structure for table 'be_groups'
#
CREATE TABLE be_groups (
  uid int(11) unsigned NOT NULL auto_increment,
  pid int(11) unsigned DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  title varchar(50) DEFAULT '' NOT NULL,
  non_exclude_fields text,
  explicit_allowdeny text,
  allowed_languages varchar(255) DEFAULT '' NOT NULL,
  custom_options text,
  db_mountpoints varchar(255) DEFAULT '' NOT NULL,
  pagetypes_select varchar(255) DEFAULT '' NOT NULL,
  tables_select text,
  tables_modify text,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
  groupMods text,
  file_mountpoints varchar(255) DEFAULT '' NOT NULL,
  fileoper_perms tinyint(4) DEFAULT '0' NOT NULL,
  hidden tinyint(1) unsigned DEFAULT '0' NOT NULL,
  inc_access_lists tinyint(3) unsigned DEFAULT '0' NOT NULL,
  description text,
  lockToDomain varchar(50) DEFAULT '' NOT NULL,
  deleted tinyint(1) unsigned DEFAULT '0' NOT NULL,
  TSconfig text,
  subgroup varchar(255) DEFAULT '' NOT NULL,
  hide_in_lists tinyint(4) DEFAULT '0' NOT NULL,
  workspace_perms tinyint(3) DEFAULT '1' NOT NULL,
  PRIMARY KEY (uid),
  KEY parent (pid)
);

#
# Table structure for table 'be_sessions'
#
CREATE TABLE be_sessions (
  ses_id varchar(32) DEFAULT '' NOT NULL,
  ses_name varchar(32) DEFAULT '' NOT NULL,
  ses_iplock varchar(39) DEFAULT '' NOT NULL,
  ses_hashlock int(11) DEFAULT '0' NOT NULL,
  ses_userid int(11) unsigned DEFAULT '0' NOT NULL,
  ses_tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  ses_data longtext,
  ses_backuserid int(11) NOT NULL default '0',
  PRIMARY KEY (ses_id,ses_name)
);

#
# Table structure for table 'be_users'
#
CREATE TABLE be_users (
  uid int(11) unsigned NOT NULL auto_increment,
  pid int(11) unsigned DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  username varchar(50) DEFAULT '' NOT NULL,
  password varchar(40) DEFAULT '' NOT NULL,
  admin tinyint(4) unsigned DEFAULT '0' NOT NULL,
  usergroup varchar(255) DEFAULT '' NOT NULL,
  disable tinyint(1) unsigned DEFAULT '0' NOT NULL,
  starttime int(11) unsigned DEFAULT '0' NOT NULL,
  endtime int(11) unsigned DEFAULT '0' NOT NULL,
  lang char(2) DEFAULT '' NOT NULL,
  email varchar(80) DEFAULT '' NOT NULL,
  db_mountpoints varchar(255) DEFAULT '' NOT NULL,
  options tinyint(4) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
  realName varchar(80) DEFAULT '' NOT NULL,
  userMods varchar(255) DEFAULT '' NOT NULL,
  allowed_languages varchar(255) DEFAULT '' NOT NULL,
  uc text,
  file_mountpoints varchar(255) DEFAULT '' NOT NULL,
  fileoper_perms tinyint(4) DEFAULT '0' NOT NULL,
  workspace_perms tinyint(3) DEFAULT '1' NOT NULL,
  lockToDomain varchar(50) DEFAULT '' NOT NULL,
  disableIPlock tinyint(1) unsigned DEFAULT '0' NOT NULL,
  deleted tinyint(1) unsigned DEFAULT '0' NOT NULL,
  TSconfig text,
  lastlogin int(10) unsigned DEFAULT '0' NOT NULL,
  createdByAction int(11) DEFAULT '0' NOT NULL,
  usergroup_cached_list varchar(255) DEFAULT '' NOT NULL,
  workspace_id int(11) DEFAULT '0' NOT NULL,
  workspace_preview tinyint(3) DEFAULT '1' NOT NULL,
  PRIMARY KEY (uid),
  KEY parent (pid),
  KEY username (username)
);

#
# Table structure for table 'cache_hash'
#
CREATE TABLE cache_hash (
  id int(11) unsigned NOT NULL auto_increment,
  hash varchar(32) DEFAULT '' NOT NULL,
  content mediumblob,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  ident varchar(32) DEFAULT '' NOT NULL,
  PRIMARY KEY (id),
  KEY hash (hash)
) ENGINE=InnoDB;


#
# Table structure for table 'cachingframework_cache_hash'
#
CREATE TABLE cachingframework_cache_hash (
  id int(11) unsigned NOT NULL auto_increment,
  identifier varchar(128) DEFAULT '' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  content mediumblob,
  lifetime int(11) unsigned DEFAULT '0' NOT NULL,
  PRIMARY KEY (id),
  KEY cache_id (identifier)
) ENGINE=InnoDB;


#
# Table structure for table 'cachingframework_cache_hash_tags'
#
CREATE TABLE cachingframework_cache_hash_tags (
  id int(11) unsigned NOT NULL auto_increment,
  identifier varchar(128) DEFAULT '' NOT NULL,
  tag varchar(128) DEFAULT '' NOT NULL,
  PRIMARY KEY (id),
  KEY cache_id (identifier),
  KEY cache_tag (tag)
) ENGINE=InnoDB;


#
# Table structure for table 'cache_imagesizes'
#
CREATE TABLE cache_imagesizes (
  md5hash varchar(32) DEFAULT '' NOT NULL,
  md5filename varchar(32) DEFAULT '' NOT NULL,
  tstamp int(11) DEFAULT '0' NOT NULL,
  filename varchar(255) DEFAULT '' NOT NULL,
  imagewidth mediumint(11) unsigned DEFAULT '0' NOT NULL,
  imageheight mediumint(11) unsigned DEFAULT '0' NOT NULL,
  PRIMARY KEY (md5filename)
) ENGINE=InnoDB;

#
# Table structure for table 'pages'
#
CREATE TABLE pages (
  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,
  t3ver_oid int(11) DEFAULT '0' NOT NULL,
  t3ver_id int(11) DEFAULT '0' NOT NULL,
  t3ver_wsid int(11) DEFAULT '0' NOT NULL,
  t3ver_label varchar(255) DEFAULT '' NOT NULL,
  t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
  t3ver_stage int(11) DEFAULT '0' NOT NULL,
  t3ver_count int(11) DEFAULT '0' NOT NULL,
  t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
  t3ver_swapmode tinyint(4) DEFAULT '0' NOT NULL,
  t3ver_move_id int(11) DEFAULT '0' NOT NULL,
  t3_origuid int(11) DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,
  deleted tinyint(1) unsigned DEFAULT '0' NOT NULL,
  perms_userid int(11) unsigned DEFAULT '0' NOT NULL,
  perms_groupid int(11) unsigned DEFAULT '0' NOT NULL,
  perms_user tinyint(4) unsigned DEFAULT '0' NOT NULL,
  perms_group tinyint(4) unsigned DEFAULT '0' NOT NULL,
  perms_everybody tinyint(4) unsigned DEFAULT '0' NOT NULL,
  editlock tinyint(4) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
  hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
  title varchar(255) DEFAULT '' NOT NULL,
  doktype tinyint(3) unsigned DEFAULT '0' NOT NULL,
  TSconfig text,
  storage_pid int(11) DEFAULT '0' NOT NULL,
  is_siteroot tinyint(4) DEFAULT '0' NOT NULL,
  php_tree_stop tinyint(4) DEFAULT '0' NOT NULL,
  tx_impexp_origuid int(11) DEFAULT '0' NOT NULL,
  url varchar(255) DEFAULT '' NOT NULL,
  starttime int(11) unsigned DEFAULT '0' NOT NULL,
  endtime int(11) unsigned DEFAULT '0' NOT NULL,
  urltype tinyint(4) unsigned DEFAULT '0' NOT NULL,
  shortcut int(10) unsigned DEFAULT '0' NOT NULL,
  shortcut_mode int(10) unsigned DEFAULT '0' NOT NULL,
  no_cache int(10) unsigned DEFAULT '0' NOT NULL,
  fe_group varchar(100) DEFAULT '0' NOT NULL,
  subtitle varchar(255) DEFAULT '' NOT NULL,
  layout tinyint(3) unsigned DEFAULT '0' NOT NULL,
  url_scheme tinyint(3) unsigned DEFAULT '0' NOT NULL,
  target varchar(80) DEFAULT '' NOT NULL,
  media text,
  lastUpdated int(10) unsigned DEFAULT '0' NOT NULL,
  keywords text,
  cache_timeout int(10) unsigned DEFAULT '0' NOT NULL,
  newUntil int(10) unsigned DEFAULT '0' NOT NULL,
  description text,
  no_search tinyint(3) unsigned DEFAULT '0' NOT NULL,
  SYS_LASTCHANGED int(10) unsigned DEFAULT '0' NOT NULL,
  abstract text,
  module varchar(10) DEFAULT '' NOT NULL,
  extendToSubpages tinyint(3) unsigned DEFAULT '0' NOT NULL,
  author varchar(255) DEFAULT '' NOT NULL,
  author_email varchar(80) DEFAULT '' NOT NULL,
  nav_title varchar(255) DEFAULT '' NOT NULL,
  nav_hide tinyint(4) DEFAULT '0' NOT NULL,
  content_from_pid int(10) unsigned DEFAULT '0' NOT NULL,
  mount_pid int(10) unsigned DEFAULT '0' NOT NULL,
  mount_pid_ol tinyint(4) DEFAULT '0' NOT NULL,
  alias varchar(32) DEFAULT '' NOT NULL,
  l18n_cfg tinyint(4) DEFAULT '0' NOT NULL,
  fe_login_mode tinyint(4) DEFAULT '0' NOT NULL,
  backend_layout int(10) DEFAULT '0' NOT NULL,
  backend_layout_next_level int(10) DEFAULT '0' NOT NULL,
  PRIMARY KEY (uid),
  KEY t3ver_oid (t3ver_oid,t3ver_wsid),
  KEY parent (pid,sorting,deleted,hidden),
  KEY alias (alias)
);

#
# Table structure for table 'sys_registry'
#
CREATE TABLE sys_registry (
  uid int(11) unsigned NOT NULL auto_increment,
  entry_namespace varchar(128) DEFAULT '' NOT NULL,
  entry_key varchar(128) DEFAULT '' NOT NULL,
  entry_value blob,
  PRIMARY KEY (uid),
  UNIQUE KEY entry_identifier (entry_namespace,entry_key)
);

#
# Table structure for table 'sys_be_shortcuts'
#
CREATE TABLE sys_be_shortcuts (
  uid int(11) unsigned NOT NULL auto_increment,
  userid int(11) unsigned DEFAULT '0' NOT NULL,
  module_name varchar(255) DEFAULT '' NOT NULL,
  url text,
  description varchar(255) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  sc_group tinyint(4) DEFAULT '0' NOT NULL,
  PRIMARY KEY (uid),
  KEY event (userid)
);


#
# Table structure for table 'sys_news'
#
CREATE TABLE sys_news (
  uid int(11) unsigned NOT NULL auto_increment,
  pid int(11) unsigned DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
  deleted tinyint(3) unsigned DEFAULT '0' NOT NULL,
  hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
  starttime int(11) unsigned DEFAULT '0' NOT NULL,
  endtime int(11) unsigned DEFAULT '0' NOT NULL,
  title varchar(255) DEFAULT '' NOT NULL,
  content mediumtext,

  PRIMARY KEY (uid),
  KEY parent (pid)
);


#
# Table structure for table 'sys_preview'
#
CREATE TABLE sys_preview (
  keyword varchar(32) DEFAULT '' NOT NULL,
  tstamp int(11) DEFAULT '0' NOT NULL,
  endtime int(11) DEFAULT '0' NOT NULL,
  config text,
  PRIMARY KEY (keyword)
);


#
# Table structure for table 'sys_filemounts'
#
CREATE TABLE sys_filemounts (
  uid int(11) unsigned NOT NULL auto_increment,
  pid int(11) unsigned DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  title varchar(30) DEFAULT '' NOT NULL,
  path varchar(120) DEFAULT '' NOT NULL,
  base tinyint(4) unsigned DEFAULT '0' NOT NULL,
  hidden tinyint(3) unsigned DEFAULT '0' NOT NULL,
  deleted tinyint(1) unsigned DEFAULT '0' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,
  PRIMARY KEY (uid),
  KEY parent (pid)
);

#
# Table structure for table 'sys_history'
#
CREATE TABLE sys_history (
  uid int(11) unsigned NOT NULL auto_increment,
  sys_log_uid int(11) DEFAULT '0' NOT NULL,
  history_data mediumtext,
  fieldlist text,
  recuid int(11) DEFAULT '0' NOT NULL,
  tablename varchar(255) DEFAULT '' NOT NULL,
  tstamp int(11) DEFAULT '0' NOT NULL,
  history_files mediumtext,
  snapshot tinyint(4) DEFAULT '0' NOT NULL,
  PRIMARY KEY (uid),
  KEY recordident_1 (tablename,recuid),
  KEY recordident_2 (tablename,tstamp),
  KEY sys_log_uid (sys_log_uid)
) ENGINE=InnoDB;

#
# Table structure for table 'sys_lockedrecords'
#
CREATE TABLE sys_lockedrecords (
  uid int(11) unsigned NOT NULL auto_increment,
  userid int(11) unsigned DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  record_table varchar(255) DEFAULT '' NOT NULL,
  record_uid int(11) DEFAULT '0' NOT NULL,
  record_pid int(11) DEFAULT '0' NOT NULL,
  username varchar(50) DEFAULT '' NOT NULL,
  feuserid int(11) unsigned DEFAULT '0' NOT NULL,
  PRIMARY KEY (uid),
  KEY event (userid,tstamp)
);

#
# Table structure for table 'sys_refindex'
#
CREATE TABLE sys_refindex (
  hash varchar(32) DEFAULT '' NOT NULL,
  tablename varchar(255) DEFAULT '' NOT NULL,
  recuid int(11) DEFAULT '0' NOT NULL,
  field varchar(40) DEFAULT '' NOT NULL,
  flexpointer varchar(255) DEFAULT '' NOT NULL,
  softref_key varchar(30) DEFAULT '' NOT NULL,
  softref_id varchar(40) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  deleted tinyint(1) DEFAULT '0' NOT NULL,
  ref_table varchar(255) DEFAULT '' NOT NULL,
  ref_uid int(11) DEFAULT '0' NOT NULL,
  ref_string varchar(200) DEFAULT '' NOT NULL,

  PRIMARY KEY (hash),
  KEY lookup_rec (tablename,recuid),
  KEY lookup_uid (ref_table,ref_uid),
  KEY lookup_string (ref_string)
);

#
# Table structure for table 'sys_log'
#
CREATE TABLE sys_log (
  uid int(11) unsigned NOT NULL auto_increment,
  userid int(11) unsigned DEFAULT '0' NOT NULL,
  action tinyint(4) unsigned DEFAULT '0' NOT NULL,
  recuid int(11) unsigned DEFAULT '0' NOT NULL,
  tablename varchar(255) DEFAULT '' NOT NULL,
  recpid int(11) DEFAULT '0' NOT NULL,
  error tinyint(4) unsigned DEFAULT '0' NOT NULL,
  details text NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  type tinyint(3) unsigned DEFAULT '0' NOT NULL,
  details_nr tinyint(3) unsigned DEFAULT '0' NOT NULL,
  IP varchar(39) DEFAULT '' NOT NULL,
  log_data varchar(255) DEFAULT '' NOT NULL,
  event_pid int(11) DEFAULT '-1' NOT NULL,
  workspace int(11) DEFAULT '0' NOT NULL,
  NEWid varchar(20) DEFAULT '' NOT NULL,
  PRIMARY KEY (uid),
  KEY event (userid,event_pid),
  KEY recuidIdx (recuid,uid)
) ENGINE=InnoDB;

#
# Table structure for table 'sys_language'
#
CREATE TABLE sys_language (
  uid int(11) unsigned NOT NULL auto_increment,
  pid int(11) unsigned DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
  title varchar(80) DEFAULT '' NOT NULL,
  flag varchar(20) DEFAULT '' NOT NULL,
  static_lang_isocode int(11) unsigned DEFAULT '0' NOT NULL,
  PRIMARY KEY (uid),
  KEY parent (pid)
);
