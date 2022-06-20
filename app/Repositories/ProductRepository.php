<?php

namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductRepository implements ProductRepositoryInterface
{
    public function save(array $data): void
    {
        DB::table('products')->insert($data);
    }

    public function truncate(): void
    {
        DB::table('products')->truncate();
    }

    public function paginate(int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return DB::table('products')->paginate($perPage, ['*'], 'page', $page);
    }
}