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
use Query;
use sys;
use Exception;

sys::import('modules.keywords.class.method');

/**
 * keywords userapi getwords function
 * @extends MethodClass<UserApi>
 */
class GetwordsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * get entries for a module item
     * @param array<mixed> $args
     * @var int $modid module id
     * @var int $itemtype item type
     * @var int $itemid item id
     * @var int $numitems number of entries to retrieve (optional)
     * @var int $startnum starting number (optional)
     * @return array|void of keywords
     * @todo This is so similar to getitems, that they could be merged. It is only the format of the results that differs.
     * @see UserApi::getwords()
     */
    public function __invoke(array $args = [])
    {
        if (!$this->sec()->checkAccess('ReadKeywords')) {
            return;
        }

        extract($args);

        if (!isset($modid) || !is_numeric($modid)) {
            $msg = $this->ml('Invalid #(1)', 'module id');
            throw new Exception($msg);
        }
        if (!isset($itemid) || !is_numeric($itemid)) {
            $msg = $this->ml('Invalid #(1)', 'item id');
            throw new Exception($msg);
        }

        $table = & $this->db()->getTables();
        $q = new Query('SELECT');
        $q->addtable($table['keywords'], 'k');
        $q->addtable($table['keywords_index'], 'i');
        $q->join('i.keyword_id', 'k.id');
        $q->addfield('i.id AS id');
        $q->addfield('k.keyword AS keyword');
        $q->eq('i.module_id', $modid);
        $q->eq('i.itemid', $itemid);
        if (!empty($itemtype)) {
            if (is_array($itemtype)) {
                $q->in('i.itemtype', $itemtype);
            } else {
                $q->eq('i.itemtype', (int) $itemtype);
            }
        }
        $q->addorder('keyword', 'ASC');
        //    $q->qecho();
        $q->run();
        $words = $q->output();

        return $words;
    }
}
