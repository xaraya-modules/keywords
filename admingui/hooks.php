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
use Xaraya\Modules\Keywords\MethodClass;
use sys;

sys::import('modules.keywords.method');

/**
 * keywords admin hooks function
 * @extends MethodClass<AdminGui>
 */
class HooksMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Configure hooks by hook module
     * @author Xaraya Development Team
     * @param array<mixed> $args
     * @var mixed $curhook current hook module (optional)
     * @var mixed $return_url URL to return to after updating the hooks (optional)
     * @return array|void data for the template display
     * @see AdminGui::hooks()
     */
    public function __invoke(array $args = [])
    {
        // Security
        if (!$this->sec()->checkAccess('ManageKeywords')) {
            return;
        }

        $data = [];
        return $data;
    }
}
