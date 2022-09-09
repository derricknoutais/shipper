@extends('layouts.welcome')


@section('content')
    <facture :data="{{ $commande->factures }}" inline-template>
        <div class="tw-container tw-mx-auto">
            <div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Montant Total</th>
                            <th>Bon Livraison</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="facture in data">
                            <td scope="row">
                                <a :href="'/facture/' + facture.id ">
                                    @{{ facture.nom }}
                                </a>
                            </td>
                            <td>@{{ total(facture) }}</td>
                            <td >
                                <a v-if="facture.bon_livraison_id" class="tw-btn tw-btn-dark"
                                    :href="'https://stapog.vendhq.com/consignment/' + facture.bon_livraison_id" target="_blank">Voir B.L dans Vend</a>
                            </td>
                        </tr>
                        <tr>
                            <td scope="row"></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </facture>
@endsection
