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
use Keywords;

/**
 * keywords hooksapi updatesettings function
 * @extends MethodClass<HooksApi>
 */
class UpdatesettingsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Summary of __invoke
     * @param array<mixed> $args
     * @return bool|void
     * @see HooksApi::updatesettings()
     */
    public function __invoke(array $args = [])
    {
        extract($args);
        /** @var HooksApi $hooksapi */
        $hooksapi = $this->hooksapi();

        if (isset($module_id)) {
            $module = $this->mod()->getName($module_id);
        }
        if (!isset($module)) {
            $module = $this->mod()->getName();
        }

        if (empty($itemtype)) {
            $itemtype = 0;
        }

        $defaults = $hooksapi->getsettings(
            [
                'module' => $module,
                'itemtype' => $itemtype,
            ]
        );

        if ($defaults['config_state'] == 'default') {
            // per module settings disabled, if this isn't the keywords module, bail
            if ($module != 'keywords') {
                return;
            }
        } elseif ($defaults['config_state'] == 'module') {
            // per itemtype settings disabled, if this isn't itemtype 0, bail
            if (!empty($itemtype)) {
                return;
            }
        }

        if (empty($settings)) {
            $settings = $defaults;
        }

        Keywords::setConfig($module, $itemtype, $settings);

        return true;
    }
}
