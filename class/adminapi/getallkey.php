<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.6.2
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords\AdminApi;

use Xaraya\Modules\Keywords\AdminApi;
use Xaraya\Modules\Keywords\MethodClass;
use xarSecurity;
use xarDB;
use sys;
use BadParameterException;

sys::import('modules.keywords.class.method');

/**
 * keywords adminapi getallkey function
 * @extends MethodClass<AdminApi>
 */
class GetallkeyMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * get entries for a module item
     * @param array<mixed> $args
     * @var mixed $modid module id
     * @return array|void of keywords
     * @see AdminApi::getallkey()
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (!isset($moduleid) || !is_numeric($moduleid)) {
            $msg = $this->ml('Invalid #(1) for #(2) function #(3)() in module #(4)', 'module id', 'user', 'getwordslimited', 'keywords');
            throw new BadParameterException(null, $msg);
        }

        if (!$this->sec()->checkAccess('AdminKeywords')) {
            return;
        }
        $dbconn = $this->db()->getConn();
        $xartable = & $this->db()->getTables();
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
