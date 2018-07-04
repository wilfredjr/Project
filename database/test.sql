DELIMITER $$

USE `project_monitoring`$$

DROP PROCEDURE IF EXISTS `update_project`$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `update_project`()
Block1: BEGIN
 DECLARE done INT DEFAULT FALSE;
 DECLARE done1 INT DEFAULT FALSE;
 DECLARE a,b INT;
 DECLARE cur1 CURSOR FOR SELECT project_id FROM project_phase_dates WHERE (status_id=1 OR status_id=3) AND date_end<=DATE_ADD(CURDATE(),INTERVAL -1 DAY);
 DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE, done1=TRUE;
 
 OPEN cur1;
	read_loop: LOOP
	FETCH cur1 INTO a;
	IF done THEN
	LEAVE read_loop;
	END IF;
	
Block2: BEGIN
DECLARE cur2 CURSOR FOR SELECT project_phase_id FROM project_phase_dates WHERE (status_id=1 OR status_id=3) AND project_id=a AND date_end<=DATE_ADD(CURDATE(),INTERVAL -1 DAY);
 OPEN cur2;
	read_loop1: LOOP
	FETCH cur2 INTO b;
	IF done1 THEN
	LEAVE read_loop1;
	END IF;
	
	UPDATE project_task_list SET status_id=4 WHERE project_id=a AND project_phase_id=b;

END LOOP read_loop1;	  
CLOSE cur2;
END Block2;
	    
	UPDATE projects SET project_status_id=4 WHERE id=a;
	UPDATE project_phase_dates SET status_id=4 WHERE (status_id=1 OR status_id=3) AND project_id=a AND date_end<=DATE_ADD(CURDATE(),INTERVAL -1 DAY);
	
  END LOOP read_loop;
  CLOSE cur1;
  END Block1;
END$$

DELIMITER ;