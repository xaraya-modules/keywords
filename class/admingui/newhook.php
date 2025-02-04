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
use Xaraya\Modules\Keywords\HooksApi;
use Xaraya\Modules\Keywords\AdminApi;
use Xaraya\Modules\Keywords\WordsApi;
use Xaraya\Modules\Keywords\MethodClass;
use xarMod;
use xarSecurity;
use xarVar;
use xarModVars;
use xarTpl;
use sys;
use BadParameterException;

sys::import('modules.keywords.class.method');

/**
 * keywords admin newhook function
 * @extends MethodClass<AdminGui>
 */
class NewhookMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Create an entry for a module item - hook for ('item','new','GUI')
     * @param array<mixed> $args
     * @var int $objectid ID of the object
     * @var array $extrainfo extra information
     * @return string|void hook output in HTML
     * @see AdminGui::newhook()
     */
    public function __invoke(array $args = [])
    {
        extract($args);
        /** @var HooksApi $hooksapi */
        $hooksapi = $this->hooksapi();
        /** @var AdminApi $adminapi */
        $adminapi = $this->adminapi();
        /** @var WordsApi $wordsapi */
        $wordsapi = $this->wordsapi();

        if (empty($extrainfo)) {
            $extrainfo = [];
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
        // new item won't have an id yet
        if (empty($itemid)) {
            $itemid = 0;
        }

        // @todo: replace this with access prop
        if (!xarSecurity::check('AddKeywords', 0, 'Item', "$modid:$itemtype:All")) {
            return '';
        }

        // get settings currently in force for this module/itemtype
        $settings = $hooksapi->getsettings(
            [
                'module' => $modname,
                'itemtype' => $itemtype,
            ]
        );

        // see if keywords were passed to hook call
        if (!empty($extrainfo['keywords'])) {
            $keywords = $extrainfo['keywords'];
        } else {
            // could be an item preview, try fetch from form input
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
            $keywords = [];
        }

        // Retrieve the list of allowed delimiters
        $delimiters = $this->mod()->getVar('delimiters');

        $data = $settings;
        if (empty($settings['restrict_words'])) {
            // no restrictions, display expects a string
            // Use first delimiter to join words
            $delimiter = !empty($delimiters) ? $delimiters[0] : ',';
            $data['keywords'] = !empty($keywords) ? implode($delimiter, $keywords) : '';
        } else {
            // get restricted list based on current settings
            $data['restricted_list'] = $wordsapi->getwords(
                [
                    'index_id' => $settings['index_id'],
                ]
            );
            // return only keywords that are also in the restricted list
            $data['keywords'] = array_intersect($keywords, $data['restricted_list']);
        }
        $data['delimiters'] = $delimiters;

        $data['context'] ??= $this->getContext();
        return $this->mod()->template('newhook', $data);
    }
}
