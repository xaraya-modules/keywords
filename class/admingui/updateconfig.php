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
use xarSec;
use xarSecurity;
use xarVar;
use xarModVars;
use xarMod;
use xarTpl;
use xarController;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * keywords admin updateconfig function
 * @extends MethodClass<AdminGui>
 */
class UpdateconfigMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Update configuration
     * @param int restricted
     * @param int useitemtype
     * @param array keywords (default = empty)
     * @return mixed true on succes and redirect to URL
     */
    public function __invoke(array $args = [])
    {
        if (!$this->sec()->confirmAuthKey()) {
            return;
        }
        if (!$this->sec()->checkAccess('AdminKeywords')) {
            return;
        }

        $this->var()->get('restricted', $restricted, 'int:0:1', 0);
        $this->var()->get('useitemtype', $useitemtype, 'int:0:1', 0);
        $this->var()->check('keywords', $keywords);
        $this->var()->check('isalias', $isalias);
        $this->var()->check('showsort', $showsort);
        $this->var()->check('displaycolumns', $displaycolumns);
        $this->var()->check('delimiters', $delimiters);

        $this->mod()->setVar('restricted', $restricted);
        $this->mod()->setVar('useitemtype', $useitemtype);

        if (isset($keywords) && is_array($keywords)) {
            xarMod::apiFunc(
                'keywords',
                'admin',
                'resetlimited'
            );
            foreach ($keywords as $modname => $value) {
                if ($modname == 'default.0' || $modname == 'default') {
                    $moduleid = '0';
                    $itemtype = '0';
                } else {
                    $moduleitem = explode(".", $modname);
                    $moduleid = xarMod::getRegId($moduleitem[0], 'module');
                    if (isset($moduleitem[1]) && is_numeric($moduleitem[1])) {
                        $itemtype = $moduleitem[1];
                    } else {
                        $itemtype = 0;
                    }
                }
                if ($value <> '') {
                    xarMod::apiFunc(
                        'keywords',
                        'admin',
                        'limited',
                        ['moduleid' => $moduleid,
                            'keyword'  => $value,
                            'itemtype' => $itemtype, ]
                    );
                }
            }
        }
        if (empty($isalias)) {
            $this->mod()->setVar('SupportShortURLs', 0);
        } else {
            $this->mod()->setVar('SupportShortURLs', 1);
        }
        if (empty($showsort)) {
            $this->mod()->setVar('showsort', 0);
        } else {
            $this->mod()->setVar('showsort', 1);
        }
        if (empty($displaycolumns)) {
            $this->mod()->setVar('displaycolumns', 2);
        } else {
            $this->mod()->setVar('displaycolumns', $displaycolumns);
        }
        if (isset($delimiters)) {
            $this->mod()->setVar('delimiters', trim($delimiters));
        }
        $data['module_settings'] = xarMod::apiFunc('base', 'admin', 'getmodulesettings', ['module' => 'keywords']);
        $data['module_settings']->setFieldList('items_per_page, use_module_alias, module_alias_name, enable_short_urls, user_menu_link');
        $data['module_settings']->getItem();

        $isvalid = $data['module_settings']->checkInput();
        if (!$isvalid) {
            $data['context'] ??= $this->getContext();
            return $this->mod()->template('modifyconfig', $data);
        } else {
            $itemid = $data['module_settings']->updateItem();
        }

        $this->ctl()->redirect($this->mod()->getURL('admin', 'modifyconfig'));
        return true;
    }
}