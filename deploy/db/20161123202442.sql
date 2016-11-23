CREATE TABLE `File` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deleted` tinyint(1) DEFAULT NULL,
  `createdTimestamp` int(11) NOT NULL,
  `path` varchar(255) DEFAULT NULL,
  `mimeType` varchar(255) DEFAULT NULL,
  `size` int(11) NOT NULL,
  `public` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
