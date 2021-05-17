CREATE TABLE tx_z7countries_country
(
    uid      int(11) NOT NULL auto_increment,
    title    varchar(255) DEFAULT '' NOT NULL,
    iso_code varchar(255) DEFAULT '' NOT NULL,
    flag     varchar(255) DEFAULT '' NOT NULL,

    PRIMARY KEY (uid),
    KEY      parent (pid)
);
