<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

namespace Fusonic\TestUtilities;

use Doctrine\ORM\EntityManagerInterface;

trait SqliteDatabaseTrait {

    protected static function setUpDatabase(EntityManagerInterface $em): void
    {
        $connection = $em->getConnection();
    
        $sqlitePath = $connection->getParams()['path'];
    
        $backupPath = pathinfo($sqlitePath)['dirname'].'/backup.sqlite';
        
        if (!file_exists($backupPath)) {
            copy($sqlitePath, $backupPath);
        } else {
            copy($backupPath, $sqlitePath);
        }
    }

    protected static function tearDownDatabase(EntityManagerInterface $em): void
    {
        $em->clear();
        $em->getConnection()->close();
    }
}
