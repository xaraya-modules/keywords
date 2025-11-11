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

/**
 * keywords admin overview function
 * @extends MethodClass<AdminGui>
 */
class OverviewMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Overview function that displays the standard Overview page
     * This function shows the overview template, currently admin-main.xd.
     * The template contains overview and help texts
     * @see AdminGui::overview()
     */
    public function __invoke(array $args = [])
    {
        /* Security Check */
        if (!$this->sec()->checkAccess('AdminKeywords', 0)) {
            return;
        }

        $data = [];

        $data['context'] = $this->getContext();
        return $this->mod()->template('main', $data, 'main');
    }
}
