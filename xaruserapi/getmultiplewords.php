<?php
/**
 * Keywords Module
 *
 * @package modules
 * @subpackage keywords module
 * @category Third Party Xaraya Module
 * @version 2.0.0
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://xaraya.com/index.php/release/187.html
 * @author mikespub
 */

/**
 * get entries for a module item
 *
 * @param int $args['modid'] module id
 * @param int $args['itemtype'] item type
 * @param int $args['objectids'] item id
 * @return array|void of keywords
 */
function keywords_userapi_getmultiplewords(array $args = [], $context = null)
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
