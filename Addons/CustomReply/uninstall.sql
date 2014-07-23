DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='custom_reply_mult' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='custom_reply_mult' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_custom_reply_mult`;

DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='custom_reply_news' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='custom_reply_news' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_custom_reply_news`;

DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='custom_reply_text' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='custom_reply_text' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_custom_reply_text`;