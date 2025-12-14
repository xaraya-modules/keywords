<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.6.2
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords\UserGui;

use Xaraya\Modules\Keywords\UserGui;
use Xaraya\Modules\Keywords\HooksApi;
use Xaraya\Modules\Keywords\IndexApi;
use Xaraya\Modules\Keywords\WordsApi;
use Xaraya\Modules\Keywords\MethodClass;
use BadParameterException;

/**
 * keywords user displayhook function
 * @extends MethodClass<UserGui>
 */
class DisplayhookMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * display keywords entry for a module item - hook for ('item','display','GUI')
     * @param array<mixed> $args
     * @var mixed $objectid ID of the object
     * @var mixed $extrainfo extra information
     * @return mixed|void Array with information for the template that is called.
     * @see UserGui::displayhook()
     */
    public function __invoke(array $args = [])
    {
        extract($args);
        /** @var HooksApi $hooksapi */
        $hooksapi = $this->hooksapi();
        /** @var IndexApi $indexapi */
        $indexapi = $this->indexapi();
        /** @var WordsApi $wordsapi */
        $wordsapi = $this->wordsapi();

        if (empty($extrainfo)) {
            $extrainfo = [];
        }

        if (!isset($objectid) || !is_numeric($objectid)) {
            $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
            $vars = ['objectid', 'user', 'displayhook', 'keywords'];
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
            $vars = ['module', 'admin', 'updatehook', 'keywords'];
            throw new BadParameterException($vars, $msg);
        }

        if (!empty($extrainfo['itemtype']) && is_numeric($extrainfo['itemtype'])) {
            $itemtype = $extrainfo['itemtype'];
        } else {
            $itemtype = 0;
        }

        if (!empty($extrainfo['itemid'])) {
            $itemid = $extrainfo['itemid'];
        } else {
            $itemid = $objectid;
        }

        // @todo: replace this with access prop
        if (!$this->sec()->check('ReadKeywords', 0, 'Item', "$modid:$itemtype:$itemid")) {
            return '';
        }

        // get settings currently in force for this module/itemtype
        $data = $hooksapi->getsettings(
            [
                'module' => $modname,
                'itemtype' => $itemtype,
            ]
        );

        // Retrieve the list of allowed delimiters
        $delimiters = $this->mod()->getVar('delimiters');
        $delimiter = !empty($delimiters) ? $delimiters[0] : ',';

        // get the index_id for this module/itemtype/item
        $index_id = $indexapi->getid(
            [
                'module' => $modname,
                'itemtype' => $itemtype,
                'itemid' => $itemid,
            ]
        );

        // get the keywords associated with this item
        $keywords = $wordsapi->getwords(
            [
                'index_id' => $index_id,
            ]
        );

        // @checkme: do we need to merge in auto tags here ?
        // if there are auto tags and they're persistent, add them to keywords
        if (!empty($data['auto_tag_create']) && !empty($data['auto_tag_persist'])) {
            $keywords = array_unique(array_merge($keywords, $data['auto_tag_create']));
        }

        // config may have changed since the keywords were added
        if (!empty($data['restrict_words'])) {
            $restricted_list = $wordsapi->getwords(
                [
                    'index_id' => $data['index_id'],
                ]
            );
            // show only keywords that are also in the restricted list
            $keywords = array_intersect($keywords, $restricted_list);
        }

        if (empty($keywords)) {
            return '';
        }

        // @fixme: mistakenly assumes this hook is called only once, and always by current main module func
        // @checkme: cache a cumultive list of keywords encountered during this request ?
        // @fixme: find some way to identify and cache the 'real' current main module/itemtype/item keywords
        $keys = implode(',', $keywords);
        $this->mem()->set('Blocks.keywords', 'keys', $keys);

        // see if we're handling dynamic keywords
        if (!empty($data['meta_keywords'])) {
            // prep data for template
            $data['meta_append'] = $data['meta_keywords'] == 1 ? 1 : 0;
            $data['meta_content'] = implode($delimiter, $keywords);
        }

        $data['keywords'] = $keywords;
        $data['showlabel'] = $extrainfo['showlabel'] ?? true;

        $tpltype = $extrainfo['tpltype'] ?? 'user';
        return $this->tpl()->module('keywords', $tpltype, 'displayhook', $data);
    }
}
