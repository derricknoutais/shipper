@extends('layouts.welcome')


@section('content')

    <fournisseur-show :fournisseur_prop="{{$fournisseur}}" inline-template>
        <div class="tw-container tw-mx-auto tw-mt-10">
            <h1 class="tw-text-2xl tw-text-center">@{{ fournisseur.nom }}</h1>
            <p>Contact: @{{ fournisseur.phone}}</p>
            <p>E-Mail: @{{ fournisseur.email}}</p>


            <h2 class="tw-text-xl">Demandes</h2>

            <table class="table">
                <thead>
                    <tr>
                        <th>Identifiant</th>
                        <th>Total</th>
                        <th>Nombre de Produits</th>
                        <th>Date Création</th>
                        <th>Date Modification</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="demande in fournisseur.demandes">
                        <td scope="row"> @{{ demande.id }} </td>
                        <td> @{{ total(demande, 'demande') }} </td>
                        <td> @{{ demande.sectionnables.length }}</td>
                        <td> @{{ demande.created_at }} </td>
                        <td> @{{ demande.updated_at }} </td>
                    </tr>
                </tbody>
            </table>

            <h2 class="tw-text-xl">Bons Commandes</h2>

            <table class="table">
                <thead>
                    <tr>
                        <th>Identifiant</th>
                        <th>Total</th>
                        <th>Nombre de Produits</th>
                        <th>Date Création</th>
                        <th>Date Modification</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="bc in fournisseur.bons_commandes">
                        <td scope="row"> @{{ bc.id }} </td>
                        <td> @{{ total(bc, 'bc') }} </td>
                        <td> @{{ bc.sectionnables.length }}</td>
                        <td> @{{ bc.created_at }} </td>
                        <td> @{{ bc.updated_at }} </td>
                    </tr>
                </tbody>
            </table>
            
        </div>
    </fournisseur-show>

@endsection 