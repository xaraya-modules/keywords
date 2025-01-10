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
        if (!xarSec::confirmAuthKey()) {
            return;
        }
        if (!xarSecurity::check('AdminKeywords')) {
            return;
        }

        xarVar::fetch('restricted', 'int:0:1', $restricted, 0);
        xarVar::fetch('useitemtype', 'int:0:1', $useitemtype, 0);
        xarVar::fetch('keywords', 'isset', $keywords, '', xarVar::DONT_SET);
        xarVar::fetch('isalias', 'isset', $isalias, '', xarVar::DONT_SET);
        xarVar::fetch('showsort', 'isset', $showsort, '', xarVar::DONT_SET);
        xarVar::fetch('displaycolumns', 'isset', $displaycolumns, '', xarVar::DONT_SET);
        xarVar::fetch('delimiters', 'isset', $delimiters, '', xarVar::DONT_SET);

        xarModVars::set('keywords', 'restricted', $restricted);
        xarModVars::set('keywords', 'useitemtype', $useitemtype);

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
            xarModVars::set('keywords', 'SupportShortURLs', 0);
        } else {
            xarModVars::set('keywords', 'SupportShortURLs', 1);
        }
        if (empty($showsort)) {
            xarModVars::set('keywords', 'showsort', 0);
        } else {
            xarModVars::set('keywords', 'showsort', 1);
        }
        if (empty($displaycolumns)) {
            xarModVars::set('keywords', 'displaycolumns', 2);
        } else {
            xarModVars::set('keywords', 'displaycolumns', $displaycolumns);
        }
        if (isset($delimiters)) {
            xarModVars::set('keywords', 'delimiters', trim($delimiters));
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

        xarController::redirect(xarController::URL('keywords', 'admin', 'modifyconfig'), null, $this->getContext());
        return true;
    }
}
