# 	This script runs some MySQL commands to split out chromosome, position, reference, alternate from the variation column of variations_7b1 table of the cordova_msa database.
#	Author: Robert Marini
#	DVD version: 8
#	Date: 11/29/2017
#	Note: This script may not be needed in future versions of DVD if implemented in other code during re-spin or generation of the DVD.
#		However, if not implemented in other code, this script will need to be run on every respin of the DVD.
#

USE cordova_msa;
CREATE TABLE variations_7b1_backup LIKE variations_7b1;
INSERT variations_7b1_backup AS SELECT * FROM variations_7b1;

#	CREATE TABLE variations_7b1 LIKE variations_7b1_backup;
#	INSERT variations_7b1 AS SELECT * FROM variations_7b1_backup;

ALTER TABLE variations_7b1
	ADD COLUMN chr VARCHAR(10) AFTER variation,
	ADD COLUMN pos VARCHAR(100) AFTER chr,
	ADD COLUMN ref VARCHAR(50) AFTER pos,
	ADD COLUMN alt VARCHAR(50) AFTER ref;

UPDATE variations_7b1 
	SET chr = SUBSTRING_INDEX(variation, ':', 1),
	pos = SUBSTRING_INDEX(SUBSTRING_INDEX(variation, ':', -2), ':', 1),
	ref = SUBSTRING_INDEX(SUBSTRING_INDEX(variation, ':', -1), '>', 1),
	alt = SUBSTRING_INDEX(SUBSTRING_INDEX(variation, ':', -1), '>', -1);
	
