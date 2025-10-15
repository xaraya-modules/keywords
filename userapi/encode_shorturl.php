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
use sys;

sys::import('modules.keywords.method');

/**
 * keywords userapi encode_shorturl function
 * @extends MethodClass<UserApi>
 */
class EncodeShorturlMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * return the path for a short URL to xarController::URL for this module
     * @author the Example module development team
     * @param mixed $args the function and arguments passed to xarController::URL
     * @return array|string|void path to be added to index.php for a short URL, or empty if failed
     * @see UserApi::encodeShorturl()
     */
    public function __invoke(array $args = [])
    {
        // Get arguments from argument array
        extract($args);
        // Check if we have something to work with
        if (!isset($func)) {
            return;
        }
        $path = [];
        $get = $args;

        $module = 'keywords';
        $path[] = $module;

        if ($func == 'main') {
            unset($get['func']);
            if (!empty($tab)) {
                $path[] = 'tab' . $tab;
                unset($get['tab']);
            } elseif (!empty($keyword)) {
                $path[] = $keyword;
                unset($get['keyword']);
                if (!empty($id)) {
                    $path[] = $id;
                    unset($getp['id']);
                }
            }
        } else {
            // anything else that you haven't defined a short URL equivalent for
            // -> don't create a path here
        }
        return ['path' => $path,'get' => $get];
    }
}
