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

/**
 * Handle the keywords index API
 *
 * @method mixed countitems(array $args = [])
 * @method mixed createitem(array $args = [])
 * @method mixed deleteitem(array $args = [])
 * @method mixed deleteitems(array $args = [])
 * @method mixed getid(array $args = [])
 * @method mixed getitem(array $args = [])
 * @method mixed getitems(array $args = [])
 * @method mixed updateitem(array $args = [])
 * @extends UserApiClass<Module>
 */
class IndexApi extends UserApiClass
{
    use OtherApiTrait;

    public function configure()
    {
        $this->setModType('index');
        // don't call xarMod:apiLoad() for keywords index API
    }
}
