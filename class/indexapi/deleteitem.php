<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.6.2
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords\IndexApi;

use Xaraya\Modules\Keywords\MethodClass;
use Xaraya\Modules\Keywords\IndexApi;
use BadParameterException;
use xarMod;
use sys;

sys::import('modules.keywords.class.method');

/**
 * keywords indexapi deleteitem function
 * @extends MethodClass<IndexApi>
 */
class DeleteitemMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    public function __invoke(array $args = [])
    {
        if (empty($args['id']) || !is_numeric($args['id'])) {
            $invalid[] = 'id';
        }

        if (!empty($invalid)) {
            $msg = 'Invalid #(1) for #(2) module #(3) function #(4)()';
            $vars = [implode(', ', $invalid), 'keywords', 'indexapi', 'deleteitem'];
            throw new BadParameterException($vars, $msg);
        }

        return xarMod::apiFunc('keywords', 'index', 'deleteitems', $args);
    }
}
