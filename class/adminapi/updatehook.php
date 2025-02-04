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
use xarMod;
use xarSecurity;
use xarModVars;
use sys;
use BadParameterException;

sys::import('modules.keywords.class.method');

/**
 * keywords adminapi updatehook function
 * @extends MethodClass<AdminApi>
 */
class UpdatehookMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * update entry for a module item - hook for ('item','update','API')
     * Optional $extrainfo['keywords'] from arguments, or 'keywords' from input
     * @param array<mixed> $args
     * @var int $objectid ID of the object
     * @var array $extrainfo extra information
     * @return mixed|void true on success, false on failure. string keywords list
     * @see AdminApi::updatehook()
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
            $vars = ['objectid', 'admin', 'updatehook', 'keywords'];
            throw new BadParameterException($vars, $msg);
        }

        // We can exit immediately if the status flag is set because we are just updating
        // the status in the articles or other content module that works on that principle
        // Bug 1960 and 3161
        if ($this->var()->isCached('Hooks.all', 'noupdate') || !empty($extrainfo['statusflag'])) {
            return $extrainfo;
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
            // keywords passed programatically, don't check current user here, this has nothing to do with them
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
            // keywords from form input, check current user has permission to add keywords here
            if (!empty($keywords) && !xarSecurity::check('AddKeywords', 0, 'Item', "$modid:$itemtype:$itemid")) {
                return $extrainfo;
            }  // no permission, no worries
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
        if (!empty($settings['auto_tag_create']) && !empty($settings['auto_tag_persist'])) {
            $keywords = array_merge($keywords, $settings['auto_tag_create']);
        }

        // get the current keywords associated with this item
        $oldwords = $wordsapi->getwords(
            [
                'index_id' => $index_id,
            ]
        );

        if (!empty($settings['restrict_words'])) {
            $restricted_list = $wordsapi->getwords(
                [
                    'index_id' => $settings['index_id'],
                ]
            );
            // store only keywords that are also in the restricted list
            $keywords = array_intersect($keywords, $restricted_list);
            // see if managers are allowed to add to restricted list
            if (!empty($settings['allow_manager_add'])) {
                // see if current user is a manager
                $data['is_manager'] = xarSecurity::check('ManageKeywords', 0, 'Item', "$modid:$itemtype:$itemid");
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
                            xarVar::NOT_REQUIRED
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
                    if (!empty($toadd)) {
                        // add words to restricted list
                        if (!$wordsapi->createitems(
                            [
                                'index_id' => $settings['index_id'],
                                'keyword' => array_unique(array_diff($toadd, $keywords)),
                            ]
                        )) {
                            return;
                        }
                        // merge words with existing keywords
                        $keywords = array_merge($keywords, $toadd);
                    }
                }
            }
        }
        $toadd = array_filter(array_unique(array_diff($keywords, $oldwords)));
        $toremove = array_filter(array_unique(array_diff($oldwords, $keywords)));

        if (!empty($toadd)) {
            if (!$wordsapi->createitems(
                [
                    'index_id' => $index_id,
                    'keyword' => $toadd,
                ]
            )) {
                return;
            }
        }
        if (!empty($toremove)) {
            if (!$wordsapi->deleteitems(
                [
                    'index_id' => $index_id,
                    'keyword' => $toremove,
                ]
            )) {
                return;
            }
        }

        // Retrieve the list of allowed delimiters
        $delimiters = $this->mod()->getVar('delimiters');
        $delimiter = !empty($delimiters) ? $delimiters[0] : ',';
        $extrainfo['keywords'] = implode($delimiter, $keywords);

        return $extrainfo;
    }
}
