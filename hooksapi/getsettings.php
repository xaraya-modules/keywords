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
 * keywords hooksapi getsettings function
 * @extends MethodClass<HooksApi>
 */
class GetsettingsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Summary of __invoke
     * @param array<mixed> $args
     * @see HooksApi::getsettings()
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (!empty($module_id)) {
            $module = $this->mod()->getName($module_id);
        }
        if (empty($module)) {
            $module = $this->mod()->getName();
        }

        if (empty($itemtype)) {
            $itemtype = 0;
        }

        // keywords module config requested or per module config is disabled, return defaults
        if ($module == 'keywords' || !empty(Keywords::getConfig('keywords')->global_config)) {
            return Keywords::getConfig('keywords', 0, ['config_state' => 'default'])->getPublicProperties();
        }

        // if we're here, per module config is enabled, and this isn't the keywords module
        // if module defaults requested or per itemtype config is disabled, return module defaults
        if (empty($itemtype) || !empty(Keywords::getConfig($module, 0)->global_config)) {
            return Keywords::getConfig($module, 0, ['config_state' => 'module'])->getPublicProperties();
        }

        // if we're here, per itemtype config is enabled and this isn't itemtype 0
        return Keywords::getConfig($module, $itemtype, ['config_state' => 'itemtype'])->getPublicProperties();
    }
}
