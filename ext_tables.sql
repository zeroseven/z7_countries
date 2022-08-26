CREATE TABLE tx_z7countries_country
(
	uid       int(11) NOT NULL auto_increment,
	enabled   tinyint(1) DEFAULT '0' NOT NULL,
	title     varchar(255) DEFAULT '' NOT NULL,
	iso_code  varchar(255) DEFAULT '' NOT NULL,
	parameter varchar(255) DEFAULT '' NOT NULL,
	flag      varchar(255) DEFAULT '' NOT NULL,

	PRIMARY KEY (uid),
	KEY       parent (pid)
);
