@extends('base')

@section('title', $cityDetail->name)

@section('content')
    <div class="container-fluid">
        <h2 class="text-center mt-5">Detail obce</h2>
        <div class="d-flex justify-content-center">
            <div class="card card-detail">
                <div class="row m-0">
                    <div class="col-lg-6 bg-light order-2 order-lg-1">
                        <div class="row card-text">
                            <div class="col-lg-6">
                                <p><strong>Meno starostu:</strong></p>
                            </div>
                            <div class="col-lg-6">
                                <p>{{ $cityDetail->mayor_name }}</p>
                            </div>
                            <div class="col-lg-6">
                                <p><strong>Adresa obecného úradu:</strong></p>
                            </div>
                            <div class="col-lg-6">
                                <p>{{ $cityDetail->address }}</p>
                            </div>
                            <div class="col-lg-6">
                                <p><strong>Telefón:</strong></p>
                            </div>
                            <div class="col-lg-6">
                                <p>{{ $cityDetail->phone }}</p>
                            </div>
                            <div class="col-lg-6">
                                <p><strong>Mobil:</strong></p>
                            </div>
                            <div class="col-lg-6">
                                <p>{{ $cityDetail->mobile }}</p>
                            </div>
                            <div class="col-lg-6">
                                <p><strong>Fax:</strong></p>
                            </div>
                            <div class="col-lg-6">
                                <p>{{ $cityDetail->fax }}</p>
                            </div>
                            <div class="col-lg-6">
                                <p><strong>Email:</strong></p>
                            </div>
                            <div class="col-lg-6">
                                <p>{{ $cityDetail->email }}</p>
                            </div>
                            <div class="col-lg-6">
                                <p><strong>Web:</strong></p>
                            </div>
                            <div class="col-lg-6">
                                <p><a href="{{ 'https://' . $cityDetail->website }}" target="_blank">{{ $cityDetail->website }}</a></p>
                            </div>
                            <div class="col-lg-6">
                                <p><strong>Zemepisné súradnice:</strong></p>
                            </div>
                            <div class="col-lg-6">
                                <p>{{ $cityDetail->lat }}, {{ $cityDetail->lng }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 p-5 text-center d-flex flex-column align-items-center justify-content-center order-1 order-lg-2">
                        <div>
                            <img src="{{ asset('storage/' . $cityDetail->imagePath) }}" alt="{{ $cityDetail->name }}">
                        </div>
                        <div class="mt-3">
                            <h1 class="text-primary">{{ $cityDetail->name }}</h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
