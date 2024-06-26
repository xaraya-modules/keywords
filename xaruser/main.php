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

/**
 * display keywords entries
 * @return mixed bool and redirect to url
 */
function keywords_user_main(array $args = [], $context = null)
{
    // Xaraya security
    if (!xarSecurity::check('ReadKeywords')) {
        return;
    }

    $redirect = xarModVars::get('keywords', 'frontend_page');
    if (!empty($redirect)) {
        $truecurrenturl = xarServer::getCurrentURL([], false);
        $urldata = xarMod::apiFunc('roles', 'user', 'parseuserhome', ['url' => $redirect,'truecurrenturl' => $truecurrenturl]);
        xarController::redirect($urldata['redirecturl'], null, $context);
    } else {
        xarController::redirect(xarController::URL('keywords', 'user', 'view', $args), null, $context);
    }
    return true;
}
