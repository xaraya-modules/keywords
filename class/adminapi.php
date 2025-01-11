<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords;

use Xaraya\Modules\AdminApiClass;
use sys;

sys::import('xaraya.modules.adminapi');

/**
 * Handle the keywords admin API
 *
 * @method mixed createhook(array $args)
 * @method mixed deletehook(array $args)
 * @method mixed getallkey(array $args)
 * @method mixed getwordslimited(array $args)
 * @method mixed limited(array $args)
 * @method mixed removehook(array $args)
 * @method mixed resetlimited(array $args)
 * @method mixed separatekeywords(array $args)
 * @method mixed updatehook(array $args)
 * @extends AdminApiClass<Module>
 */
class AdminApi extends AdminApiClass
{
    // ...
}
