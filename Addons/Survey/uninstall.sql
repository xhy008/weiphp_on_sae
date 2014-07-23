DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='survey' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='survey' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_survey`;

DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='survey_question' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='survey_question' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_survey_question`;

DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='survey_answer' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='survey_answer' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_survey_answer`;