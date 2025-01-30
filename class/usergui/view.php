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
use Xaraya\Modules\Keywords\WordsApi;
use Xaraya\Modules\Keywords\UserApi;
use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarVar;
use xarModVars;
use xarMod;
use xarController;
use sys;
use Exception;

sys::import('xaraya.modules.method');

/**
 * keywords user view function
 * @extends MethodClass<UserGui>
 */
class ViewMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * display keywords entries
     * @return mixed bool and redirect to url
     * @see UserGui::view()
     */
    public function __invoke(array $args = [])
    {
        /** @var WordsApi $wordsapi */
        $wordsapi = $this->wordsapi();
        /** @var UserApi $userapi */
        $userapi = $this->userapi();
        if (!$this->sec()->checkAccess('ReadKeywords')) {
            return;
        }

        if (!$this->var()->check('keyword', $keyword, 'pre:trim:str:1:')) {
            return;
        }
        if (!$this->var()->find('startnum', $startnum, 'int:1:')) {
            return;
        }

        $data = [];

        if (!empty($keyword)) {
            $items_per_page = $this->mod()->getVar('items_per_page', 20);
            $total = $wordsapi->countitems([
                    //'module_id' => $module_id,
                    //'itemtype' => $itemtype,
                    'keyword' => $keyword,
                    'skip_restricted' => true,
                ]
            );
            $items = $wordsapi->getitems([
                    //'module_id' => $module_id,
                    //'itemtype' => $itemtype,
                    'keyword' => $keyword,
                    'skip_restricted' => true,
                    'startnum' => $startnum,
                    'numitems' => $items_per_page,
                ]
            );

            $modlist = $wordsapi->getmodulecounts([
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

            $seenitems = [];
            foreach ($items as $item) {
                if (!isset($seenitems[$item['module']])) {
                    $seenitems[$item['module']] = [];
                }
                if (!isset($seenitems[$item['module']][$item['itemtype']])) {
                    $seenitems[$item['module']][$item['itemtype']] = [];
                }
                $seenitems[$item['module']][$item['itemtype']][$item['itemid']] = $item;
            }
            foreach ($seenitems as $module => $itemtypes) {
                $modules[$module]['itemlinks'] = [];
                foreach ($itemtypes as $typeid => $itemids) {
                    $modules[$module]['itemlinks'][$typeid] = $itemids;
                    $itemids = array_keys($itemids);
                    try {
                        $itemlinks = $this->mod()->apiFunc(
                            $module,
                            'user',
                            'getitemlinks',
                            ['itemtype' => $typeid,
                                'itemids' => $itemids]
                        );
                    } catch (Exception $e) {
                        $itemlinks = [];
                    }
                    foreach (array_keys($itemids) as $id) {
                        if (!isset($itemlinks[$id])) {
                            $itemlinks[$id] = [
                                'label' => $this->ml('Item #(1)', $id),
                                'title' => $this->ml('Display Item #(1)', $id),
                                'url' => $this->ctl()->getModuleURL(
                                    $module,
                                    'user',
                                    'display',
                                    ['itemtype' => !empty($itemtype) ? $itemtype : null, 'itemid' => $id]
                                ),
                            ];
                        }
                        $modules[$module]['itemlinks'][$typeid][$id] += $itemlinks[$id];
                    }
                }
            }
            $data['modules'] = $modules;
            $data['items_per_page'] = $items_per_page;
            $data['total'] = $total;
            $data['items'] = $items;
            $data['use_icons'] = $this->mod()->getVar('use_module_icons');
        } else {
            $user_layout = $this->mod()->getVar('user_layout', 'list');

            switch ($user_layout) {
                case 'list':
                default:
                    $cols_per_page = $this->mod()->getVar('cols_per_page', 2);
                    $items_per_page = $this->mod()->getVar('words_per_page', 50);
                    $total = $wordsapi->countwords([
                            'skip_restricted' => true,
                        ]
                    );
                    $items = $wordsapi->getwordcounts([
                            'startnum' => $startnum,
                            'numitems' => $items_per_page,
                            'skip_restricted' => true,
                        ]
                    );

                    $data['cols_per_page'] = $cols_per_page;
                    $data['items_per_page'] = $items_per_page;
                    $data['total'] = $total;
                    $data['items'] = $items;
                    break;
                case 'cloud':
                    $items = $wordsapi->getwordcounts([
                            'skip_restricted' => true,
                        ]
                    );
                    $counts = [];
                    foreach ($items as $item) {
                        $counts[$item['keyword']] = $item['count'];
                    }
                    $font_min = $this->mod()->getVar('cloud_font_min');
                    $font_max = $this->mod()->getVar('cloud_font_max');
                    $font_unit = $this->mod()->getVar('cloud_font_unit');
                    $min_count = min($counts);
                    $max_count = max($counts);
                    $range = $max_count - $min_count;
                    if ($range <= 0) {
                        $range = 1;
                    }
                    $font_range = $font_min - $font_max;
                    if ($font_range <= 0) {
                        $font_range = 1;
                    }
                    $range_step = $font_range / $range;
                    foreach ($items as $k => $item) {
                        $count = $counts[$item['keyword']];
                        $items[$k]['weight'] = $font_min + (($count - $min_count) * $range_step);
                    }
                    $data['items'] = $items;
                    $data['unit'] = $font_unit;

                    break;
            }
            $data['user_layout'] = $user_layout;
        }

        $data['startnum'] = $startnum;
        $data['keyword'] = $keyword;

        return $data;

        $this->var()->check('keyword', $keyword, 'str', '');
        $this->var()->check('id', $id, 'id', '');
        $this->var()->check('tab', $tab, 'int:0:5', '0');

        //extract($args);
        $displaycolumns = $this->mod()->getVar('displaycolumns');
        if (!isset($displaycolumns) or (empty($displaycolumns))) {
            $displaycolumns = 1;
        }

        if (empty($keyword)) {
            // get the list of keywords that are in use
            $words = $userapi->getlist(['count' => 1,
                    'tab' => $tab, ]
            );

            $items = [];
            foreach ($words as $word => $count) {
                if (empty($word)) {
                    continue;
                }
                $items[] = [
                    'url' => $this->mod()->getURL(
                        'user',
                        'view',
                        ['keyword' => $word]
                    ),
                    'label' => $this->var()->prep($word),
                    'count' => $count,
                ];
            }

            return ['status' => 0,
                'displaycolumns' => $displaycolumns,
                'items' => $items,
                'tab' => $tab, ];
        } elseif (empty($id)) {
            // @checkme: necessary to decode? already done by php?
            $keyword = rawurldecode($keyword);
            // @checkme: we don't replace spaces with underscores when constructing links
            if (strpos($keyword, '_') !== false) {
                $keyword = str_replace('_', ' ', $keyword);
            }
            // get the list of items to which this keyword is assigned
            $items = $userapi->getitems(['keyword' => $keyword]
            );

            if (!isset($items)) {
                return;
            }

            // build up a list of item ids per module & item type
            $modules = [];
            foreach ($items as $id => $item) {
                if (!isset($modules[$item['module_id']])) {
                    $modules[$item['module_id']] = [];
                }
                if (empty($item['itemtype'])) {
                    $item['itemtype'] = 0;
                }
                if (!isset($modules[$item['module_id']][$item['itemtype']])) {
                    $modules[$item['module_id']][$item['itemtype']] = [];
                }
                $modules[$item['module_id']][$item['itemtype']][$item['itemid']] = $id;
            }

            // get the corresponding URL and title (if any)
            foreach ($modules as $moduleid => $itemtypes) {
                $modinfo = $this->mod()->getInfo($moduleid);
                if (!isset($modinfo) || empty($modinfo['name'])) {
                    return;
                }

                // Get the list of all item types for this module (if any)
                try {
                    $mytypes = $this->mod()->apiFunc($modinfo['name'], 'user', 'getitemtypes');
                } catch (Exception $e) {
                    $mytypes = [];
                }

                foreach ($itemtypes as $itemtype => $itemlist) {
                    $itemids = array_keys($itemlist);
                    try {
                        $itemlinks = $this->mod()->apiFunc(
                            $modinfo['name'],
                            'user',
                            'getitemlinks',
                            ['itemtype' => $itemtype,
                                'itemids' => $itemids]
                        );
                    } catch (Exception $e) {
                        $itemlinks = [];
                    }
                    foreach ($itemlist as $itemid => $id) {
                        if (!isset($items[$id])) {
                            continue;
                        }
                        if (isset($itemlinks) && isset($itemlinks[$itemid])) {
                            $items[$id]['url'] = $itemlinks[$itemid]['url'];
                            $items[$id]['label'] = $itemlinks[$itemid]['label'];
                        } else {
                            $items[$id]['url'] = $this->ctl()->getModuleURL(
                                $modinfo['name'],
                                'user',
                                'display',
                                //$items[$id]['url'] = $this->ctl()->getModuleURL($modinfo['name'],'user','main',
                                ['itemtype' => $itemtype,
                                    'itemid' => $itemid, ]
                            );
                            // you could skip those in the template
                        }
                        if (!empty($itemtype)) {
                            if (isset($mytypes) && isset($mytypes[$itemtype])) {
                                $items[$id]['modname'] = $mytypes[$itemtype]['label'];
                            } else {
                                $items[$id]['modname'] = ucwords($modinfo['name']) . ' ' . $itemtype;
                            }
                        } else {
                            $items[$id]['modname'] = ucwords($modinfo['name']);
                        }
                    }
                }
            }
            unset($modules);

            return ['status' => 1,
                'displaycolumns' => $displaycolumns,
                'keyword' => $this->var()->prep($keyword),
                'items' => $items, ];
        }

        // @checkme: what's all this?
        // if we're given an id we redirect to item display?
        // we already got a link pointing to the item display url, why isn't that used
        // in the template instead of pointing here?
        $items = $userapi->getitems(['keyword' => $keyword,
                'id' => $id, ]
        );
        if (!isset($items)) {
            return;
        }
        if (!isset($items[$id])) {
            return ['status' => 2];
        }

        $item = $items[$id];
        if (!isset($item['moduleid'])) {
            return ['status' => 2];
        }

        $modinfo = $this->mod()->getInfo($item['moduleid']);
        if (!isset($modinfo) || empty($modinfo['name'])) {
            return ['status' => 3];
        }

        // TODO: make configurable per module/itemtype
        try {
            $itemlinks = $this->mod()->apiFunc(
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
            $url = $itemlinks[$item['itemid']]['url'];
        } else {
            $url = $this->ctl()->getModuleURL(
                $modinfo['name'],
                'user',
                'display',
                ['itemtype' => $item['itemtype'],
                    'itemid' => $item['itemid'], ]
            );
        }

        $this->ctl()->redirect($url);
        return true;
    }
}
