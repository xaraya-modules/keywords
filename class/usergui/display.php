<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords\UserGui;


use Xaraya\Modules\Keywords\UserGui;
use Xaraya\Modules\Keywords\UserApi;
use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarVar;
use xarMod;
use xarController;
use sys;
use Exception;

sys::import('xaraya.modules.method');

/**
 * keywords user display function
 * @extends MethodClass<UserGui>
 */
class DisplayMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * display keywords entry
     * @param array<mixed> $args
     * @var mixed $itemid item id of the keywords entry
     * @checkme: this appears to display a link to a display of an item, why is this needed?
     * @return array|void Item
     * @see UserGui::display()
     */
    public function __invoke(array $args = [])
    {
        /** @var UserApi $userapi */
        $userapi = $this->userapi();
        if (!$this->sec()->checkAccess('ReadKeywords')) {
            return;
        }

        $this->var()->check('itemid', $itemid, 'id', '');
        extract($args);

        if (empty($itemid)) {
            return [];
        }
        $items = $userapi->getitems(['id' => $itemid]
        );
        if (!isset($items)) {
            return;
        }
        if (!isset($items[$itemid])) {
            return [];
        }

        $item = $items[$itemid];
        if (count($item) == 0 || empty($item['moduleid'])) {
            return [];
        }

        $modinfo = xarMod::getInfo($item['moduleid']);
        if (!isset($modinfo) || empty($modinfo['name'])) {
            return [];
        }

        if (!empty($item['itemtype'])) {
            // Get the list of all item types for this module (if any)
            try {
                $mytypes = xarMod::apiFunc($modinfo['name'], 'user', 'getitemtypes');
            } catch (Exception $e) {
                $mytypes = [];
            }
            if (isset($mytypes) && isset($mytypes[$item['itemtype']])) {
                $item['modname'] = $mytypes[$item['itemtype']]['label'];
            } else {
                $item['modname'] = ucwords($modinfo['name']);
            }
        } else {
            $item['modname'] = ucwords($modinfo['name']);
        }

        try {
            $itemlinks = xarMod::apiFunc(
                $modinfo['name'],
                'user',
                'getitemlinks',
                ['itemtype' => $item['itemtype'],
                    'itemids' => [$item['itemid']]]
            );
        } catch (Exception $e) {
            $itemlinks = [];
        }

        if (isset($itemlinks[$item['itemid']]) && !empty($itemlinks[$item['itemid']]['url'])) {
            // normally we should have url, title and label here
            foreach ($itemlinks[$item['itemid']] as $field => $value) {
                $item[$field] = $value;
            }
        } else {
            $item['url'] = $this->ctl()->getModuleURL(
                $modinfo['name'],
                'user',
                'display',
                ['itemtype' => $item['itemtype'],
                    'itemid' => $item['itemid'], ]
            );
        }
        return $item;
    }
}
