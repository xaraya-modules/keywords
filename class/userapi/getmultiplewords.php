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
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * keywords userapi getmultiplewords function
 * @extends MethodClass<UserApi>
 */
class GetmultiplewordsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * get entries for a module item
     * @param array<mixed> $args
     * @var int $modid module id
     * @var int $itemtype item type
     * @var int $objectids item id
     * @return array|void of keywords
     */
    public function __invoke(array $args = [])
    {
        if (!$this->sec()->checkAccess('ReadKeywords')) {
            return;
        }

        extract($args);

        if (!isset($modid) || !is_numeric($modid)) {
            $msg = $this->ml('Invalid Parameters');
            throw new BadParameterException(null, $msg);
        }
        if (!is_array($objectids)) {
            $msg = $this->ml('Invalid Parameters');
            throw new BadParameterException(null, $msg);
        }
        $keywords = [];
        $dbconn = $this->db()->getConn();
        $xartable = & $this->db()->getTables();
        $keywordstable = $xartable['keywords'];

        foreach ($objectids as $item) {
            $query = "SELECT id,
                             keyword
                      FROM $keywordstable
                      WHERE module_id = ?
                      AND itemid = ?";

            if (isset($itemtype) && is_numeric($itemtype)) {
                $query .= " AND itemtype = $itemtype";
            }
            $bindvars = [$modid, $item];
            $result = & $dbconn->Execute($query, $bindvars);
            if (!$result) {
                return;
            }

            for (; !$result->EOF; $result->MoveNext()) {
                [$id, $keyword] = $result->fields;
                $keywords[$item][] = ['id'      => $id,
                    'keyword' => $keyword, ];
            }
        }
        return $keywords;
    }
}
