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
use Xaraya\Modules\Keywords\WordsApi;
use Xaraya\Modules\Keywords\HooksGui;
use Xaraya\Modules\Keywords\MethodClass;
use xarVar;
use xarMod;
use xarHooks;
use sys;
use Exception;

sys::import('modules.keywords.method');

/**
 * keywords admin view function
 * @extends MethodClass<AdminGui>
 */
class ViewMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * show the links for module items
     * @return array|void
     * @see AdminGui::view()
     */
    public function __invoke(array $args = [])
    {
        /** @var WordsApi $wordsapi */
        $wordsapi = $this->wordsapi();
        /** @var HooksGui $hooksgui */
        $hooksgui = $this->hooksgui();
        if (!$this->sec()->checkAccess('ManageKeywords')) {
            return;
        }

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
            'int:0:',
            $itemtype,
            null,
            xarVar::DONT_SET
        )) {
            return;
        }
        if (!$this->var()->fetch(
            'keyword',
            'pre:trim:str:1:',
            $keyword,
            null,
            xarVar::DONT_SET
        )) {
            return;
        }

        if (!$this->var()->fetch(
            'sort',
            'pre:trim:str:1',
            $sort,
            null,
            xarVar::NOT_REQUIRED
        )) {
            return;
        }
        if (!$this->var()->fetch(
            'startnum',
            'int:1',
            $startnum,
            null,
            xarVar::NOT_REQUIRED
        )) {
            return;
        }
        $items_per_page = $this->mod()->getVar('stats_per_page') ?? 100;

        if (empty($module_id)) {
            $modname = $itemtype = null;
        } else {
            $modname = $this->mod()->getName($module_id);
        }

        $data = [];

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

        $total = $wordsapi->countitems(
            [
                'module_id' => $module_id,
                'itemtype' => $itemtype,
                'keyword' => $keyword,
                'skip_restricted' => true,
            ]
        );
        $items = $wordsapi->getitemcounts(
            [
                'module_id' => $module_id,
                'itemtype' => $itemtype,
                'keyword' => $keyword,
                'skip_restricted' => true,
                'startnum' => $startnum,
                'numitems' => $items_per_page,
            ]
        );


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

        $delimiters = $this->mod()->getVar('delimiters');
        if (!empty($keyword) && is_array($keyword)) {
            $delimiter = !empty($delims) ? $delims[0] : ',';
            $keyword = implode($delimiter, $keyword);
        }

        $data['modules'] = $modules;
        $data['module_id'] = $module_id;
        $data['modname'] = $modname;
        $data['itemtype'] = $itemtype;
        $data['delimiters'] = $delimiters;
        $data['keyword'] = $keyword;
        $data['items'] = $items;
        $data['startnum'] = $startnum;
        $data['items_per_page'] = $items_per_page;
        $data['total'] = $total;
        $data['use_icons'] = $this->mod()->getVar('use_module_icons');

        return $data;
    }

    private function _legacy(array $args = [])
    {
        // dummy values to stop IDE from complaining
        $data = [];
        $modname = null;
        $itemtype = null;
        /** @var WordsApi $wordsapi */
        $wordsapi = $this->wordsapi();
        /** @var HooksGui $hooksgui */
        $hooksgui = $this->hooksgui();

        switch ($data['tab']) {
            case 'list':


                if (!empty($data['keyword'])) {
                    // list items by keyword
                    // get a list of items associated with this keyword
                    $data['items'] = $wordsapi->getitems(
                        [
                            'module' => $modname,
                            'itemtype' => $itemtype,
                            'skip_restricted' => true,
                            'keyword' => $data['keyword'],
                        ]
                    );
                } else {
                    // list keywords
                    // get a list of keywords (with counts)
                    $data['items'] = $wordsapi->getwordcounts(
                        [
                            'module' => $modname,
                            'itemtype' => $itemtype,
                            'skip_restricted' => true,
                        ]
                    );
                }

                break;

            case 'cloud':

                break;
        }


        extract($args);

        $data = [];

        if (!$this->var()->fetch(
            'modname',
            'pre:trim:lower:str:1:',
            $modname,
            null,
            xarVar::NOT_REQUIRED
        )) {
            return;
        }
        if (!$this->var()->fetch(
            'itemtype',
            'int:1:',
            $itemtype,
            null,
            xarVar::NOT_REQUIRED
        )) {
            return;
        }
        if (!$this->var()->fetch(
            'itemid',
            'int:1:',
            $itemid,
            null,
            xarVar::NOT_REQUIRED
        )) {
            return;
        }

        if (!$this->var()->fetch(
            'tab',
            'pre:trim:lower:str:1:',
            $data['tab'],
            'list',
            xarVar::NOT_REQUIRED
        )) {
            return;
        }

        $subjects = xarHooks::getObserverSubjects('keywords');
        if (!empty($subjects)) {
            foreach ($subjects as $hookedto => $hooks) {
                $modinfo = $this->mod()->getInfo($this->mod()->getRegID($hookedto));
                try {
                    $itemtypes = $this->mod()->apiFunc($hookedto, 'user', 'getitemtypes');
                } catch (Exception $e) {
                    $itemtypes = [];
                }
                $modinfo['itemtypes'] = [];
                foreach ($itemtypes as $typeid => $typeinfo) {
                    if (!isset($hooks[0]) && !isset($hooks[$typeid])) {
                        continue;
                    } // not hooked
                    $modinfo['itemtypes'][$typeid] = $typeinfo;
                }
                $subjects[$hookedto] += $modinfo;
            }
        }

        switch ($data['tab']) {
            case 'list':
                if (!$this->var()->fetch(
                    'keyword',
                    'pre:trim:str:1:',
                    $data['keyword'],
                    null,
                    xarVar::NOT_REQUIRED
                )) {
                    return;
                }
                if (!empty($data['keyword'])) {
                    // list items by keyword
                    // get a list of items associated with this keyword
                    $data['items'] = $wordsapi->getitems(
                        [
                            'module' => $modname,
                            'itemtype' => $itemtype,
                            'skip_restricted' => true,
                            'keyword' => $data['keyword'],
                        ]
                    );
                } else {
                    // list keywords
                    // get a list of keywords (with counts)
                    $data['items'] = $wordsapi->getwordcounts(
                        [
                            'module' => $modname,
                            'itemtype' => $itemtype,
                            'skip_restricted' => true,
                        ]
                    );
                }
                break;
            case 'assoc':
                // list items
                // get a list of item associations
                $data['items'] = $wordsapi->getitems(
                    [
                        'module' => $modname,
                        'itemtype' => $itemtype,
                        'skip_restricted' => true,
                    ]
                );

                break;
            case 'cloud':
                // list keywords
                // same as list but with weighting applied
                // get a list of keywords (with counts)
                $data['items'] = $wordsapi->getwordcounts(
                    [
                        'module' => $modname,
                        'itemtype' => $itemtype,
                        'skip_restricted' => true,
                    ]
                );
                /*
                  // how wordpress does it
                    $min_count = min( $counts );
                    $spread = max( $counts ) - $min_count;
                    if ( $spread <= 0 )
                         $spread = 1;
                            $font_spread = $largest - $smallest;
                            if ( $font_spread < 0 )
                                    $font_spread = 1;
                            $font_step = $font_spread / $spread;

                        $a = array();

                        foreach ( $tags as $key => $tag ) {
                                $count = $counts[ $key ];
                                $real_count = $real_counts[ $key ];
                                $tag_link = '#' != $tag->link ? esc_url( $tag->link ) : '#';
                                $tag_id = isset($tags[ $key ]->id) ? $tags[ $key ]->id : $key;
                                $tag_name = $tags[ $key ]->name;
                                    $a[] = "<a href='$tag_link' class='tag-link-$tag_id' title='" . esc_attr( call_user_func( $topic_count_text_callback, $real_count ) ) . "' style='font-size: " .
                                        ( $smallest + ( ( $count - $min_count ) * $font_step ) )
                                            . "$unit;'>$tag_name</a>";
                            }
                */

                $min_ems = 1;
                $max_ems = 3;
                $num_tags = count($data['items']);
                foreach ($data['items'] as $k => $item) {
                    $item['weight'] = $item['count'] == 1 ? $min_ems :
                        round((($item['count'] / $num_tags) * ($max_ems - $min_ems)) + $min_ems, 2);
                    $data['items'][$k] = $item;
                }
                break;
            case 'config':
                // config is supplied by our modifyconfig hook
                $data['config'] = $hooksgui->modulemodifyconfig(
                    [
                        'objectid' => $modname,
                        'extrainfo' => ['module' => $modname, 'itemtype' => $itemtype],
                    ]
                );
                break;
        }

        $data['subjects'] = $subjects;
        $data['modname'] = $modname;
        $data['itemtype'] = $itemtype;
        $date['itemid'] = $itemid;

        return $data;
    }
}
