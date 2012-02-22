#
# TYPO3 SVN ID: $Id: ext_tables.sql 6525 2009-11-25 11:27:34Z steffenk $
#


#
# Table structure for table 'cache_pages'
#
CREATE TABLE cache_pages (
  id int(11) unsigned NOT NULL auto_increment,
  hash varchar(32) DEFAULT '' NOT NULL,
  page_id int(11) unsigned DEFAULT '0' NOT NULL,
  reg1 int(11) unsigned DEFAULT '0' NOT NULL,
  HTML mediumblob,
  temp_content int(1) DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  expires int(10) unsigned DEFAULT '0' NOT NULL,
  cache_data mediumblob,
  KEY page_id (page_id),
  KEY sel (hash,page_id),
  PRIMARY KEY (id)
) ENGINE=InnoDB;


#
# Table structure for table 'cache_pagesection'
#
CREATE TABLE cache_pagesection (
  page_id int(11) unsigned DEFAULT '0' NOT NULL,
  mpvar_hash int(11) unsigned DEFAULT '0' NOT NULL,
  content blob,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  PRIMARY KEY (page_id,mpvar_hash)
) ENGINE=InnoDB;




#
# Table structure for table 'cachingframework_cache_pages'
#
CREATE TABLE cachingframework_cache_pages (
  id int(11) unsigned NOT NULL auto_increment,
  identifier varchar(128) DEFAULT '' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  content mediumtext,
  lifetime int(11) unsigned DEFAULT '0' NOT NULL,
  PRIMARY KEY (id),
  KEY cache_id (identifier)
) ENGINE=InnoDB;


#
# Table structure for table 'cachingframework_cache_pages_tags'
#
CREATE TABLE cachingframework_cache_pages_tags (
  id int(11) unsigned NOT NULL auto_increment,
  identifier varchar(128) DEFAULT '' NOT NULL,
  tag varchar(128) DEFAULT '' NOT NULL,
  PRIMARY KEY (id),
  KEY cache_id (identifier),
  KEY cache_tag (tag)
) ENGINE=InnoDB;


#
# Table structure for table 'cachingframework_cache_pagesection'
#
CREATE TABLE cachingframework_cache_pagesection (
  id int(11) unsigned NOT NULL auto_increment,
  identifier varchar(128) DEFAULT '' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  content mediumtext,
  lifetime int(11) unsigned DEFAULT '0' NOT NULL,
  PRIMARY KEY (id),
  KEY cache_id (identifier)
) ENGINE=InnoDB;


#
# Table structure for table 'cachingframework_cache_pagesection_tags'
#
CREATE TABLE cachingframework_cache_pagesection_tags (
  id int(11) unsigned NOT NULL auto_increment,
  identifier varchar(128) DEFAULT '' NOT NULL,
  tag varchar(128) DEFAULT '' NOT NULL,
  PRIMARY KEY (id),
  KEY cache_id (identifier),
  KEY cache_tag (tag)
) ENGINE=InnoDB;


#
# Table structure for table 'cache_typo3temp_log'
#
CREATE TABLE cache_typo3temp_log (
  md5hash varchar(32) DEFAULT '' NOT NULL,
  tstamp int(11) DEFAULT '0' NOT NULL,
  filename varchar(255) DEFAULT '' NOT NULL,
  orig_filename varchar(255) DEFAULT '' NOT NULL,
  PRIMARY KEY (md5hash)
) ENGINE=InnoDB;


