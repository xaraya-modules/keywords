<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.6.2
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords\HooksApi;

use Xaraya\Modules\Keywords\MethodClass;
use Xaraya\Modules\Keywords\HooksApi;
use Xaraya\Modules\Keywords\AdminApi;
use Xaraya\Modules\Keywords\WordsApi;
use BadParameterException;
use xarMod;
use xarSecurity;
use xarVar;
use sys;

sys::import('modules.keywords.method');

/**
 * keywords hooksapi moduleupdateconfig function
 * @extends MethodClass<HooksApi>
 */
class ModuleupdateconfigMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * ModuleUpdateconfig Hook
     * Updates subject module (+itemtype) keywords configuration
     * @see HooksApi::moduleupdateconfig()
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

        // objectid is the name of the module
        if (empty($objectid)) {
            if (!empty($extrainfo['module']) && is_string($extrainfo['module'])) {
                $objectid = $extrainfo['module'];
            } else {
                $objectid = $this->mod()->getName();
            }
        }

        if (!isset($objectid) || !is_string($objectid)) {
            $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
            $vars = ['objectid (module name)', 'admin', 'moduleupdatehook', 'keywords'];
            throw new BadParameterException($vars, $msg);
        }

        $modname = $objectid;

        $modid = $this->mod()->getRegID($modname);
        if (empty($modid)) {
            $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
            $vars = ['module', 'admin', 'moduleupdatehook', 'keywords'];
            throw new BadParameterException($vars, $msg);
        }

        if (!empty($extrainfo['itemtype']) && is_numeric($extrainfo['itemtype'])) {
            $itemtype = $extrainfo['itemtype'];
        } else {
            $itemtype = 0;
        }

        if (!$this->sec()->check('AdminKeywords', 0, 'Item', "$modid:$itemtype:All")) {
            return $extrainfo;
        }

        $settings = $hooksapi->getsettings(
            [
                'module' => $modname,
                'itemtype' => $itemtype,
            ]
        );

        if ($settings['config_state'] == 'default') {
            // per module settings disabled, if this isn't the keywords module, bail
            if ($modname != 'keywords') {
                return $extrainfo;
            }
        } elseif ($settings['config_state'] == 'module') {
            // per itemtype settings disabled, if this isn't itemtype 0, bail
            if (!empty($itemtype)) {
                return $extrainfo;
            }
        }

        if (!$this->var()->fetch(
            'keywords_settings["global_config"]',
            'checkbox',
            $global_config,
            false,
            xarVar::NOT_REQUIRED
        )) {
            return;
        }
        if (!$this->var()->fetch(
            'keywords_settings["auto_tag_create"]',
            'pre:trim:str:1:',
            $auto_tag_create,
            '',
            xarVar::NOT_REQUIRED
        )) {
            return;
        }
        if (!$this->var()->fetch(
            'keywords_settings["auto_tag_persist"]',
            'checkbox',
            $auto_tag_persist,
            false,
            xarVar::NOT_REQUIRED
        )) {
            return;
        }

        if (!$this->var()->fetch(
            'keywords_settings["meta_keywords"]',
            'int:0:2',
            $meta_keywords,
            0,
            xarVar::NOT_REQUIRED
        )) {
            return;
        }

        if (!$this->var()->fetch(
            'keywords_settings["restrict_words"]',
            'checkbox',
            $restrict_words,
            false,
            xarVar::NOT_REQUIRED
        )) {
            return;
        }

        if (!empty($auto_tag_create)) {
            $auto_tag_create = $adminapi->separatekeywords(
                [
                    'keywords' => $auto_tag_create,
                ]
            );
        }

        if (!empty($meta_keywords)) {
            if (!$this->var()->fetch(
                'keywords_settings["meta_lang"]',
                'pre:trim:lower:str:1:',
                $meta_lang,
                $settings['meta_lang'],
                xarVar::NOT_REQUIRED
            )) {
                return;
            }
            $settings['meta_lang'] = $meta_lang;
        }

        // when switching between restricted and unrestricted we want to preserve settings
        $status_quo = $restrict_words == $settings['restrict_words'];
        if ($restrict_words && $status_quo) {
            if (!$this->var()->fetch(
                'keywords_settings["restricted_list"]',
                'pre:trim:str:1:',
                $restricted_list,
                '',
                xarVar::NOT_REQUIRED
            )) {
                return;
            }
            if (!$this->var()->fetch(
                'keywords_settings["allow_manager_add"]',
                'checkbox',
                $allow_manager_add,
                false,
                xarVar::NOT_REQUIRED
            )) {
                return;
            }
            $settings['allow_manager_add'] = $allow_manager_add;
            $old_list = $wordsapi->getwords(
                [
                    'index_id' => $settings['index_id'],
                ]
            );
            $new_list = $adminapi->separatekeywords(
                [
                    'keywords' => $restricted_list,
                ]
            );
            // be sure to add any auto tags to the list
            if (!empty($auto_tag_create)) {
                $new_list = array_merge($new_list, $auto_tag_create);
            }
            $new_list = array_values(array_unique(array_filter($new_list)));
            // add everything from new list that's not in old list
            $toadd = array_diff($new_list, $old_list);
            // remove everything from old list that's not in new list
            $toremove = array_diff($old_list, $new_list);

            if (!empty($toadd)) {
                if (!$wordsapi->createitems(
                    [
                        'index_id' => $settings['index_id'],
                        'keyword' => $toadd,
                    ]
                )) {
                    return;
                }
            }
            if (!empty($toremove)) {
                if (!$wordsapi->deleteitems(
                    [
                        'index_id' => $settings['index_id'],
                        'keyword' => $toremove,
                    ]
                )) {
                    return;
                }
            }
        }

        $settings['global_config'] = $global_config;
        $settings['auto_tag_create'] = !empty($auto_tag_create) ? $auto_tag_create : [];
        $settings['auto_tag_persist'] = $auto_tag_persist;
        $settings['meta_keywords'] = $meta_keywords;
        $settings['restrict_words'] = $restrict_words;
        if (!$hooksapi->updatesettings(
            [
                'module' => $modname,
                'itemtype' => $itemtype,
                'settings' => $settings,
            ]
        )) {
            return $extrainfo;
        }

        return $extrainfo;
    }
}
