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

use Xaraya\Modules\MethodClass as CoreMethodClass;
use Xaraya\Modules\ModuleServicesInterface;
use Xaraya\Modules\UserApiInterface;
use Xaraya\Modules\UserGuiInterface;

/**
 * Handle single module function as method from api/gui module class
 * @template TComponent of ModuleServicesInterface
 * @extends CoreMethodClass<TComponent>
 */
class MethodClass extends CoreMethodClass
{
    /**
     * Get module hooks API class for this module
     */
    public function hooksapi(): UserApiInterface|null
    {
        return $this->getParent()->hooksapi();
    }

    /**
     * Get module hooks GUI class for this module
     */
    public function hooksgui(): UserGuiInterface|null
    {
        return $this->getParent()->hooksgui();
    }

    /**
     * Get module index API class for this module
     */
    public function indexapi(): UserApiInterface|null
    {
        return $this->getParent()->indexapi();
    }

    /**
     * Get module words API class for this module
     */
    public function wordsapi(): UserApiInterface|null
    {
        return $this->getParent()->wordsapi();
    }
}
