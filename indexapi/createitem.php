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
use sys;

sys::import('modules.keywords.method');

/**
 * keywords indexapi createitem function
 * @extends MethodClass<IndexApi>
 */
class CreateitemMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Summary of __invoke
     * @param array<mixed> $args
     * @throws \BadParameterException
     * @see IndexApi::createitem()
     */
    public function __invoke(array $args = [])
    {
        extract($args);
        /** @var IndexApi $indexapi */
        $indexapi = $this->indexapi();

        if (!empty($module)) {
            $module_id = $this->mod()->getID($module);
        }
        if (empty($module_id) || !is_numeric($module_id)) {
            $invalid[] = 'module_id';
        }

        if (empty($itemtype)) {
            $itemtype = 0;
        }
        if (!is_numeric($itemtype)) {
            $invalid[] = 'itemtype';
        }

        if (empty($itemid)) {
            $itemid = 0;
        }
        if (!is_numeric($itemid)) {
            $invalid[] = 'itemid';
        }

        if (!empty($invalid)) {
            $msg = 'Invalid #(1) for #(2) module #(3) function #(4)()';
            $vars = [implode(', ', $invalid), 'keywords', 'indexapi', 'createitem'];
            throw new BadParameterException($vars, $msg);
        }

        if ($item = $indexapi->getitem(
            [
                'module_id' => $module_id,
                'itemtype' => $itemtype,
                'itemid' => $itemid,
            ]
        )) {
            return $item;
        }

        $dbconn = $this->db()->getConn();
        $tables = & $this->db()->getTables();
        $idxtable = $tables['keywords_index'];

        // Insert item
        try {
            $dbconn->begin();
            $nextId = $dbconn->GenId($idxtable);
            $query = "INSERT INTO $idxtable
                      (id, module_id, itemtype, itemid)
                      VALUES (?,?,?,?)";
            $bindvars = [$nextId, $module_id, $itemtype, $itemid];
            $stmt = $dbconn->prepareStatement($query);
            $result = $stmt->executeUpdate($bindvars);
            $id = $dbconn->getLastId($idxtable);
            $dbconn->commit();
        } catch (SQLException $e) {
            $dbconn->rollback();
            throw $e;
        }

        if (empty($id)) {
            return;
        }
        // return item to caller (saves a further getitem call)
        $item = [
            'id' => $id,
            'module_id' => $module_id,
            'itemtype' => $itemtype,
            'itemid' => $itemid,
        ];

        return $item;
    }
}
