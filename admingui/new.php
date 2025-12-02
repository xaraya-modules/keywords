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
use Xaraya\Modules\Keywords\MethodClass;

/**
 * keywords admin new function
 * @extends MethodClass<AdminGui>
 */
class NewMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * create new keywords assignment
     * @param array<mixed> $args with confirm
     * @return array|bool|void with data
     * @see AdminGui::new()
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        $this->var()->find('confirm', $confirm);
        if (!$this->sec()->checkAccess('AdminKeywords')) {
            return;
        }

        $data = [];
        $data['object'] = $this->mod()->apiFunc(
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
            if (!$this->sec()->confirmAuthKey()) {
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
                $this->ctl()->redirect($this->mod()->getURL('admin', 'view'));
                return true;
            }
        }
        $item = [];
        $item['module'] = 'keywords';
        $hooks = $this->mod()->callHooks('item', 'new', '', $item);
        if (empty($hooks)) {
            $data['hooks'] = '';
        } elseif (is_array($hooks)) {
            $data['hooks'] = join('', $hooks);
        } else {
            $data['hooks'] = $hooks;
        }
        $data['authid'] = $this->sec()->genAuthKey();
        $data['confirm'] = $this->ml('Create');

        return $data;
    }
}
