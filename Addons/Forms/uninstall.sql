DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='forms' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='forms' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_forms`;

DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='forms_attribute' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='forms_attribute' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_forms_attribute`;

DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='forms_value' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='forms_value' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_forms_value`;