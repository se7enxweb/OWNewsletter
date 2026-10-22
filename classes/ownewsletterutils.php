<?php

class OWNewsletterUtils {

    /**
     * generate a unique hash md5
     *
     * @param string $flexibleVar is used as a part of string for md5
     * @return string md5
     */
    static function generateUniqueMd5Hash( $flexibleVar = '' ) {
        $stringForHash = $flexibleVar . '-' . microtime( true ) . '-' . mt_rand() . '-' . mt_rand();
        return md5( $stringForHash );
    }

    /**
     * Convert array to string
     * ;$1;$2;$3;
     * for searching : begin and end is ";"
     * like %;$1;%
     *
     * @param array $array
     * @return string
     */
    static function arrayToString( $array ) {
        return ';' . implode( ';', $array ) . ';';
    }

    /**
     * Convert string to array
     * ;$1;$2;$3; to array( $1, $2, $3 )
     *
     * @param $string
     * @return unknown_type
     */
    static function stringToArray( $string ) {
        $string = substr( $string, 1, strlen( $string ) - 2 );
        return empty( $string ) ? null : explode( ';', $string );
    }

    /**
     * Parse a duration setting such as ConfirmationTimeOut into seconds.
     * Accepted: a whole number ("518400") or a product of whole numbers
     * ("6*24*60*60"), with optional spaces around "*". Nothing else is
     * accepted and nothing is evaluated.
     *
     * @param mixed $value
     * @return int|false the number of seconds, false when the value is not valid
     */
    static function parseDurationSeconds( $value ) {
        if( !is_scalar( $value ) ) {
            return false;
        }
        $value = trim( (string) $value );
        if( !preg_match( '/^\d+(\s*\*\s*\d+)*$/', $value ) ) {
            return false;
        }
        $seconds = 1;
        foreach( explode( '*', $value ) as $factor ) {
            $factor = ltrim( trim( $factor ), '0' );
            if( $factor === '' ) {
                return 0;
            }
            if( strlen( $factor ) > 18 ) {
                return false;
            }
            $factor = (int) $factor;
            if( $seconds > intdiv( PHP_INT_MAX, $factor ) ) {
                return false;
            }
            $seconds *= $factor;
        }
        return $seconds;
    }

}
