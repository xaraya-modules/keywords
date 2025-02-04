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
use SQLException;
use xarDB;
use xarMod;
use sys;

sys::import('modules.keywords.class.method');

/**
 * keywords wordsapi getwordcounts function
 * @extends MethodClass<WordsApi>
 */
class GetwordcountsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     *
     * @access public
     * @param array<mixed> $args
     * @var string $args [module]
     * @var int $args [module_id]
     * @var int $args [itemtype]
     * @var int $args [itemid]
     * @var bool $args [skip_restricted]
     * @var int $args [startnum]
     * @var int $args [numitems]
     * @var mixed $args [keyword]
     * @var mixed $args [sort]
     * @var string $args [index_key]
     * @return array
     * @throws \BadParameterException SQLException
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (!empty($module)) {
            $module_id = xarMod::getRegID($module);
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
            $vars = [implode(', ', $invalid), 'keywords', 'wordsapi', 'getwordcounts'];
            throw new BadParameterException($vars, $msg);
        }

        // list of unique keywords, with density count
        // optionally by module/itemtype
        // sort on name or count

        $dbconn = xarDB::getConn();
        $tables = & xarDB::getTables();
        $wordstable = $tables['keywords'];
        $idxtable = $tables['keywords_index'];

        $select = [];
        $from = [];
        $join = [];
        $where = [];
        $groupby = [];
        $orderby = [];
        $bindvars = [];

        $select['keyword'] = "words.keyword";
        $select['count'] = "COUNT(words.keyword) AS wordcount";

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
            $bindvars[] = xarMod::getRegID('keywords');
        }

        if (!empty($from['idx'])) {
            $where[] = 'words.id = idx.keyword_id';
        }

        $groupby['keyword'] = 'words.keyword';

        if (empty($orderby)) {
            $orderby['keyword'] = 'words.keyword ASC';
        }
        //$orderby['count'] = 'wordcount DESC';


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
        if (!empty($orderby)) {
            $query .= " ORDER BY " . implode(',', $orderby);
        }

        $stmt = $dbconn->prepareStatement($query);
        if (!empty($numitems)) {
            $stmt->setLimit($numitems);
            if (empty($startnum)) {
                $startnum = 1;
            }
            $stmt->setOffset($startnum - 1);
        }
        $result = $stmt->executeQuery($bindvars);

        $items = [];
        while ($result->next()) {
            $item = [];
            foreach (array_keys($select) as $field) {
                $item[$field] = array_shift($result->fields);
            }
            $items[] = $item;
        }
        $result->close();

        return $items;
    }
}
