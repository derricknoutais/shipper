<h1>Services Tous Azimuts</h1>
<p>Auto Parts - Car Rental</p>
<p>Phone Number: +2411560855 / +24177158215</p>
<p>Whatsapp Number: +24107158215</p>
<p>E-Mail: servicesazimuts@gmail.com</p>
<p>Address: Rue Fréderic Dioni, Case d'Écoute Port-Gentil</p>

<h1>Bon Commande {{$commande->nom}}</h1>
<table>
    <thead>
        <tr>
            <th>Name</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $commande->nom }}</td>
        </tr>
        <tr></tr>
        <tr>
            <td>ID</td>
            <td>Product</td>
            <td>Quantity</td>
            <td>Unit Price</td>
            <td>Total</td>
        </tr>
        @foreach ($commande->sections as $section)
            @foreach ($section->sectionnables as $sectionnable)

                @if ($sectionnable->sectionnable_type === 'App\\Product')
                    <tr>
                        <td>{{ $loop->index }}</td>
                        @if($sectionnable->product )
                            <td>
                                {{ $sectionnable->product->name }}
                            </td>
                        @endif
                        <td>{{ $sectionnable->quantite }}</td>
                        <td>{{ $sectionnable->prix_achat / 165 }}</td>
                        <td>{{ ($sectionnable->prix_achat /165) * $sectionnable->quantite }}</td>
                    </tr>
                @else
                    <tr>
                        <td>{{ $sectionnable->id }}</td>
                        @if ($sectionnable->article)
                            <td>{{ $sectionnable->article->nom }}</td>
                        @endif
                        <td>{{ $sectionnable->quantite }}</td>
                        <td>{{ $sectionnable->prix_achat / 165 }}</td>
                        <td>{{ ($sectionnable->prix_achat /165) * $sectionnable->quantite }}</td>
                    </tr>
                @endif
            @endforeach
        @endforeach
        <tr></tr>
    </tbody>
</table>
