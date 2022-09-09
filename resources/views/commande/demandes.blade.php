@extends('layouts.welcome')


@section('content')
    <demandes-commande-list inline-template :commande_prop="{{$commande}}">
        <div class="tw-container tw-mx-auto tw-mt-10">
            <h1 class="tw-text-4xl">@{{ commande.name}} </h1>
            <button type="button" class="btn tw-btn-dark" @click="générerBons()">Générer Bons Commande</button>
            <input type="file" ref="myFiles" @change="uploadFiles()" multiple>
            {{--
                <button type="button" class="btn w-btn-dark" data-toggle="modal" data-target="#modelId">
                Launch
                </button>


                <div class="modal fade" id="modelId" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Modal title</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                Body
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
            --}}

            <table class="table tw-mt-10">
                <thead>
                    <tr>
                        <th>Identifiant Demande</th>
                        <th>Nom de la Demande</th>
                        <th>Créé le </th>
                        <th>État</th>
                        <th>Total Demande</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="demande in commande.demandes">
                        <td scope="row">
                            <a :href="'/demande/' + demande.id">@{{ demande.id }}</a>
                        </td>
                        <td>@{{ demande.nom }}</td>
                        <td>@{{ demande.created_at | date }}</td>
                        <td>@{{ demande.etat }}</td>
                        <td>@{{ totalDemande(demande) | currency }}</td>
                        <td>
                            <i class="fas fa-edit tw-text-blue-500 tw-mr-3 tw-cursor-pointer" @editDemandeName()></i>
                            <i class="fas fa-times tw-text-red-500 tw-mr-3 tw-cursor-pointer"></i>
                            <a :href="'/demande/export/' + demande.id" class="tw-text-green-500 tw-cursor-pointer">
                                <i class="fas fa-download"></i>
                            </a>
                        </td>
                        <td>

                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="tw-flex tw-my-10 tw-py-5 tw-justify-center tw-items-center tw-sticky tw-bottom-0">
                <a href="/commande/{{$commande->id}}/prepa-demande" class="tw-btn tw-btn-dark tw-leading-none">Précédent</a>
                <a href="/commande/{{$commande->id}}/conflits" class="tw-btn tw-btn-dark tw-leading-none tw-ml-5">Suivant</a>
            </div>
        </div>
    </demandes-commande-list>
@endsection
