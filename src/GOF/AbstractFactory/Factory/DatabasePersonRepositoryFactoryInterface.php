<?php
declare(strict_types=1);

namespace App\GOF\AbstractFactory\Factory;

use App\GOF\AbstractFactory\Repository\DatabasePersonRepository;
use App\GOF\AbstractFactory\Repository\PersonRepositoryInterface;

class DatabasePersonRepositoryFactoryInterface implements PersonRepositoryFactoryInterface {
    public function createPersonRepository(): PersonRepositoryInterface {
        return new DatabasePersonRepository();
    }
}
