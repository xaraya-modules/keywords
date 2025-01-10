<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords\UserGui;

use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarModVars;
use xarServer;
use xarMod;
use xarController;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * keywords user main function
 */
class MainMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * display keywords entries
     * @return mixed bool and redirect to url
     */
    public function __invoke(array $args = [])
    {
        // Xaraya security
        if (!xarSecurity::check('ReadKeywords')) {
            return;
        }

        $redirect = xarModVars::get('keywords', 'frontend_page');
        if (!empty($redirect)) {
            $truecurrenturl = xarServer::getCurrentURL([], false);
            $urldata = xarMod::apiFunc('roles', 'user', 'parseuserhome', ['url' => $redirect,'truecurrenturl' => $truecurrenturl]);
            xarController::redirect($urldata['redirecturl'], null, $this->getContext());
        } else {
            xarController::redirect(xarController::URL('keywords', 'user', 'view', $args), null, $this->getContext());
        }
        return true;
    }
}
