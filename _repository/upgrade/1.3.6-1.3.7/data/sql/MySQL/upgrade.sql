--
-- Author:        Pierre-Henry Soria <ph7software@gmail.com>
-- Copyright:     (c) 2013-2017, Pierre-Henry Soria. All Rights Reserved.
-- License:       GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
--


UPDATE PH7_Settings SET `name` = 'bgProfileManualApproval' WHERE `name` = 'profileBackgroundManualApproval';

INSERT INTO PH7_Settings (`name`, value, `desc`, `group`) VALUES
('socialMediaWidgets', 0, 'Enable the Social Media Widgets such as Like and Sharing buttons. 0 = Disable | 1 = Enable', 'general');