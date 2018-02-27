<?php

namespace Uniondrug\Validation;

use Phalcon\Di\ServiceProviderInterface;

class ValidationServiceProvider implements ServiceProviderInterface
{
    public function register(\Phalcon\DiInterface $di)
    {
        $di->setShared(
            'validationService',
            function () {
                return new Param();
            }
        );
    }
}
