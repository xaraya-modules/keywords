<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.6.2
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords\AdminApi;

use Xaraya\Modules\Keywords\AdminApi;
use Xaraya\Modules\Keywords\WordsApi;
use Xaraya\Modules\Keywords\IndexApi;
use Xaraya\Modules\Keywords\MethodClass;
use sys;
use BadParameterException;

sys::import('modules.keywords.method');

/**
 * keywords adminapi removehook function
 * @extends MethodClass<AdminApi>
 */
class RemovehookMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * delete all entries for a module - hook for ('module','remove','API')
     * @param array<mixed> $args
     * @var mixed $objectid ID of the object (must be the module name here !!)
     * @var mixed $extrainfo extra information
     * @return bool|void true on success, false on failure
     * @see AdminApi::removehook()
     */
    public function __invoke(array $args = [])
    {
        extract($args);
        /** @var WordsApi $wordsapi */
        $wordsapi = $this->wordsapi();
        /** @var IndexApi $indexapi */
        $indexapi = $this->indexapi();

        if (empty($extrainfo)) {
            $extrainfo = [];
        }

        // When called via hooks, we should get the real module name from objectid
        // here, because the current module is probably going to be 'modules' !!!
        if (!isset($objectid) || !is_string($objectid)) {
            $msg = 'Invalid #(1) for #(2) module #(3) function #(4)()';
            $vars = ['objectid (module name)', 'keywords', 'adminapi', 'removehook'];
            throw new BadParameterException($vars, $msg);
        }

        $modname = $objectid;

        $modid = $this->mod()->getRegID($modname);
        if (empty($modid)) {
            $msg = 'Invalid #(1) for #(2) module #(3) function #(4)()';
            $vars = ['objectid (module name)', 'keywords', 'adminapi', 'removehook'];
            throw new BadParameterException($vars, $msg);
        }

        // delete all words associated with this module
        if (!$wordsapi->deleteitems(
            [
                'module_id' => $modid,
            ]
        )) {
            return;
        }

        // delete all indexes for this module
        if (!$indexapi->deleteitems(
            [
                'module_id' => $modid,
            ]
        )) {
            return;
        }

        // Return the extra info
        return $extrainfo;
    }
}
