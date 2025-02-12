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

use Xaraya\Modules\AdminGuiClass;
use sys;

sys::import('xaraya.modules.admingui');
sys::import('modules.keywords.adminapi');

/**
 * Handle the keywords admin GUI
 *
 * @method mixed delete(array $args)
 * @method mixed hooks(array $args)
 * @method mixed main(array $args)
 * @method mixed modify(array $args)
 * @method mixed modifyconfig(array $args)
 * @method mixed modifyhook(array $args)
 * @method mixed new(array $args)
 * @method mixed newhook(array $args)
 * @method mixed overview(array $args)
 * @method mixed privileges(array $args)
 * @method mixed updateconfig(array $args)
 * @method mixed view(array $args)
 * @extends AdminGuiClass<Module>
 */
class AdminGui extends AdminGuiClass
{
    use OtherApiTrait;
    // ...
}
