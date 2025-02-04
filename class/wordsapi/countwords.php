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
use BadParameterException;
use xarDB;
use xarMod;
use sys;

sys::import('modules.keywords.class.method');

/**
 * keywords wordsapi countwords function
 * @extends MethodClass<WordsApi>
 */
class CountwordsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Summary of __invoke
     * @param array<mixed> $args
     * @throws \BadParameterException
     * @see WordsApi::countwords()
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (!empty($module)) {
            $module_id = $this->mod()->getRegID($module);
        }
        if (isset($module_id) && (empty($module_id) || !is_numeric($module_id))) {
            $invalid[] = 'module_id';
        }

        if (isset($itemtype) && !is_numeric($itemtype)) {
            $invalid[] = 'itemtype';
        }

        if (isset($itemid) && !is_numeric($itemid)) {
            $invalid[] = 'itemid';
        }


        if (!empty($invalid)) {
            $msg = 'Invalid #(1) for #(2) module #(3) function #(4)()';
            $vars = [implode(', ', $invalid), 'keywords', '', ''];
            throw new BadParameterException($vars, $msg);
        }

        // count of unique keywords
        // optionally by module/itemtype

        $dbconn = $this->db()->getConn();
        $tables = & $this->db()->getTables();
        $wordstable = $tables['keywords'];
        $idxtable = $tables['keywords_index'];

        $select = [];
        $from = [];
        $join = [];
        $where = [];
        $groupby = [];
        $bindvars = [];

        $select['count'] = "COUNT(DISTINCT words.keyword) AS wordcount";

        $from['words'] = "$wordstable words";

        if (!empty($module_id)) {
            $from['idx'] = "$idxtable idx";
            $where[] = 'idx.module_id = ?';
            $bindvars[] = (int) $module_id;
        }

        if (isset($itemtype)) {
            $from['idx'] = "$idxtable idx";
            $where[] = 'idx.itemtype = ?';
            $bindvars[] = $itemtype;
        }

        if (isset($itemid)) {
            $from['idx'] = "$idxtable idx";
            $where[] = 'idx.itemid = ?';
            $bindvars[] = $itemid;
        }

        if (!empty($skip_restricted)) {
            $from['idx'] = "$idxtable idx";
            $where[] = '(idx.module_id != ? OR idx.itemid != 0)';
            $bindvars[] = $this->mod()->getRegID('keywords');
        }

        if (!empty($from['idx'])) {
            $where[] = 'words.index_id = idx.id';
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
