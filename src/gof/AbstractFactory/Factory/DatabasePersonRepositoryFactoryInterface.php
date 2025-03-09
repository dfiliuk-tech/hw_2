<?php
declare(strict_types=1);

namespace App\gof\AbstractFactory\Factory;

use App\gof\AbstractFactory\Repository\DatabasePersonRepository;
use App\gof\AbstractFactory\Repository\PersonRepositoryInterface;

class DatabasePersonRepositoryFactoryInterface implements PersonRepositoryFactoryInterface {
    public function createPersonRepository(): PersonRepositoryInterface {
        return new DatabasePersonRepository();
    }
}
