<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\UploadCsvData;
use App\Http\Requests\UploadCsvRequest;
use App\Jobs\ImportProductsCsv;
use App\Repositories\ProductRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function getUploadCsvPage()
    {
        return view('upload_csv');
    }

    public function postUploadCsv(UploadCsvRequest $request)
    {
        $path = Storage::path(
            $request->csv_file->storeAs('csvses', 'products.csv')
        );

        $uploadCsvData = new UploadCsvData(
            $path,
            $request->separator,
            $request->boolean('detect_encoding')
        );

        ImportProductsCsv::dispatch($uploadCsvData);

        return redirect()->back()->with('success', 'Данные появятся после обработки');
    }

    public function getProducts(Request $request, ProductRepositoryInterface $repository)
    {
        $paginator = $repository->paginate(15, $request->get('page', 1));
        
        return view('products_in_db', compact('paginator'));
    }
}
