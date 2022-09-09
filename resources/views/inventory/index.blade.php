@extends('layouts.welcome')


@section('content')
    <inventory-index inline-template>
        <section>
            <section class="tw-flex tw-flex-col tw-items-center tw-bg-gray-800 tw-text-white">
                <p class="tw-text-4xl tw-text-bold tw-my-4">Mes Inventaires</p>
                <p class="tw-mb-4">
                    Les Templates vous permettent d'enregistrer un groupe de produits que vous pouvez réutiliser dans vos commandes
                </p>
                <button class="tw-btn tw-btn-white tw-mb-4" data-toggle="modal" data-target="#modelId">Créer Inventaire</button>
                <!-- Modal -->
            </section>

            <div class="modal fade" id="modelId" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Enregistrer Template en tant que...</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        {{-- <form  method="POST"> --}}
                        <div class="modal-body">

                                @csrf
                                <div class="form-group">
                                <label for="">Nom*</label>
                                <input type="text" v-model="name" class="form-control" name="name" id="" aria-describedby="helpId" placeholder="">
                                </div>
                                <div class="form-group">
                                    <label for="">*</label>
                                    <multiselect  v-model="template" :options="{{ $templates }}" ref="SearchBar" :searchable="true" :close-on-select="true" :show-labels="false"
                                    placeholder="Pick a value" label="name" ></multiselect>
                                </div>
                                <ul class="list-group" v-if="template">
                                    <li class="list-group-item" v-for="product in template.products">@{{ product.name }}</li>
                                    
                                </ul>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                            <button type="submit" class="btn btn-primary" @click="sendForm()">Enregistrer</button>
                        </div>
                        {{-- </form> --}}
                    </div>
                </div>
            </div>
        </section>
    </inventory-index>
    


@endsection