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
use ForbiddenOperationException;
use sys;

sys::import('modules.keywords.class.method');

/**
 * keywords indexapi updateitem function
 * @extends MethodClass<IndexApi>
 */
class UpdateitemMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    public function __invoke(array $args = [])
    {
        // there's absolutely no good reason to need this, once created an index never changes
        throw new ForbiddenOperationException(null, 'Changing an index is not permitted');
    }
}
