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
     * @param int $args ['modid'] module id
     * @param int $args ['itemtype'] item type
     * @param int $args ['objectids'] item id
     * @return array|void of keywords
     */
    public function __invoke(array $args = [])
    {
        if (!xarSecurity::check('ReadKeywords')) {
            return;
        }

        extract($args);

        if (!isset($modid) || !is_numeric($modid)) {
            $msg = xarML('Invalid Parameters');
            throw new BadParameterException(null, $msg);
        }
        if (!is_array($objectids)) {
            $msg = xarML('Invalid Parameters');
            throw new BadParameterException(null, $msg);
        }
        $keywords = [];
        $dbconn = xarDB::getConn();
        $xartable = & xarDB::getTables();
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
