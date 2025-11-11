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
use EmptyParameterException;

/**
 * keywords indexapi getitem function
 * @extends MethodClass<IndexApi>
 */
class GetitemMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Summary of __invoke
     * @param array<mixed> $args
     * @throws \EmptyParameterException
     * @see IndexApi::getitem()
     */
    public function __invoke(array $args = [])
    {
        /** @var IndexApi $indexapi */
        $indexapi = $this->indexapi();
        if (empty($args)) {
            $msg = 'Missing #(1) for #(2) module #(3) function #(4)()';
            $vars = ['arguments', 'keywords', 'indexapi', 'getitem'];
            throw new EmptyParameterException($vars, $msg);
        }

        $items = $indexapi->getitems($args);

        if (empty($items)) {
            return false;
        } elseif (count($items) > 1) {
            $msg = 'Missing or invalid #(1) for #(2) module #(3) function #(4)()';
            $vars = ['arguments', 'keywords', 'indexapi', 'getitem'];
            throw new EmptyParameterException($vars, $msg);
        } else {
            return reset($items);
        }
    }
}
