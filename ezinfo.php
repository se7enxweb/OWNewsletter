<?php
/**
 * File containing the ownewsletterInfo class.
 *
 * @copyright Copyright (C) 2013 - 2015 Open Wide. All rights reserved.
 * @copyright Copyright (C) 1998 - 2026 7x and the Exponential Foundation. All rights reserved.
 * @license GNU General Public License v2.0
 * @package ownewsletter
 */
class ownewsletterInfo
{
    // The release version; also sent in the version header of every newsletter mail
    const SOFTWARE_VERSION = '0.1.1';

    static function info()
    {
        return array(
            'Name' => "<a href='https://github.com/se7enxweb/OWNewsletter'>OWNewsletter - Multi Channel Marketing</a>",
            'Version' => self::SOFTWARE_VERSION,
            'Copyright' => array( "Copyright (C) 2013 - 2015 <a href='http://www.openwide.fr'>Open Wide</a>",
                                  "Copyright (C) 1998 - 2026 7x and the Exponential Foundation" ),
            'Author' => "Open Wide and 7x",
            'License' => "GNU General Public License v2.0",
            'Info_url' => "https://github.com/se7enxweb/OWNewsletter"
        );
    }

    /**
     * get some additional infos about the newsletter
     * for future use
     */
    static function packageInfo()
    {
        $infoArray = array();
        $infoArray[ 'release_version' ] = self::SOFTWARE_VERSION;
        return $infoArray;
    }
}
?>
