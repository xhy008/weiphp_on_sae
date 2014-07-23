DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='test' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='test' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_test`;

DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='test_question' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='test_question' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_test_question`;

DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='test_answer' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='test_answer' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_test_answer`;