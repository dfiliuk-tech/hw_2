<?php
declare(strict_types=1);

namespace App\gof\AbstractFactory\Factory;

use App\gof\AbstractFactory\Repository\PersonRepositoryInterface;

interface PersonRepositoryFactoryInterface {
    public function createPersonRepository(): PersonRepositoryInterface;
}
