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
use Xaraya\Modules\Keywords\IndexApi;
use Xaraya\Modules\Keywords\WordsApi;
use Xaraya\Modules\Keywords\UserGui;
use Xaraya\Modules\Keywords\MethodClass;
use xarVar;
use EmptyParameterException;
use Exception;

/**
 * keywords admin delete function
 * @extends MethodClass<AdminGui>
 */
class DeleteMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * delete existing keywords assignment
     * @see AdminGui::delete()
     */
    public function __invoke(array $args = [])
    {
        /** @var IndexApi $indexapi */
        $indexapi = $this->indexapi();
        /** @var WordsApi $wordsapi */
        $wordsapi = $this->wordsapi();
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

        $modname = $this->mod()->getName($module_id);

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
                return true;
            }
            if (!$this->sec()->confirmAuthKey()) {
                return $this->ctl()->badRequest('bad_author');
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

        $data['display_hook'] = $usergui->displayhook(
            [
                'objectid' => $itemid,
                'extrainfo' => ['module' => $modname, 'itemtype' => $itemtype, 'itemid' => $itemid, 'showlabel' => false, 'tpltype' => 'admin'],
            ]
        );

        return $data;
    }
}
