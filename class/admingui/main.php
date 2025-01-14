<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords\AdminGui;


use Xaraya\Modules\Keywords\AdminGui;
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
 * keywords admin main function
 * @extends MethodClass<AdminGui>
 */
class MainMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the main administration function
     * Redirects to modifyconfig
     * @author mikespub
     * @access public
     * @return bool|void true on success or void on falure
     */
    public function __invoke(array $args = [])
    {
        // Security Check
        if (!$this->checkAccess('EditKeywords')) {
            return;
        }

        if (xarModVars::get('modules', 'disableoverview') == 0) {
            return [];
        } else {
            $redirect = $this->getModVar('defaultbackpage');
            if (!empty($redirect)) {
                $truecurrenturl = xarServer::getCurrentURL([], false);
                $urldata = xarMod::apiFunc('roles', 'user', 'parseuserhome', ['url' => $redirect,'truecurrenturl' => $truecurrenturl]);
                $this->redirect($urldata['redirecturl']);
            } else {
                $this->redirect($this->getUrl('admin', 'view'));
            }
        }
        return true;
    }
}
