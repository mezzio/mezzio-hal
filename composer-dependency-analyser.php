<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;

$config = new Configuration();
return $config
    // Mark as soft dependencies to avoid dependency hell for users
    ->ignoreErrorsOnPackage('doctrine/orm', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackage('laminas/laminas-paginator', [ErrorType::DEV_DEPENDENCY_IN_PROD]);
