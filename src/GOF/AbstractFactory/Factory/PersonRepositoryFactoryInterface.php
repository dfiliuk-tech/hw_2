<?php
declare(strict_types=1);

namespace App\GOF\AbstractFactory\Factory;

use App\GOF\AbstractFactory\Repository\PersonRepositoryInterface;

interface PersonRepositoryFactoryInterface {
    public function createPersonRepository(): PersonRepositoryInterface;
}
