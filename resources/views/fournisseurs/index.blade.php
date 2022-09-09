@extends('layouts.welcome')


@section('content')
    <fournisseur-index :fournisseurs_prop="{{ $fournisseurs }}" inline-template>
        <div class="tw-container tw-mx-auto tw-mt-10">
            <!-- Button trigger modal -->
            <button type="button" class="tw-btn tw-btn-dark " data-toggle="modal" data-target="#ajouter-fournisseur-modal">
              Ajouter Fournisseur
            </button>

            <h1 class="tw-text-3xl tw-mt-5">Mes Fournisseurs</h1>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>e-mail</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="fournisseur in fournisseurs">
                        <td scope="row">@{{fournisseur.nom}}</td>
                        <td>@{{fournisseur.email}}</td>
                        <td>@{{fournisseur.phone}}</td>
                        <td>
                            <i class="fas fa-edit tw-text-blue-500 tw-cursor-pointer" @click="openEditModal(fournisseur)"></i>
                            <i class="fas fa-trash tw-text-red-500 tw-cursor-pointer tw-ml-3" @click="openDeleteModal(fournisseur)"></i>
                        </td>
                    </tr>
                </tbody>
            </table>


            <!-- Modal -->
            <div class="modal fade" id="ajouter-fournisseur-modal" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true" @keyup.enter="ajouterFournisseur">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Modal title</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                              <label for="">Nom</label>
                              <input type="text" class="form-control"  aria-describedby="helpId" placeholder="" v-model="nouveau_fournisseur.nom">
                            </div>
                            <div class="form-group">
                              <label for="">e-mail</label>
                              <input type="text" class="form-control"  aria-describedby="helpId" placeholder="" v-model="nouveau_fournisseur.email">
                            </div>
                            <div class="form-group">
                              <label for="">Phone</label>
                              <input type="text" class="form-control"  aria-describedby="helpId" placeholder="" v-model="nouveau_fournisseur.phone">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal" >Fermer</button>
                            <button type="button" class="btn btn-primary" @click="ajouterFournisseur">Ajouter</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="update-fournisseur-modal" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true" @keyup.enter="updateFournisseur">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Modal title</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body" v-if="fournisseur_being_updated">
                            <div class="form-group">
                              <label for="">Nom</label>
                              <input type="text" class="form-control"  aria-describedby="helpId" placeholder="" v-model="fournisseur_being_updated.nom">
                            </div>
                            <div class="form-group">
                              <label for="">e-mail</label>
                              <input type="text" class="form-control"  aria-describedby="helpId" placeholder="" v-model="fournisseur_being_updated.email">
                            </div>
                            <div class="form-group">
                              <label for="">Phone</label>
                              <input type="text" class="form-control"  aria-describedby="helpId" placeholder="" v-model="fournisseur_being_updated.phone">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal" >Fermer</button>
                            <button type="button" class="btn btn-primary" @click="updateFournisseur">Mettre à Jour </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="delete-fournisseur-modal" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true" @keyup.enter="deleteFournisseur()">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Modal title</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                        </div>
                        <div class="modal-body" v-if="fournisseur_being_deleted">
                            <p>Êtes-vous sûr de vouloir supprimer " @{{fournisseur_being_deleted.nom}} " ? Toutes les ressources de ce fournisseur seront supprimées également!</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal" >Fermer</button>
                            <button type="button" class="btn btn-primary" @click="deleteFournisseur">Oui, Supprimer</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </fournisseur-index>
@endsection