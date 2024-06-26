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
 * @param $args['modid'] module id
 * @return array|void of keywords
 */
function keywords_adminapi_getallkey(array $args = [], $context = null)
{
    extract($args);

    if (!isset($moduleid) || !is_numeric($moduleid)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)', 'module id', 'user', 'getwordslimited', 'keywords');
        throw new BadParameterException(null, $msg);
    }

    if (!xarSecurity::check('AdminKeywords')) {
        return;
    }
    $dbconn = xarDB::getConn();
    $xartable = & xarDB::getTables();
    $keywordstable = $xartable['keywords_restr'];
    // Get restricted keywords for this module item
    $query = "SELECT id,
                     keyword
              FROM $keywordstable
              WHERE module_id = ?
              OR module_id = '0'
              ORDER BY keyword ASC";
    $result = & $dbconn->Execute($query, [$moduleid]);
    if (!$result) {
        return;
    }

    $keywords = [];

    //$keywords[''] = '';
    if ($result->EOF) {
        $result->Close();
        return $keywords;
    }

    while (!$result->EOF) {
        [$id,
            $word] = $result->fields;
        $keywords[$id] = $word;
        $result->MoveNext();
    }
    $result->Close();
    return $keywords;
}
