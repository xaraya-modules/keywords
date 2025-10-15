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
use sys;

sys::import('modules.keywords.method');

/**
 * keywords wordsapi countitems function
 * @extends MethodClass<WordsApi>
 */
class CountitemsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Summary of __invoke
     * @param array<mixed> $args
     * @throws \BadParameterException
     * @see WordsApi::countitems()
     */
    public function __invoke(array $args = [])
    {
        extract($args);
        /** @var AdminApi $adminapi */
        $adminapi = $this->adminapi();

        if (isset($id) && (empty($id) || !is_numeric($id))) {
            $invalid[] = 'id';
        }

        if (isset($index_id) && (empty($index_id) || !is_numeric($index_id))) {
            $invalid[] = 'index_id';
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
            $vars = [implode(', ', $invalid), 'keywords', 'wordsapi', 'countitems'];
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

        $select['count'] = "COUNT(DISTINCT words.index_id) as count";

        $from['words'] = "$wordstable words";

        if (!empty($id)) {
            $where[] = 'words.id = ?';
            $bindvars[] = $id;
        }

        if (!empty($index_id)) {
            $where[] = 'words.index_id = ?';
            $bindvars[] = $index_id;
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
            $where[] = 'words.id = idx.keyword_id';
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
