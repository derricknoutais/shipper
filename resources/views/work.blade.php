@extends('layouts.welcome')


@section('content')
<ul class="list-group">
    @foreach ($produits as $produit)
        <li class="list-group-item">
            <a href="https://stapog.vendhq.com/product/{{ $produit->id }}" target="_blank">{{ $produit->name }}</a>
        </li>    
    @endforeach
</ul>
@endsection