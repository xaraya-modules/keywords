<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.6.2
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords\AdminApi;

use Xaraya\Modules\Keywords\AdminApi;
use Xaraya\Modules\Keywords\HooksApi;
use Xaraya\Modules\Keywords\IndexApi;
use Xaraya\Modules\Keywords\WordsApi;
use Xaraya\Modules\Keywords\MethodClass;
use xarVar;
use sys;
use BadParameterException;

sys::import('modules.keywords.method');

/**
 * keywords adminapi createhook function
 * @extends MethodClass<AdminApi>
 */
class CreatehookMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * create an entry for a module item - hook for ('item','create','GUI')
     * Optional $extrainfo['keywords'] from arguments, or 'keywords' from input
     * @param array<mixed> $args
     * @var mixed $objectid ID of the object
     * @var mixed $extrainfo extra information
     * @return array|void Extrainfo array
     * @see AdminApi::createhook()
     */
    public function __invoke(array $args = [])
    {
        extract($args);
        /** @var HooksApi $hooksapi */
        $hooksapi = $this->hooksapi();
        /** @var IndexApi $indexapi */
        $indexapi = $this->indexapi();
        /** @var AdminApi $adminapi */
        $adminapi = $this->adminapi();
        /** @var WordsApi $wordsapi */
        $wordsapi = $this->wordsapi();

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
            $modname = $this->mod()->getName();
        } else {
            $modname = $extrainfo['module'];
        }

        $modid = $this->mod()->getRegID($modname);
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
        //if (!$this->sec()->check('AddKeywords',0,'Item', "$modid:$itemtype:$itemid"))
        //    return $extrainfo;

        // get settings currently in force for this module/itemtype
        $settings = $hooksapi->getsettings(
            [
                'module' => $modname,
                'itemtype' => $itemtype,
            ]
        );

        // get the index_id for this module/itemtype/item
        $index_id = $indexapi->getid(
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
            if (!$this->var()->fetch(
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
            $keywords = $adminapi->separatekeywords(
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
            $restricted_list = $wordsapi->getwords(
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
        if (!$wordsapi->createitems(
            [
                'index_id' => $index_id,
                'keyword' => $keywords,
            ]
        )) {
            return;
        }

        // Retrieve the list of allowed delimiters
        $delimiters = $this->mod()->getVar('delimiters');
        $delimiter = !empty($delimiters) ? $delimiters[0] : ',';
        $extrainfo['keywords'] = implode($delimiter, $keywords);

        return $extrainfo;
    }
}
