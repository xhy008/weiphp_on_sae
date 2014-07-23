DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='card_coupons' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='card_coupons' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_card_coupons`;

DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='card_notice' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='card_notice' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_card_notice`;

DELETE FROM `wp_attribute` WHERE model_id = (SELECT id FROM wp_model WHERE `name`='card_member' ORDER BY id DESC LIMIT 1);
DELETE FROM `wp_model` WHERE `name`='card_member' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_card_member`;