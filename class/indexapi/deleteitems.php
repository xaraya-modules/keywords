<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.6.2
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords\IndexApi;

use Xaraya\Modules\Keywords\MethodClass;
use Xaraya\Modules\Keywords\IndexApi;
use BadParameterException;
use SQLException;
use xarDB;
use xarMod;
use sys;

sys::import('modules.keywords.class.method');

/**
 * keywords indexapi deleteitems function
 * @extends MethodClass<IndexApi>
 */
class DeleteitemsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Summary of __invoke
     * @param array<mixed> $args
     * @throws \BadParameterException
     * @return bool
     * @see IndexApi::deleteitems()
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (isset($id)) {
            // deleting item by id
            if (empty($id) || !is_numeric($id)) {
                $invalid[] = 'id';
            }
        } elseif (isset($module) || isset($module_id)) {
            // deleting some items by module_id (+ itemtype) (+ itemid)
            if (!empty($module)) {
                $module_id = $this->mod()->getRegID($module);
            }
            if (empty($module_id) || !is_numeric($module_id)) {
                $invalid[] = 'module_id';
            }
            if (isset($itemtype) && !is_numeric($itemtype)) {
                $invalid[] = 'itemtype';
            }
            if (isset($itemid) && !is_numeric($itemid)) {
                $invalid[] = 'itemid';
            }
        } else {
            // trying to delete everything!
            $invalid[] = 'arguments';
        }

        if (!empty($invalid)) {
            $msg = 'Invalid #(1) for #(2) module #(3) function #(4)()';
            $vars = [implode(', ', $invalid), 'keywords', 'indexapi', 'getid'];
            throw new BadParameterException($vars, $msg);
        }

        $dbconn = $this->db()->getConn();
        $tables = & $this->db()->getTables();
        $idxtable = $tables['keywords_index'];

        $where = [];
        $bindvars = [];
        if (!empty($id)) {
            $where[] = 'id = ?';
            $bindvars[] = $id;
        } else {
            $where[] = 'module_id = ?';
            $bindvars[] = $module_id;
            if (isset($itemtype)) {
                $where[] = 'itemtype = ?';
                $bindvars[] = $itemtype;
            }
            if (isset($itemid)) {
                $where[] = 'itemid = ?';
                $bindvars[] = $itemid;
            }
        }

        $delete = "DELETE FROM $idxtable";
        $delete .= " WHERE " . join(" AND ", $where);
        try {
            $dbconn->begin();
            $stmt = $dbconn->prepareStatement($delete);
            $result = $stmt->executeUpdate($bindvars);
            $dbconn->commit();
        } catch (SQLException $e) {
            $dbconn->rollback();
            throw $e;
        }
        return true;
    }
}
