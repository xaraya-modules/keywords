<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.6.2
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
 **/

namespace Xaraya\Modules\Keywords;

use Xaraya\Modules\UserGuiClass;
use sys;

sys::import('xaraya.modules.usergui');
sys::import('modules.keywords.class.userapi');

/**
 * Handle the keywords user GUI
 *
 * @method mixed display(array $args)
 * @method mixed displayhook(array $args)
 * @method mixed main(array $args)
 * @method mixed search(array $args)
 * @method mixed view(array $args)
 * @extends UserGuiClass<Module>
 */
class UserGui extends UserGuiClass
{
    use OtherApiTrait;
    // ...
}
