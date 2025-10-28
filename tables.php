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

namespace Xaraya\Modules\Keywords;

class Tables
{
    /**
     * Return keywords table names to xaraya
     *
     * This function is called internally by the core whenever the module is
     * loaded.  It is loaded by xar::mod()->loadDbInfo().
     *
     * @access private
     * @return array
     */
    public function __invoke(string $prefix = 'xar')
    {
        // Initialise table array
        $xarTables = [];
        $xarTables['keywords'] = $prefix . '_keywords';
        $xarTables['keywords_restr'] = $prefix . '_keywords_restr';
        $xarTables['keywords_index'] = $prefix . '_keywords_index';

        // Return the table information
        return $xarTables;
    }
}
