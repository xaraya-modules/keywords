<?php
/**
 * Keywords Module
 *
 * @package modules
 * @subpackage keywords module
 * @category Third Party Xaraya Module
 * @version 2.0.0
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://xaraya.com/index.php/release/187.html
 * @author mikespub
 */

/**
 * show the links for module items
 * @return array
 */
function keywords_admin_view(array $args = [], $context = null)
{
    if (!xarSecurity::check('ManageKeywords')) {
        return;
    }

    if (!xarVar::fetch(
        'module_id',
        'id',
        $module_id,
        null,
        xarVar::DONT_SET
    )) {
        return;
    }
    if (!xarVar::fetch(
        'itemtype',
        'int:0:',
        $itemtype,
        null,
        xarVar::DONT_SET
    )) {
        return;
    }
    if (!xarVar::fetch(
        'keyword',
        'pre:trim:str:1:',
        $keyword,
        null,
        xarVar::DONT_SET
    )) {
        return;
    }

    if (!xarVar::fetch(
        'sort',
        'pre:trim:str:1',
        $sort,
        null,
        xarVar::NOT_REQUIRED
    )) {
        return;
    }
    if (!xarVar::fetch(
        'startnum',
        'int:1',
        $startnum,
        null,
        xarVar::NOT_REQUIRED
    )) {
        return;
    }
    $items_per_page = xarModVars::get('keywords', 'stats_per_page', 100);

    if (empty($module_id)) {
        $modname = $itemtype = null;
    } else {
        $modname = xarMod::getName($module_id);
    }

    $data = [];

    $modlist = xarMod::apiFunc(
        'keywords',
        'words',
        'getmodulecounts',
        [
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
                $modtypes[$module] = xarMod::apiFunc($module, 'user', 'getitemtypes');
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
                    'label' => xarML('Itemtype #(1)', $typeid),
                    'title' => xarML('View itemtype #(1) items', $typeid),
                    'url' => xarController::URL($module, 'user', 'view', ['itemtype' => $typeid]),
                ];
            }
            $modules[$module]['itemtypes'][$typeid] += $modtypes[$module][$typeid];
        }
    }

    $total = xarMod::apiFunc(
        'keywords',
        'words',
        'countitems',
        [
            'module_id' => $module_id,
            'itemtype' => $itemtype,
            'keyword' => $keyword,
            'skip_restricted' => true,
        ]
    );
    $items = xarMod::apiFunc(
        'keywords',
        'words',
        'getitemcounts',
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
                $itemlinks = xarMod::apiFunc(
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
                        'label' => xarML('Item #(1)', $id),
                        'title' => xarML('Display Item #(1)', $id),
                        'url' => xarController::URL(
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

    $delimiters = xarModVars::get('keywords', 'delimiters');
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
    $data['use_icons'] = xarModVars::get('keywords', 'use_module_icons');

    return $data;



    switch ($data['tab']) {
        case 'list':


            if (!empty($data['keyword'])) {
                // list items by keyword
                // get a list of items associated with this keyword
                $data['items'] = xarMod::apiFunc(
                    'keywords',
                    'words',
                    'getitems',
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
                $data['items'] = xarMod::apiFunc(
                    'keywords',
                    'words',
                    'getwordcounts',
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

    if (!xarVar::fetch(
        'modname',
        'pre:trim:lower:str:1:',
        $modname,
        null,
        xarVar::NOT_REQUIRED
    )) {
        return;
    }
    if (!xarVar::fetch(
        'itemtype',
        'int:1:',
        $itemtype,
        null,
        xarVar::NOT_REQUIRED
    )) {
        return;
    }
    if (!xarVar::fetch(
        'itemid',
        'int:1:',
        $itemid,
        null,
        xarVar::NOT_REQUIRED
    )) {
        return;
    }

    if (!xarVar::fetch(
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
            $modinfo = xarMod::getInfo(xarMod::getRegID($hookedto));
            try {
                $itemtypes = xarMod::apiFunc($hookedto, 'user', 'getitemtypes');
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
            if (!xarVar::fetch(
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
                $data['items'] = xarMod::apiFunc(
                    'keywords',
                    'words',
                    'getitems',
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
                $data['items'] = xarMod::apiFunc(
                    'keywords',
                    'words',
                    'getwordcounts',
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
            $data['items'] = xarMod::apiFunc(
                'keywords',
                'words',
                'getitems',
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
            $data['items'] = xarMod::apiFunc(
                'keywords',
                'words',
                'getwordcounts',
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
            $data['config'] = xarMod::guiFunc(
                'keywords',
                'hooks',
                'modulemodifyconfig',
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
