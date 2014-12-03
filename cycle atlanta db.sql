#
# SQL Export
# Created by Querious (927)
# Created: August 29, 2014 at 4:12:26 PM EDT
# Encoding: Unicode (UTF-8)
#


DROP TABLE IF EXISTS `user`;
DROP TABLE IF EXISTS `trip_note_totals`;
DROP TABLE IF EXISTS `trip_dup_details`;
DROP TABLE IF EXISTS `trip_dup`;
DROP TABLE IF EXISTS `trip`;
DROP TABLE IF EXISTS `rider_type`;
DROP TABLE IF EXISTS `rider_history`;
DROP TABLE IF EXISTS `note_type`;
DROP TABLE IF EXISTS `note`;
DROP TABLE IF EXISTS `income`;
DROP TABLE IF EXISTS `gender`;
DROP TABLE IF EXISTS `ethnicity`;
DROP TABLE IF EXISTS `email`;
DROP TABLE IF EXISTS `cycling_freq`;
DROP TABLE IF EXISTS `coord`;
DROP TABLE IF EXISTS `btw_challenge`;
DROP TABLE IF EXISTS `age`;


CREATE TABLE `age` (
  `id` tinyint(4) NOT NULL DEFAULT '0',
  `text` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `btw_challenge` (
  `email` varchar(128) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `coord` (
  `trip_id` int(10) unsigned NOT NULL DEFAULT '0',
  `recorded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `altitude` double DEFAULT NULL,
  `speed` double DEFAULT NULL,
  `hAccuracy` double DEFAULT NULL,
  `vAccuracy` double DEFAULT NULL,
  PRIMARY KEY (`trip_id`,`recorded`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `cycling_freq` (
  `id` tinyint(4) NOT NULL DEFAULT '0',
  `text` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `email` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email_address` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=799 DEFAULT CHARSET=utf8;


CREATE TABLE `ethnicity` (
  `id` tinyint(4) NOT NULL DEFAULT '0',
  `text` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `gender` (
  `id` tinyint(4) NOT NULL DEFAULT '0',
  `text` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `income` (
  `id` tinyint(4) NOT NULL DEFAULT '0',
  `text` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `note` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `trip_id` int(10) unsigned DEFAULT NULL,
  `recorded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `altitude` double DEFAULT NULL,
  `speed` double DEFAULT NULL,
  `hAccuracy` double DEFAULT NULL,
  `vAccuracy` double DEFAULT NULL,
  `note_type` tinyint(4) DEFAULT NULL,
  `details` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`recorded`)
) ENGINE=InnoDB AUTO_INCREMENT=535 DEFAULT CHARSET=latin1;


CREATE TABLE `note_type` (
  `id` tinyint(4) NOT NULL DEFAULT '0',
  `text` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `rider_history` (
  `id` tinyint(4) NOT NULL DEFAULT '0',
  `text` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `rider_type` (
  `id` tinyint(4) NOT NULL DEFAULT '0',
  `text` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `trip` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `purpose` varchar(255) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `stop` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `n_coord` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`start`)
) ENGINE=InnoDB AUTO_INCREMENT=24230 DEFAULT CHARSET=utf8;


CREATE TABLE `trip_dup` (
  `user_id` int(10) DEFAULT NULL,
  `trip_id` int(10) DEFAULT '0',
  `dup_count` varchar(128) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `trip_dup_details` (
  `user_id` int(11) DEFAULT NULL,
  `similar_trips` varchar(128) DEFAULT NULL,
  `singleton_trips` varchar(128) DEFAULT NULL,
  `total_trips` varchar(128) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `trip_note_totals` (
  `user_id` int(11) DEFAULT NULL,
  `total_trips` varchar(128) DEFAULT NULL,
  `total_notes` varchar(128) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `device` varchar(128) DEFAULT NULL,
  `email` varchar(64) DEFAULT NULL,
  `age` tinyint(4) DEFAULT NULL,
  `gender` tinyint(4) DEFAULT NULL,
  `income` tinyint(4) DEFAULT NULL,
  `ethnicity` tinyint(4) DEFAULT NULL,
  `homeZIP` varchar(32) DEFAULT NULL,
  `schoolZIP` varchar(32) DEFAULT NULL,
  `workZIP` varchar(32) DEFAULT NULL,
  `cycling_freq` tinyint(4) DEFAULT NULL,
  `rider_history` tinyint(4) DEFAULT NULL,
  `rider_type` tinyint(4) DEFAULT NULL,
  `app_version` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `device` (`device`)
) ENGINE=InnoDB AUTO_INCREMENT=1542 DEFAULT CHARSET=utf8;








