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

class Version
{
    /**
     * Get module version information
     *
     * @return array<string, mixed>
     */
    public function __invoke(): array
    {
        return [
            'name' => 'Keywords',
            'id' => '187',
            'version' => '2.0.0',
            'displayname' => 'Keywords',
            'description' => 'Assign keywords to module items (taxonomy) and look up items by keyword',
            'official' => 1,
            'author' => 'mikespub,alberto cazzaniga <janez>, Kams',
            'contact' => 'http://www.xaraya.com/',
            'admin' => 1,
            'user' => 1,
            'class' => 'Utility',
            'category' => 'Miscellaneous',
            'namespace' => 'Xaraya\\Modules\\Keywords',
            'twigtemplates' => true,
            'dependencyinfo'
             => [
                 0
                  => [
                      'name' => 'Xaraya Core',
                      'version_ge' => '2.4.1',
                  ],
             ],
        ];
    }
}
