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

use Xaraya\Modules\UserApiInterface;
use Xaraya\Modules\UserGuiInterface;

/**
 * Trait to handle other api/gui functions
 */
trait OtherApiTrait
{
    /**
     * Get module hooks API class for this module
     */
    public function hooksapi(): ?UserApiInterface
    {
        $component = $this->getModule()->getComponent('HooksApi');
        assert($component instanceof UserApiInterface);
        return $component;
    }

    /**
     * Get module hooks GUI class for this module
     */
    public function hooksgui(): ?UserGuiInterface
    {
        $component = $this->getModule()->getComponent('HooksGui');
        assert($component instanceof UserGuiInterface);
        return $component;
    }

    /**
     * Get module index API class for this module
     */
    public function indexapi(): ?UserApiInterface
    {
        $component = $this->getModule()->getComponent('IndexApi');
        assert($component instanceof UserApiInterface);
        return $component;
    }

    /**
     * Get module words API class for this module
     */
    public function wordsapi(): ?UserApiInterface
    {
        $component = $this->getModule()->getComponent('WordsApi');
        assert($component instanceof UserApiInterface);
        return $component;
    }
}
