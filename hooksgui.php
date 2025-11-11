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

    public function configure()
    {
        $this->setModType('hooks');
        // don't call xarMod:load() for keywords hooks GUI
    }
}
