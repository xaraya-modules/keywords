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
 * the main administration function
 *
 * Redirects to modifyconfig
 *
 * @author mikespub
 * @access public
 * @return bool|void true on success or void on falure
 */
function keywords_admin_main(array $args = [], $context = null)
{
    // Security Check
    if (!xarSecurity::check('EditKeywords')) {
        return;
    }

    if (xarModVars::get('modules', 'disableoverview') == 0) {
        return [];
    } else {
        $redirect = xarModVars::get('keywords', 'defaultbackpage');
        if (!empty($redirect)) {
            $truecurrenturl = xarServer::getCurrentURL([], false);
            $urldata = xarMod::apiFunc('roles', 'user', 'parseuserhome', ['url' => $redirect,'truecurrenturl' => $truecurrenturl]);
            xarController::redirect($urldata['redirecturl'], null, $context);
        } else {
            xarController::redirect(xarController::URL('keywords', 'admin', 'view'), null, $context);
        }
    }
    return true;
}
