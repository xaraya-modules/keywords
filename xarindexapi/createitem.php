<?php

function keywords_indexapi_createitem(array $args = [], $context = null)
{
    extract($args);

    if (!empty($module)) {
        $module_id = xarMod::getID($module);
    }
    if (empty($module_id) || !is_numeric($module_id)) {
        $invalid[] = 'module_id';
    }

    if (empty($itemtype)) {
        $itemtype = 0;
    }
    if (!is_numeric($itemtype)) {
        $invalid[] = 'itemtype';
    }

    if (empty($itemid)) {
        $itemid = 0;
    }
    if (!is_numeric($itemid)) {
        $invalid[] = 'itemid';
    }

    if (!empty($invalid)) {
        $msg = 'Invalid #(1) for #(2) module #(3) function #(4)()';
        $vars = [implode(', ', $invalid), 'keywords', 'indexapi', 'createitem'];
        throw new BadParameterException($vars, $msg);
    }

    if ($item = xarMod::apiFunc(
        'keywords',
        'index',
        'getitem',
        [
            'module_id' => $module_id,
            'itemtype' => $itemtype,
            'itemid' => $itemid,
        ]
    )) {
        return $item;
    }

    $dbconn = xarDB::getConn();
    $tables = & xarDB::getTables();
    $idxtable = $tables['keywords_index'];

    // Insert item
    try {
        $dbconn->begin();
        $nextId = $dbconn->GenId($idxtable);
        $query = "INSERT INTO $idxtable
                  (id, module_id, itemtype, itemid)
                  VALUES (?,?,?,?)";
        $bindvars = [$nextId, $module_id, $itemtype, $itemid];
        $stmt = $dbconn->prepareStatement($query);
        $result = $stmt->executeUpdate($bindvars);
        $id = $dbconn->getLastId($idxtable);
        $dbconn->commit();
    } catch (SQLException $e) {
        $dbconn->rollback();
        throw $e;
    }

    if (empty($id)) {
        return;
    }
    // return item to caller (saves a further getitem call)
    $item = [
        'id' => $id,
        'module_id' => $module_id,
        'itemtype' => $itemtype,
        'itemid' => $itemid,
    ];

    return $item;
}
