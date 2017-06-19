<?php

if (!($loader = @include __DIR__.'/../vendor/autoload.php')) {
    echo 'You need to run `composer install` first!';
    exit(1);
}
