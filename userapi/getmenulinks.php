<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.6.2
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords\UserApi;

use Xaraya\Modules\Keywords\UserApi;
use Xaraya\Modules\Keywords\MethodClass;
use sys;

sys::import('modules.keywords.method');

/**
 * keywords userapi getmenulinks function
 * @extends MethodClass<UserApi>
 */
class GetmenulinksMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * utility function pass individual menu items to the main menu
     * @author mikespub
     * @return array containing the menulinks for the main menu items (empty for user)
     * @see UserApi::getmenulinks()
     */
    public function __invoke(array $args = [])
    {
        $menulinks = [];

        return $menulinks;
    }
}
