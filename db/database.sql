-- Adminer 4.0.3 MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = '+00:00';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `culture`;
CREATE TABLE `culture` (
  `culture` varchar(3) NOT NULL,
  `language` varchar(30) NOT NULL,
  PRIMARY KEY (`culture`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `culture` (`culture`, `language`) VALUES
  ('es',	'Español');

DROP TABLE IF EXISTS `family_tag`;
CREATE TABLE `family_tag` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `culture` varchar(3) NOT NULL,
  `name` varchar(100) NOT NULL,
  UNIQUE KEY `idx_id_culture` (`id`,`culture`),
  KEY `idx_culture` (`culture`),
  CONSTRAINT `family_tag_ibfk_1` FOREIGN KEY (`culture`) REFERENCES `culture` (`culture`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `family_tag` (`id`, `culture`, `name`) VALUES
  (1,	'es',	'Color'),
  (2,	'es',	'Añada'),
  (3,	'es',	'Aroma'),
  (4,	'es',	'Gusto');

DROP TABLE IF EXISTS `report`;
CREATE TABLE `report` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `template_id` int(11) unsigned DEFAULT NULL,
  `culture` varchar(3) NOT NULL,
  `region` text NOT NULL,
  `comment` text NOT NULL,
  `advice` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `template_id` (`template_id`),
  KEY `culture` (`culture`),
  CONSTRAINT `report_ibfk_6` FOREIGN KEY (`culture`) REFERENCES `culture` (`culture`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `response_template`;
CREATE TABLE `response_template` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `used` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `response_template_i18n`;
CREATE TABLE `response_template_i18n` (
  `template_id` int(11) unsigned NOT NULL,
  `culture` varchar(3) NOT NULL,
  `region` text,
  `comment` text,
  `advice` text,
  PRIMARY KEY (`template_id`,`culture`),
  KEY `idx_template_id` (`template_id`),
  KEY `idx_culture` (`culture`),
  CONSTRAINT `response_template_i18n_ibfk_9` FOREIGN KEY (`template_id`) REFERENCES `response_template` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `response_template_i18n_ibfk_6` FOREIGN KEY (`culture`) REFERENCES `culture` (`culture`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `tag`;
CREATE TABLE `tag` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `family_id` int(11) unsigned NOT NULL,
  `parent_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_family_id` (`family_id`),
  KEY `idx_parent_id` (`parent_id`),
  CONSTRAINT `tag_ibfk_1` FOREIGN KEY (`family_id`) REFERENCES `family_tag` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tag_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `tag` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `tag` (`id`, `family_id`, `parent_id`) VALUES
  (1,	1,	NULL),
  (2,	1,	1),
  (3,	1,	1),
  (4,	1,	1),
  (5,	2,	NULL),
  (6,	2,	5),
  (7,	2,	5),
  (8,	2,	5),
  (9,	2,	5),
  (10,	3,	NULL),
  (11,	3,	10),
  (12,	3,	10),
  (13,	3,	10),
  (14,	4,	NULL),
  (15,	4,	14),
  (16,	4,	14),
  (17,	4,	14);

DROP TABLE IF EXISTS `tag_i18n`;
CREATE TABLE `tag_i18n` (
  `tag_id` int(11) unsigned NOT NULL,
  `culture` varchar(3) NOT NULL,
  `name` varchar(100) NOT NULL,
  UNIQUE KEY `tag_id` (`tag_id`,`culture`),
  KEY `culture` (`culture`),
  CONSTRAINT `tag_i18n_ibfk_1` FOREIGN KEY (`culture`) REFERENCES `culture` (`culture`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tag_i18n_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tag` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `tag_i18n` (`tag_id`, `culture`, `name`) VALUES
  (1,	'es',	'Color'),
  (2,	'es',	'Blanco'),
  (3,	'es',	'Rosado'),
  (4,	'es',	'Negro'),
  (5,	'es',	'Añada'),
  (6,	'es',	'Joven'),
  (7,	'es',	'Medio'),
  (8,	'es',	'Reserva'),
  (9,	'es',	'Gran reserva'),
  (10,	'es',	'Aroma'),
  (11,	'es',	'Suave'),
  (12,	'es',	'Ligero'),
  (13,	'es',	'Intenso'),
  (14,	'es',	'Gusto'),
  (15,	'es',	'Madera'),
  (16,	'es',	'Afrutado'),
  (17,	'es',	'Avellana');

DROP TABLE IF EXISTS `tag_x_response_template`;
CREATE TABLE `tag_x_response_template` (
  `tag_id` int(11) unsigned NOT NULL,
  `template_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`tag_id`,`template_id`),
  KEY `template_id` (`template_id`),
  CONSTRAINT `tag_x_response_template_ibfk_7` FOREIGN KEY (`template_id`) REFERENCES `response_template` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tag_x_response_template_ibfk_6` FOREIGN KEY (`tag_id`) REFERENCES `tag` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 2014-04-07 21:44:54