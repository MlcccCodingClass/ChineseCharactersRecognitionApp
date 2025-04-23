DROP TABLE IF EXISTS  `grade`;

CREATE TABLE IF NOT EXISTS `grade` (
    `GradeId` TINYINT NOT NULL,
    `GradeName` varchar(30) NOT NULL,
    `NumberOfWords` INT(32) NOT NULL,
    `TimeLimit` INT(32) NOT NULL,
    `CreateDate` DATETIME NOT NULL default CURRENT_TIMESTAMP,
    `lastModified` DATETIME on update CURRENT_TIMESTAMP NOT NULL default CURRENT_TIMESTAMP,
    PRIMARY KEY (`GradeId`)
    );

INSERT INTO `grade` (`GradeId`, `GradeName`,  `NumberOfWords`,`TimeLimit`, `CreateDate`, `lastModified`)
    VALUES
    (1, 'G1-MLP', 10, 8,  current_timestamp(), current_timestamp()),
    (2, 'G2-MLP', 10, 9, current_timestamp(), current_timestamp()),
    (3, 'G3-MLP', 15, 10, current_timestamp(), current_timestamp()),
    (4, 'G4-MLP', 15, 10, current_timestamp(), current_timestamp()),
    (5, 'G5-MLP', 15, 10, current_timestamp(), current_timestamp()),
    (6, 'G6-MLP', 15, 10, current_timestamp(), current_timestamp()),
    (7, 'G7-MLP', 20, 10, current_timestamp(), current_timestamp()),
    (8, 'G8-MLP', 20, 10, current_timestamp(), current_timestamp()),
    (9, 'AP', 20, 10, current_timestamp(), current_timestamp()),
    (10, 'G1-MZHY', 20, 10, current_timestamp(), current_timestamp()),
    (11, 'G2-MZHY', 20, 10, current_timestamp(), current_timestamp()),
    (12, 'G3-MZHY', 20, 10, current_timestamp(), current_timestamp()),
    (13, 'G4-MZHY', 20, 10, current_timestamp(), current_timestamp()),
    (14, 'G5-MZHY', 20, 10, current_timestamp(), current_timestamp()),
    (15, 'G6-MZHY', 20, 10, current_timestamp(), current_timestamp()),
    (16, 'CSL-1', 20, 10, current_timestamp(), current_timestamp()),
    (17, 'CSL-2', 20, 10, current_timestamp(), current_timestamp()),
    (18, 'CSL-3', 20, 10, current_timestamp(), current_timestamp()),
    (19, 'CSL-4', 20, 10, current_timestamp(), current_timestamp()),
    (20, 'CSL-5/6', 20, 10, current_timestamp(), current_timestamp());
  


INSERT INTO `grade` (`GradeId`, `GradeName`,  `NumberOfWords`,`TimeLimit`, `CreateDate`, `lastModified`)
    VALUES (21, 'G9-MLP', 50, 6, current_timestamp(), current_timestamp());
INSERT INTO `grade` (`GradeId`, `GradeName`,  `NumberOfWords`,`TimeLimit`, `CreateDate`, `lastModified`)
    VALUES (22, 'G7-MZHY', 50, 6, current_timestamp(), current_timestamp()),
    (23, 'G8-MZHY', 50, 6, current_timestamp(), current_timestamp()),
     (24, 'G8-MZHY', 50, 6, current_timestamp(), current_timestamp()),
