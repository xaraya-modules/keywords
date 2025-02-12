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
 * Handle the keywords words API
 *
 * @method mixed countitems(array $args = [])
 * @method mixed countmoduleitems(array $args = [])
 * @method mixed countwords(array $args = [])
 * @method mixed createitems(array $args = [])
 * @method mixed deleteitems(array $args = [])
 * @method mixed getitemcounts(array $args = [])
 * @method mixed getitems(array $args = [])
 * @method mixed getmodulecounts(array $args = [])
 * @method mixed getwordcounts(array $args)
 * @method mixed getwords(array $args = [])
 * @extends UserApiClass<Module>
 */
class WordsApi extends UserApiClass
{
    use OtherApiTrait;
    // ...
}
