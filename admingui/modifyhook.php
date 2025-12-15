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
use Xaraya\Modules\Keywords\IndexApi;
use Xaraya\Modules\Keywords\WordsApi;
use Xaraya\Modules\Keywords\AdminApi;
use Xaraya\Modules\Keywords\MethodClass;
use ixarVar;
use BadParameterException;

/**
 * keywords admin modifyhook function
 * @extends MethodClass<AdminGui>
 */
class ModifyhookMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * modify an entry for a module item - hook for ('item','modify','GUI')
     * @param array<mixed> $args
     * @var int $objectid ID of the object
     * @var array $extrainfo
     * @var string $extrainfo['keywords'] or 'keywords' from input (optional)
     * @return string|void hook output in HTML
     * @see AdminGui::modifyhook()
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
        /** @var AdminApi $adminapi */
        $adminapi = $this->adminapi();

        if (empty($extrainfo)) {
            $extrainfo = [];
        }

        if (!isset($objectid) || !is_numeric($objectid)) {
            $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
            $vars = ['objectid', 'admin', 'modifyhook', 'keywords'];
            throw new BadParameterException($vars, $msg);
        }

        // When called via hooks, the module name may be empty. Get it from current module.
        if (empty($extrainfo['module'])) {
            $modname = $this->req()->getModule();
        } else {
            $modname = $extrainfo['module'];
        }

        $modid = $this->mod()->getRegID($modname);
        if (empty($modid)) {
            $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
            $vars = ['module', 'admin', 'modifyhook', 'keywords'];
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

        // no permission, no worries, just don't display the form
        if (!$this->sec()->check('AddKeywords', 0, 'Item', "$modid:$itemtype:$itemid")) {
            return '';
        }

        // get settings currently in force for this module/itemtype
        $data = $hooksapi->getsettings(
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
            // could be an item preview, try fetch from form input
            if (!$this->var()->fetch(
                'keywords',
                'isset',
                $keywords,
                null,
                ixarVar::DONT_SET
            )) {
                return;
            }
        }
        // keywords not supplied
        if (!isset($keywords)) {
            // get the keywords associated with this item
            $keywords = $wordsapi->getwords(
                [
                    'index_id' => $index_id,
                ]
            );
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

        // if there are auto tags and they're persistent, add them to keywords
        if (!empty($data['auto_tag_create']) && !empty($data['auto_tag_persist'])) {
            $keywords = array_merge($keywords, $data['auto_tag_create']);
        }


        // Retrieve the list of allowed delimiters
        $delimiters = $this->mod()->getVar('delimiters');
        $delimiter = !empty($delimiters) ? $delimiters[0] : ',';

        if (empty($data['restrict_words'])) {
            // no restrictions, display expects a string
            // Use first delimiter to join words
            $data['keywords'] = !empty($keywords) ? implode($delimiter, array_unique($keywords)) : '';
        } else {
            // get restricted list based on current settings
            $data['restricted_list'] = $wordsapi->getwords(
                [
                    'index_id' => $data['index_id'],
                ]
            );
            // return only keywords that are also in the restricted list
            $data['keywords'] = array_intersect($keywords, $data['restricted_list']);
            // see if managers are allowed to add to restricted list
            if (!empty($data['allow_manager_add'])) {
                // see if current user is a manager
                $data['is_manager'] = $this->sec()->check('ManageKeywords', 0, 'Item', "$modid:$itemtype:$itemid");
                if (!empty($data['is_manager'])) {
                    // see if keywords were passed to hook call
                    if (!empty($extrainfo['restricted_extra'])) {
                        $toadd = $extrainfo['restricted_extra'];
                    } else {
                        // could be an item preview, try fetch from form input
                        if (!$this->var()->fetch(
                            'restricted_extra',
                            'isset',
                            $toadd,
                            [],
                            ixarVar::NOT_REQUIRED
                        )) {
                            return;
                        }
                    }
                    // we may have been given a string list
                    if (!empty($toadd) && !is_array($toadd)) {
                        $toadd = $adminapi->separatekeywords(
                            [
                                'keywords' => $toadd,
                            ]
                        );
                    }
                    // display expects a string
                    $data['restricted_extra'] = !empty($toadd) ? implode($delimiter, array_unique($toadd)) : '';
                }
            }
        }
        $data['delimiters'] = $delimiters;

        return $this->render('modifyhook', $data);
    }
}
