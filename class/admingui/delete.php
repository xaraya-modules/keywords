<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords\AdminGui;


use Xaraya\Modules\Keywords\AdminGui;
use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarVar;
use xarController;
use xarMod;
use xarSec;
use sys;
use EmptyParameterException;
use Exception;

sys::import('xaraya.modules.method');

/**
 * keywords admin delete function
 * @extends MethodClass<AdminGui>
 */
class DeleteMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * delete existing keywords assignment
     */
    public function __invoke(array $args = [])
    {
        if (!$this->sec()->checkAccess('ManageKeywords')) {
            return;
        }

        $data = [];

        if (!$this->var()->fetch(
            'module_id',
            'id',
            $module_id,
            null,
            xarVar::DONT_SET
        )) {
            return;
        }
        if (!$this->var()->fetch(
            'itemtype',
            'id',
            $itemtype,
            null,
            xarVar::DONT_SET
        )) {
            return;
        }
        if (!$this->var()->fetch(
            'itemid',
            'id',
            $itemid,
            null,
            xarVar::DONT_SET
        )) {
            return;
        }
        if (!$this->var()->fetch(
            'return_url',
            'pre:trim:str:1:',
            $return_url,
            '',
            xarVar::NOT_REQUIRED
        )) {
            return;
        }

        if (empty($return_url)) {
            $return_url = $this->mod()->getURL(
                'admin',
                'stats',
                [
                    'module_id' => $module_id,
                    'itemtype' => $itemtype,
                ]
            );
        }

        if (empty($module_id)) {
            $invalid[] = 'module_id';
        }
        if (empty($itemid)) {
            $invalid[] = 'itemid';
        }

        if (!empty($invalid)) {
            $msg = 'Missing #(1) for #(2) module #(3) function #(4)()';
            $vars = [implode(', ', $invalid), 'keywords', 'admin', 'delete'];
            throw new EmptyParameterException($vars, $msg);
        }

        if (!$this->var()->fetch(
            'phase',
            'pre:trim:lower:enum:confirm',
            $phase,
            'form',
            xarVar::NOT_REQUIRED
        )) {
            return;
        }

        $modname = xarMod::getName($module_id);

        if ($phase == 'confirm') {
            if (!$this->var()->fetch(
                'cancel',
                'checkbox',
                $cancel,
                false,
                xarVar::NOT_REQUIRED
            )) {
                return;
            }
            if ($cancel) {
                $this->ctl()->redirect($return_url);
            }
            if (!$this->sec()->confirmAuthKey()) {
                return xarController::badRequest('bad_author', $this->getContext());
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
            $this->ctl()->redirect($return_url);
        }

        try {
            $item = xarMod::apiFunc(
                $modname,
                'user',
                'getitemlinks',
                ['itemtype' => $itemtype,
                    'itemids' => [$itemid]]
            );
            $item = reset($item);
        } catch (Exception $e) {
            $item = [
                'label' => $this->ml('Item #(1)', $itemid),
                'title' => $this->ml('Display Item #(1)', $itemid),
                'url' => xarController::URL(
                    $modname,
                    'user',
                    'display',
                    ['itemtype' => $itemtype, 'itemid' => $itemid]
                ),
            ];
        }

        $modlist = xarMod::apiFunc(
            'keywords',
            'words',
            'getmodulecounts',
            [
                'skip_restricted' => true,
            ]
        );
        $modtypes = [];
        $modules = [];
        foreach ($modlist as $module => $itemtypes) {
            $modules[$module] = xarMod::getBaseInfo($module);
            $modules[$module]['itemtypes'] = $itemtypes;
            if (!isset($modtypes[$module])) {
                try {
                    $modtypes[$module] = xarMod::apiFunc($module, 'user', 'getitemtypes');
                } catch (Exception $e) {
                    $modtypes[$module] = [];
                }
            }
            foreach ($itemtypes as $typeid => $typeinfo) {
                if (empty($typeid)) {
                    continue;
                }
                if (!isset($modtypes[$module][$typeid])) {
                    $modtypes[$module][$typeid] = [
                        'label' => $this->ml('Itemtype #(1)', $typeid),
                        'title' => $this->ml('View itemtype #(1) items', $typeid),
                        'url' => xarController::URL($module, 'user', 'view', ['itemtype' => $typeid]),
                    ];
                }
                $modules[$module]['itemtypes'][$typeid] += $modtypes[$module][$typeid];
            }
        }

        $data['modules'] = $modules;
        $data['module_id'] = $module_id;
        $data['modname'] = $modname;
        $data['itemtype'] = $itemtype;
        $data['itemid'] = $itemid;
        $data['item'] = $item;
        $data['return_url'] = $return_url;

        $data['display_hook'] = xarMod::guiFunc(
            'keywords',
            'user',
            'displayhook',
            [
                'objectid' => $itemid,
                'extrainfo' => ['module' => $modname, 'itemtype' => $itemtype, 'itemid' => $itemid, 'showlabel' => false, 'tpltype' => 'admin'],
            ]
        );

        return $data;
    }
}