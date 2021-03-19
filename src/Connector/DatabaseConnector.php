<?php

namespace Webform\Connector;

use \SQLite3;
use \Exception;

class DatabaseConnector implements DatabaseConnectorInterface
{
    public function __construct($db_path)
    {
         $this->db = new SQLite3($db_path);
    }

    public function doQuery($sql, $params = null)
    {
        if (!$sql) {
            throw new Exception('No SQL submitted.');
            return false;
        }
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new Exception('Prepare failed.');
            return false;
        }
        if ($params) {
            foreach ($params as $param => $value) {
                $bind = $stmt->bindValue(':' . $param, $value);
                if (!$bind) {
                    throw new Exception('Bind failed.');
                    return false;
                }
            }
        }
        $result = $stmt->execute();
        
        if ($result === false) {
            throw new Exception('Query could not execute.');
            return false;
        }
        
        $qtype = explode(' ', trim($sql))[0];
        if ($qtype === 'SELECT' || $qtype === 'PRAGMA') {
            $dataSet = [];
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $dataSet[] = $row;
            }
            return $dataSet;
        } else {
            return true;
        }
    }

    public function getLastRowId ()
    {
        return $this->db->lastInsertRowID();
    }

}
?>