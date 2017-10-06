# Table : cart_rule								
ALTER TABLE `_DB_PREFIX_cart_rule` ADD COLUMN `package_restriction` TINYINT(1) NOT NULL DEFAULT '0';

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