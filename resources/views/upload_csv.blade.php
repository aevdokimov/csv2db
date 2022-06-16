@extends('layouts.main')

@section('content')
    @if (\Session::has('success'))
    <div class="row justify-content-md-center">
        <div class="col-md-6">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ \Session::get('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
    @endif

    @if ($errors->any())
    <div class="row justify-content-md-center">
        <div class="col-md-6">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
    @endif


    <div class="row justify-content-md-center">
        <div class="col-md-6">
            <form method="post" action="/" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="csvFile" class="form-label">CSV файл</label>
                    <input name="csv_file" class="form-control" type="file" id="csvFile" accept=".csv" required>
                </div>

                <div class="mb-3 form-check">
                    <input name="detect_encoding" value="1" type="checkbox" class="form-check-input" id="detectEncoding">
                    <label class="form-check-label" for="detectEncoding">Автоопределение кодировки</label>
                    <div class="form-text">Если кодировка отличается от UTF-8</div>
                </div>

                <div class="mb-3">
                    <label for="separator" class="form-label">Разделитель</label>
                    <select name="separator" id="separator" class="form-select">
                        <option value=";" selected>Точка с запятой</option>
                        <option value=",">Запятая</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Загрузить</button>
            </form>
        </div>
    </div>
@endsection
