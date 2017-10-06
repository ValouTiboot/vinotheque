# Disable fees						
UPDATE `_DB_PREFIX_cart_rule` SET `active` = 0 WHERE `is_fee` & 1;

# Table : cart_rule								
ALTER TABLE `_DB_PREFIX_cart_rule` DROP COLUMN `is_fee`;
ALTER TABLE `_DB_PREFIX_cart_rule` DROP COLUMN `payment_restriction`;
ALTER TABLE `_DB_PREFIX_cart_rule` DROP COLUMN `dimension_restriction`;
ALTER TABLE `_DB_PREFIX_cart_rule` DROP COLUMN `zipcode_restriction`;
ALTER TABLE `_DB_PREFIX_cart_rule` DROP COLUMN `maximum_amount`;
ALTER TABLE `_DB_PREFIX_cart_rule` DROP COLUMN `maximum_amount_tax`;
ALTER TABLE `_DB_PREFIX_cart_rule` DROP COLUMN `maximum_amount_currency`;
ALTER TABLE `_DB_PREFIX_cart_rule` DROP COLUMN `maximum_amount_shipping`;
ALTER TABLE `_DB_PREFIX_cart_rule` DROP COLUMN `package_restriction`;
ALTER TABLE `_DB_PREFIX_cart_rule` DROP COLUMN `tax_rules_group`;
ALTER TABLE `_DB_PREFIX_cart_rule` DROP COLUMN `display_visible`;
ALTER TABLE `_DB_PREFIX_cart_rule` DROP COLUMN `display_selectable`;

# Table : cart_rule_payment
DROP TABLE IF EXISTS `_DB_PREFIX_cart_rule_payment`;

# Table : cart_rule_dimension_rule_group
DROP TABLE IF EXISTS `_DB_PREFIX_cart_rule_dimension_rule_group`;

# Table : cart_rule_dimension_rule
DROP TABLE IF EXISTS `_DB_PREFIX_cart_rule_dimension_rule`;

# Table : cart_rule_zipcode_rule_group
DROP TABLE IF EXISTS `_DB_PREFIX_cart_rule_zipcode_rule_group`;

# Table : cart_rule_zipcode_rule
DROP TABLE IF EXISTS `_DB_PREFIX_cart_rule_zipcode_rule`;

# Table : cart_rule_package_rule_group
DROP TABLE IF EXISTS `_DB_PREFIX_cart_rule_package_rule_group`;

# Table : cart_rule_package_rule
DROP TABLE IF EXISTS `_DB_PREFIX_cart_rule_package_rule`;