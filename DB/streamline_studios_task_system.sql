-- phpMyAdmin SQL Dump
-- version 4.2.11
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Feb 21, 2017 at 08:01 PM
-- Server version: 5.6.21
-- PHP Version: 5.6.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `streamline_studios_task_system`
--

DELIMITER $$
--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `checkParentIsComplete`(`givenID` INT) RETURNS tinyint(1)
    DETERMINISTIC
BEGIN
    DECLARE childrenCount INT;
    DECLARE childrenCompleteCount INT;

    SELECT COUNT(*) INTO childrenCount
    FROM
      (SELECT *
       FROM tasks
       ORDER BY parent_id,
                id) tasks_sorted,
      (SELECT @pv := givenId) initialisation
    WHERE find_in_set(parent_id, @pv) > 0
      and @pv := concat(@pv, ',', id);

    SELECT count(*) status INTO childrenCompleteCount
    FROM
    (
        SELECT status
        FROM
          (SELECT *
           FROM tasks
           ORDER BY parent_id,
                    id) tasks_sorted,
          (SELECT @pv := givenId) initialisation
        WHERE find_in_set(parent_id, @pv) > 0
          and @pv := concat(@pv, ',', id)
    ) temp
    WHERE temp.status = 2;

    IF childrenCount = childrenCompleteCount THEN
        RETURN 1;
    ELSE
        RETURN 0;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `getAncestry`(GivenID INT) RETURNS varchar(1024) CHARSET utf8
    DETERMINISTIC
BEGIN
    DECLARE rv VARCHAR(1024);
    DECLARE cm CHAR(1);
    DECLARE ch INT;

    SET rv = '';
    SET cm = '';
    SET ch = GivenID;
    WHILE ch > 0 DO
        SELECT IFNULL(parent_id,-1) INTO ch FROM
        (SELECT parent_id FROM tasks WHERE id = ch) A;
        IF ch > 0 THEN
            SET rv = CONCAT(rv,cm,ch);
            SET cm = ',';
        END IF;
    END WHILE;
    RETURN rv;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `GetParentIDByID`(GivenID INT) RETURNS int(11)
    DETERMINISTIC
BEGIN
    DECLARE rv INT;

    SELECT IFNULL(parent_id,-1) INTO rv FROM
    (SELECT parent_id FROM tasks WHERE id = GivenID) A;
    RETURN rv;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `GetRootNodes`(`givenId` INT) RETURNS varchar(1024) CHARSET utf8
    DETERMINISTIC
BEGIN

    DECLARE rv,q,queue,queue_children VARCHAR(1024);
    DECLARE queue_length,front_id,pos INT;

    SET rv = '';
    SET queue = givenId;
    SET queue_length = 1;

    WHILE queue_length > 0 DO
        IF queue_length = 1 THEN
            SET front_id = queue;
            SET queue = '';
        ELSE
            SET front_id = SUBSTR(queue,1,LOCATE(',',queue)-1);
            SET pos = LOCATE(',',queue) + 1;
            SET q = SUBSTR(queue,pos);
            SET queue = q;
        END IF;
        SET queue_length = queue_length - 1;

        SELECT IFNULL(qc,'') INTO queue_children
        FROM (SELECT GROUP_CONCAT(id) qc
        FROM tasks WHERE parent_id = front_id) A;

        IF LENGTH(queue_children) = 0 THEN
            IF LENGTH(queue) = 0 THEN
                SET queue_length = 0;
            END IF;
        ELSE
            IF LENGTH(rv) = 0 THEN
                SET rv = queue_children;
            ELSE
                SET rv = CONCAT(rv,',',queue_children);
            END IF;
            IF LENGTH(queue) = 0 THEN
                SET queue = queue_children;
            ELSE
                SET queue = CONCAT(queue,',',queue_children);
            END IF;
            SET queue_length = LENGTH(queue) - LENGTH(REPLACE(queue,',','')) + 1;
        END IF;
    END WHILE;

    RETURN rv;

END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `hasCircularDependency`(`givenId` INT, `givenParentId` INT) RETURNS varchar(1024) CHARSET utf8
    DETERMINISTIC
BEGIN
    DECLARE path VARCHAR(1024);
    DECLARE delimiter CHAR(1);
    DECLARE currentId INT;
	DECLARE oldParentId INT;
    
    SELECT parent_id INTO oldParentId FROM tasks WHERE id = givenId;
    UPDATE tasks SET parent_id = givenParentId WHERE id = givenId;
    
    SET path = '|';
    SET delimiter = '|';
    SET currentId = givenId;        
    
    WHILE currentId > 0 DO
        SELECT IFNULL(parent_id,-1) INTO currentId FROM
        (SELECT parent_id FROM tasks WHERE id = currentId) A;
        IF LOCATE(CONCAT(delimiter, givenId, delimiter), path) THEN     
        	UPDATE tasks SET parent_id = oldParentId WHERE id = givenId;
        	RETURN 'yes';
        END IF;
        IF currentId > 0 THEN
            SET path = CONCAT(path, currentId, delimiter);
        END IF;
    END WHILE;
    RETURN path;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE IF NOT EXISTS `tasks` (
`id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `parent_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `title`, `status`, `parent_id`) VALUES
(1, 'Task A', 2, 0),
(2, 'Task B', 2, 1),
(3, 'Task C', 2, 1),
(4, 'Task D', 2, 2),
(5, 'Task E', 2, 3),
(6, 'Task F', 2, 3),
(7, 'Task G', 2, 0),
(8, 'Task H', 2, 0),
(9, 'Task I', 2, 8),
(10, 'Task J', 2, 8);

-- --------------------------------------------------------

--
-- Table structure for table `web_directories`
--

CREATE TABLE IF NOT EXISTS `web_directories` (
`id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `web_directory_id` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `web_directories`
--

INSERT INTO `web_directories` (`id`, `task_id`, `web_directory_id`) VALUES
(1, 1, 1),
(2, 2, 1),
(3, 3, 1),
(4, 4, 1),
(5, 5, 1),
(6, 6, 1),
(7, 7, 7),
(8, 8, 8),
(9, 9, 8),
(10, 10, 8);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `web_directories`
--
ALTER TABLE `web_directories`
 ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;
--
-- AUTO_INCREMENT for table `web_directories`
--
ALTER TABLE `web_directories`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
