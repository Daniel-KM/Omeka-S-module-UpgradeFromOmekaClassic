<?php
namespace UpgradeFromOmekaClassic\View\Helper;

/**
 * @note Upgraded from Omeka Classic via the plugin "Upgrade to Omeka S".
 * @link https://github.com/Daniel-KM/UpgradeToOmekaS
 * @link https://github.com/Daniel-KM/UpgradeFromOmekaClassic
 * @internal Copied methods removed between Omeka C and Omeka S.
 * Methods that are in Zend or Doctrine should be used preferably.
 *
 * @author Bermi Ferrer Martinez <bermi akelos com>
 * @copyright Copyright (c) 2002-2006, Akelos Media, S.L. http://www.akelos.org
 * @license GNU Lesser General Public License <http://www.gnu.org/copyleft/lesser.html>
 * @since 0.1
 * @package Akelos
 */

// +----------------------------------------------------------------------+
// | Akelos PHP Application Framework                                     |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2006, Akelos Media, S.L.  http://www.akelos.com/  |
// | Released under the GNU Lesser General Public License                 |
// +----------------------------------------------------------------------+
// | You should have received the following files along with this library |
// | - COPYRIGHT (Additional copyright notice)                            |
// | - DISCLAIMER (Disclaimer of warranty)                                |
// | - README (Important information regarding this library)              |
// +----------------------------------------------------------------------+


/**
 * Inflector for pluralize and singularize English nouns.
 *
 * This Inflector is a port of Ruby on Rails Inflector.
 *
 * It can be really helpful for developers that want to
 * create frameworks based on naming conventions rather than
 * configurations.
 *
 * It was ported to PHP for the Akelos Framework, a
 * multilingual Ruby on Rails like framework for PHP that will
 * be launched soon.
 *
 * @author Bermi Ferrer Martinez <bermi akelos com>
 * @copyright Copyright (c) 2002-2006, Akelos Media, S.L. http://www.akelos.org
 * @license GNU Lesser General Public License <http://www.gnu.org/copyleft/lesser.html>
 * @since 0.1
 */
class Inflector
{
    // {{{ titleize()

    /**
     * Converts an underscored or CamelCase word into a English
     * sentence.
     *
     * The titleize function converts text like "WelcomePage",
     * "welcome_page" or  "welcome page" to this "Welcome
     * Page".
     * If second parameter is set to 'first' it will only
     * capitalize the first character of the title.
     *
     * @access public
     * @static
     * @param    string    $word    Word to format as tile
     * @param    string    $uppercase    If set to 'first' it will only uppercase the
     * first character. Otherwise it will uppercase all
     * the words in the title.
     * @return string Text formatted as title
     */
    static function titleize($word, $uppercase = '')
    {
        $word = self::humanize(self::underscore($word));
        return $uppercase == 'first' ? ucfirst($word) : ucwords($word);
    }

    // }}}
    // {{{ underscore()

    /**
     * Converts a word "into_it_s_underscored_version"
     *
     * Convert any "CamelCased" or "ordinary Word" into an
     * "underscored_word".
     *
     * This can be really useful for creating friendly URLs.
     *
     * @access public
     * @static
     * @param    string    $word    Word to underscore
     * @return string Underscored word
     */
    static function underscore($word)
    {
        return  strtolower(
            preg_replace('/[^A-Z^a-z^0-9]+/','_',
            preg_replace('/([a-z\d])([A-Z])/','\1_\2',
            preg_replace('/([A-Z]+)([A-Z][a-z])/','\1_\2', $word))));
    }

    // }}}
    // {{{ humanize()

    /**
     * Returns a human-readable string from $word
     *
     * Returns a human-readable string from $word, by replacing
     * underscores with a space, and by upper-casing the initial
     * character by default.
     *
     * If you need to uppercase all the words you just have to
     * pass 'all' as a second parameter.
     *
     * @access public
     * @static
     * @param    string    $word    String to "humanize"
     * @param    string    $uppercase    If set to 'all' it will uppercase all the words
     * instead of just the first one.
     * @return string Human-readable word
     */
    static function humanize($word, $uppercase = '')
    {
        $word = str_replace('_',' ',preg_replace('/_id$/', '',$word));
        return $uppercase == 'all' ? ucwords($word) : ucfirst($word);
    }

    // }}}
    // {{{ variablize()

    /**
     * Same as camelize but first char is underscored
     *
     * Converts a word like "send_email" to "sendEmail". It
     * will remove non alphanumeric character from the word, so
     * "who's online" will be converted to "whoSOnline"
     *
     * @access public
     * @static
     * @see camelize
     * @param    string    $word    Word to lowerCamelCase
     * @return string Returns a lowerCamelCasedWord
     */
    static function variablize($word)
    {
        $word = \Doctrine\Common\Inflector\Inflector::camelize($word);
        return strtolower($word[0]).substr($word,1);
    }

    // }}}
    // {{{ ordinalize()

    /**
     * Converts number to its ordinal English form.
     *
     * This method converts 13 to 13th, 2 to 2nd ...
     *
     * @access public
     * @static
     * @param    integer    $number    Number to get its ordinal value
     * @return string Ordinal representation of given string.
     */
    static function ordinalize($number)
    {
        if (in_array(($number % 100),range(11,13))){
            return $number.'th';
        }else{
            switch (($number % 10)) {
                case 1:
                return $number.'st';
                break;
                case 2:
                return $number.'nd';
                break;
                case 3:
                return $number.'rd';
                default:
                return $number.'th';
                break;
            }
        }
    }

    // }}}

}
