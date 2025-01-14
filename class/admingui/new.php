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
use xarVar;
use xarSecurity;
use xarMod;
use xarSec;
use xarController;
use xarModHooks;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * keywords admin new function
 * @extends MethodClass<AdminGui>
 */
class NewMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * create new keywords assignment
     * @param string confirm
     * @return array with data
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (!$this->fetch('confirm', 'isset', $confirm, null, xarVar::NOT_REQUIRED)) {
            return;
        }
        if (!$this->checkAccess('AdminKeywords')) {
            return;
        }

        $data = [];
        $data['object'] = xarMod::apiFunc(
            'dynamicdata',
            'user',
            'getobject',
            ['module' => 'keywords']
        );
        if (!isset($data['object'])) {
            return;
        }
        if (!empty($confirm)) {
            // Confirm authorisation code
            if (!$this->confirmAuthKey()) {
                return;
            }
            // check the input values for this object
            $isvalid = $data['object']->checkInput();
            if ($isvalid) {
                // create the item here
                $itemid = $data['object']->createItem();
                if (empty($itemid)) {
                    return;
                } // throw back

                // let's go back to the admin view
                $this->redirect($this->getUrl('admin', 'view'));
                return true;
            }
        }
        $item = [];
        $item['module'] = 'keywords';
        $hooks = xarModHooks::call('item', 'new', '', $item);
        if (empty($hooks)) {
            $data['hooks'] = '';
        } elseif (is_array($hooks)) {
            $data['hooks'] = join('', $hooks);
        } else {
            $data['hooks'] = $hooks;
        }
        $data['authid'] = $this->genAuthKey();
        $data['confirm'] = $this->translate('Create');

        return $data;
    }
}
