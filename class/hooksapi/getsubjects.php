<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.6.2
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords\HooksApi;

use Xaraya\Modules\Keywords\MethodClass;
use Xaraya\Modules\Keywords\HooksApi;
use Exception;
use xarHooks;
use xarMod;
use sys;

sys::import('modules.keywords.class.method');

/**
 * keywords hooksapi getsubjects function
 * @extends MethodClass<HooksApi>
 */
class GetsubjectsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Summary of __invoke
     * @param array<mixed> $args
     * @return array<mixed>
     * @see HooksApi::getsubjects()
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (!isset($module) || empty($module)) {
            $module = null;
        }

        $subjects = xarHooks::getObserverSubjects('keywords', $module);
        if (!empty($subjects)) {
            foreach ($subjects as $hookedto => $hooks) {
                $modinfo = $this->mod()->getInfo($this->mod()->getRegID($hookedto));
                try {
                    $itemtypes = $this->mod()->apiFunc($hookedto, 'user', 'getitemtypes');
                } catch (Exception $e) {
                    $itemtypes = [];
                }
                $modinfo['itemtypes'] = [];
                foreach ($itemtypes as $typeid => $typeinfo) {
                    if (!isset($hooks[0]) && !isset($hooks[$typeid])) {
                        continue;
                    } // not hooked
                    $modinfo['itemtypes'][$typeid] = $typeinfo;
                }
                $subjects[$hookedto] += $modinfo;
            }
            ksort($subjects);
        }
        return $subjects;
    }
}
