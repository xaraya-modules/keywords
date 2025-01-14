<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords\UserApi;


use Xaraya\Modules\Keywords\UserApi;
use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarDB;
use xarModVars;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * keywords userapi getwordslimited function
 * @extends MethodClass<UserApi>
 */
class GetwordslimitedMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * get entries for a module item
     * This function gets the restricted keywords for one module
     * @param array<mixed> $args
     * @var int $moduleid module id
     * @return array|void of keywords, sorted ASC
     */
    public function __invoke(array $args = [])
    {
        if (!$this->checkAccess('ReadKeywords')) {
            return;
        }

        extract($args);

        if (!isset($moduleid) || !is_numeric($moduleid)) {
            $msg = $this->translate(
                'Invalid #(1) for #(2) function #(3)() in module #(4)',
                'module id',
                'user',
                'getwordslimited',
                'keywords'
            );
            throw new BadParameterException(null, $msg);
        }


        $dbconn = xarDB::getConn();
        $xartable = & xarDB::getTables();
        $keywordstable = $xartable['keywords_restr'];
        $bindvars = [];

        // Get restricted keywords for this module item

        $useitemtype = $this->getModVar('useitemtype');

        $query = "SELECT id,
                         keyword
                 FROM $keywordstable ";
        if (!empty($useitemtype) && isset($itemtype)) {
            $query .= " WHERE module_id = '0' OR ( module_id= ? AND  itemtype = ? ) ORDER BY keyword ASC";
            $bindvars[] = $moduleid;
            $bindvars[] = $itemtype;
        } else {
            $query .= " WHERE module_id = '0' OR  module_id= ? ORDER BY keyword ASC";
            $bindvars[] = $moduleid;
        }


        $result = & $dbconn->Execute($query, $bindvars);
        if (!$result) {
            return;
        }
        if ($result->EOF) {
            $result->Close();
        }

        $keywords = [];

        while (!$result->EOF) {
            [$id,
                $word] = $result->fields;
            $keywords[$id] = $word;
            $result->MoveNext();
        }
        $result->Close();

        return $keywords;
    }
}
