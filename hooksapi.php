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

use Xaraya\Modules\UserApiClass;
use sys;

sys::import('xaraya.modules.userapi');

/**
 * Handle the keywords hooks API
 *
 * @method mixed getsettings(array $args = [])
 * @method mixed getsubjects(array $args = [])
 * @method mixed moduleupdateconfig(array $args = []) ModuleUpdateconfig Hook - Updates subject module (+itemtype) keywords configuration
 * @method mixed updatesettings(array $args = [])
 * @extends UserApiClass<Module>
 */
class HooksApi extends UserApiClass
{
    use OtherApiTrait;

    public function configure()
    {
        $this->setModType('hooks');
        // don't call xarMod:apiLoad() for keywords hooks API
    }
}
