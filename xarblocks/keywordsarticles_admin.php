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
 * Original Author of file: Camille Perinel
 * Mostly taken from the topitems.php block of the articles module.(See credits)
 */
sys::import('modules.keywords.xarblocks.keywordsarticles');

class Keywords_KeywordsarticlesBlockAdmin extends Keywords_KeywordsarticlesBlock implements iBlock
{
    public function modify()
    {
        $vars = $this->getContent();
        $vars['pubtypes'] = $this->mod()->apiFunc('articles', 'user', 'getpubtypes');
        $vars['categorylist'] = $this->mod()->apiFunc('categories', 'user', 'getcat');
        $vars['statusoptions'] = [['id' => '3,2',
                                         'name' => $this->ml('All Published'), ],
                                   ['id' => '3',
                                         'name' => $this->ml('Frontpage'), ],
                                   ['id' => '2',
                                         'name' => $this->ml('Approved'), ],
                                  ];

        $vars['blockid'] = $this->block_id;
        // Return output
        return $vars;
    }

    public function update($data = [])
    {
        $this->var()->fetch('ptid', 'id', $vars['ptid'], $this->ptid, xarVar::NOT_REQUIRED);
        $this->var()->fetch('cid', 'int:1:', $vars['cid'], $this->cid, xarVar::NOT_REQUIRED);
        $this->var()->fetch('status', 'str:1:', $vars['status'], $this->status, xarVar::NOT_REQUIRED);
        $this->var()->fetch('refreshtime', 'int:1:', $vars['refreshtime'], 1, xarVar::NOT_REQUIRED);
        $this->setContent($vars);
        return true;
    }
}
