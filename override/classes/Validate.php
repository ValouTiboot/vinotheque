<?php

class Validate extends ValidateCore
{
	/**
     * Check for birthDate validity
     *
     * @param string $date birthdate to validate
     * @return bool Validity is ok or not
     */
    public static function isBirthDate($date)
    {
        if (empty($date) || $date == '0000-00-00') {
            return true;
        }
        if (preg_match('/^((?:19?[0-9]{2})|(?:20[0-9]{2}))-((?:0?[1-9])|(?:1[0-2]))-((?:0?[1-9])|(?:[1-2][0-9])|(?:3[01]))([0-9]{2}:[0-9]{2}:[0-9]{2})?$/', $date, $birth_date)) {
            if ( $birth_date[1] > date('Y')
                || ($birth_date[1] > date('Y') && $birth_date[2] > date('m'))
                || ($birth_date[1] > date('Y') && $birth_date[2] > date('m') && $birth_date[3] > date('d'))
                || ($birth_date[1] == date('Y') && $birth_date[2] == date('m') && $birth_date[3] > date('d'))
                || ($birth_date[1] == date('Y') && $birth_date[2] > date('m')) ) {
                return false;
            }
            return true;
        }
        return false;
    }
}