@extends('layouts.welcome')


@section('content')
    <bons-commandes :bc_prop="{{$commande}}" inline-template>
        <div class="tw-container tw-mx-auto">
            <h1 class="tw-text-center tw-text-6xl">Bon Commande @{{ bcs.nom }}</h1>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Montant Total</th>
                        <th>Créé le </th>

                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="bc in bcs">
                        <td scope="row">
                            <a :href="'/commande/' + commande.id + '/bons-commandes/' + bc.id">
                                @{{ bc.nom }}
                            </a>

                        </td>
                        <td>@{{ totalBC(bc) | currency }}</td>
                        <td>@{{ bc.created_at }}</td>
                        <td>
                            <a :href="'/bons-commandes/export/' + bc.id" class="tw-text-green-500">
                                <i class="fas fa-download "></i>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="tw-text-right">Total</td>
                        <td>@{{ montantTotal | currency }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </bons-commandes>
@endsection
