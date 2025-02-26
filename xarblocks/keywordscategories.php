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
 * Original Author of file:Camille Perinel
 * Mostly taken from the topitems.php block of the articles module.(See credits)
 * @TODO : Add multi categories support with + - ...
 */
sys::import('xaraya.structures.containers.blocks.basicblock');

class Keywords_KeywordscategoriesBlock extends BasicBlock implements iBlock
{
    // File Information, supplied by developer, never changes during a versions lifetime, required
    protected $type             = 'keywordscategories';
    protected $module           = 'keywords'; // module block type belongs to, if any
    protected $text_type        = 'Keywords Categories';  // Block type display name
    protected $text_type_long   = 'Show categories related by keywords'; // Block type description
    // Additional info, supplied by developer, optional
    protected $type_category    = 'block'; // options [(block)|group]
    protected $author           = '';
    protected $contact          = '';
    protected $credits          = '';
    protected $license          = '';

    // blocks subsystem flags
    protected $show_preview = true;  // let the subsystem know if it's ok to show a preview
    protected $show_help    = false; // let the subsystem know if this block type has a help() method

    public $refreshtime = 1440;

    public function display()
    {
        $vars = $this->getContent();

        // Allow refresh by setting refreshrandom variable
        $this->var()->check('refreshrandom', $vars['refreshtime'], 'int:1:1', 0);

        // Check cache
        $refresh = (time() - ($vars['refreshtime'] * 60));
        $varDir = sys::varpath();
        $cacheKey = md5($blockinfo['bid']);
        $cachedFileName = $varDir . '/cache/templates/' . $cacheKey;
        if ((file_exists($cachedFileName)) &&
           (filemtime($cachedFileName) > $refresh)) {
            $fp = @fopen($cachedFileName, 'r');

            // Read From Our Cache
            $vars = unserialize(fread($fp, filesize($cachedFileName)));
            fclose($fp);
        } else {
            //Get the keywords related categories
            if ($this->var()->isCached('Blocks.articles', 'cids')) {
                $vars['modid'] = $this->mod()->getRegID('categories');
                $vars['cids'] = $this->var()->getCached('Blocks.articles', 'cids');
                if (empty($vars['cids']) || !is_array($vars['cids']) || count($vars['cids']) == 0) {
                    return '';
                }

                $keywords = [];
                foreach ($vars['cids'] as $id => $cid) {
                    // if we're viewing all items below a certain category, i.e. catid = _NN
                    $cid = str_replace('_', '', $cid);
                    $keywords = $this->mod()->apiMethod(
                        'keywords',
                        'user',
                        'getwords',
                        ['itemid' => $cid,
                                            'modid' => $vars['modid'], ]
                    );
                }
                if (empty($keywords) || !is_array($keywords) || count($keywords) == 0) {
                    return '';
                }
                //for each keyword in keywords[]
                $items = [];
                $vars['items'] = [];
                foreach ($keywords as $id => $word) {
                    // get the list of items to which this keyword is assigned
                    //TODO Make itemtype / modid dependant
                    $items = $items + $this->mod()->apiMethod(
                        'keywords',
                        'user',
                        'getitems',
                        ['keyword' => $word,
                                        'modid' => $vars['modid'], ]
                    );
                }
                //make itemid unique (worst ever code)
                $tmp = [];
                $itemsB = [];
                foreach ($items as $id => $item) {
                    if (!in_array($item['itemid'], $tmp)) {
                        $tmp[] = $item['itemid'];
                        $itemsB[] = $item;
                    }
                }
                foreach ($itemsB as $id => $item) {
                    if (!in_array($item['itemid'], $vars['cids'])) {
                        $categories = $this->mod()->apiFunc(
                            'categories',
                            'user',
                            'getcatinfo',
                            ['cid' => $item['itemid']]
                        );
                        //TODO : display config
                        //'aid','title','summary','authorid', 'pubdate','pubtypeid','notes','status','body'
                        //if the related article already exist do not add it
                        $vars['items'][] = [
                                'keyword' => $item['keyword'],
                                'modid' =>  $item['moduleid'],
                                'itemtype' => $item['itemtype'],
                                'itemid' => $item['itemid'],
                                'name' => $categories['name'],
                                'description' => $categories['description'],
                                'image' => $categories['image'],
                                'parent' => $categories['parent'],
                                'left' => $categories['left'],
                                'right' => $categories['right'],
                                'link' => $this->ctl()->getModuleURL('articles', 'user', 'view', ['cids' => [0 => $item['itemid']]]),
                                ];
                    }
                }
            }
        }

        return $vars;
    }
}
