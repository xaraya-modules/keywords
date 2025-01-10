<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords\AdminGui;

use Xaraya\Modules\MethodClass;
use xarSecurity;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * keywords admin hooks function
 */
class HooksMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Configure hooks by hook module
     * @author Xaraya Development Team
     * @param mixed $args ['curhook'] current hook module (optional)
     * @param mixed $args ['return_url'] URL to return to after updating the hooks (optional)
     * @return array data for the template display
     */
    public function __invoke(array $args = [])
    {
        // Security
        if (!xarSecurity::check('ManageKeywords')) {
            return;
        }

        $data = [];
        return $data;
    }
}
