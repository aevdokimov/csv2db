<?php

namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function save(array $data): void;
    
    public function truncate(): void;

    public function paginate(int $perPage = 15, int $page = 1): LengthAwarePaginator;
}