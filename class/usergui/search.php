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


use Xaraya\Modules\Keywords\UserGui;
use Xaraya\Modules\Keywords\UserApi;
use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarVar;
use xarMod;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * keywords user search function
 * @extends MethodClass<UserGui>
 */
class SearchMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Search for keywords
     * @return array|string|void retrieved keywords
     * @see UserGui::search()
     */
    public function __invoke(array $args = [])
    {
        /** @var UserApi $userapi */
        $userapi = $this->userapi();
        if (!$this->sec()->checkAccess('ReadKeywords', 0)) {
            return '';
        }

        if (!$this->var()->check('search', $data['search'])) {
            return;
        }
        if (!$this->var()->check('bool', $bool)) {
            return;
        }
        if (!$this->var()->check('sort', $sort)) {
            return;
        }

        $data['keys'] = [];
        if ($data['search'] == '') {
            return $data;
        }

        $data['keys'] = $userapi->search(['search' => $data['search']]);

        return $data;
    }
}
