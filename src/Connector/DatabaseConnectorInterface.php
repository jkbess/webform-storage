<?php

namespace Webform\Connector;

interface DatabaseConnectorInterface
{
    /* takes SQL statement, binds parameters in $params array, executes */
    /* returns result array or true/false */
    public function doQuery($sql, $params);
}

?>