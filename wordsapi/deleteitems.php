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
use Xaraya\Modules\Keywords\AdminApi;
use BadParameterException;
use SQLException;

/**
 * keywords wordsapi deleteitems function
 * @extends MethodClass<WordsApi>
 */
class DeleteitemsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Summary of __invoke
     * @param array<mixed> $args
     * @throws \BadParameterException
     * @return bool
     * @see WordsApi::deleteitems()
     */
    public function __invoke(array $args = [])
    {
        extract($args);
        /** @var AdminApi $adminapi */
        $adminapi = $this->adminapi();

        if (isset($index_id)) {
            // deleting some words by index_id
            if (empty($index_id) || !is_numeric($index_id)) {
                $invalid[] = 'index_id';
            }
        } elseif (isset($module) || isset($module_id)) {
            // deleting some words by module_id (+ itemtype) (+ itemid)
            if (!empty($module)) {
                $module_id = $this->mod()->getRegID($module);
            }
            if (empty($module_id) || !is_numeric($module_id)) {
                $invalid[] = 'module_id';
            }
            if (isset($itemtype) && !is_numeric($itemtype)) {
                $invalid[] = 'itemtype';
            }
            if (isset($itemid) && !is_numeric($itemid)) {
                $invalid[] = 'itemid';
            }
        } elseif (!empty($keyword)) {
            // deleting some words
        } else {
            // trying to delete everything!
            $invalid[] = 'arguments';
        }

        // we may have been given a list of words to delete
        if (isset($keyword)) {
            if (is_string($keyword)) {
                $keyword = $adminapi->separatekeywords(
                    [
                        'keywords' => $keyword,
                    ]
                );
            }
            if (is_array($keyword)) {
                foreach ($keyword as $dt) {
                    if (!is_string($dt)) {
                        $invalid[] = 'keyword';
                        break;
                    }
                }
                $keyword = array_values(array_unique(array_filter($keyword)));
            } else {
                $invalid[] = 'keyword';
            }
        }

        if (!empty($invalid)) {
            $msg = 'Invalid #(1) for #(2) module #(3) function #(4)()';
            $vars = [implode(', ', $invalid), 'keywords', 'wordsapi', 'deleteitems'];
            throw new BadParameterException($vars, $msg);
        }

        $dbconn = $this->db()->getConn();
        $tables = & $this->db()->getTables();
        $wordstable = $tables['keywords'];
        $idxtable = $tables['keywords_index'];

        $where = [];
        $bindvars = [];

        if (!empty($index_id)) {
            // deleting some words by index_id
            $delete = "DELETE FROM $wordstable";
            $where[] = "$wordstable.index_id = ?";
            $bindvars[] = $index_id;
        } elseif (!empty($module_id)) {
            // deleting some words by module_id (+ itemtype) (+ itemid)
            $delete = "DELETE $wordstable FROM $wordstable, $idxtable";
            $where[] = "$wordstable.index_id = $idxtable.id";
            $where[] = "$idxtable.module_id = ?";
            $bindvars[] = $module_id;
            if (isset($itemtype)) {
                $where[] = "$idxtable.itemtype = ?";
                $bindvars[] = $itemtype;
            }
            if (isset($itemid)) {
                $where[] = "$idxtable.itemid = ?";
                $bindvars[] = $itemid;
            }
        } else {
            // deleting some words
            $delete = "DELETE FROM $wordstable";
        }
        if (!empty($keyword)) {
            if (count($keyword) == 1) {
                $where[] = "$wordstable.keyword = ?";
                $bindvars[] = $keyword[0];
            } else {
                $where[] = "$wordstable.keyword IN (" . implode(',', array_fill(0, count($keyword), '?')) . ')';
                $bindvars = array_merge($bindvars, $keyword);
            }
        }

        try {
            $dbconn->begin();
            $delete .= " WHERE " . join(" AND ", $where);
            $stmt = $dbconn->prepareStatement($delete);
            $result = $stmt->executeUpdate($bindvars);
            $dbconn->commit();
        } catch (SQLException $e) {
            $dbconn->rollback();
            throw $e;
        }

        return true;
    }
}
