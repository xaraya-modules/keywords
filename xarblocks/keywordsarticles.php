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
 * Initialise block
 *
 * Original Author of file:Camille Perinel
 * Mostly taken from the topitems.php block of the articles module.(See credits)
 * @return bool true on success
 */
sys::import('xaraya.structures.containers.blocks.basicblock');

class Keywords_KeywordsarticlesBlock extends BasicBlock implements iBlock
{
    // File Information, supplied by developer, never changes during a versions lifetime, required
    protected $type             = 'keywordsarticles';
    protected $module           = 'keywords'; // module block type belongs to, if any
    protected $text_type        = 'Keywords Articles';  // Block type display name
    protected $text_type_long   = 'Show articles related by keywords'; // Block type description
    // Additional info, supplied by developer, optional
    protected $type_category    = 'block'; // options [(block)|group]
    protected $author           = '';
    protected $contact          = '';
    protected $credits          = '';
    protected $license          = '';

    // blocks subsystem flags
    protected $show_preview = true;  // let the subsystem know if it's ok to show a preview
    protected $show_help    = false; // let the subsystem know if this block type has a help() method

    public $ptid = '';
    public $cid = '';
    public $status = '2,3';
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
            //Get the keywords related articles
            if ($this->var()->isCached('Blocks.articles', 'aid')) {
                $vars['itemid'] = $this->var()->getCached('Blocks.articles', 'aid');
                $itemtype = $this->var()->getCached('Blocks.articles', 'ptid');
                if (!empty($itemtype) && is_numeric($itemtype)) {
                    $vars['itemtype'] = $itemtype;
                } else {
                    $article = $this->mod()->apiFunc(
                        'articles',
                        'user',
                        'get',
                        ['aid' => $vars['itemid']]
                    );
                    $vars['itemtype'] = $article['pubtypeid'];
                }
                $vars['modid'] = $this->mod()->getRegID('articles');
                $keywords = $this->mod()->apiMethod(
                    'keywords',
                    'user',
                    'getwords',
                    ['itemid' => $vars['itemid'],
                                            'itemtype' => $vars['itemtype'],
                                            'modid' => $vars['modid'], ]
                );
                if (empty($keywords) || !is_array($keywords) || count($keywords) == 0) {
                    return '';
                }
                //for each keyword in keywords[]
                $items = [];
                $vars['items'] = [];
                foreach ($keywords as $id => $word) {
                    //$item['id'] = $id;
                    //$item['keyword'] = $this->var()->prep($word);
                    // get the list of items to which this keyword is assigned
                    //TODO Make itemtype / modid dependant
                    $items = $items + $this->mod()->apiMethod(
                        'keywords',
                        'user',
                        'getitems',
                        ['keyword' => $word,
                                        'itemtype' => $vars['ptid'], ]
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
                    if ($vars['itemid'] != $item['itemid'] || $vars['modid'] != $item['moduleid']
                    || $vars['itemtype'] != $item['itemtype']) {
                        if ($articles = $this->mod()->apiFunc(
                            'articles',
                            'user',
                            'get',
                            ['aid' => $item['itemid']]
                        )) {
                            //TODO : display config
                            //'aid','title','summary','authorid', 'pubdate','pubtypeid','notes','status','body'
                            //if the related article already exist do not add it
                            if (stristr($vars['status'], $articles['status'])) {
                                $vars['items'][] = [
                                        'keyword' => $item['keyword'],
                                        'modid' =>  $item['moduleid'],
                                        'itemtype' => $item['itemtype'],
                                        'itemid' => $item['itemid'],
                                        'title' => $articles['title'],
                                        'summary' => $articles['summary'],
                                        'authorid' => $articles['authorid'],
                                        'pubdate' => $articles['pubdate'],
                                        'pubtypeid' => $articles['pubtypeid'],
                                        'status' => $articles['status'],
                                        'link' => $this->ctl()->getModuleURL('articles', 'user', 'display', ['aid' => $articles['aid'], 'ptid' => $articles['pubtypeid']]),
                                        ];
                            }
                        }
                    }
                }
            }
        }
        return $vars;
    }
}
