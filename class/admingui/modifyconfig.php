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
use xarMod;
use xarSec;
use xarController;
use xarModVars;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * keywords admin modifyconfig function
 * @extends MethodClass<AdminGui>
 */
class ModifyconfigMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Prepare data for form. May be called from form itself with updated
     * configuration parameters.
     * @author mikespub
     * @access public
     * @param int $restricted 1 for pregiven keyword list, 0 for free input
     * @param int $useitemtype 1 for itemtype specific keyword lists
     * @return bool|string|void on success or void on failure
     * @todo nothing
     */
    public function __invoke(array $args = [])
    {
        if (!$this->sec()->checkAccess('AdminKeywords')) {
            return;
        }

        if (!$this->var()->check('module_id', $module_id, 'id')) {
            return;
        }
        if (!$this->var()->check('itemtype', $itemtype, 'int:0:')) {
            return;
        }
        if (!$this->var()->find('phase', $phase, 'pre:trim:lower:enum:update', 'form')) {
            return;
        }
        if (!$this->var()->find('return_url', $return_url, 'pre:trim:str:1:', '')) {
            return;
        }

        $data = [];

        if (empty($module_id)) {
            $modname = 'keywords';
            $itemtype = null;
        } else {
            $modname = xarMod::getName($module_id);
        }

        if ($modname == 'keywords') {
            $data['module_settings'] = xarMod::apiFunc('base', 'admin', 'getmodulesettings', ['module' => 'keywords']);
            $data['module_settings']->setFieldList('items_per_page, use_module_alias, module_alias_name, enable_short_urls, use_module_icons, frontend_page, backend_page');
            $data['module_settings']->getItem();
        }

        if ($phase == 'update') {
            if (!$this->sec()->confirmAuthKey()) {
                return xarController::badRequest('bad_author', $this->getContext());
            }
            if ($modname == 'keywords') {
                $isvalid = $data['module_settings']->checkInput();
                if ($isvalid) {
                    $itemid = $data['module_settings']->updateItem();
                    if (!$this->var()->get('delimiters', $delimiters, 'pre:trim:str:1:', $this->mod()->getVar('delimiters', ','), xarVar::NOT_REQUIRED)) {
                        return;
                    }
                    if (!$this->var()->get('stats_per_page', $stats_per_page, 'int:0:', $this->mod()->getVar('stats_per_page', 100), xarVar::NOT_REQUIRED)) {
                        return;
                    }
                    if (!$this->var()->get('items_per_page', $items_per_page, 'int:0:', $this->mod()->getVar('items_per_page', 20), xarVar::NOT_REQUIRED)) {
                        return;
                    }
                    if (!$this->var()->get('user_layout', $user_layout, 'pre:trim:lower:enum:list:cloud', $this->mod()->getVar('user_layout', 'list'), xarVar::NOT_REQUIRED)) {
                        return;
                    }
                    $this->mod()->setVar('delimiters', $delimiters);
                    $this->mod()->setVar('stats_per_page', $stats_per_page);
                    $this->mod()->setVar('items_per_page', $items_per_page);
                    $this->mod()->setVar('user_layout', $user_layout);
                    //if ($user_layout == 'list') {
                    if (!$this->var()->get('cols_per_page', $cols_per_page, 'int:0:', $this->mod()->getVar('cols_per_page', 2), xarVar::NOT_REQUIRED)) {
                        return;
                    }
                    if (!$this->var()->get('words_per_page', $words_per_page, 'int:0:', $this->mod()->getVar('words_per_page', 50), xarVar::NOT_REQUIRED)) {
                        return;
                    }
                    $this->mod()->setVar('cols_per_page', $cols_per_page);
                    $this->mod()->setVar('words_per_page', $words_per_page);
                    //} else {
                    // the cloudy stuff
                    if (!$this->var()->get('cloud_font_min', $cloud_font_min, 'int:1:', $this->mod()->getVar('cloud_font_min', 1), xarVar::NOT_REQUIRED)) {
                        return;
                    }
                    if (!$this->var()->get('cloud_font_max', $cloud_font_max, 'int:1:', $this->mod()->getVar('cloud_font_max', 1), xarVar::NOT_REQUIRED)) {
                        return;
                    }
                    if (!$this->var()->get('cloud_font_unit', $cloud_font_unit, 'pre:trim:lower:enum:em:pt:px:%', $this->mod()->getVar('cloud_font_unit', 'em'), xarVar::NOT_REQUIRED)) {
                        return;
                    }
                    $this->mod()->setVar('cloud_font_min', $cloud_font_min);
                    $this->mod()->setVar('cloud_font_max', $cloud_font_max);
                    $this->mod()->setVar('cloud_font_unit', $cloud_font_unit);
                    //}
                }
            }
            if (!xarMod::apiFunc(
                'keywords',
                'hooks',
                'moduleupdateconfig',
                [
                    'objectid' => $modname,
                    'extrainfo' => ['module' => $modname, 'itemtype' => $itemtype],
                ]
            )) {
                return;
            }
            if (empty($return_url)) {
                $return_url = $this->mod()->getURL(
                    'admin',
                    'modifyconfig',
                    [
                        'module_id' => $module_id,
                        'itemtype' => $itemtype,
                    ]
                );
            }
            $this->ctl()->redirect($return_url);
        }

        // form phase
        $data['module_id'] = $module_id;
        $data['modname'] = $modname;
        $data['itemtype'] = $itemtype;

        if ($modname == 'keywords') {
            $data['delimiters'] = $this->mod()->getVar('delimiters', ',');
            $data['stats_per_page'] = $this->mod()->getVar('stats_per_page', 100);
            $data['items_per_page'] = $this->mod()->getVar('items_per_page', 20);
            $data['user_layout'] = $this->mod()->getVar('user_layout', 'list');

            if ($data['user_layout'] == 'list') {
                $data['cols_per_page'] = $this->mod()->getVar('cols_per_page', 2);
                $data['words_per_page'] = $this->mod()->getVar('words_per_page', 50);
            } else {
                $data['cloud_font_min'] = $this->mod()->getVar('cloud_font_min', 1);
                $data['cloud_font_max'] = $this->mod()->getVar('cloud_font_max', 3);
                $data['cloud_font_unit'] = $this->mod()->getVar('cloud_font_unit', 'em');
                $data['font_units'] = [
                    ['id' => 'em', 'name' => 'em'],
                    ['id' => 'pt', 'name' => 'pt'],
                    ['id' => 'px', 'name' => 'px'],
                    ['id' => '%', 'name' => '%'],
                ];
            }

            $data['user_layouts'] = [
                ['id' => 'list', 'name' => $this->ml('List')],
                ['id' => 'cloud', 'name' => $this->ml('Cloud')],
            ];
        }

        $data['subjects'] = xarMod::apiFunc('keywords', 'hooks', 'getsubjects');
        $data['hook_config'] = xarMod::guiFunc(
            'keywords',
            'hooks',
            'modulemodifyconfig',
            [
                'objectid' => $modname,
                'extrainfo' => ['module' => $modname, 'itemtype' => $itemtype],
            ]
        );

        return $data;
    }
}