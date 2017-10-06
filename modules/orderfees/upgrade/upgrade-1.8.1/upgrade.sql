# Set visibility for saved fees
UPDATE `_DB_PREFIX_cart_rule`
    SET `display_visible` = 181, `display_selectable` = 0
    WHERE `is_fee` > 0;

# Remove visibility from is_fee column
UPDATE `_DB_PREFIX_cart_rule`
    SET `is_fee` = (`is_fee` - 252)
    WHERE `is_fee` > 252;

# Replace IS_REDUCTION value from 512 to 4
UPDATE `_DB_PREFIX_cart_rule`
    SET `is_fee` = (`is_fee` - 512 + 4)
    WHERE `is_fee` & 512;