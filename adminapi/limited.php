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
use sys;
use BadParameterException;

sys::import('modules.keywords.method');

/**
 * keywords adminapi limited function
 * @extends MethodClass<AdminApi>
 */
class LimitedMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Unknown
     * Remark: gestire errore su inserted
     * @todo MichelV <1> Keep this file?
     * @see AdminApi::limited()
     */
    public function __invoke(array $args = [])
    {
        extract($args);
        /** @var AdminApi $adminapi */
        $adminapi = $this->adminapi();
        if (!$this->sec()->checkAccess('AdminKeywords')) {
            return;
        }
        $invalid = [];
        if (!isset($moduleid) || !is_numeric($moduleid)) {
            $invalid[] = 'moduleid';
        }
        if (!isset($keyword) || !is_string($keyword)) {
            $invalid[] = 'keyword';
        }
        if (!isset($itemtype) || !is_numeric($itemtype)) {
            $invalid[] = 'itemtype';
        }
        if (count($invalid) > 0) {
            $msg = $this->ml(
                'Invalid #(1) for #(2) function #(3)() in module #(4)',
                join(', ', $invalid),
                'admin',
                'update limited',
                'Keywords'
            );
            throw new BadParameterException(null, $msg);
        }

        $key = $adminapi->separatekeywords(
            ['keywords' => $keyword]
        );

        foreach ($key as $keyres) {
            $keyres = trim($keyres);

            $dbconn = $this->db()->getConn();
            $xartable = & $this->db()->getTables();
            $keywordstable = $xartable['keywords_restr'];
            $nextId = $dbconn->GenId($keywordstable);
            $query = "INSERT INTO $keywordstable (
                  id,
                  keyword,
                  module_id,
                  itemtype)
                  VALUES (
                  ?,
                  ?,
                  ?,
                  ?)";
            $result = & $dbconn->Execute($query, [$nextId, $keyres, $moduleid, $itemtype]);
            if (!$result) {
                return;
            }
        }
        return;
    }
}