#
# Table structure for table 'cache_md5params'
#
CREATE TABLE cache_md5params (
  md5hash varchar(20) DEFAULT '' NOT NULL,
  tstamp int(11) DEFAULT '0' NOT NULL,
  type tinyint(3) DEFAULT '0' NOT NULL,
  params text,
  PRIMARY KEY (md5hash)
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
# Table structure for table 'cache_treelist'
#
CREATE TABLE cache_treelist (
  md5hash char(32) DEFAULT '' NOT NULL,
  pid int(11) DEFAULT '0' NOT NULL,
  treelist text,
  tstamp int(11) DEFAULT '0' NOT NULL,
  expires int(11) unsigned  DEFAULT '0' NOT NULL,
  PRIMARY KEY (md5hash)
) ENGINE=InnoDB;


#
# Table structure for table 'fe_groups'
#
CREATE TABLE fe_groups (
  uid int(11) unsigned NOT NULL auto_increment,
  pid int(11) unsigned DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
  title varchar(50) DEFAULT '' NOT NULL,
  hidden tinyint(3) unsigned DEFAULT '0' NOT NULL,
  lockToDomain varchar(50) DEFAULT '' NOT NULL,
  deleted tinyint(3) unsigned DEFAULT '0' NOT NULL,
  description text,
  subgroup tinytext,
  TSconfig text,
  PRIMARY KEY (uid),
  KEY parent (pid)
);


#
# Table structure for table 'fe_session_data'
#
CREATE TABLE fe_session_data (
  hash varchar(32) DEFAULT '' NOT NULL,
  content mediumblob,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  PRIMARY KEY (hash)
) ENGINE=InnoDB;


#
# Table structure for table 'fe_sessions'
#
CREATE TABLE fe_sessions (
  ses_id varchar(32) DEFAULT '' NOT NULL,
  ses_name varchar(32) DEFAULT '' NOT NULL,
  ses_iplock varchar(39) DEFAULT '' NOT NULL,
  ses_hashlock int(11) DEFAULT '0' NOT NULL,
  ses_userid int(11) unsigned DEFAULT '0' NOT NULL,
  ses_tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  ses_data blob,
  ses_permanent tinyint(1) unsigned DEFAULT '0' NOT NULL,
  PRIMARY KEY (ses_id,ses_name)
) ENGINE=InnoDB;


#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
  uid int(11) unsigned NOT NULL auto_increment,
  pid int(11) unsigned DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  username varchar(50) DEFAULT '' NOT NULL,
  password varchar(40) DEFAULT '' NOT NULL,
  usergroup tinytext,
  disable tinyint(4) unsigned DEFAULT '0' NOT NULL,
  starttime int(11) unsigned DEFAULT '0' NOT NULL,
  endtime int(11) unsigned DEFAULT '0' NOT NULL,
  name varchar(80) DEFAULT '' NOT NULL,
  address varchar(255) DEFAULT '' NOT NULL,
  telephone varchar(20) DEFAULT '' NOT NULL,
  fax varchar(20) DEFAULT '' NOT NULL,
  email varchar(80) DEFAULT '' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
  lockToDomain varchar(50) DEFAULT '' NOT NULL,
  deleted tinyint(3) unsigned DEFAULT '0' NOT NULL,
  uc blob,
  title varchar(40) DEFAULT '' NOT NULL,
  zip varchar(10) DEFAULT '' NOT NULL,
  city varchar(50) DEFAULT '' NOT NULL,
  country varchar(40) DEFAULT '' NOT NULL,
  www varchar(80) DEFAULT '' NOT NULL,
  company varchar(80) DEFAULT '' NOT NULL,
  image tinytext,
  TSconfig text,
  fe_cruser_id int(10) unsigned DEFAULT '0' NOT NULL,
  lastlogin int(10) unsigned DEFAULT '0' NOT NULL,
  is_online int(10) unsigned DEFAULT '0' NOT NULL,
  PRIMARY KEY (uid),
  KEY parent (pid,username),
  KEY username (username),
  KEY is_online (is_online)
);


#
# Table structure for table 'pages_language_overlay'
#
CREATE TABLE pages_language_overlay (
  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,
  doktype tinyint(3) unsigned DEFAULT '0' NOT NULL,
  t3ver_oid int(11) DEFAULT '0' NOT NULL,
  t3ver_id int(11) DEFAULT '0' NOT NULL,
  t3ver_wsid int(11) DEFAULT '0' NOT NULL,
  t3ver_label varchar(255) DEFAULT '' NOT NULL,
  t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
  t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
  t3ver_count int(11) DEFAULT '0' NOT NULL,
  t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
  t3_origuid int(11) DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
  sys_language_uid int(11) unsigned DEFAULT '0' NOT NULL,
  title varchar(255) DEFAULT '' NOT NULL,
  hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
  starttime int(11) unsigned DEFAULT '0' NOT NULL,
  endtime int(11) unsigned DEFAULT '0' NOT NULL,
  deleted tinyint(3) unsigned DEFAULT '0' NOT NULL,
  subtitle varchar(255) DEFAULT '' NOT NULL,
  nav_title varchar(255) DEFAULT '' NOT NULL,
  media tinytext,
  keywords text,
  description text,
  abstract text,
  author varchar(255) DEFAULT '' NOT NULL,
  author_email varchar(80) DEFAULT '' NOT NULL,
  tx_impexp_origuid int(11) DEFAULT '0' NOT NULL,
  l18n_diffsource mediumblob,
  url varchar(255) DEFAULT '' NOT NULL,
  urltype tinyint(4) unsigned DEFAULT '0' NOT NULL,
  shortcut int(10) unsigned DEFAULT '0' NOT NULL,
  shortcut_mode int(10) unsigned DEFAULT '0' NOT NULL,

  PRIMARY KEY (uid),
  KEY t3ver_oid (t3ver_oid,t3ver_wsid),
  KEY parent (pid,sys_language_uid)
);


