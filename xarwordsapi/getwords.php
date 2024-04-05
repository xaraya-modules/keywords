<?php

function keywords_wordsapi_getwords(array $args = [], $context = null)
{
    $items = xarMod::apiFunc('keywords', 'words', 'getitems', $args);
    if (empty($items)) {
        return $items;
    }
    foreach ($items as $item) {
        $words[$item['keyword']] = $item['keyword'];
    }
    return $words;
}
