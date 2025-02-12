<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.6.2
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords\AdminGui;

use Xaraya\Modules\Keywords\AdminGui;
use Xaraya\Modules\Keywords\MethodClass;
use xarSecurity;
use xarModVars;
use xarServer;
use xarMod;
use xarController;
use sys;
use BadParameterException;

sys::import('modules.keywords.method');

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
     * @return array|bool|void true on success or void on falure
     * @see AdminGui::main()
     */
    public function __invoke(array $args = [])
    {
        // Security Check
        if (!$this->sec()->checkAccess('EditKeywords')) {
            return;
        }

        if (xarModVars::get('modules', 'disableoverview') == 0) {
            return [];
        } else {
            $redirect = $this->mod()->getVar('defaultbackpage');
            if (!empty($redirect)) {
                $truecurrenturl = $this->ctl()->getCurrentURL([], false);
                $urldata = $this->mod()->apiFunc('roles', 'user', 'parseuserhome', ['url' => $redirect,'truecurrenturl' => $truecurrenturl]);
                $this->ctl()->redirect($urldata['redirecturl']);
            } else {
                $this->ctl()->redirect($this->mod()->getURL('admin', 'view'));
            }
        }
        return true;
    }
}
