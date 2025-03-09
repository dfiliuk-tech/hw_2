<?php
declare(strict_types=1);

namespace App\gof\AbstractFactory\Factory;

use App\gof\AbstractFactory\Repository\FilesystemPersonRepository;
use App\gof\AbstractFactory\Repository\PersonRepositoryInterface;

class FilesystemPersonRepositoryFactory implements PersonRepositoryFactoryInterface {
    public function createPersonRepository(): PersonRepositoryInterface {
        return new FilesystemPersonRepository();
    }
}
