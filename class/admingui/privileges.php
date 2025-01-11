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
use xarVar;
use xarMod;
use xarPrivileges;
use xarController;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * keywords admin privileges function
 * @extends MethodClass<AdminGui>
 */
class PrivilegesMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Manage definition of instances for privileges (unfinished)
     * @param array $args all privilege parts
     * @return array with the new privileges
     */
    public function __invoke(array $args = [])
    {
        // Security Check
        if (!xarSecurity::check('AdminKeywords')) {
            return;
        }

        extract($args);

        if (!xarVar::fetch('moduleid', 'id', $moduleid, null, xarVar::DONT_SET)) {
            return;
        }
        if (!xarVar::fetch('itemtype', 'int:1:', $itemtype, null, xarVar::DONT_SET)) {
            return;
        }
        if (!xarVar::fetch('itemid', 'id', $itemid, null, xarVar::DONT_SET)) {
            return;
        }
        if (!xarVar::fetch('apply', 'isset', $apply, null, xarVar::DONT_SET)) {
            return;
        }
        if (!xarVar::fetch('extpid', 'isset', $extpid, null, xarVar::DONT_SET)) {
            return;
        }
        if (!xarVar::fetch('extname', 'isset', $extname, null, xarVar::DONT_SET)) {
            return;
        }
        if (!xarVar::fetch('extrealm', 'isset', $extrealm, null, xarVar::DONT_SET)) {
            return;
        }
        if (!xarVar::fetch('extmodule', 'isset', $extmodule, null, xarVar::DONT_SET)) {
            return;
        }
        if (!xarVar::fetch('extcomponent', 'isset', $extcomponent, null, xarVar::DONT_SET)) {
            return;
        }
        if (!xarVar::fetch('extinstance', 'isset', $extinstance, null, xarVar::DONT_SET)) {
            return;
        }
        if (!xarVar::fetch('extlevel', 'isset', $extlevel, null, xarVar::DONT_SET)) {
            return;
        }
        if (!xarVar::fetch('pparentid', 'isset', $pparentid, null, xarVar::DONT_SET)) {
            return;
        }

        if (!empty($extinstance)) {
            $parts = explode(':', $extinstance);
            if (count($parts) > 0 && !empty($parts[0])) {
                $moduleid = $parts[0];
            }
            if (count($parts) > 1 && !empty($parts[1])) {
                $itemtype = $parts[1];
            }
            if (count($parts) > 2 && !empty($parts[2])) {
                $itemid = $parts[2];
            }
        }

        if (empty($moduleid) || $moduleid == 'All' || !is_numeric($moduleid)) {
            $moduleid = 0;
        }
        if (empty($itemtype) || $itemtype == 'All' || !is_numeric($itemtype)) {
            $itemtype = 0;
        }
        if (empty($itemid) || $itemid == 'All' || !is_numeric($itemid)) {
            $itemid = 0;
        }

        // get the list of modules (and their itemtypes) keywords is currently hooked to
        $subjects = xarMod::apiFunc('keywords', 'hooks', 'getsubjects');

        $modlist = [];
        $typelist = [];
        foreach ($subjects as $modname => $modinfo) {
            $modlist[$modinfo['regid']] = ['id' => $modinfo['regid'], 'name' => $modinfo['displayname']];
            if ($moduleid == $modinfo['regid'] && !empty($modinfo['itemtypes'])) {
                foreach ($modinfo['itemtypes'] as $typeid => $typeinfo) {
                    $typelist[$typeid] = ['id' => $typeid, 'name' => $typeid . ' - ' . $typeinfo['label']];
                }
            }
        }

        // define the new instance
        $newinstance = [];
        $newinstance[] = empty($moduleid) ? 'All' : $moduleid;
        $newinstance[] = empty($itemtype) ? 'All' : $itemtype;
        $newinstance[] = empty($itemid) ? 'All' : $itemid;

        if (!empty($apply)) {
            // create/update the privilege
            $pid = xarPrivileges::external(
                $extpid,
                $extname,
                $extrealm,
                $extmodule,
                $extcomponent,
                $newinstance,
                $extlevel,
                $pparentid
            );
            if (empty($pid)) {
                return; // throw back
            }

            // redirect to the privilege
            xarController::redirect(xarController::URL(
                'privileges',
                'admin',
                'modifyprivilege',
                ['id' => $pid]
            ), null, $this->getContext());
            return true;
        }

        /*
            if (!empty($moduleid)) {
                $numitems = xarMod::apiFunc('categories','user','countitems',
                                          array('modid' => $moduleid,
                                                'cids'  => (empty($cid) ? null : array($cid))
                                               ));
            } else {
                $numitems = xarML('probably');
            }
        */
        $numitems = xarML('probably');

        $extlevels = [
            0 => ['id' => 0, 'name' => 'No Access'],
            200 => ['id' => 200, 'name' => 'Read Access'],
            300 => ['id' => 300, 'name' => 'Add Access'],
            700 => ['id' => 700, 'name' => 'Manage Access'],
            800 => ['id' => 800, 'name' => 'Admin Access'],
        ];

        $data = [
            'moduleid'     => $moduleid,
            'itemtype'     => $itemtype,
            'itemid'       => $itemid,
            'modlist'      => $modlist,
            'typelist'     => $typelist,
            'numitems'     => $numitems,
            'extpid'       => $extpid,
            'extname'      => $extname,
            'extrealm'     => $extrealm,
            'extmodule'    => $extmodule,
            'extcomponent' => $extcomponent,
            'extlevel'     => $extlevel,
            'extlevels'    => $extlevels,
            'pparentid'    => $pparentid,
            'extinstance'  => xarVar::prepForDisplay(join(':', $newinstance)),
        ];

        $data['refreshlabel'] = xarML('Refresh');
        $data['applylabel'] = xarML('Finish and Apply to Privilege');

        return $data;



        // Get the list of all modules currently hooked to categories
        $hookedmodlist = xarMod::apiFunc(
            'modules',
            'admin',
            'gethookedmodules',
            ['hookModName' => 'keywords']
        );
        if (!isset($hookedmodlist)) {
            $hookedmodlist = [];
        }
        $modlist = [];
        foreach ($hookedmodlist as $modname => $val) {
            if (empty($modname)) {
                continue;
            }
            $modid = xarMod::getRegId($modname);
            if (empty($modid)) {
                continue;
            }
            $modinfo = xarMod::getInfo($modid);
            $modlist[$modid] = $modinfo['displayname'];
        }



        // define the new instance
        $newinstance = [];
        $newinstance[] = empty($moduleid) ? 'All' : $moduleid;
        $newinstance[] = empty($itemtype) ? 'All' : $itemtype;
        $newinstance[] = empty($itemid) ? 'All' : $itemid;

        if (!empty($apply)) {
            // create/update the privilege
            $pid = xarPrivileges::external($extpid, $extname, $extrealm, $extmodule, $extcomponent, $newinstance, $extlevel);
            if (empty($pid)) {
                return; // throw back
            }

            // redirect to the privilege
            xarController::redirect(xarController::URL(
                'privileges',
                'admin',
                'modifyprivilege',
                ['pid' => $pid]
            ), null, $this->getContext());
            return true;
        }

        /*
            if (!empty($moduleid)) {
                $numitems = xarMod::apiFunc('categories','user','countitems',
                                          array('modid' => $moduleid,
                                                'cids'  => (empty($cid) ? null : array($cid))
                                               ));
            } else {
                $numitems = xarML('probably');
            }
        */
        $numitems = xarML('probably');

        $data = [
            'moduleid'     => $moduleid,
            'itemtype'     => $itemtype,
            'itemid'       => $itemid,
            'modlist'      => $modlist,
            'numitems'     => $numitems,
            'extpid'       => $extpid,
            'extname'      => $extname,
            'extrealm'     => $extrealm,
            'extmodule'    => $extmodule,
            'extcomponent' => $extcomponent,
            'extlevel'     => $extlevel,
            'extinstance'  => xarVar::prepForDisplay(join(':', $newinstance)),
        ];

        $data['refreshlabel'] = xarML('Refresh');
        $data['applylabel'] = xarML('Finish and Apply to Privilege');

        return $data;
    }
}
