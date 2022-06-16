@extends('layouts.main')

@section('content')
    <div class="row">
        <div class="col-md-12">
            @if ($paginator->isEmpty())
            <div class="alert alert-light" role="alert">В базе нет данных</div>
            @else
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Код</th>
                        <th>Наименование</th>
                        <th>Уровень1</th>
                        <th>Уровень2</th>
                        <th>Уровень3</th>
                        <th>Цена</th>
                        <th>ЦенаСП</th>
                        <th>Количество</th>
                        <th>Поля свойств</th>
                        <th>Совместные покупки</th>
                        <th>Единица измерения</th>
                        <th>Картинка</th>
                        <th>Выводить на главной</th>
                        <th>Описание</th>
                    </tr>
                </thead>
                @foreach($paginator->items() as $product)
                <tr>
                    <td>{{ $product->sku }}</td>
                    <td>{{ $product->title }}</td>
                    <td>{{ $product->level_1 }}</td>
                    <td>{{ $product->level_2 }}</td>
                    <td>{{ $product->level_3 }}</td>
                    <td>{{ $product->price }}</td>
                    <td>{{ $product->price_sp }}</td>
                    <td>{{ $product->count }}</td>
                    <td>{{ $product->properties }}</td>
                    <td>{{ $product->joint_purchases }}</td>
                    <td>{{ $product->units }}</td>
                    <td>{{ $product->img }}</td>
                    <td>{{ $product->on_homepage }}</td>
                    <td>{{ $product->description }}</td>
                </tr>
                @endforeach
            </table>
            @endif
            <div class="d-flex justify-content-center">
                {!! $paginator->links() !!}
            </div>
        </div>
    </div>
@endsection