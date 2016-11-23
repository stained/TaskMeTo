CREATE TABLE `TaskFile` (
  `taskId` int(11) DEFAULT NULL,
  `fileId` int(11) DEFAULT NULL,
  KEY `taskId` (`taskId`),
  KEY `fileId` (`fileId`),
  CONSTRAINT `TaskFile_ibfk_1` FOREIGN KEY (`taskId`) REFERENCES `Task` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `TaskFile_ibfk_2` FOREIGN KEY (`fileId`) REFERENCES `File` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

