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


use Xaraya\Modules\Keywords\AdminApi;
use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarDB;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * keywords adminapi resetlimited function
 * @extends MethodClass<AdminApi>
 */
class ResetlimitedMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     *
     * @todo MichelV what is this?
     * @todo ? inserire controllo sicurezza
     * @see AdminApi::resetlimited()
     */
    public function __invoke(array $args = [])
    {
        //if (!$this->sec()->checkAccess('AdminKeywords')) return;
        if (!$this->sec()->checkAccess('AddKeywords')) {
            return;
        }
        $dbconn = $this->db()->getConn();
        $xartable = & $this->db()->getTables();
        $keywordstable = $xartable['keywords_restr'];
        $query = "DELETE FROM $keywordstable";
        $result = & $dbconn->Execute($query);
        if (!$result) {
            return;
        }
        return true;
    }
}
