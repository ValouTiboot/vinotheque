# Table : cart_rule - precheck
SELECT count(*) INTO @exist
FROM information_schema.columns 
WHERE table_schema = '_DB_NAME_'
    AND table_name = '_DB_PREFIX_cart_rule'
    AND column_name = 'is_fee';

SET @query = IF(
    @exist <= 0,
    "ALTER TABLE `_DB_PREFIX_cart_rule` ADD COLUMN `is_fee` INT(10) UNSIGNED NOT NULL DEFAULT '0'", 
    "SELECT 'column is_fee exists' status"
);

PREPARE stmt FROM @query;
EXECUTE stmt;

# Table : order_cart_rule (with precheck)
SELECT count(*) INTO @exist
FROM information_schema.columns 
WHERE table_schema = '_DB_NAME_'
    AND table_name = '_DB_PREFIX_order_cart_rule'
    AND column_name = 'unit_value_real';

SET @query = IF(
    @exist <= 0,
    "
        ALTER TABLE `_DB_PREFIX_order_cart_rule` ADD COLUMN `quantity` INT(10) NOT NULL DEFAULT '1',
        ADD COLUMN `unit_value_real` DECIMAL(17,2) NOT NULL DEFAULT '0.00',
        ADD COLUMN `unit_value_tax_exc` DECIMAL(17,2) NOT NULL DEFAULT '0.00',
        ADD COLUMN `tax_rate` DECIMAL(10,3) NOT NULL DEFAULT '0.00';
    ", 
    "SELECT 'column is_fee exists' status"
);

PREPARE stmt FROM @query;
EXECUTE stmt;

# Table : cart_rule								
ALTER TABLE `_DB_PREFIX_cart_rule` ADD COLUMN `payment_restriction` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `_DB_PREFIX_cart_rule` ADD COLUMN `dimension_restriction` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `_DB_PREFIX_cart_rule` ADD COLUMN `zipcode_restriction` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `_DB_PREFIX_cart_rule` ADD COLUMN `maximum_amount` DECIMAL(17,2) NOT NULL DEFAULT '0.00';
ALTER TABLE `_DB_PREFIX_cart_rule` ADD COLUMN `maximum_amount_tax` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `_DB_PREFIX_cart_rule` ADD COLUMN `maximum_amount_currency` INT(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `_DB_PREFIX_cart_rule` ADD COLUMN `maximum_amount_shipping` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `_DB_PREFIX_cart_rule` ADD COLUMN `package_restriction` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `_DB_PREFIX_cart_rule` ADD COLUMN `tax_rules_group` INT(10) NOT NULL DEFAULT '0';
ALTER TABLE `_DB_PREFIX_cart_rule` ADD COLUMN `display_visible` INT(10) NOT NULL DEFAULT '0';
ALTER TABLE `_DB_PREFIX_cart_rule` ADD COLUMN `display_selectable` INT(10) NOT NULL DEFAULT '0';

# Table : cart_rule_payment
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_cart_rule_payment` (
    `id_cart_rule` INT(10) UNSIGNED NOT NULL,
    `id_module` INT(10) UNSIGNED NOT NULL,
    PRIMARY KEY (`id_cart_rule`, `id_module`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;

# Table : cart_rule_dimension_rule_group
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_cart_rule_dimension_rule_group` (
    `id_dimension_rule_group` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_cart_rule` INT(10) UNSIGNED NOT NULL,
    `base` VARCHAR(32) NOT NULL,
    PRIMARY KEY (`id_dimension_rule_group`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;

# Table : cart_rule_dimension_rule
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_cart_rule_dimension_rule` (
    `id_dimension_rule` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_dimension_rule_group` INT(10) UNSIGNED NOT NULL,
    `type` VARCHAR(32) NOT NULL,
    `operator` CHAR(5) NOT NULL,
    `value` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id_dimension_rule`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;

# Table : cart_rule_zipcode_rule_group
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_cart_rule_zipcode_rule_group` (
    `id_zipcode_rule_group` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_cart_rule` INT(10) UNSIGNED NOT NULL,
    PRIMARY KEY (`id_zipcode_rule_group`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;

# Table : cart_rule_zipcode_rule
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_cart_rule_zipcode_rule` (
    `id_zipcode_rule` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_zipcode_rule_group` INT(10) UNSIGNED NOT NULL,
    `type` VARCHAR(32) NOT NULL,
    `operator` CHAR(5) NOT NULL,
    `value` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id_zipcode_rule`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;

# Table : cart_rule_package_rule_group
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_cart_rule_package_rule_group` (
    `id_package_rule_group` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_cart_rule` INT(10) UNSIGNED NOT NULL,
    `unit` VARCHAR(32) NOT NULL,
    `unit_weight` VARCHAR(32) NOT NULL,
    `ratio` VARCHAR(32) NOT NULL,
    PRIMARY KEY (`id_package_rule_group`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;

# Table : cart_rule_package_rule
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_cart_rule_package_rule` (
    `id_package_rule` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_package_rule_group` INT(10) UNSIGNED NOT NULL,
    `range_start` DECIMAL(10,2) NOT NULL,
    `range_end` DECIMAL(10,2) NOT NULL,
    `round` DECIMAL(10,2) NOT NULL DEFAULT '1',
    `divider` DECIMAL(10,2) NOT NULL DEFAULT '1',
    `currency` INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `tax` TINYINT(1) NOT NULL DEFAULT '0',
    `value` DECIMAL(17,2) NOT NULL DEFAULT '0.00',
    PRIMARY KEY (`id_package_rule`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;