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
        if (!$this->confirmAuthKey()) {
            return;
        }
        if (!$this->checkAccess('AdminKeywords')) {
            return;
        }

        $this->fetch('restricted', 'int:0:1', $restricted, 0);
        $this->fetch('useitemtype', 'int:0:1', $useitemtype, 0);
        $this->fetch('keywords', 'isset', $keywords, '', xarVar::DONT_SET);
        $this->fetch('isalias', 'isset', $isalias, '', xarVar::DONT_SET);
        $this->fetch('showsort', 'isset', $showsort, '', xarVar::DONT_SET);
        $this->fetch('displaycolumns', 'isset', $displaycolumns, '', xarVar::DONT_SET);
        $this->fetch('delimiters', 'isset', $delimiters, '', xarVar::DONT_SET);

        $this->setModVar('restricted', $restricted);
        $this->setModVar('useitemtype', $useitemtype);

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
            $this->setModVar('SupportShortURLs', 0);
        } else {
            $this->setModVar('SupportShortURLs', 1);
        }
        if (empty($showsort)) {
            $this->setModVar('showsort', 0);
        } else {
            $this->setModVar('showsort', 1);
        }
        if (empty($displaycolumns)) {
            $this->setModVar('displaycolumns', 2);
        } else {
            $this->setModVar('displaycolumns', $displaycolumns);
        }
        if (isset($delimiters)) {
            $this->setModVar('delimiters', trim($delimiters));
        }
        $data['module_settings'] = xarMod::apiFunc('base', 'admin', 'getmodulesettings', ['module' => 'keywords']);
        $data['module_settings']->setFieldList('items_per_page, use_module_alias, module_alias_name, enable_short_urls, user_menu_link');
        $data['module_settings']->getItem();

        $isvalid = $data['module_settings']->checkInput();
        if (!$isvalid) {
            $data['context'] ??= $this->getContext();
            return xarTpl::module('keywords', 'admin', 'modifyconfig', $data);
        } else {
            $itemid = $data['module_settings']->updateItem();
        }

        $this->redirect($this->getUrl('admin', 'modifyconfig'));
        return true;
    }
}
