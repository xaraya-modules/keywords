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
sys::import('modules.keywords.hooksapi');

/**
 * Handle the keywords hooks GUI
 *
 * @method mixed modulemodifyconfig(array $args = [])
 * @extends UserGuiClass<Module>
 */
class HooksGui extends UserGuiClass
{
    use OtherApiTrait;
    // ...
}
