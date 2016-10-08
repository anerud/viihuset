<?php

interface iController
{

    public function init($router, $dbContext);

    public function getLinksByLevel($level, $endpointLevel, $brf, $domain);
}

?>
