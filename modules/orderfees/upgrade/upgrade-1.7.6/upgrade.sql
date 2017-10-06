# Table : order_cart_rule
ALTER TABLE `_DB_PREFIX_order_cart_rule`
        ADD COLUMN `quantity` INT(10) NOT NULL DEFAULT '1',
        ADD COLUMN `unit_value_real` DECIMAL(17,2) NOT NULL DEFAULT '0.00',
        ADD COLUMN `unit_value_tax_exc` DECIMAL(17,2) NOT NULL DEFAULT '0.00',
        ADD COLUMN `tax_rate` DECIMAL(10,3) NOT NULL DEFAULT '0.00';

# Table : cart_rule
ALTER TABLE `_DB_PREFIX_cart_rule` ADD COLUMN `display_visible` INT(10) NOT NULL DEFAULT '0';
ALTER TABLE `_DB_PREFIX_cart_rule` ADD COLUMN `display_selectable` INT(10) NOT NULL DEFAULT '0';