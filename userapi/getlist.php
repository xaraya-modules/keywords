<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.6.2
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords\UserApi;

use Xaraya\Modules\Keywords\UserApi;
use Xaraya\Modules\Keywords\MethodClass;
use xarSecurity;
use xarDB;
use sys;
use BadParameterException;

sys::import('modules.keywords.method');

/**
 * keywords userapi getlist function
 * @extends MethodClass<UserApi>
 */
class GetlistMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * get list of keywords (from the existing assignments for now)
     * @param array<mixed> $args
     * @var mixed $count if you want to count items per keyword
     * @var mixed $tab = int(1:5) returns keywords with initial withn
     * a specific letter range (1=[A-F]; 2=[G-L]; etc...)
     * @return array|void of found keywords
     * @see UserApi::getlist()
     */
    public function __invoke(array $args = [])
    {
        if (!$this->sec()->checkAccess('ReadKeywords')) {
            return;
        }

        extract($args);

        $dbconn = $this->db()->getConn();
        $xartable = & $this->db()->getTables();
        $keywordstable = $xartable['keywords'];

        if (!isset($tab)) {
            $tab = '0';
        }

        if ($tab == '0') {
            $where = null;
        } elseif ($tab == '1') {
            $where = " WHERE ("
            . "'A' <= " . $dbconn->substr . "(" . $dbconn->upperCase . "(keyword),1,1) AND "
            . $dbconn->substr . "(" . $dbconn->upperCase . "(keyword),1,1) <= 'F')";
        } elseif ($tab == '2') {
            $where = " WHERE ("
            . "'G' <= " . $dbconn->substr . "(" . $dbconn->upperCase . "(keyword),1,1) AND "
            . $dbconn->substr . "(" . $dbconn->upperCase . "(keyword),1,1) <= 'L')";
        } elseif ($tab == '3') {
            $where = " WHERE ("
            . "'M' <= " . $dbconn->substr . "(" . $dbconn->upperCase . "(keyword),1,1) AND "
            . $dbconn->substr . "(" . $dbconn->upperCase . "(keyword),1,1) <= 'R')";
        } elseif ($tab == '4') {
            $where = " WHERE ("
            . "'S' <= " . $dbconn->substr . "(" . $dbconn->upperCase . "(keyword),1,1) AND "
            . $dbconn->substr . "(" . $dbconn->upperCase . "(keyword),1,1) <= 'Z')";
        } elseif ($tab == '5') {
            $where = " WHERE ("
            . $dbconn->substr . "(" . $dbconn->upperCase . "(keyword),1,1) < 'A' OR "
            . $dbconn->substr . "(" . $dbconn->upperCase . "(keyword),1,1) > 'Z')";
        }


        // Get count per keyword from the database
        if (!empty($args['count'])) {
            $query = "SELECT keyword, COUNT(id)
                      FROM $keywordstable $where
                      GROUP BY keyword
                      ORDER BY keyword ASC";
            $result = & $dbconn->Execute($query);
            if (!$result) {
                return;
            }

            $items = [];
            if ($result->EOF) {
                $result->Close();
                return $items;
            }
            while ($result->next()) {
                [$word, $count] = $result->fields;
                $items[$word] = $count;
            }
            $result->Close();
            return $items;
        }

        // Get distinct keywords from the database
        $query = "SELECT DISTINCT keyword
                  FROM $keywordstable  $where
                  ORDER BY keyword ASC";
        $result = & $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        $items = [];
        $items[''] = '';
        if ($result->EOF) {
            $result->Close();
            return $items;
        }
        while ($result->next()) {
            [$word] = $result->fields;
            $items[$word] = $word;
        }
        $result->Close();
        return $items;
    }
}
