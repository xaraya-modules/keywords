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

use Xaraya\Modules\ModuleClass;

/**
 * Get keywords module classes via xarMod::getModule()
 */
class Module extends ModuleClass
{
    public function setClassTypes(): void
    {
        parent::setClassTypes();
        // add other class types for keywords
        $this->classtypes['hooksapi'] = 'HooksApi';
        $this->classtypes['hooksgui'] = 'HooksGui';
        $this->classtypes['indexapi'] = 'IndexApi';
        $this->classtypes['wordsapi'] = 'WordsApi';
    }
}
