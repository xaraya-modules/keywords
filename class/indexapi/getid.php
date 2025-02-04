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
use xarCoreCache;
use xarMod;
use sys;

sys::import('modules.keywords.class.method');

/**
 * keywords indexapi getid function
 * @extends MethodClass<IndexApi>
 */
class GetidMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    public function __invoke(array $args = [])
    {
        extract($args);

        if (!empty($module)) {
            $module_id = xarMod::getRegID($module);
        }
        if (empty($module_id) || !is_numeric($module_id)) {
            $invalid[] = 'module_id';
        }

        if (empty($itemtype)) {
            $itemtype = 0;
        }
        if (!is_numeric($itemtype)) {
            $invalid[] = 'itemtype';
        }

        if (empty($itemid)) {
            $itemid = 0;
        }
        if (!is_numeric($itemid)) {
            $invalid[] = 'itemid';
        }

        if (!empty($invalid)) {
            $msg = 'Invalid #(1) for #(2) module #(3) function #(4)()';
            $vars = [implode(', ', $invalid), 'keywords', 'indexapi', 'getid'];
            throw new BadParameterException($vars, $msg);
        }

        $cacheKey = "$module_id:$itemtype:$itemid";
        if (xarCoreCache::isCached('Keywords.Index', $cacheKey)) {
            return xarCoreCache::getCached('Keywords.Index', $cacheKey);
        }

        if (!$item = xarMod::apiFunc(
            'keywords',
            'index',
            'getitem',
            [
                'module_id' => $module_id,
                'itemtype' => $itemtype,
                'itemid' => $itemid,
            ]
        )) {
            $item = xarMod::apiFunc(
                'keywords',
                'index',
                'createitem',
                [
                    'module_id' => $module_id,
                    'itemtype' => $itemtype,
                    'itemid' => $itemid,
                ]
            );
        }

        xarCoreCache::setCached('Keywords.Index', $cacheKey, $item['id']);

        return $item['id'];
    }
}
