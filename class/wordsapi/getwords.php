<?php

/**
 * @package modules\keywords
 * @category Xaraya Web Applications Framework
 * @version 2.6.2
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Keywords\WordsApi;

use Xaraya\Modules\Keywords\MethodClass;
use Xaraya\Modules\Keywords\WordsApi;
use xarMod;
use sys;

sys::import('modules.keywords.class.method');

/**
 * keywords wordsapi getwords function
 * @extends MethodClass<WordsApi>
 */
class GetwordsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Summary of __invoke
     * @param array<mixed> $args
     * @see WordsApi::getwords()
     */
    public function __invoke(array $args = [])
    {
        /** @var WordsApi $wordsapi */
        $wordsapi = $this->wordsapi();
        $items = $wordsapi->getitems($args);
        if (empty($items)) {
            return $items;
        }
        foreach ($items as $item) {
            $words[$item['keyword']] = $item['keyword'];
        }
        return $words;
    }
}
