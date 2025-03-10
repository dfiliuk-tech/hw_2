<?php

declare(strict_types=1);

namespace App\GOF\AbstractFactory\Factory;

use App\GOF\AbstractFactory\Repository\FilesystemPersonRepository;
use App\GOF\AbstractFactory\Repository\PersonRepositoryInterface;

class FilesystemPersonRepositoryFactory implements PersonRepositoryFactoryInterface
{
    public function createPersonRepository(): PersonRepositoryInterface
    {
        return new FilesystemPersonRepository();
    }
}
