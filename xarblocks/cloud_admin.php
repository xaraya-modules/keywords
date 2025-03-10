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
sys::import('modules.keywords.xarblocks.cloud');

class Keywords_CloudBlockAdmin extends Keywords_CloudBlock implements iBlock
{
    public function modify()
    {
        $data = $this->getContent();

        $data['status'] = '';
        switch ($data['cloudtype']) {
            default:
            case 1:
                if (!$this->mod()->isAvailable('categories')) {
                    $data['status'] = 'not_available';
                }
                break;
            case 3:
                if (!$this->mod()->isAvailable('keywords')) {
                    $data['status'] = 'not_available';
                }
                break;
        }
        return $data;
    }

    public function update($data = [])
    {
        // Get the cloud type
        $this->var()->find('cloudtype', $vars['cloudtype'], 'int', $this->cloudtype);
        $this->var()->find('color', $vars['color'], 'str:1:', $this->color);
        $this->var()->find('background', $vars['background'], 'str:1:', $this->background);
        $this->var()->find('module_id', $vars['module_id'], 'str:1:', $this->module_id);
        $this->var()->find('itemtype', $vars['itemtype'], 'str:1:', $this->itemtype);
        $this->setContent($vars);
        return true;
    }
}
