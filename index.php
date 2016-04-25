<?php
    require 'vendor/autoload.php';

    $jE = new Samshal\Scripd\JsonDbStructure('tests/5.json', 'mysql');

    $jE->parseStructure();

    $sql = $jE->getGeneratedSql();

    $fh = fopen('./sql.sql', 'ar');

    fwrite($fh, $sql);

    //echo $jE->toString();
;
