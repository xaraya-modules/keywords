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
use Xaraya\Modules\Keywords\AdminApi;
use BadParameterException;
use xarDB;
use xarMod;
use sys;

sys::import('modules.keywords.class.method');

/**
 * keywords wordsapi getitemcounts function
 * @extends MethodClass<WordsApi>
 */
class GetitemcountsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Summary of __invoke
     * @param array<mixed> $args
     * @throws \BadParameterException
     * @return array<array>
     * @see WordsApi::getitemcounts()
     */
    public function __invoke(array $args = [])
    {
        extract($args);
        /** @var AdminApi $adminapi */
        $adminapi = $this->adminapi();

        if (isset($id) && (empty($id) || !is_numeric($id))) {
            $invalid[] = 'id';
        }

        if (isset($keyword)) {
            // we may have been given a string list
            if (!empty($keyword) && !is_array($keyword)) {
                $keyword = $adminapi->separatekeywords(
                    [
                        'keywords' => $keyword,
                    ]
                );
            }
            if (is_array($keyword)) {
                foreach ($keyword as $dt) {
                    if (!is_string($dt)) {
                        $invalid[] = 'keyword';
                        break;
                    }
                }
            } else {
                $invalid[] = 'keyword';
            }
        }

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
            $vars = [implode(', ', $invalid), 'keywords', 'wordsapi', 'getitemcounts'];
            throw new BadParameterException($vars, $msg);
        }

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
        $orderby = [];
        $bindvars = [];

        $select['module_id'] = 'idx.module_id';
        $select['itemtype'] = 'idx.itemtype';
        $select['itemid'] = 'idx.itemid';
        $select['module'] = 'mods.name';
        $select['numwords'] = 'COUNT(DISTINCT words.keyword) as numwords';

        $from['idx'] = "$idxtable idx";
        $from['mods'] = "$modstable mods";
        $from['words'] = "$wordstable words";

        $where[] = 'mods.regid = idx.module_id';
        $where[] = 'idx.id = words.id';


        if (!empty($id)) {
            $where[] = 'words.id = ?';
            $bindvars[] = $id;
        }

        if (!empty($id)) {
            $where[] = 'words.id = ?';
            $bindvars[] = $id;
        }

        if (!empty($keyword)) {
            if (count($keyword) == 1) {
                $where[] = 'words.keyword = ?';
                $bindvars[] = $keyword[0];
            } else {
                $where[] = 'words.keyword IN (' . implode(',', array_fill(0, count($keyword), '?')) . ')';
                $bindvars = array_merge($bindvars, $keyword);
            }
        }

        if (!empty($module_id)) {
            $where[] = 'idx.module_id = ?';
            $bindvars[] = (int) $module_id;
        }

        if (isset($itemtype)) {
            $where[] = 'idx.itemtype = ?';
            $bindvars[] = $itemtype;
        }

        if (isset($itemid)) {
            $where[] = 'idx.itemid = ?';
            $bindvars[] = $itemid;
        }

        if (!empty($skip_restricted)) {
            $where[] = 'idx.itemid != 0';
        }

        $groupby[] = 'idx.module_id';
        $groupby[] = 'idx.itemtype';
        $groupby[] = 'idx.itemid';

        $orderby[] = 'mods.name';
        $orderby[] = 'idx.itemtype';
        $orderby[] = 'idx.itemid';

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

            if (!empty($index_key) && isset($item[$index_key])) {
                $items[$item[$index_key]] = $item;
            } else {
                $items[] = $item;
            }
        }
        $result->close();

        return $items;
    }
}
