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
use xarMod;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * keywords adminapi deletehook function
 */
class DeletehookMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * delete entry for a module item - hook for ('item','delete','API')
     * @param mixed $args ['objectid'] ID of the object
     * @param mixed $args ['extrainfo'] extra information
     * @return bool true on success, false on failure
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (empty($extrainfo)) {
            $extrainfo = [];
        }

        if (!isset($objectid) || !is_numeric($objectid)) {
            $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
            $vars = ['objectid', 'adminapi', 'deletehook', 'keywords'];
            throw new BadParameterException($vars, $msg);
        }

        // When called via hooks, the module name may be empty. Get it from current module.
        if (empty($extrainfo['module'])) {
            $modname = xarMod::getName();
        } else {
            $modname = $extrainfo['module'];
        }

        $modid = xarMod::getRegId($modname);
        if (empty($modid)) {
            $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
            $vars = ['module', 'adminapi', 'deletehook', 'keywords'];
            throw new BadParameterException($vars, $msg);
        }

        if (!empty($extrainfo['itemtype']) && is_numeric($extrainfo['itemtype'])) {
            $itemtype = $extrainfo['itemtype'];
        } else {
            $itemtype = 0;
        }

        if (!empty($extrainfo['itemid']) && is_numeric($extrainfo['itemid'])) {
            $itemid = $extrainfo['itemid'];
        } else {
            $itemid = $objectid;
        }

        // get the index_id for this module/itemtype/item
        $index_id = xarMod::apiFunc(
            'keywords',
            'index',
            'getid',
            [
                'module' => $modname,
                'itemtype' => $itemtype,
                'itemid' => $itemid,
            ]
        );

        // delete all keywords associated with this item
        if (!xarMod::apiFunc(
            'keywords',
            'words',
            'deleteitems',
            [
                'index_id' => $index_id,
            ]
        )) {
            return;
        }

        // delete the index
        if (!xarMod::apiFunc(
            'keywords',
            'index',
            'deleteitem',
            [
                'id' => $index_id,
            ]
        )) {
            return;
        }

        return $extrainfo;
    }
}
