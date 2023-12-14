<h1>Services Tous Azimuts</h1>
<p>Auto Parts - Car Rental</p>
<p>Phone Number: +2411560855 / +24177158215</p>
<p>Whatsapp Number: +24107158215</p>
<p>E-Mail: servicesazimuts@gmail.com</p>
<p>Address: Rue Fréderic Dioni, Case d'Écoute Port-Gentil</p>

<h1>Bon Commande {{ $bonCommande->nom }}</h1>
<table>
    <thead>
        <tr>
            <th>Name</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $bonCommande->nom }}</td>
        </tr>
        <tr></tr>
        <tr>
            <td>Ligne Nº</td>
            <td>Product</td>
            <td>Quantity</td>
            <td>Unit Price</td>
            <td>Total</td>
        </tr>
        @foreach ($bonCommande->sectionnables as $sectionnable)

            @if ($sectionnable->sectionnable_type === 'App\\Product')
                <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    {{-- <td>{{ $sectionnable->pivot->id }}</td> --}}
                    @if ($sectionnable->pivot->traduction)
                        <td>{{ $sectionnable->pivot->traduction }}</td>
                    @elseif($sectionnable->traduction)
                        <td>{{ $sectionnable->traduction }}</td>
                    @elseif(!$sectionnable->product)
                        <td>SUPP/SUPP</td>
                    @elseif($sectionnable->product->handle)
                        <td>
                            {{ $sectionnable->product->handle->translation }}
                            @if ($sectionnable->product->handle->display1)
                                <span>/ {{ $sectionnable->product[$sectionnable->product->handle->display1] }}</span>
                            @endif
                            @if ($sectionnable->product->handle->display2)
                                <span>/ {{ $sectionnable->product[$sectionnable->product->handle->display2] }}</span>
                            @endif
                            @if ($sectionnable->product->handle->display3)
                                <span>/ {{ $sectionnable->product[$sectionnable->product->handle->display3] }}</span>
                            @endif
                        </td>
                    @endif
                    <td>{{ $sectionnable->pivot->quantite }}</td>
                    <td>{{ $sectionnable->pivot->prix_achat }}</td>
                    <td>{{ $sectionnable->pivot->prix_achat * $sectionnable->quantite }}</td>
                </tr>
            @else
                <tr>
                    <td>{{ $sectionnable->pivot->id }}</td>
                    @if ($sectionnable->pivot->traduction)
                        <td>{{ $sectionnable->pivot->traduction }}</td>
                    @elseif($sectionnable->traduction)
                        <td>{{ $sectionnable->traduction }}</td>
                    @else
                        <td>{{ $sectionnable->article->nom }}</td>
                    @endif
                    <td>{{ $sectionnable->pivot->quantite }}</td>
                    <td>{{ $sectionnable->pivot->prix_achat }}</td>
                    <td>{{ $sectionnable->pivot->prix_achat * $sectionnable->quantite }}</td>
                </tr>
            @endif
        @endforeach
        <tr></tr>
    </tbody>
</table>
