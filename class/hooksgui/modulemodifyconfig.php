<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.6.2
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords\Hooks;

use Xaraya\Modules\Keywords\MethodClass;
use Xaraya\Modules\Keywords\HooksGui;
use BadParameterException;
use xarMod;
use xarTpl;
use sys;

sys::import('modules.keywords.class.method');

/**
 * keywords hooks modulemodifyconfig function
 * @extends MethodClass<HooksGui>
 */
class ModulemodifyconfigMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    public function __invoke(array $args = [])
    {
        extract($args);

        if (empty($extrainfo)) {
            $extrainfo = [];
        }

        // objectid is the name of the module
        if (empty($objectid)) {
            if (!empty($extrainfo['module']) && is_string($extrainfo['module'])) {
                $objectid = $extrainfo['module'];
            } else {
                $objectid = xarMod::getName();
            }
        }

        if (!isset($objectid) || !is_string($objectid)) {
            $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
            $vars = ['objectid (module name)', 'hooks', 'modifyconfig', 'keywords'];
            throw new BadParameterException($vars, $msg);
        }

        $modname = $objectid;

        $modid = xarMod::getRegID($modname);
        if (empty($modid)) {
            $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
            $vars = ['module', 'hooks', 'modifyconfig', 'keywords'];
            throw new BadParameterException($vars, $msg);
        }

        if (!empty($extrainfo['itemtype']) && is_numeric($extrainfo['itemtype'])) {
            $itemtype = $extrainfo['itemtype'];
        } else {
            $itemtype = 0;
        }

        $data = xarMod::apiFunc(
            'keywords',
            'hooks',
            'getsettings',
            [
                'module' => $modname,
                'itemtype' => $itemtype,
            ]
        );

        if (!empty($data['restrict_words'])) {
            $restricted_list = xarMod::apiFunc(
                'keywords',
                'words',
                'getwords',
                [
                    'index_id' => $data['index_id'],
                ]
            );
            $data['restricted_list'] = implode(', ', $restricted_list);
        }

        if (empty($data['delimiters'])) {
            $data['delimiters'] = ',';
        }

        $data['module'] = $modname;
        $data['module_id'] = $modid;
        $data['itemtype'] = $itemtype;

        $data['context'] ??= $this->getContext();
        return xarTpl::module('keywords', 'hooks', 'modulemodifyconfig', $data);
    }
}
