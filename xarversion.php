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

$modversion['name']           = 'Keywords';
$modversion['id']             = '187';
$modversion['version']        = '2.0.0';
$modversion['displayname']    = 'Keywords';
$modversion['description']    = 'Assign keywords to module items (taxonomy) and look up items by keyword';
$modversion['official']       = 1;
$modversion['author']         = 'mikespub,alberto cazzaniga <janez>, Kams';
$modversion['contact']        = 'http://www.xaraya.com/';
$modversion['admin']          = 1;
$modversion['user']           = 1;
$modversion['class']          = 'Utility';
$modversion['category']       = 'Miscellaneous';
$modversion['namespace']      = 'Xaraya\Modules\Keywords';
$modversion['twigtemplates']  = true;
$modversion['dependencyinfo'] = [
    0 => [
        'name' => 'Xaraya Core',
        'version_ge' => '2.4.1',
    ],
];

if (false) {
    xarMLS::translate('Keywords');
    xarMLS::translate('Assign keywords to module items (taxonomy) and look up items by keyword');
}
