<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.6.2
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords\WordsApi;

use Xaraya\Modules\Keywords\MethodClass;
use Xaraya\Modules\Keywords\WordsApi;
use xarDB;
use sys;

sys::import('modules.keywords.method');

/**
 * keywords wordsapi countmoduleitems function
 * @extends MethodClass<WordsApi>
 */
class CountmoduleitemsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Summary of __invoke
     * @param array<mixed> $args
     * @see WordsApi::countmoduleitems()
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        $dbconn = $this->db()->getConn();
        $tables = & $this->db()->getTables();
        $wordstable = $tables['keywords'];
        $idxtable = $tables['keywords_index'];
        $modstable = $tables['modules'];

        $select = [];
        $from = [];
        $join = [];
        $where = [];
        $groupby = [];
        $bindvars = [];

        $select['count'] = "COUNT(DISTINCT idx.module_id, idx.itemtype)";
        $from['idx'] = "$idxtable idx";

        if (!empty($skip_restricted)) {
            $where[] = 'idx.itemid != 0';
        }

        $query = "SELECT " . implode(',', $select);
        $query .= " FROM " . implode(',', $from);
        if (!empty($join)) {
            $query .= " " . implode(' ', $join);
        }
        if (!empty($where)) {
            $query .= " WHERE " . implode(' AND ', $where);
        }
        if (!empty($groupby)) {
            $query .= " GROUP BY " . implode(',', $groupby);
        }

        // return the count
        $result = $dbconn->Execute($query, $bindvars);
        [$numitems] = $result->fields;
        $result->Close();
        return $numitems;
    }
}
