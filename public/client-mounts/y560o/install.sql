SET foreign_key_checks = 0;

DROP TABLE IF EXISTS `role`;
CREATE TABLE `role`(
  `role_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `role`(
`role_id` ,
`name` ,
`description`
)
VALUES
('1', 'guest', 'Used for temporary access to the API. Client expires after their session ends.'),
('2', 'client', 'Used for a registered client with an API-Key.'),
('3', 'admin', 'Used administrative access to the web panel.');

DROP TABLE IF EXISTS `mount`;
CREATE TABLE `mount`(
  `mount_id` int(11) unsigned NOT NULL,
  `created_on` datetime,
  `updated_on` datetime,
  `locked` bit default 0,
  `salt` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `route` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  KEY (`route`),
  PRIMARY KEY (`mount_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;


DROP TABLE IF EXISTS `client`;
CREATE TABLE `client`(
  `client_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(11) unsigned NOT NULL,
  `mount_id` int(11) unsigned,
  `ip` char(45) COLLATE utf8_unicode_ci NOT NULL,
  `geo_location` varchar(255) COLLATE utf8_unicode_ci,
  `created_on` datetime,
  `updated_on` datetime,
  `label` text COLLATE utf8_unicode_ci,
   UNIQUE KEY (`ip`),
   PRIMARY KEY (`client_id`),
   CONSTRAINT `mount_id_fk` FOREIGN KEY (`mount_id`) REFERENCES `mount` (`mount_id`) ON DELETE SET NULL ON UPDATE CASCADE,
   CONSTRAINT `role_id_fk` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `client`(
`client_id` ,
`role_id` ,
`ip` ,
`label`
)
VALUES
('1', '3', '127.0.0.1', 'administrator');

DROP TABLE IF EXISTS `client_api_key`;
CREATE TABLE `client_api_key`(
  `client_api_key_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(11) unsigned NOT NULL,
  `api_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_on` datetime,
  `updated_on` datetime,
  UNIQUE KEY (`api_key`),
  PRIMARY KEY (`client_api_key_id`),
  CONSTRAINT `client_id_fk` FOREIGN KEY (`client_id`) REFERENCES `client` (`client_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `session`;
CREATE TABLE `session`(
  `session_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `session_hash` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `created_on` datetime,
  `updated_on` datetime NOT NULL,
  `data` text COLLATE utf8_unicode_ci,
  KEY (`updated_on`),
  UNIQUE KEY (`session_hash`),
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `prong`;
CREATE TABLE `prong` (
  `prong_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(11) unsigned NOT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci,
  `domain` varchar(255) COLLATE utf8_unicode_ci,
  `platform` varchar(255) COLLATE utf8_unicode_ci,
  `browser` varchar(255) COLLATE utf8_unicode_ci,
  `os` varchar(255) COLLATE utf8_unicode_ci,
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `version` varchar(255) COLLATE utf8_unicode_ci,
  `created_on` datetime,
  `updated_on` datetime,
  UNIQUE KEY (`key`),
  PRIMARY KEY (`prong_id`),
  CONSTRAINT `client_id_fk2` FOREIGN KEY (`client_id`) REFERENCES `client` (`client_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `vector`;
CREATE TABLE `vector`(
  `vector_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `prong_id` int(11) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  UNIQUE KEY (`prong_id`),
  PRIMARY KEY (`vector_id`),
  CONSTRAINT `prong_id_fk` FOREIGN KEY (`prong_id`) REFERENCES `prong` (`prong_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `task`;
CREATE TABLE `task`(
  `task_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `prong_id` int(11) unsigned NOT NULL,
  `dispatched` bit default 0,
  `responded` bit default 0,
  `action` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `dispatch_payload` text COLLATE utf8_unicode_ci NOT NULL,
  `response_payload` text COLLATE utf8_unicode_ci,
  `dispatch_log_id` int(11) unsigned,
  `response_log_id` int(11) unsigned,
  `expires_on` datetime NOT NULL,
  `created_on` datetime NOT NULL,
  `updated_on` datetime,
  KEY(`dispatched`),
  KEY(`responded`),
  PRIMARY KEY (`task_id`),
  CONSTRAINT `log_id_fk` FOREIGN KEY (`dispatch_log_id`) REFERENCES `log` (`log_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `log_id_fk2` FOREIGN KEY (`response_log_id`) REFERENCES `log` (`log_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `prong_id_fk2` FOREIGN KEY (`prong_id`) REFERENCES `prong` (`prong_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `log`;
CREATE TABLE `log`
(
  `log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `log_type` int(11) unsigned NOT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `cleanable` bit default 1,
  `notifiable` bit default 0,
  `created_on` datetime,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
