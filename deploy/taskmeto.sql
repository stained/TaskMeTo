DROP DATABASE IF EXISTS taskmeto;

CREATE DATABASE taskmeto;
USE taskmeto;

CREATE TABLE `User` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deleted` tinyint(1) DEFAULT NULL,
  `createdTimestamp` int(11) DEFAULT NULL,
  `updatedTimestamp` int(11) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `passwordHash` varchar(255) DEFAULT NULL,
  `loginToken` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `Task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deleted` tinyint(1) DEFAULT NULL,
  `createdTimestamp` int(11) DEFAULT NULL,
  `updatedTimestamp` int(11) DEFAULT NULL,
  `createdByUserId` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `deadlineTimestamp` int(11) DEFAULT NULL,
  `published` tinyint(1) DEFAULT 0 NULL,
  PRIMARY KEY (`id`),
  KEY `createdByUserId` (`createdByUserId`),
  CONSTRAINT `Task_ibfk_1` FOREIGN KEY (`createdByUserId`) REFERENCES `User` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `UserTask` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deleted` tinyint(1) DEFAULT NULL,
  `userId` int(11) NOT NULL,
  `taskId` int(11) NOT NULL,
  `completedTimestamp` int(11) DEFAULT NULL,
  `acceptedTimestamp` int(11) DEFAULT NULL,
  `acceptanceToken` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  CONSTRAINT `UserTask_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `User` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `UserTask_ibfk_2` FOREIGN KEY (`taskId`) REFERENCES `Task` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `TaskTag` (
  `taskId` int(11) DEFAULT NULL,
  `tag` varchar(255) DEFAULT NULL,
  KEY `taskId` (`taskId`),
  CONSTRAINT `TaskTag_ibfk_1` FOREIGN KEY (`taskId`) REFERENCES `Task` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
