<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords\AdminApi;

use Xaraya\Modules\MethodClass;
use xarModVars;
use xarVar;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * keywords adminapi separatekeywords function
 */
class SeparatekeywordsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Now using 'strlist' validation to do the hard work.
     * @return array
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        $delimiters = xarModVars::get('keywords', 'delimiters');

        // Colons are the only character we can't use (ATM).
        // TODO: remove this then xarVar::validate() is able to handle escape
        // sequences for colons as data in the validation rules.
        str_replace(':', '', $delimiters);

        // Ensure we can fall back to a default.
        if (empty($delimiters)) {
            $delimiters = ';';
        }

        // Get first delimiter for creating the array.
        $first = substr($delimiters, 0, 1);

        // Normalise the delimiters and trim the strings.
        xarVar::validate("strlist:$delimiters:pre:trim", $keywords);

        // Explode into an array of words.
        $words = explode($first, $keywords);

        return $words;
    }
}
