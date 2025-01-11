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

use Xaraya\Modules\UserApiClass;
use sys;

sys::import('xaraya.modules.userapi');

/**
 * Handle the keywords user API
 *
 * @method mixed decodeShorturl(array $args)
 * @method mixed encodeShorturl(array $args)
 * @method mixed getitems(array $args)
 * @method mixed getkeywordhits(array $args)
 * @method mixed getlist(array $args)
 * @method mixed getmenulinks(array $args)
 * @method mixed getmultiplewords(array $args)
 * @method mixed getwords(array $args)
 * @method mixed getwordslimited(array $args)
 * @method mixed search(array $args)
 * @extends UserApiClass<Module>
 */
class UserApi extends UserApiClass
{
    // ...
}
