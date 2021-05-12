CREATE TABLE tx_z7countries_country
(
    uid      int(11) NOT NULL auto_increment,
    title    varchar(255) DEFAULT '' NOT NULL,
    iso_code varchar(255) DEFAULT '' NOT NULL,
    flag     varchar(255) DEFAULT '' NOT NULL,

    PRIMARY KEY (uid),
    KEY      parent (pid)
);

CREATE TABLE tx_z7countries_country_mm
(
    uid_local       int(11) unsigned DEFAULT '0' NOT NULL,
    uid_foreign     int(11) unsigned DEFAULT '0' NOT NULL,
    sorting         int(11) unsigned DEFAULT '0' NOT NULL,
    sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,
    table_foreign   varchar(255) DEFAULT '' NOT NULL,

    PRIMARY KEY (uid_local, uid_foreign),
    KEY             uid_local (uid_local),
    KEY             uid_foreign (uid_foreign)
);

CREATE TABLE tt_content
(
    tx_z7countries_mode int(11) unsigned DEFAULT '0' NOT NULL,
    tx_z7countries_list int(11) unsigned DEFAULT '0' NOT NULL,
);
