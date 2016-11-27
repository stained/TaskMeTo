ALTER TABLE `Task`
   ADD `viewHash` varchar(255) DEFAULT NULL;

UPDATE Task SET viewHash = SHA1(CONCAT(id, title));