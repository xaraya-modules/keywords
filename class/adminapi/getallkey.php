<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords\AdminApi;

use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarDB;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * keywords adminapi getallkey function
 */
class GetallkeyMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * get entries for a module item
     * @param mixed $args ['modid'] module id
     * @return array|void of keywords
     */
    public function __invoke(array $args = [])
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
}
