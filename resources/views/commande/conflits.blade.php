@extends('layouts.welcome')


@section('content')
<commande-conflits inline-template :commande_prop="{{ $commande }}">
    <div class="container">
        <h1 class="tw-text-3xl tw-text-center tw-mt-10">Liste des Conflits ( @{{ commande.conflits.length }} )</h1>
        <div class="mt-10 tw-flex">
            <button class="tw-btn tw-btn-dark tw-leading-none" @click="definirNonDisponible()">
                Définir les Non Disponibles
                <i class="fas fa-spinner fa-spin" v-if="isLoading.non_disponible"></i>
            </button>
            <button class="tw-btn tw-btn-dark tw-leading-none" @click="selectionnerChoixUnique()">
                Selectionner Choix Unique
                <i class="fas fa-spinner fa-spin" v-if="isLoading.non_disponible"></i>
            </button>
            <button class="tw-btn tw-btn-dark tw-leading-none" @click="selectionnerPrixBas()">
                Selectionner Moins Cher
                <i class="fas fa-spinner fa-spin" v-if="isLoading.non_disponible"></i>
            </button>
            <button class="tw-btn tw-btn-dark tw-leading-none" @click="showNotAvailable()">
                Voir Non Disponible
                <i class="fas fa-spinner fa-spin" v-if="isLoading.non_disponible"></i>
            </button>
        </div>

        <div id="accordianId" role="tablist" aria-multiselectable="true" class="tw-mt-10">
            <div class="card" v-for="conflit in commande.conflits">
                <div class="card-header" role="tab" id="section1HeaderId">
                    <h5 class="mb-0">
                        <a data-toggle="collapse" data-parent="#accordianId" :href="'#Conflit' + conflit.pivot.id" aria-expanded="true" aria-controls="section1ContentId" v-if="conflit.name">
                            @{{ conflit.name }} --- @{{ conflit.pivot.quantite }}
                        </a>

                    </h5>
                </div>
                <div :id="'Conflit' + conflit.pivot.id" class="collapse in" role="tabpanel" aria-labelledby="section1HeaderId">
                    <div class="card-body tw-flex tw-flex-col">
                        <ul class="list-group">
                            <li class="list-group-item tw-px-10 tw-flex tw-justify-between" v-for="(element, index) in conflit.elements_conflictuels">

                                {{-- ACCORDION DE CHAQUE ELEMENT CONFLICTUEL --}}
                                <div :id="'element_conflictuel_accord' + element.id " role="tablist" aria-multiselectable="true">
                                    <div class="card">
                                        <div class="card-header" role="tab" id="section1HeaderId">
                                            <h5 class="mb-0">
                                                <a data-toggle="collapse" :data-parent="'#element_conflictuel_accord' + element.id" :href="'#element_confictuel_content' + element.id" aria-expanded="true" aria-controls="section1ContentId">
                                                    <span v-if="element.demande">Fournisseur : @{{ element.demande.nom }}</span>
                                                    <span>Prix : @{{ element.offre | currency }}</span>
                                                    <span>Quantité Offerte : @{{ element.quantite_offerte }}</span>
                                                    <span v-if="element.differente_offre">Différente Offre : @{{ element.reference_differente_offre }}</span>
                                                </a>
                                            </h5>
                                        </div>
                                        <div :id="'element_confictuel_content' + element.id" class="collapse in" role="tabpanel" aria-labelledby="section1HeaderId">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label for="">Quantité Selectionnée</label>
                                                    <input type="text" class="form-control" v-model="element.quantite_offerte" aria-describedby="helpId" placeholder="">
                                                </div>
                                                <button type="button" name="" id="" class="btn btn-primary btn-block" @click="selectionnerElementConflictuel(element, index, conflit.elements_conflictuels)">Valider</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div id="accordianId" role="tablist" aria-multiselectable="true">
            <div class="card">
                <div class="card-header" role="tab" id="section1HeaderId">
                    <h5 class="mb-0">
                        <a data-toggle="collapse" data-parent="#accordianId" href="#section1ContentId" aria-expanded="true" aria-controls="section1ContentId">
                            Non Disponibles
                        </a>
                    </h5>
                </div>
                <div id="section1ContentId" class="collapse in" role="tabpanel" aria-labelledby="section1HeaderId">
                    <div class="card-body">
                        <table class="table" v-if="sectionnablesNotAvailable">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="sect in sectionnablesNotAvailable" v-if="sect.pivot.sectionnable_type === 'App\\Product' ">
                                    <td scope="row">@{{ sect.name }}</td>
                                    <td>@{{ sect.quantity }}</td>
                                </tr>
                                <tr v-for="sect in sectionnablesNotAvailable" v-if="sect.pivot.sectionnable_type === 'App\\Article' ">
                                    <td scope="row">@{{ sect.nom }}</td>
                                    <td>@{{ sect.quantity }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header" role="tab" id="section2HeaderId">
                    <h5 class="mb-0">
                        <a data-toggle="collapse" data-parent="#accordianId" href="#section2ContentId" aria-expanded="true" aria-controls="section2ContentId">
                  Section 2
                </a>
                    </h5>
                </div>
                <div id="section2ContentId" class="collapse in" role="tabpanel" aria-labelledby="section2HeaderId">
                    <div class="card-body">
                        Section 2 content
                    </div>
                </div>
            </div>
        </div>

        <div class="tw-flex tw-my-10 tw-py-5 tw-justify-center tw-items-center tw-sticky tw-bottom-0">
            <a href="/commande/{{$commande->id}}/demandes" class="tw-btn tw-btn-dark tw-leading-none">Précédent</a>
            <a href="/commande/{{$commande->id}}/bons-commandes" class="tw-btn tw-btn-dark tw-leading-none tw-ml-5">Suivant</a>
        </div>
    </div>
</commande-conflits>

@endsection
