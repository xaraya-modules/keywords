<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords\AdminApi;

use Xaraya\Modules\MethodClass;
use xarMod;
use xarSecurity;
use xarVar;
use xarModVars;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * keywords adminapi createhook function
 */
class CreatehookMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * create an entry for a module item - hook for ('item','create','GUI')
     * Optional $extrainfo['keywords'] from arguments, or 'keywords' from input
     * @param mixed $args ['objectid'] ID of the object
     * @param mixed $args ['extrainfo'] extra information
     * @return array|void Extrainfo array
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (empty($extrainfo)) {
            $extrainfo = [];
        }

        if (!isset($objectid) || !is_numeric($objectid)) {
            $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
            $vars = ['objectid', 'admin', 'createhook', 'keywords'];
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
            $vars = ['module', 'admin', 'newhook', 'keywords'];
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

        // @todo: replace this with access prop
        // chris: amazingly, this is the only function that didn't call this originally ?
        //if (!xarSecurity::check('AddKeywords',0,'Item', "$modid:$itemtype:$itemid"))
        //    return $extrainfo;

        // get settings currently in force for this module/itemtype
        $settings = xarMod::apiFunc(
            'keywords',
            'hooks',
            'getsettings',
            [
                'module' => $modname,
                'itemtype' => $itemtype,
            ]
        );

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

        // see if keywords were passed to hook call
        if (!empty($extrainfo['keywords'])) {
            $keywords = $extrainfo['keywords'];
        } else {
            // otherwise, try fetch from form input
            if (!xarVar::fetch(
                'keywords',
                'isset',
                $keywords,
                null,
                xarVar::DONT_SET
            )) {
                return;
            }
        }

        // we may have been given a string list
        if (!empty($keywords) && !is_array($keywords)) {
            $keywords = xarMod::apiFunc(
                'keywords',
                'admin',
                'separatekeywords',
                [
                    'keywords' => $keywords,
                ]
            );
        }

        // it's ok if there are no keywords
        if (empty($keywords)) {
            return $extrainfo;
        } //$keywords = array();

        if (!empty($settings['restrict_words'])) {
            $restricted_list = xarMod::apiFunc(
                'keywords',
                'words',
                'getwords',
                [
                    'index_id' => $settings['index_id'],
                ]
            );
            // store only keywords that are also in the restricted list
            $keywords = array_intersect($keywords, $restricted_list);
        }
        $keywords = array_filter(array_unique($keywords));

        // keywords may be empty after restrictions and filters are applied
        if (empty($keywords)) {
            return $extrainfo;
        } //$keywords = array();

        // have keywords, create associations
        if (!xarMod::apiFunc(
            'keywords',
            'words',
            'createitems',
            [
                'index_id' => $index_id,
                'keyword' => $keywords,
            ]
        )) {
            return;
        }

        // Retrieve the list of allowed delimiters
        $delimiters = xarModVars::get('keywords', 'delimiters');
        $delimiter = !empty($delimiters) ? $delimiters[0] : ',';
        $extrainfo['keywords'] = implode($delimiter, $keywords);

        return $extrainfo;
    }
}
