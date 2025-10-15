<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.6.2
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords\AdminApi;

use Xaraya\Modules\Keywords\AdminApi;
use Xaraya\Modules\Keywords\IndexApi;
use Xaraya\Modules\Keywords\WordsApi;
use Xaraya\Modules\Keywords\MethodClass;
use sys;
use BadParameterException;

sys::import('modules.keywords.method');

/**
 * keywords adminapi deletehook function
 * @extends MethodClass<AdminApi>
 */
class DeletehookMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * delete entry for a module item - hook for ('item','delete','API')
     * @param array<mixed> $args
     * @var mixed $objectid ID of the object
     * @var mixed $extrainfo extra information
     * @return bool|void true on success, false on failure
     * @see AdminApi::deletehook()
     */
    public function __invoke(array $args = [])
    {
        extract($args);
        /** @var IndexApi $indexapi */
        $indexapi = $this->indexapi();
        /** @var WordsApi $wordsapi */
        $wordsapi = $this->wordsapi();

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
            $modname = $this->mod()->getName();
        } else {
            $modname = $extrainfo['module'];
        }

        $modid = $this->mod()->getRegID($modname);
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
        $index_id = $indexapi->getid(
            [
                'module' => $modname,
                'itemtype' => $itemtype,
                'itemid' => $itemid,
            ]
        );

        // delete all keywords associated with this item
        if (!$wordsapi->deleteitems(
            [
                'index_id' => $index_id,
            ]
        )) {
            return;
        }

        // delete the index
        if (!$indexapi->deleteitem(
            [
                'id' => $index_id,
            ]
        )) {
            return;
        }

        return $extrainfo;
    }
}
