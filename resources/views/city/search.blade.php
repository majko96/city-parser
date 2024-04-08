@extends('base')

@section('content')
    <div class="search-section d-flex justify-content-center align-items-center flex-column">
        <h1 class="text-center">
            Vyhladať v databáze obcí
        </h1>
{{--        <select class="select2 form-control mt-3 text-left" aria-label="Zadajte názov"></select>--}}
        <input type="text" id="autocomplete-input" class="form-control" placeholder="Zadajte názov">
    </div>

    @vite('resources/js/search.js')
@endsection
