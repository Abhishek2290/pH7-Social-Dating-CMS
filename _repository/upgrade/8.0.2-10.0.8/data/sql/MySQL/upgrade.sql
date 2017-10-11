--
-- Author:        Pierre-Henry Soria <hi@ph7.me>
-- Copyright:     (c) 2017, Pierre-Henry Soria. All Rights Reserved.
-- License:       GNU General Public License
--

-- Change reserved MySQL column names
-- Using backticks around reserved MySQL words is ugly and not working well with all environments
ALTER TABLE pH7_Settings CHANGE `name` settingName varchar(64) NOT NULL;
ALTER TABLE pH7_Settings CHANGE `value` settingValue varchar(150) DEFAULT '';
ALTER TABLE pH7_Settings CHANGE `desc` description varchar(120) DEFAULT '' COMMENT 'Informative desc about the setting';
ALTER TABLE pH7_Settings CHANGE `group` settingGroup varchar(12) NOT NULL;

-- Change pH7_Settings's primary key
ALTER TABLE pH7_Settings DROP PRIMARY KEY, ADD PRIMARY KEY (settingName);

-- Update pH7CMS's SQL schema version
UPDATE PH7_Modules SET version = '1.4.1' WHERE vendorName = 'pH7CMS';