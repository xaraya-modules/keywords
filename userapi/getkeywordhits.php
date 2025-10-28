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
use Query;
use sys;

sys::import('modules.keywords.method');

/**
 * keywords userapi getkeywordhits function
 * @extends MethodClass<UserApi>
 */
class GetkeywordhitsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Keywords Module
     * @package modules
     * @subpackage keywords module
     * @category Third Party Xaraya Module
     * @version 2.0.0
     * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
     * @link http://xaraya.com/index.php/release/187.html
     * @author Marc Lutolf <mfl@netspan.ch>
     * @see UserApi::getkeywordhits()
     */
    public function __invoke(array $args = [])
    {
        if (!isset($args['cloudtype'])) {
            $args['cloudtype'] = 3;
        }

        // Return nothing if we asked for hits and the hitcount module is not available
        if ($args['cloudtype'] == 1 && !$this->mod()->isAvailable('hitcount')) {
            return [];
        }

        sys::import('xaraya.structures.query');

        $dbconn = $this->db()->getConn();
        $xartable = & $this->db()->getTables();

        $q = new Query('SELECT');
        $q->addtable($xartable['keywords_index'], 'i');
        $q->addtable($xartable['keywords'], 'k');
        $q->join('i.keyword_id', 'k.id');
        $q->addfield('k.keyword AS keyword');
        $q->addfield('COUNT(i.id) AS count');

        if ($args['cloudtype'] == 2) {
            $this->mod()->loadDbInfo('hitcount');
            $xartable = & $this->db()->getTables();
            $q->addtable($xartable['hitcount'], 'h');
            $q->join('k.module_id', 'h.module_id');
            $q->join('k.itemtype', 'h.itemtype');
            $q->join('k.itemid', 'h.itemid');
            $q->addfield('SUM(h.hits) AS hits');
        }
        $q->addgroup('k.keyword');
        $q->addorder('k.keyword', 'ASC');
        $q->optimize = false;
        $q->run();
        $result = $q->output();

        // Reorganize to an array where the keywords are keys
        $tags = [];
        if ($args['cloudtype'] == 2) {
            foreach ($result as $tag) {
                $tags[$tag['keyword']] = $tag['hits'];
            }
        } else {
            foreach ($result as $tag) {
                $tags[$tag['keyword']] = $tag['count'];
            }
        }
        return $tags;
    }
}
