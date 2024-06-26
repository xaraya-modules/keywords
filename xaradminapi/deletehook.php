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
 * delete entry for a module item - hook for ('item','delete','API')
 *
 * @param $args['objectid'] ID of the object
 * @param $args['extrainfo'] extra information
 * @return bool true on success, false on failure
 */
function keywords_adminapi_deletehook(array $args = [], $context = null)
{
    extract($args);

    if (empty($extrainfo)) {
        $extrainfo = [];
    }

    if (!isset($objectid) || !is_numeric($objectid)) {
        $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
        $vars = ['objectid', 'adminapi', 'deletehook', 'keywords'];
        throw new BadParameterException($vars, $msg);
    }

    // When called via hooks, the module name may be empty. Get it from current module.
    if (empty($extrainfo['module'])) {
        $modname = xarMod::getName();
    } else {
        $modname = $extrainfo['module'];
    }

    $modid = xarMod::getRegId($modname);
    if (empty($modid)) {
        $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
        $vars = ['module', 'adminapi', 'deletehook', 'keywords'];
        throw new BadParameterException($vars, $msg);
    }

    if (!empty($extrainfo['itemtype']) && is_numeric($extrainfo['itemtype'])) {
        $itemtype = $extrainfo['itemtype'];
    } else {
        $itemtype = 0;
    }

    if (!empty($extrainfo['itemid']) && is_numeric($extrainfo['itemid'])) {
        $itemid = $extrainfo['itemid'];
    } else {
        $itemid = $objectid;
    }

    // get the index_id for this module/itemtype/item
    $index_id = xarMod::apiFunc(
        'keywords',
        'index',
        'getid',
        [
            'module' => $modname,
            'itemtype' => $itemtype,
            'itemid' => $itemid,
        ]
    );

    // delete all keywords associated with this item
    if (!xarMod::apiFunc(
        'keywords',
        'words',
        'deleteitems',
        [
            'index_id' => $index_id,
        ]
    )) {
        return;
    }

    // delete the index
    if (!xarMod::apiFunc(
        'keywords',
        'index',
        'deleteitem',
        [
            'id' => $index_id,
        ]
    )) {
        return;
    }

    return $extrainfo;
}
