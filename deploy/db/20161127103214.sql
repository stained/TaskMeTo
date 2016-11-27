CREATE TABLE `TaskResponse` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deleted` tinyint(1) DEFAULT NULL,
  `createdTimestamp` int(11) DEFAULT NULL,
  `userId` int(11) DEFAULT NULL,
  `taskId` int(11) DEFAULT NULL,
  `userTaskId` int(11) DEFAULT NULL,
  `response` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  KEY `taskId` (`taskId`),
  KEY `userTaskId` (`userTaskId`),
  CONSTRAINT `TaskResponse_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `User` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `TaskResponse_ibfk_2` FOREIGN KEY (`taskId`) REFERENCES `Task` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `TaskResponse_ibfk_3` FOREIGN KEY (`userTaskId`) REFERENCES `UserTask` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `TaskResponseFile` (
  `taskResponseId` int(11) DEFAULT NULL,
  `fileId` int(11) DEFAULT NULL,
  KEY `taskResponseId` (`taskResponseId`),
  KEY `fileId` (`fileId`),
  CONSTRAINT `TaskResponseFile_ibfk_1` FOREIGN KEY (`taskResponseId`) REFERENCES `TaskResponse` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `TaskResponseFile_ibfk_2` FOREIGN KEY (`fileId`) REFERENCES `File` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