#
# Table structure for table 'static_template'
#
CREATE TABLE static_template (
  uid int(11) unsigned NOT NULL auto_increment,
  pid int(11) unsigned DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  title varchar(255) DEFAULT '' NOT NULL,
  include_static tinytext,
  constants text,
  config text,
  editorcfg text,
  description text,
  PRIMARY KEY (uid),
  KEY parent (pid)
);


#
# Table structure for table 'sys_domain'
#
CREATE TABLE sys_domain (
  uid int(11) unsigned NOT NULL auto_increment,
  pid int(11) unsigned DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
  hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
  domainName varchar(80) DEFAULT '' NOT NULL,
  redirectTo varchar(120) DEFAULT '' NOT NULL,
  redirectHttpStatusCode int(4) unsigned DEFAULT '301' NOT NULL,
  sorting int(10) unsigned DEFAULT '0' NOT NULL,
  prepend_params int(10) DEFAULT '0' NOT NULL,
  forced tinyint(3) unsigned DEFAULT '0' NOT NULL,

  PRIMARY KEY (uid),
  KEY parent (pid)
);



#
# Table structure for table 'sys_template'
#
CREATE TABLE sys_template (
  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,
  t3ver_oid int(11) DEFAULT '0' NOT NULL,
  t3ver_id int(11) DEFAULT '0' NOT NULL,
  t3ver_wsid int(11) DEFAULT '0' NOT NULL,
  t3ver_label varchar(255) DEFAULT '' NOT NULL,
  t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
  t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
  t3ver_count int(11) DEFAULT '0' NOT NULL,
  t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
  t3_origuid int(11) DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
  title varchar(255) DEFAULT '' NOT NULL,
  sitetitle varchar(255) DEFAULT '' NOT NULL,
  hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
  starttime int(11) unsigned DEFAULT '0' NOT NULL,
  endtime int(11) unsigned DEFAULT '0' NOT NULL,
  root tinyint(4) unsigned DEFAULT '0' NOT NULL,
  clear tinyint(4) unsigned DEFAULT '0' NOT NULL,
  include_static tinytext,
  include_static_file text,
  constants text,
  config text,
  editorcfg text,
  resources text,
  nextLevel varchar(5) DEFAULT '' NOT NULL,
  description text,
  basedOn tinytext,
  deleted tinyint(3) unsigned DEFAULT '0' NOT NULL,
  includeStaticAfterBasedOn tinyint(4) unsigned DEFAULT '0' NOT NULL,
  static_file_mode tinyint(4) unsigned DEFAULT '0' NOT NULL,
  tx_impexp_origuid int(11) DEFAULT '0' NOT NULL,
  PRIMARY KEY (uid),
  KEY t3ver_oid (t3ver_oid,t3ver_wsid),
  KEY parent (pid,sorting,deleted,hidden)
);


