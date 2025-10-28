<?php

/**
 * Handle module installer functions
 *
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.6.2
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords;

use Xaraya\Modules\InstallerClass;
use xarMod;
use xarHooks;
use xarPrivileges;
use xarMasks;
use xarTableDDL;
use xarModHooks;
use Query;
use sys;
use SQLException;
use Exception;

sys::import('xaraya.modules.installer');

/**
 * Handle module installer functions
 *
 * @todo add extra use ...; statements above as needed
 * @todo replaced keywords_*() function calls with $this->*() calls
 * @extends InstallerClass<Module>
 */
class Installer extends InstallerClass
{
    /**
     * Configure this module - override this method
     *
     * @todo use this instead of init() etc. for standard installation
     * @return void
     */
    public function configure()
    {
        $this->objects = [
            // add your DD objects here
            //'keywords_object',
        ];
        $this->variables = [
            // add your module variables here
            'hello' => 'world',
        ];
        $this->oldversion = '2.4.1';
    }

    /** xarinit.php functions imported by bermuda_cleanup */

    public function init()
    {
        $module = 'keywords';

        // Create tables inside transaction
        try {
            $q = new Query();
            $prefix = $this->db()->getPrefix();

            # --------------------------------------------------------
            #
            # Table structures
            #
            $query = "DROP TABLE IF EXISTS " . $prefix . "_keywords_index";
            if (!$q->run($query)) {
                return;
            }
            $query = "CREATE TABLE " . $prefix . "_keywords_index (
              id                integer unsigned NOT NULL auto_increment,
              module_id         integer unsigned NOT NULL default 0,
              itemtype          integer unsigned NOT NULL default 0,
              itemid            integer unsigned NOT NULL default 0,
              keyword_id        integer unsigned NOT NULL default 0,
              PRIMARY KEY  (id),
              UNIQUE KEY `i_xar_keywords_index` (`module_id`,`itemtype`,`itemid`,`keyword_id`),
              KEY `keyword_id` (`keyword_id`)
            )";
            if (!$q->run($query)) {
                return;
            }

            $query = "DROP TABLE IF EXISTS " . $prefix . "_keywords";
            if (!$q->run($query)) {
                return;
            }
            $query = "CREATE TABLE " . $prefix . "_keywords (
              id                integer unsigned NOT NULL auto_increment,
              index_id          integer unsigned NOT NULL default 0,
              keyword           varchar(64),
              PRIMARY KEY  (id),
              UNIQUE KEY `keyword` (`keyword`),
              KEY `index_id` (`index_id`)
            )";
            if (!$q->run($query)) {
                return;
            }
        } catch (Exception $e) {
            throw new Exception($this->ml('Could not create module tables'));
        }

        /*********************************************************************
         * Set up Module Vars (common configuration)
         *********************************************************************/

        $module_settings = $this->mod()->apiFunc('base', 'admin', 'getmodulesettings', ['module' => $module]);
        $module_settings->initialize();


        /*********************************************************************
         * Set Module Vars (module configuration)
         *********************************************************************/

        $this->mod()->setVar('delimiters', ',;');
        $this->mod()->setVar('stats_per_page', 100);
        $this->mod()->setVar('items_per_page', 20);
        $this->mod()->setVar('user_layout', 'list');
        $this->mod()->setVar('cols_per_page', 2);
        $this->mod()->setVar('words_per_page', 50);
        $this->mod()->setVar('cloud_font_min', 1);
        $this->mod()->setVar('cloud_font_max', 3);
        $this->mod()->setVar('cloud_font_unit', 'em');
        $this->mod()->setVar('use_module_icons', true);

        /*********************************************************************
         * Create Module DD Objects
         *********************************************************************/

        $objects = ['keywords_keywords'];
        if (!$this->mod()->apiFunc(
            'modules',
            'admin',
            'standardinstall',
            ['module' => $module, 'objects' => $objects]
        )) {
            return;
        }

        /*********************************************************************
         * Register Module Hook Observers
         *********************************************************************/

        xarHooks::registerObserver('ItemNew', $module, 'gui', 'admin', 'newhook');
        xarHooks::registerObserver('ItemCreate', $module, 'api', 'admin', 'createhook');
        xarHooks::registerObserver('ItemDisplay', $module, 'gui', 'user', 'displayhook');
        xarHooks::registerObserver('ItemModify', $module, 'gui', 'admin', 'modifyhook');
        xarHooks::registerObserver('ItemUpdate', $module, 'api', 'admin', 'updatehook');
        xarHooks::registerObserver('ItemDelete', $module, 'api', 'admin', 'deletehook');

        xarHooks::registerObserver('ItemSearch', $module, 'gui', 'user', 'search');

        xarHooks::registerObserver('ModuleModifyconfig', $module, 'gui', 'hooks', 'modulemodifyconfig');
        xarHooks::registerObserver('ModuleUpdateconfig', $module, 'api', 'hooks', 'moduleupdateconfig');
        xarHooks::registerObserver('ModuleRemove', $module, 'api', 'admin', 'removehook');

        /*********************************************************************
         * Define Module Privilege Instances
         *********************************************************************/

        // Defined Instances are: module_id, itemtype and itemid
        $instances = [
            ['header' => 'external', // this keyword indicates an external "wizard"
                'query'  => $this->ctl()->getModuleURL($module, 'admin', 'privileges'),
                'limit'  => 0,
            ],
        ];
        xarPrivileges::defineInstance($module, 'Item', $instances);


        /*********************************************************************
         * Register Module Privilege Masks
         *********************************************************************/

        // TODO: tweak this - allow viewing keywords of "your own items" someday ?
        // MichelV: Why not have an add privilege in here? Admin to add keywords seems way overdone
        xarMasks::register('ReadKeywords', 'All', $module, 'Item', 'All:All:All', 'ACCESS_READ');
        xarMasks::register('EditKeywords', 'All', $module, 'Item', 'All:All:All', 'ACCESS_EDIT');
        xarMasks::register('AddKeywords', 'All', $module, 'Item', 'All:All:All', 'ACCESS_COMMENT');
        xarMasks::register('ManageKeywords', 'All', $module, 'Item', 'All:All:All', 'ACCESS_DELETE');
        xarMasks::register('AdminKeywords', 'All', $module, 'Item', 'All:All:All', 'ACCESS_ADMIN');

        // Initialisation successful
        return true;
    }

    /**
     * upgrade the keywords module from an old version
     * This function can be called multiple times
     * @return bool
     */
    public function upgrade($oldversion)
    {
        $dbconn = $this->db()->getConn();
        $tables = & $this->db()->getTables();
        $prefix = $this->db()->getPrefix();

        // Upgrade dependent on old version number
        switch ($oldversion) {
            case '1.0':
            case '1.0.0':

                $this->mod()->setVar('restricted', 0);
                $this->mod()->setVar('default', 'xaraya');

                $dbconn = $this->db()->getConn();
                $xartable = & $this->db()->getTables();
                $query = xarTableDDL::createTable(
                    $xartable['keywords_restr'],
                    ['id'         => ['type'        => 'integer',
                        'null'       => false,
                        'increment'  => true,
                        'primary_key' => true, ],
                        'keyword'    => ['type'        => 'varchar',
                            'size'        => 254,
                            'null'        => false,
                            'default'     => '', ],
                        'module_id'   => ['type'        => 'integer',
                            'unsigned'    => true,
                            'null'        => false,
                            'default'     => '0', ],
                    ]
                );

                if (empty($query)) {
                    return false;
                } // throw back

                // Pass the Table Create DDL to adodb to create the table and send exception if unsuccessful
                $result = $dbconn->Execute($query);
                if (!$result) {
                    return false;
                }

                if (!xarModHooks::register(
                    'item',
                    'search',
                    'GUI',
                    'keywords',
                    'user',
                    'search'
                )) {
                    return false;
                }

                // no break
            case '1.0.2':
                //Alter table restr to add itemtype
                // Get database information
                $dbconn = $this->db()->getConn();
                $xartable = & $this->db()->getTables();

                // Add column 'itemtype' to table
                $query = xarTableDDL::alterTable(
                    $xartable['keywords_restr'],
                    ['command' => 'add',
                        'field' => 'itemtype',
                        'type' => 'integer',
                        'null' => false,
                        'default' => '0', ]
                );
                $result = & $dbconn->Execute($query);
                if (!$result) {
                    return false;
                }

                // Register blocks
                if (!$this->mod()->apiFunc(
                    'blocks',
                    'admin',
                    'register_block_type',
                    ['modName'  => 'keywords',
                        'blockType' => 'keywordsarticles', ]
                )) {
                    return false;
                }
                if (!$this->mod()->apiFunc(
                    'blocks',
                    'admin',
                    'register_block_type',
                    ['modName'  => 'keywords',
                        'blockType' => 'keywordscategories', ]
                )) {
                    return false;
                }

                // no break
            case '1.0.3':
                $this->mod()->setVar('useitemtype', 0);

                // no break
            case '1.0.4':
                xarMasks::register('AddKeywords', 'All', 'keywords', 'Item', 'All:All:All', 'ACCESS_COMMENT');

                // no break
            case '1.0.5':
                // upgrade to v2.0.0
                if (!$this->upgrade_200()) {
                    return false;
                }

                break;
        }
        // Update successful
        return true;
    }

    /**
     * delete the keywords module
     * This function is only ever called once during the lifetime of a particular
     * module instance
     * @return bool true on success
     */
    public function delete()
    {
        // hooks are removed automatically
        // blocks are removed automatically
        sys::import('xaraya.structures.query');
        $tables = $this->db()->getTables();
        $indextable = $tables['keywords_index'];
        $keywordstable = $tables['keywords'];

        $q = new Query();
        // drop tables
        $query = "DROP TABLE IF EXISTS " . $indextable;
        if (!$q->run($query)) {
            return false;
        }
        $query = "DROP TABLE IF EXISTS " . $keywordstable;
        if (!$q->run($query)) {
            return false;
        }

        // Remove Masks and Instances
        xarMasks::removemasks('keywords');
        xarPrivileges::removeInstances('keywords');

        return $this->mod()->apiFunc('modules', 'admin', 'standarddeinstall', ['module' => 'keywords']);
    }

    public function upgrade_200()
    {
        // upgrade to 2.0.0, normalise tables
        sys::import('xaraya.structures.query');
        $dbconn = $this->db()->getConn();
        $tables = & $this->db()->getTables();
        $prefix = $this->db()->getPrefix();
        $indextable = $tables['keywords_index'];
        $keywordstable = $tables['keywords'];
        $restrtable = $tables['keywords_restr'];  // $prefix . '_keywords_restr';

        // Create index table
        try {
            $dbconn->begin();
            $q = new Query();
            // drop table
            $query = "DROP TABLE IF EXISTS " . $indextable;
            if (!$q->run($query)) {
                return;
            }
            //
            // CREATE TABLE {$prefix}_keywords_index (
            //   id         integer NOT NULL auto_increment,
            //   module_id  integer default 0,
            //   itemtype   integer default 0,
            //   itemid     integer default 0
            //   PRIMARY KEY (id)
            // )
            //
            $fields = [
                'id' => ['type' => 'integer', 'unsigned' => true, 'null' => false, 'increment' => true, 'primary_key' => true],
                'module_id' => ['type' => 'integer', 'size' => 11, 'unsigned' => true, 'null' => false, 'default' => '0'],
                'itemtype' => ['type' => 'integer', 'size' => 11, 'unsigned' => true, 'null' => false, 'default' => '0'],
                'itemid' => ['type' => 'integer', 'size' => 11, 'unsigned' => true, 'null' => false, 'default' => '0'],
            ];
            // Create the index table
            $query = xarTableDDL::createTable($indextable, $fields);
            $dbconn->Execute($query);

            // Create indices
            // unique entries
            $index = [
                'name'   => 'i_' . $prefix . '_keywords_index',
                'fields' => ['module_id', 'itemtype', 'itemid'],
                'unique' => true,
            ];
            $query = xarTableDDL::createIndex($indextable, $index);
            $dbconn->Execute($query);
            // Let's commit this, since we're gonna do some other stuff
            $dbconn->commit();
        } catch (Exception $e) {
            $dbconn->rollback();
            throw $e;
        }

        // get all mod, itemtype, itemids from keywords table
        $query = "SELECT module_id, itemtype, itemid
                  FROM $keywordstable
                  GROUP BY module_id, itemtype, itemid";
        $stmt = $dbconn->prepareStatement($query);
        $result = $stmt->executeQuery([]);

        $values = [];
        $bindvars = [];
        while ($result->next()) {
            $values[] = "(?,?,?)";
            [$module_id, $itemtype, $itemid] = $result->fields;
            $bindvars = array_merge($bindvars, [$module_id, $itemtype, $itemid]);
        }
        $result->close();

        // get all mod, itemtype from keywords_restr table
        $query = "SELECT module_id, itemtype
                  FROM $restrtable
                  GROUP BY module_id, itemtype";
        $stmt = $dbconn->prepareStatement($query);
        $result = $stmt->executeQuery([]);
        while ($result->next()) {
            $values[] = "(?,?,?)";
            [$module_id, $itemtype] = $result->fields;
            $bindvars = array_merge($bindvars, [$module_id, $itemtype, 0]);
        }
        $result->close();

        // populate index table
        if (!empty($values)) {
            $insert = "INSERT INTO $indextable (module_id, itemtype, itemid)";
            $insert .= " VALUES " . implode(',', $values);
            try {
                $dbconn->begin();
                $stmt = $dbconn->prepareStatement($insert);
                $stmt->executeUpdate($bindvars);
                $dbconn->commit();
            } catch (SQLException $e) {
                $dbconn->rollback();
                throw $e;
            }
        }

        // get keywords for all module, itemtype, itemids in keywords table
        $query = "SELECT module_id, itemtype, itemid, keyword
                  FROM $keywordstable";
        $stmt = $dbconn->prepareStatement($query);
        $result = $stmt->executeQuery([]);

        $keywords = [];
        while ($result->next()) {
            [$module_id, $itemtype, $itemid, $keyword] = $result->fields;
            if (!isset($keywords[$keyword])) {
                $keywords[$keyword] = [];
            }
            $keywords[$keyword][] = ['module_id' => $module_id, 'itemtype' => $itemtype, 'itemid' => $itemid];
        }
        $result->close();

        // get keywords for all module, itemtype in keywords_restr table
        $query = "SELECT module_id, itemtype, keyword
                  FROM $restrtable";
        $stmt = $dbconn->prepareStatement($query);
        $result = $stmt->executeQuery([]);
        while ($result->next()) {
            [$module_id, $itemtype, $keyword] = $result->fields;
            if (!isset($keywords[$keyword])) {
                $keywords[$keyword] = [];
            }
            $keywords[$keyword][] = ['module_id' => $module_id, 'itemtype' => $itemtype, 'itemid' => 0];
        }
        $result->close();

        // (re)Create keywords table
        try {
            $dbconn->begin();
            $q = new Query();
            // drop keywords table
            $query = "DROP TABLE IF EXISTS " . $keywordstable;
            if (!$q->run($query)) {
                return;
            }
            // drop keywords_restr table
            $query = "DROP TABLE IF EXISTS " . $restrtable;
            if (!$q->run($query)) {
                return;
            }
            //
            // CREATE TABLE {$prefix}_keywords (
            //   id         integer NOT NULL auto_increment,
            //   index_id   integer default 0,
            //   keyword    varchar(254) default ''
            //   PRIMARY KEY (id)
            // )
            //
            $fields = [
                'id' => ['type' => 'integer', 'unsigned' => true, 'null' => false, 'increment' => true, 'primary_key' => true],
                'index_id' => ['type' => 'integer', 'size' => 11, 'unsigned' => true, 'null' => false, 'default' => '0'],
                'keyword' => ['type' => 'varchar', 'size' => 254,'null' => false,'default' => ''],
            ];
            // Create the keywords table
            $query = xarTableDDL::createTable($keywordstable, $fields);
            $dbconn->Execute($query);

            // Create indices
            $index = [
                'name'   => 'i_' . $prefix . '_keywords_keyword',
                'fields' => ['keyword'],
                'unique' => false,
            ];
            $query = xarTableDDL::createIndex($keywordstable, $index);
            $dbconn->Execute($query);
            $index = [
                'name'   => 'i_' . $prefix . '_keywords_index_id',
                'fields' => ['index_id'],
                'unique' => false,
            ];
            $query = xarTableDDL::createIndex($keywordstable, $index);
            $dbconn->Execute($query);
            // Let's commit this, since we're gonna do some other stuff
            $dbconn->commit();
        } catch (Exception $e) {
            $dbconn->rollback();
            throw $e;
        }

        // populate keywords table
        if (!empty($keywords)) {
            // get indexes for all module, itemtype, itemids in index table
            $query = "SELECT id, module_id, itemtype, itemid
                      FROM $indextable";
            $stmt = $dbconn->prepareStatement($query);
            $result = $stmt->executeQuery([]);
            // create hash table of index ids
            while ($result->next()) {
                [$id, $module_id, $itemtype, $itemid] = $result->fields;
                if (!isset($indexes[$module_id])) {
                    $indexes[$module_id] = [];
                }
                if (!isset($indexes[$module_id][$itemtype])) {
                    $indexes[$module_id][$itemtype] = [];
                }
                $indexes[$module_id][$itemtype][$itemid] = $id;
            }
            $result->close();

            $values = [];
            $bindvars = [];
            foreach ($keywords as $keyword => $items) {
                foreach ($items as $item) {
                    if (isset($indexes[$item['module_id']][$item['itemtype']][$item['itemid']])) {
                        $values[] = '(?,?)';
                        $bindvars[] = $indexes[$item['module_id']][$item['itemtype']][$item['itemid']];
                        $bindvars[] = $keyword;
                    }
                }
            }
            // populate keywords table
            if (!empty($values)) {
                $insert = "INSERT INTO $keywordstable (index_id, keyword)";
                $insert .= " VALUES " . implode(',', $values);
                try {
                    $dbconn->begin();
                    $stmt = $dbconn->prepareStatement($insert);
                    $stmt->executeUpdate($bindvars);
                    $dbconn->commit();
                } catch (SQLException $e) {
                    $dbconn->rollback();
                    throw $e;
                }
            }
        }

        // transpose deprecated modvar settings to new format
        $restricted = $this->mod()->getVar('restricted');
        if ($restricted) {
            $useitemtype = $this->mod()->getVar('useitemtype');
            $subjects = $this->mod()->apiFunc('keywords', 'hooks', 'getsubjects');
            if (!empty($subjects)) {
                foreach (array_keys($subjects) as $hookedto) {
                    // get the modules default settings
                    $settings = $this->mod()->apiFunc(
                        'keywords',
                        'hooks',
                        'getsettings',
                        [
                            'module' => $hookedto,
                        ]
                    );
                    // set module default to restricted words
                    $settings['restrict_words'] = true;
                    if (!$useitemtype) {
                        // not per itemtype, all itemtypes use module default settings
                        $settings['global_config'] = true;
                    } else {
                        // per itemtype allowed, set restriction per itemtype
                        if (!empty($subjects[$hookedto]['itemtypes'])) {
                            foreach (array_keys($subjects[$hookedto]['itemtypes']) as $itemtype) {
                                if (empty($itemtype)) {
                                    continue;
                                }
                                $typesettings = $this->mod()->apiFunc(
                                    'keywords',
                                    'hooks',
                                    'getsettings',
                                    [
                                        'module' => $hookedto,
                                        'itemtype' => $itemtype,
                                    ]
                                );
                                $typesettings['restrict_words'] = true;
                                $this->mod()->apiFunc(
                                    'keywords',
                                    'hooks',
                                    'updatesettings',
                                    [
                                        'module' => $hookedto,
                                        'itemtype' => $itemtype,
                                        'settings' => $typesettings,
                                    ]
                                );
                            }
                        }
                    }
                    $this->mod()->apiFunc(
                        'keywords',
                        'hooks',
                        'updatesettings',
                        [
                            'module' => $hookedto,
                            'settings' => $settings,
                        ]
                    );
                }
            }
        }
        $this->mod('keywords')->delVar('restricted');
        $this->mod('keywords')->delVar('useitemtype');

        $cols_per_page = $this->mod()->getVar('displaycolumns') ?? 2;
        $this->mod('keywords')->delVar('displaycolumns');

        // new modvars
        $this->mod()->setVar('stats_per_page', 100);
        $this->mod()->setVar('items_per_page', 20);
        $this->mod()->setVar('user_layout', 'list');
        $this->mod()->setVar('cols_per_page', $cols_per_page);
        $this->mod()->setVar('words_per_page', 50);
        $this->mod()->setVar('cloud_font_min', 1);
        $this->mod()->setVar('cloud_font_max', 3);
        $this->mod()->setVar('cloud_font_unit', 'em');
        $this->mod()->setVar('use_module_icons', true);

        xarHooks::registerObserver('ModuleModifyconfig', 'keywords', 'gui', 'hooks', 'modulemodifyconfig');
        xarHooks::registerObserver('ModuleUpdateconfig', 'keywords', 'api', 'hooks', 'moduleupdateconfig');

        return true;
    }
}
