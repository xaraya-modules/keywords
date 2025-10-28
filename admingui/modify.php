<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.6.2
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords\AdminGui;

use Xaraya\Modules\Keywords\AdminGui;
use Xaraya\Modules\Keywords\AdminApi;
use Xaraya\Modules\Keywords\WordsApi;
use Xaraya\Modules\Keywords\UserGui;
use Xaraya\Modules\Keywords\MethodClass;
use xarVar;
use xarMod;
use sys;
use EmptyParameterException;
use Exception;

sys::import('modules.keywords.method');

/**
 * keywords admin modify function
 * @extends MethodClass<AdminGui>
 */
class ModifyMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * modify existing keywords assignment
     * @param array<mixed> $args
     * @var int module_id id of the module the item belongs to, required
     * @var int itemtype, id of the module itemtype the item belongs to, optional
     * @var int itemid, id of the item
     * @var string phase, current function phase (form)|update
     * @return mixed array of template data in form phase or bool redirected in update phase
     * @throws \EmptyParameterException
     * @see AdminGui::modify()
     */
    public function __invoke(array $args = [])
    {
        /** @var AdminApi $adminapi */
        $adminapi = $this->adminapi();
        /** @var WordsApi $wordsapi */
        $wordsapi = $this->wordsapi();
        /** @var AdminGui $admingui */
        $admingui = $this->admingui();
        /** @var UserGui $usergui */
        $usergui = $this->usergui();
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

        if (empty($module_id)) {
            $invalid[] = 'module_id';
        }
        if (empty($itemid)) {
            $invalid[] = 'itemid';
        }

        if (!empty($invalid)) {
            $msg = 'Missing #(1) for #(2) module #(3) function #(4)()';
            $vars = [implode(', ', $invalid), 'keywords', 'admin', 'modify'];
            throw new EmptyParameterException($vars, $msg);
        }

        if (!$this->var()->fetch(
            'phase',
            'pre:trim:lower:enum:update',
            $phase,
            'form',
            xarVar::NOT_REQUIRED
        )) {
            return;
        }

        $modname = $this->mod()->getName($module_id);

        if ($phase == 'update') {
            if (!$this->sec()->confirmAuthKey()) {
                return $this->ctl()->badRequest('bad_author');
            }
            // check for keywords empty and redirect to delete confirm
            if (!$this->var()->fetch(
                'keywords',
                'isset',
                $keywords,
                null,
                xarVar::DONT_SET
            )) {
                return;
            }
            if (empty($keywords)) {
                $delete_url = $this->mod()->getURL(
                    'admin',
                    'delete',
                    [
                        'module_id' => $module_id,
                        'itemtype' => $itemtype,
                        'itemid' => $itemid,
                    ]
                );
                $this->ctl()->redirect($delete_url);
                return true;
            }
            $adminapi->updatehook(
                [
                    'objectid' => $itemid,
                    'extrainfo' => ['module' => $modname, 'itemtype' => $itemtype, 'itemid' => $itemid],
                ]
            );
            if (empty($return_url)) {
                $return_url = $this->mod()->getURL(
                    'admin',
                    'modify',
                    [
                        'module_id' => $module_id,
                        'itemtype' => $itemtype,
                        'itemid' => $itemid,
                    ]
                );
            }
            $this->ctl()->redirect($return_url);
            return true;
        }

        try {
            $item = $this->mod()->apiFunc(
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
                'url' => $this->ctl()->getModuleURL(
                    $modname,
                    'user',
                    'display',
                    ['itemtype' => $itemtype, 'itemid' => $itemid]
                ),
            ];
        }

        $modlist = $wordsapi->getmodulecounts(
            [
                'skip_restricted' => true,
            ]
        );
        $modtypes = [];
        $modules = [];
        foreach ($modlist as $module => $itemtypes) {
            $modules[$module] = $this->mod()->getBaseInfo($module);
            $modules[$module]['itemtypes'] = $itemtypes;
            if (!isset($modtypes[$module])) {
                try {
                    $modtypes[$module] = $this->mod()->apiFunc($module, 'user', 'getitemtypes');
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
                        'url' => $this->ctl()->getModuleURL($module, 'user', 'view', ['itemtype' => $typeid]),
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

        $data['modify_hook'] = $admingui->modifyhook(
            [
                'objectid' => $itemid,
                'extrainfo' => ['module' => $modname, 'itemtype' => $itemtype, 'itemid' => $itemid],
            ]
        );
        $data['display_hook'] = $usergui->displayhook(
            [
                'objectid' => $itemid,
                'extrainfo' => ['module' => $modname, 'itemtype' => $itemtype, 'itemid' => $itemid, 'showlabel' => false, 'tpltype' => 'admin'],
            ]
        );

        return $data;
    }
}
