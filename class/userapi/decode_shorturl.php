<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.6.2
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords\UserApi;

use Xaraya\Modules\Keywords\UserApi;
use Xaraya\Modules\Keywords\MethodClass;
use sys;
use BadParameterException;

sys::import('modules.keywords.class.method');

/**
 * keywords userapi decode_shorturl function
 * @extends MethodClass<UserApi>
 */
class DecodeShorturlMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * extract function and arguments from short URLs for this module, and pass
     * them back to xarGetRequestInfo()
     * @author the Example module development team
     * @param mixed $params array containing the different elements of the virtual path
     * @return array|void containing func the function to be called and args the query
     * string arguments, or empty if it failed
     * @see UserApi::decodeShorturl()
     */
    public function __invoke(array $params = [])
    {
        // Initialise the argument list we will return
        $args = [];
        $module = 'keywords';

        if (empty($params[1])) {
            return ['main', $args];
        } elseif (preg_match('/^tab[0-5]$/', $params[1])) {
            $args['tab'] = $params[1][3];
            return ['main',$args];
        } elseif (!empty($params[1])) {
            $args['keyword'] = $params[1];
            if (!empty($params[2]) && is_numeric($params[2])) {
                $args['id'] = $params[2];
            }
            return ['main',$args];
        } else {
            // default : return nothing -> no short URL decoded
        }
    }
}
