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


use Xaraya\Modules\Keywords\AdminApi;
use Xaraya\Modules\MethodClass;
use xarMod;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * keywords adminapi removehook function
 * @extends MethodClass<AdminApi>
 */
class RemovehookMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * delete all entries for a module - hook for ('module','remove','API')
     * @param mixed $args ['objectid'] ID of the object (must be the module name here !!)
     * @param mixed $args ['extrainfo'] extra information
     * @return bool|void true on success, false on failure
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (empty($extrainfo)) {
            $extrainfo = [];
        }

        // When called via hooks, we should get the real module name from objectid
        // here, because the current module is probably going to be 'modules' !!!
        if (!isset($objectid) || !is_string($objectid)) {
            $msg = 'Invalid #(1) for #(2) module #(3) function #(4)()';
            $vars = ['objectid (module name)', 'keywords', 'adminapi', 'removehook'];
            throw new BadParameterException($vars, $msg);
        }

        $modname = $objectid;

        $modid = xarMod::getRegId($modname);
        if (empty($modid)) {
            $msg = 'Invalid #(1) for #(2) module #(3) function #(4)()';
            $vars = ['objectid (module name)', 'keywords', 'adminapi', 'removehook'];
            throw new BadParameterException($vars, $msg);
        }

        // delete all words associated with this module
        if (!xarMod::apiFunc(
            'keywords',
            'words',
            'deleteitems',
            [
                'module_id' => $modid,
            ]
        )) {
            return;
        }

        // delete all indexes for this module
        if (!xarMod::apiFunc(
            'keywords',
            'index',
            'deleteitems',
            [
                'module_id' => $modid,
            ]
        )) {
            return;
        }

        // Return the extra info
        return $extrainfo;
    }
}
