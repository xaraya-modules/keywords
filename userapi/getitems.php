<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.6.2
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords\UserApi;

use Xaraya\Modules\Keywords\UserApi;
use Xaraya\Modules\Keywords\MethodClass;
use Exception;

/**
 * keywords userapi getitems function
 * @extends MethodClass<UserApi>
 */
class GetitemsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * get module items for a keyword
     * @param array<mixed> $args
     * @var mixed $id id(s) of the keywords entry(ies), or
     * @var mixed $keyword keyword
     * @var mixed $modid modid
     * @var mixed $itemtype itemtype
     * @var mixed $numitems number of entries to retrieve (optional)
     * @var mixed $startnum starting number (optional)
     * @return array|void of module id, item type and item id
     * @see UserApi::getitems()
     */
    public function __invoke(array $args = [])
    {
        if (!$this->sec()->checkAccess('ReadKeywords')) {
            return;
        }

        extract($args);

        if (!empty($id)) {
            if (!is_numeric($id) && !is_array($id)) {
                $msg = $this->ml('Invalid #(1)', 'keywords id');
                throw new Exception($msg);
            }
        } else {
            if (!isset($keyword)) {
                $msg = $this->ml('Invalid #(1)', 'keyword');
                throw new Exception($msg);
            }
        }

        $dbconn = $this->db()->getConn();
        $xartable = & $this->db()->getTables();
        $keywordstable = $xartable['keywords'];
        $bindvars = [];

        // Get module item for this id
        $query = "SELECT id,
                         itemid,
                         keyword,
                         module_id,
                         itemtype
                  FROM $keywordstable";
        if (!empty($id)) {
            if (is_array($id)) {
                $query .= " WHERE id IN (" . join(', ', $id) . ")";
            } else {
                $query .= " WHERE id = ?";
                $bindvars[] = $id;
            }
        } else {
            $query .= " WHERE keyword = ?";
            $bindvars[] = $keyword;
        }
        if (!empty($itemid) && is_numeric($itemid)) {
            $query .= " AND itemid = ?";
            $bindvars[] = $itemid;
        }
        if (!empty($itemtype)) {
            if (is_array($itemtype)) {
                $query .= ' AND itemtype IN (?' . str_repeat(',?', count($itemtype) - 1) . ')';
                $bindvars = array_merge($bindvars, $itemtype);
            } else {
                $query .= ' AND itemtype = ?';
                $bindvars[] = (int) $itemtype;
            }
        }
        if (!empty($modid) && is_numeric($modid)) {
            $query .= " AND module_id = ?";
            $bindvars[] = $modid;
        }
        $query .= " ORDER BY module_id ASC, itemtype ASC, itemid DESC";

        if (isset($numitems) && is_numeric($numitems)) {
            if (empty($startnum)) {
                $startnum = 1;
            }
            $result = & $dbconn->SelectLimit($query, $numitems, $startnum - 1, $bindvars);
        } else {
            $result = & $dbconn->Execute($query, $bindvars);
        }
        if (!$result) {
            return;
        }

        $items = [];
        if ($result->EOF) {
            $result->Close();
            return $items;
        }
        while ($result->next()) {
            $item = [];
            [$item['id'],
                $item['itemid'],
                $item['keyword'],
                $item['module_id'],
                $item['itemtype']] = $result->fields;
            $items[$item['id']] = $item;
        }
        $result->Close();
        return $items;
    }
}
