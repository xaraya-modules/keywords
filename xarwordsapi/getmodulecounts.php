<?php

function keywords_wordsapi_getmodulecounts(array $args = [], $context = null)
{
    extract($args);

    $dbconn = xarDB::getConn();
    $tables = & xarDB::getTables();
    $wordstable = $tables['keywords'];
    $idxtable = $tables['keywords_index'];
    $modstable = $tables['modules'];

    $select = [];
    $from = [];
    $join = [];
    $where = [];
    $groupby = [];
    $orderby = [];
    $bindvars = [];

    $select['module_id'] = 'idx.module_id';
    $select['itemtype'] = 'idx.itemtype';
    $select['module'] = 'mods.name';
    $select['numitems'] = 'COUNT(DISTINCT idx.itemid) as numitems';
    $select['numwords'] = 'COUNT(words.keyword) as numwords';

    $from['idx'] = "$idxtable idx";
    $from['mods'] = "$modstable mods";
    $from['words'] = "$wordstable words";

    $where[] = 'mods.regid = idx.module_id';
    $where[] = 'idx.keyword_id = words.id';

    if (!empty($skip_restricted)) {
        $where[] = 'idx.itemid != 0';
    }

    $groupby[] = 'idx.module_id';
    $groupby[] = 'idx.itemtype';

    $orderby[] = 'mods.name';
    $orderby[] = 'idx.itemtype';

    $query = "SELECT " . implode(',', $select);
    $query .= " FROM " . implode(',', $from);
    if (!empty($join)) {
        $query .= " " . implode(' ', $join);
    }
    if (!empty($where)) {
        $query .= " WHERE " . implode(' AND ', $where);
    }
    if (!empty($groupby)) {
        $query .= " GROUP BY " . implode(',', $groupby);
    }
    if (!empty($orderby)) {
        $query .= " ORDER BY " . implode(',', $orderby);
    }

    $stmt = $dbconn->prepareStatement($query);
    if (!empty($numitems)) {
        $stmt->setLimit($numitems);
        if (empty($startnum)) {
            $startnum = 1;
        }
        $stmt->setOffset($startnum - 1);
    }
    $result = $stmt->executeQuery($bindvars);

    $items = [];
    while ($result->next()) {
        $item = [];
        foreach (array_keys($select) as $field) {
            $item[$field] = array_shift($result->fields);
        }
        if (!isset($items[$item['module']])) {
            $items[$item['module']] = [];
        }
        $items[$item['module']][$item['itemtype']] = $item;
    }
    $result->close();

    return $items;
}
