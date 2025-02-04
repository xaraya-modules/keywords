<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.6.2
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords\WordsApi;

use Xaraya\Modules\Keywords\MethodClass;
use Xaraya\Modules\Keywords\WordsApi;
use BadParameterException;
use SQLException;
use xarDB;
use sys;

sys::import('modules.keywords.class.method');

/**
 * keywords wordsapi createitems function
 * @extends MethodClass<WordsApi>
 */
class CreateitemsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    public function __invoke(array $args = [])
    {
        extract($args);

        if (empty($index_id) || !is_numeric($index_id)) {
            $invalid[] = 'index_id';
        }

        if (isset($keyword)) {
            if (is_string($keyword)) {
                $keyword = (strpos($keyword, ',') !== false) ?
                    array_map('trim', explode(',', $keyword)) : [trim($keyword)];
            }
            if (is_array($keyword)) {
                $keyword = array_unique(array_filter($keyword));
                foreach ($keyword as $dt) {
                    if (!is_string($dt)) {
                        $invalid[] = 'keyword';
                        break;
                    }
                }
            } else {
                $invalid[] = 'keyword';
            }
        } else {
            $invalid[] = 'keyword';
        }

        if (!empty($invalid)) {
            $msg = 'Invalid #(1) for #(2) module #(3) function #(4)()';
            $vars = [implode(', ', $invalid), 'keywords', 'wordsapi', 'createitems'];
            throw new BadParameterException($vars, $msg);
        }

        $dbconn = xarDB::getConn();
        $tables = & xarDB::getTables();
        $wordstable = $tables['keywords'];

        $values = [];
        $bindvars = [];

        foreach ($keyword as $word) {
            $values[] = '(?,?)';
            $bindvars[] = $index_id;
            $bindvars[] = $word;
        }

        // Insert items
        try {
            $dbconn->begin();
            $insert = "INSERT INTO $wordstable (index_id, keyword)";
            $insert .= " VALUES " . implode(',', $values);
            $stmt = $dbconn->prepareStatement($insert);
            $result = $stmt->executeUpdate($bindvars);
            $dbconn->commit();
        } catch (SQLException $e) {
            $dbconn->rollback();
            throw $e;
        }
        return true;
    }
}
