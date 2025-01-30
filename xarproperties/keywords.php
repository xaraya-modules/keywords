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
 * @author Marc Lutolf <mfl@netspan.ch>
 */

sys::import('modules.base.xarproperties.textarea');

class KeywordsProperty extends TextAreaProperty
{
    public $id         = 30117;
    public $name       = 'keywords';
    public $desc       = 'Keywords';
    public $reqmodules = ['keywords'];

    private $wordcache  = null;

    public function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);
        $this->filepath   = 'modules/keywords/xarproperties';
        // We want a reference to the object here
        $this->include_reference = 1;

        // Force setting of the datastore to NONE
        $this->source = '';
    }

    public function validateValue($value = null)
    {
        if (!parent::validateValue($value)) {
            return false;
        }

        $words = $this->mod()->apiMethod('keywords', 'admin', 'separatekeywords', ['keywords' => $value]);
        $cleanwords = [];
        foreach ($words as $word) {
            if (empty($word)) {
                continue;
            }
            $cleanwords[] = $word;
        }
        $this->value = $cleanwords;
        return true;
    }

    public function getValue()
    {
        return $this->getKeywords();
    }

    public function getItemValue($itemid)
    {
        return $this->getKeywords(['itemid' => $itemid]);
    }

    public function showInput(array $data = [])
    {
        //  if(!empty($this->getValue())) {
        if (!isset($data['value'])) {
            $data['value'] = $this->getValue();
        }

        $keywords = [];
        foreach ($data['value'] as $word) {
            $keywords[] = $word['keyword'];
        }
        $data['value'] = implode(',', $keywords);
        //   }

        return parent::showInput($data);
    }

    public function showOutput(array $data = [])
    {
        if (!isset($data['value'])) {
            $data['value'] = $this->getValue();
            $keywords = [];
            foreach ($data['value'] as $word) {
                $keywords[] = $word['keyword'];
            }
            $data['value'] = implode(',', $keywords);
        }
        return parent::showOutput($data);
    }

    private function getKeywords(array $data = [])
    {
        // The virtual datastore will use the itemid as value for this property
        if (!isset($data['itemid'])) {
            $data['itemid'] = $this->_itemid;
        }

        // Make sure we have the keywords table
        $this->mod()->apiLoad('keywords');

        $table = & $this->db()->getTables();
        $q = new Query('SELECT');
        $q->addtable($table['keywords'], 'k');
        $q->addtable($table['keywords_index'], 'i');
        $q->join('i.keyword_id', 'k.id');
        $q->addfield('i.id AS id');
        $q->addfield('k.keyword AS keyword');
        if (!empty($this->objectref->moduleid)) {
            $q->eq('i.module_id', $this->objectref->moduleid);
        }
        if (!empty($this->objectref->itemtype)) {
            $q->eq('i.itemtype', $this->objectref->itemtype);
        }
        $q->eq('i.itemid', $data['itemid']);
        $q->addorder('keyword', 'ASC');
        //        $q->qecho();
        $q->run();
        $words = $q->output();
        return $words;
    }

    public function createValue($itemid = 0)
    {
        $words = $this->value;
        $keyword_ids = $this->updateKeywords($words);
        $this->updateAssociations($itemid, $keyword_ids);
        return $itemid;
    }

    public function updateValue($itemid = 0)
    {
        return $this->createValue($itemid);
    }

    public function deleteValue($itemid = 0)
    {
        $associations = $this->getAssociations($itemid);
        $this->deleteAssociations($itemid, array_keys($associations));
        return true;
    }

    #----------------------------------------------------------------
    # Check if we have the words in the database and add those missing
    #
    private function updateKeywords($words)
    {
        if (empty($words)) {
            return [];
        }

        // Make sure we have the keywords table
        $this->mod()->apiLoad('keywords');

        $table = & $this->db()->getTables();
        $q = new Query('SELECT', $table['keywords']);
        $q->in('keyword', $words);
        $q->run();
        $keywords = [];
        $keyword_ids = [];

        // Reshuffle the results. This may be overkill as we don't (for now) pass it back
        foreach ($q->output() as $row) {
            $keywords[$row['keyword']] = $row;
            $keyword_ids[$row['id']] = $row;
        }

        $q = new Query('INSERT', $table['keywords']);
        foreach ($this->value as $word) {
            // If we already have this keyword in the database, move on
            if (isset($keywords[$word])) {
                continue;
            }

            // Thiis is a new keyword; add it to the index
            $q->addfield('keyword', $word);
            $q->run();
            $keyword_id = $q->lastid($table['keywords'], 'id');
            $keywords[$word] = ['id' => $keyword_id, 'keyword' => $word];
            $keyword_ids[$keyword_id] = ['id' => $keyword_id, 'keyword' => $word];
            $q->clearfields();
        }
        $ids = array_keys($keyword_ids);
        return $ids;
    }

    #----------------------------------------------------------------
    # After saving one or more keyword entries, update the associations table
    #
    private function updateAssociations($itemid, $keyword_ids = [])
    {
        // Check if we are in an object or not
        $moduleid = $this->objectref->moduleid ?? null;
        if (!empty($moduleid) && !empty($itemid)) {
            sys::import('modules.keywords.class.association');
            $association = new Keyword_Association();
            $association->sync_associations($moduleid, $this->objectref->itemtype, $itemid, $keyword_ids);
        }
        return true;
    }

    #----------------------------------------------------------------
    # Get the associations of this item
    #
    private function getAssociations($itemid)
    {
        $associations = [];
        // Check if we are in an object or not
        $moduleid = $this->objectref->moduleid ?? null;
        if (!empty($moduleid) && !empty($itemid)) {
            sys::import('modules.keywords.class.association');
            $association = new Keyword_Association();
            $args = [
                    'module_id'    => $moduleid,
                    'itemtype'     => $this->objectref->itemtype,
                    'property_id'  => (int) $this->id,
                    'itemid'       => $itemid,
            ];
            $associations = $association->get_associations($args);
        }
        return $associations;
    }

    public function preList()
    {
        if (empty($this->objectref)) {
            return true;
        }

        // Get the parent object's query;
        $q = $this->objectref->dataquery;

        // Get the primary propety of the parent object, and its source
        $primary = $this->objectref->primary;
        $primary_source = $this->objectref->properties[$primary]->source;

        // Assemble the links to the object's table
        //$this->mod()->load('keywords');
        $this->mod()->apiLoad('keywords');
        //$this->mod()->load('dam');
        $tables = $this->db()->getTables();

        $q->addtable($tables['dam_resources'], 'resource');
        $q->addtable($tables['keywords'], 'k');
        $q->addtable($tables['keywords_index'], 'i');
        $q->join('resource.id', 'i.itemid');
        $q->join('i.keyword_id', 'k.id');
        // A zero means "all"
        // Itemtype & module ID = 0 means the objects listing
        if (!empty($this->objectref->module_id)) {
            $q->eq('i.module_id', $this->objectref->module_id);
        }
        if (!empty($this->objectref->itemtype)) {
            $q->eq('i.itemtype', $this->objectref->itemtype);
        }
        if (!empty($data['itemid'])) {
            $q->eq('i.itemid', $data['itemid']);
        }
        // Set the source of this property
        $this->source = 'k.keyword';
        return true;
    }

    #----------------------------------------------------------------
    # After creating a keyword entry, add the required association
    #
    private function addAssociation($itemid, $keyword_id = 0)
    {
        // Check if we are in an object or not
        $moduleid = $this->objectref->moduleid ?? null;
        if (!empty($moduleid) && !empty($itemid)) {
            sys::import('modules.keywords.class.association');
            $association = new Keyword_Association();
            $args = [
                    'keyword_id'  => $keyword_id,
                    'module_id'    => $moduleid,
                    'itemtype'     => $this->objectref->itemtype,
                    'property_id'  => (int) $this->id,
                    'itemid'       => $itemid,
            ];
            $association->add_association($args);
        }
        return true;
    }
}