#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,
  t3ver_oid int(11) DEFAULT '0' NOT NULL,
  t3ver_id int(11) DEFAULT '0' NOT NULL,
  t3ver_wsid int(11) DEFAULT '0' NOT NULL,
  t3ver_label varchar(255) DEFAULT '' NOT NULL,
  t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
  t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
  t3ver_count int(11) DEFAULT '0' NOT NULL,
  t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
  t3ver_move_id int(11) DEFAULT '0' NOT NULL,
  t3_origuid int(11) DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
  hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,
  CType varchar(30) DEFAULT '' NOT NULL,
  header varchar(255) DEFAULT '' NOT NULL,
  header_position varchar(6) DEFAULT '' NOT NULL,
  bodytext mediumtext,
  image text,
  imagewidth mediumint(11) unsigned DEFAULT '0' NOT NULL,
  imageorient tinyint(4) unsigned DEFAULT '0' NOT NULL,
  imagecaption text,
  imagecols tinyint(4) unsigned DEFAULT '0' NOT NULL,
  imageborder tinyint(4) unsigned DEFAULT '0' NOT NULL,
  media text,
  layout tinyint(3) unsigned DEFAULT '0' NOT NULL,
  deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
  cols tinyint(3) unsigned DEFAULT '0' NOT NULL,
  records text,
  pages tinytext,
  starttime int(11) unsigned DEFAULT '0' NOT NULL,
  endtime int(11) unsigned DEFAULT '0' NOT NULL,
  colPos tinyint(3) unsigned DEFAULT '0' NOT NULL,
  subheader varchar(255) DEFAULT '' NOT NULL,
  spaceBefore tinyint(4) unsigned DEFAULT '0' NOT NULL,
  spaceAfter tinyint(4) unsigned DEFAULT '0' NOT NULL,
  fe_group varchar(100) DEFAULT '0' NOT NULL,
  header_link varchar(255) DEFAULT '' NOT NULL,
  imagecaption_position varchar(6) DEFAULT '' NOT NULL,
  image_link varchar(255) DEFAULT '' NOT NULL,
  image_zoom tinyint(3) unsigned DEFAULT '0' NOT NULL,
  image_noRows tinyint(3) unsigned DEFAULT '0' NOT NULL,
  image_effects tinyint(3) unsigned DEFAULT '0' NOT NULL,
  image_compression tinyint(3) unsigned DEFAULT '0' NOT NULL,
  altText text,
  titleText text,
  longdescURL text,
  header_layout varchar(30) DEFAULT '0' NOT NULL,
  text_align varchar(6) DEFAULT '' NOT NULL,
  text_face tinyint(3) unsigned DEFAULT '0' NOT NULL,
  text_size tinyint(3) unsigned DEFAULT '0' NOT NULL,
  text_color tinyint(3) unsigned DEFAULT '0' NOT NULL,
  text_properties tinyint(3) unsigned DEFAULT '0' NOT NULL,
  menu_type varchar(30) DEFAULT '0' NOT NULL,
  list_type varchar(36) DEFAULT '0' NOT NULL,
  table_border tinyint(3) unsigned DEFAULT '0' NOT NULL,
  table_cellspacing tinyint(3) unsigned DEFAULT '0' NOT NULL,
  table_cellpadding tinyint(3) unsigned DEFAULT '0' NOT NULL,
  table_bgColor tinyint(3) unsigned DEFAULT '0' NOT NULL,
  select_key varchar(80) DEFAULT '' NOT NULL,
  sectionIndex tinyint(3) unsigned DEFAULT '0' NOT NULL,
  linkToTop tinyint(3) unsigned DEFAULT '0' NOT NULL,
  filelink_size tinyint(3) unsigned DEFAULT '0' NOT NULL,
  section_frame tinyint(3) unsigned DEFAULT '0' NOT NULL,
  date int(10) unsigned DEFAULT '0' NOT NULL,
  splash_layout varchar(30) DEFAULT '0' NOT NULL,
  multimedia tinytext,
  image_frames tinyint(3) unsigned DEFAULT '0' NOT NULL,
  recursive tinyint(3) unsigned DEFAULT '0' NOT NULL,
  imageheight mediumint(8) unsigned DEFAULT '0' NOT NULL,
  rte_enabled tinyint(4) DEFAULT '0' NOT NULL,
  sys_language_uid int(11) DEFAULT '0' NOT NULL,
  tx_impexp_origuid int(11) DEFAULT '0' NOT NULL,
  pi_flexform mediumtext,
  l18n_parent int(11) DEFAULT '0' NOT NULL,
  l18n_diffsource mediumblob,

  PRIMARY KEY (uid),
  KEY t3ver_oid (t3ver_oid,t3ver_wsid),
  KEY parent (pid,sorting)
);


#
# Table structure for table 'pages'
#
CREATE TABLE pages (
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
  KEY alias (alias)
);
