#
# Table structure for table 'tx_formhandler_subscription_authcodes'
#
CREATE TABLE tx_formhandler_subscription_authcodes (
	uid int(11) unsigned NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	reference_table varchar(255) DEFAULT '' NOT NULL,
	reference_table_uid_field varchar(255) DEFAULT '' NOT NULL,
	reference_table_uid varchar(255) DEFAULT '' NOT NULL,
	reference_table_hidden_field varchar(255) DEFAULT '' NOT NULL,
	action varchar(255) DEFAULT '' NOT NULL,
	serialized_auth_data text,
	auth_code varchar(255) DEFAULT '' NOT NULL,
	PRIMARY KEY (uid),
	KEY parent (pid),
	UNIQUE KEY auth_code (auth_code)
);

