@extends('layouts.welcome')


@section('content')
    <prepa-demande :commande_prop="{{ $commande }}" :commandes_prop="{{ $commandes }}"
        :fournisseurs_prop="{{ $fournisseurs }}" inline-template>
        <div class="tw-flex tw-flex-col">

            <div class="tw-flex">
                {{-- Prepa Demande --}}
                <div class="tw-mx-auto tw-container-fluid tw-w-full tw-bg-gray-300">
                    {{-- Titre --}}
                    <h1 class="tw-text-4xl tw-mt-10 tw-tracking-widest tw-font-hairline tw-font- tw-text-center">Prepa -
                        Demandes - {{ $commande->name }}</h1>
                    {{-- Bouton Flottant Ajouter a Demande --}}
                    <div class="tw-flex tw-mt-5 tw-py-1 tw-justify-center tw-items-center tw-sticky tw-top-0"
                        v-if="selected_products.length > 0">
                        <button class="tw-btn tw-btn-dark tw-leading-none tw-ml-5" data-toggle="modal"
                            data-target="#ajouter-demande-modal">Ajouter à <span>@{{ selected_products.length }}</span> éléments à
                            Demande ... <i class="fas fa-mail-bulk tw-ml-2"></i></button>
                    </div>
                    {{-- Bouttons Options --}}
                    <div class="tw-mt-24 tw-bg-gray-500 tw-py-10 tw-w-full">
                        <button class="tw-btn tw-btn-dark tw-leading-none tw-ml-5" @click="filter_demandé()">Déja
                            Demandé</button>
                        <button class="tw-btn tw-btn-dark tw-leading-none tw-ml-5" @click="filter_non_demandé()">Pas encore
                            Demandé</button>
                        {{-- <button
                        class="tw-btn tw-btn-dark tw-leading-none tw-ml-5"
                        @click="openMagicSelectorModal"
                    > --}}
                        <i class="fas fa-wand"></i>Selection Automatique
                        </button>
                        <button class="tw-btn tw-btn-dark tw-leading-none tw-ml-5"
                            @click="réinitialiser()">Réinitialiser</button>
                        <button v-if="! demande_banner "
                            class="tw-btn tw-bg-green-500 tw-text-white tw-leading-none tw-ml-5"
                            @click="demande_banner = true">Voir Demandes</button>
                        <button v-if=" demande_banner " class="tw-btn tw-bg-green-500 tw-text-white tw-leading-none tw-ml-5"
                            @click="demande_banner = false">Cacher Demandes</button>
                    </div>


                    {{-- Sections --}}
                    <div class="tw-bg-gray-300 tw-px-32" v-for="section in commande.sections"
                        v-show="filtered.sections.length <= 0">
                        <div class="tw-flex tw-items-center tw-mt-24 tw-cursor-pointer" @click="toggleSection(section)">
                            <i class="fas fa-chevron-down" v-if="section.show"></i>
                            <i class="fas fa-chevron-right" v-else></i>
                            <h4 class="tw-text-2xl tw-ml-4 tw-font-thin tw-tracking-wide">
                                @{{ section.nom }} [
                                <span class="tw-text-blue-500">@{{ niveauDAchevement(section, 'niveau') }}</span>
                                <span class="tw-text-red-500">@{{ niveauDAchevement(section, 'pourcentage') }}%</span>
                                ]
                            </h4>
                        </div>

                        <table class="table" v-show="section.show">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" @click="checkAll(section)" v-model="section.checkAll">
                                    </th>
                                    <th>
                                    </th>
                                    <th>Identifiant</th>
                                    <th>Nom</th>
                                    <th>Traduction</th>
                                    <th>Quantité</th>
                                    <th># Demande</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-for="produit in section.products">
                                    <tr :class="produit.demandes.length > 0 ? 'tw-text-red-500' : ''">
                                        <td class="tw-flex tw-items-center">
                                            <input type="checkbox" v-model="selected_products" :value="produit">
                                        </td>
                                        <td class="tw-ml-3">
                                            <i class="fas fa-chevron-down tw-cursor-pointer" v-if="produit.displayDetails"
                                                @click="toggleRow(produit)"></i>
                                            <i class="fas fa-chevron-right tw-cursor-pointer" v-else
                                                @click="toggleRow(produit)"></i>
                                        </td>
                                        <td scope="row">
                                            <a :href="'https://stapog.vendhq.com/product/' + produit.id" target="_blank">
                                                @{{ produit.id }}
                                            </a>
                                        </td>
                                        <td>@{{ produit.variant_name }}</td>
                                        <td v-if="produit.pivot.traduction">
                                            <span class="tw-flex tw-items-center tw-justify-between">
                                                <span class="tw-w-full">
                                                    <span v-if="! produit.editing">
                                                        @{{ produit.pivot.traduction }}
                                                    </span>
                                                    <input v-else type="text"
                                                        class="form-control tw-w-full tw-inline-block"
                                                        v-model="produit.pivot.traduction">
                                                </span>
                                                <span>
                                                    <i v-if="! produit.editing"
                                                        class="fas fa-pen tw-mx-3 tw-cursor-pointer tw-text-blue-600"
                                                        @click="editTraduction(produit)"></i>
                                                    <i v-else class="fas fa-save tw-mx-3 tw-cursor-pointer tw-text-blue-600"
                                                        @click="saveTraduction(produit)"></i>
                                                </span>

                                            </span>
                                        </td>
                                        <td scope="row" v-else>

                                            <span class="tw-flex tw-items-center tw-justify-between">
                                                <span v-if="! produit.editing && produit.handle">
                                                    <span v-if="produit.handle.translation">
                                                        @{{ produit.handle.translation }}
                                                    </span>
                                                    <span v-if="produit.handle.display1">/ @{{ produit[produit.handle.display1] }}</span>
                                                    <span v-if="produit.handle.display2">/ @{{ produit[produit.handle.display2] }}</span>
                                                    <span v-if="produit.handle.display3">/ @{{ produit[produit.handle.display3] }}</span>
                                                </span>
                                                <input v-else type="text" class="form-control tw-w-full tw-inline-block"
                                                    v-model="produit.pivot.traduction">
                                                <i v-if="! produit.editing"
                                                    class="fas fa-pen tw-mx-3 tw-cursor-pointer tw-text-blue-600"
                                                    @click="editTraduction(produit)"></i>
                                                <i v-else class="fas fa-save tw-mx-3 tw-cursor-pointer tw-text-blue-600"
                                                    @click="saveTraduction(produit)"></i>
                                            </span>
                                        </td>


                                        <td>@{{ produit.pivot.quantite }}</td>
                                        <td>@{{ produit.demandes.length }}</td>
                                    </tr>
                                    {{-- Ligne des Détails --}}
                                    <tr v-if="produit.displayDetails">
                                        <td></td>
                                        <td></td>

                                        <td colspan=1 class=" tw-bg-gray-700 tw-text-white">

                                            <ul>
                                                <li v-for="demande in produit.demandes"
                                                    class="tw-flex tw-items-center tw-justify-between">
                                                    <span>@{{ demande.nom }}</span>
                                                    <i class="tw-text-red-500 fas fa-trash tw-cursor-pointer tw-ml-3"
                                                        @click="remove(produit, demande)"></i>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                </template>
                                <template v-for="article in section.articles">
                                    <tr :class="article.demandes.length > 0 ? 'tw-text-red-500' : ''">
                                        <td>
                                            <input type="checkbox" v-model="selected_products" :value="article">
                                        </td>
                                        <td class="tw-ml-3">
                                            <i class="fas fa-chevron-down tw-cursor-pointer" v-if="article.displayDetails"
                                                @click="toggleRow(article)"></i>
                                            <i class="fas fa-chevron-right tw-cursor-pointer" v-else
                                                @click="toggleRow(article)"></i>
                                        </td>
                                        <td scope="row">
                                            <a :href="'https://stapog.com/fiche-renseignement/' + article.fiche_renseignement_id"
                                                target="_blank">
                                                0af7n3os-{{ rand(1000, 9999) }}-ff13-kj{{ rand(100, 999) }}-@{{ article.fiche_renseignement_id }}
                                            </a>
                                        </td>
                                        <td>
                                            @{{ article.nom }}
                                            <button class="tw-bg-green-500 tw-px-3"
                                                @click="transferNameAsTraduction(article)">
                                                <i class="fa fa-arrow-right"></i>
                                            </button>
                                        </td>
                                        <td>
                                            <span class="tw-flex tw-items-center tw-justify-between">
                                                <span class="tw-w-full">
                                                    <span v-if="! article.editing">
                                                        @{{ article.pivot.traduction }}
                                                    </span>
                                                    <input v-else type="text"
                                                        class="form-control tw-w-full tw-inline-block"
                                                        v-model="article.pivot.traduction">
                                                </span>
                                                <span>
                                                    <i v-if="! article.editing"
                                                        class="fas fa-pen tw-mx-3 tw-cursor-pointer tw-text-blue-600"
                                                        @click="editTraduction(article)"></i>
                                                    <i v-else
                                                        class="fas fa-save tw-mx-3 tw-cursor-pointer tw-text-blue-600"
                                                        @click="saveTraduction(article)"></i>
                                                </span>

                                            </span>
                                        </td>
                                        <td>@{{ article.pivot.quantite }}</td>
                                        <td>@{{ article.demandes.length }}</td>
                                    </tr>
                                    <tr v-if="article.displayDetails">
                                        <td></td>
                                        <td></td>

                                        <td colspan=1 class=" tw-bg-gray-700 tw-text-white">

                                            <ul>
                                                <li v-for="demande in article.demandes"
                                                    class="tw-flex tw-items-center tw-justify-between">
                                                    <span>@{{ demande.nom }}</span>
                                                    <i class="tw-text-red-500 fas fa-trash tw-cursor-pointer tw-ml-3"
                                                        @click="remove(article, demande)"></i>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                </template>

                            </tbody>
                        </table>
                    </div>
                    <div v-for="section in filtered.sections" v-show="filtered.sections.length > 0">
                        <h4 class="tw-text-2xl tw-mt-24 tw-font-thin tw-tracking-wide">@{{ section.nom }}</h4>

                        <table class="table">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" @click="checkAll(section)" v-model="section.checkAll">
                                    </th>
                                    <th>Identifiant</th>
                                    <th>Nom </th>
                                    <th>Traduction </th>
                                    <th>Quantité</th>
                                    <th>Prix Achat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="produit in section.products">
                                    <td>
                                        <input type="checkbox" v-model="selected_products" :value="produit">
                                    </td>
                                    <td scope="row">
                                        <a :href="'https://stapog.vendhq.com/product/' + produit.id" target="_blank">
                                            @{{ produit.id }}
                                        </a>
                                    </td>
                                    <td>@{{ produit.name }}</td>
                                    <td>section.products</td>
                                    <td>@{{ produit.pivot.quantite }}</td>
                                    <td></td>
                                </tr>

                                <tr v-for="produit in section.articles">
                                    <td>
                                        <input type="checkbox" v-model="selected_products" :value="produit">
                                    </td>
                                    <td scope="row">
                                        <a :href="'https://stapog.com/fiche-renseignement/' + produit.fiche_renseignement_id"
                                            target="_blank">
                                            0af7n3os-{{ rand(1000, 9999) }}-ff13-kj{{ rand(100, 999) }}-@{{ produit.fiche_renseignement_id }}
                                        </a>
                                    </td>
                                    <td>@{{ produit.nom }}</td>
                                    <td>section.articles</td>
                                    <td>@{{ produit.pivot.quantite }}</td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Demandes --}}
                <div v-if="demande_banner"
                    class="tw-mx-auto tw-container tw-w-1/4 tw-flex tw-flex-col tw-items-center tw-bg-gray-300 tw-border tw-border-gray-400 tw-border-r-0 tw-border-t-0 tw-border-b-0">

                    <h1 class="tw-text-3xl tw-uppercase tw-tracking-wide tw-text-center tw-my-5 ">Demandes</h1>

                    <button type="button" name="" id="" class="tw-btn tw-btn-dark tw-uppercase"
                        data-toggle="modal" data-target="#demande-modal">Ajouter Une Demande</button>
                    <button type="button" name="" id=""
                        class="tw-mt-5 tw-btn tw-btn-dark tw-uppercase " @click="dispatchProduits()">
                        <i class="fas fa-spinner fa-spin" v-if="isLoading.toutesDemandes"></i>
                        <span>
                            Génerer Toutes Les Demandes
                        </span>
                    </button>
                    <button type="button" class="tw-mt-5 tw-btn tw-btn-dark tw-uppercase" @click="prendreOffreDe(5)">
                        {{-- <i class="fas fa-spinner fa-spin" v-if="isLoading.toutesDemandes"></i> --}}
                        Offres a Partir de ...
                    </button>

                    <div class="tw-w-3/4">
                        <ul class="tw-mt-10">
                            <a :href="'/demande/' + demande.id" v-for="demande in commande.demandes">
                                <li class="list-group-item d-flex justify-content-between align-items-center">

                                    @{{ demande.nom }}

                                    <span class="badge badge-secondary badge-pill tw-ml-8" v-if="demande.sectionnables">
                                        @{{ demande.sectionnables.length }}
                                    </span>
                                </li>
                            </a>
                        </ul>
                    </div>
                </div>

                {{-- Modal Demandes --}}
                <div class="modal fade" id="demande-modal" tabindex="-1" role="dialog" aria-labelledby="modelTitleId"
                    aria-hidden="true" @keydown.enter="saveDemande">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">AJOUTER UNE DEMANDE</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                        aria-hidden="true">&times;</span></button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="">Nom de la Demande</label>
                                    <multiselect v-model="selected_fournisseur" :options="fournisseurs" label="nom"
                                        :searchable="true" :close-on-select="true" :show-labels="true"
                                        placeholder="Pick a value"></multiselect>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                                <button type="button" class="btn btn-primary" @click="saveDemande">Enregistrer</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Boutons <-- Précédent - Suivant --> --}}
            <div class="tw-flex tw-p-5 tw-justify-center tw-items-center tw-sticky tw-bottom-0 tw-bg-gray-500">
                <div class="tw-w-1/3 ">
                    <p class="tw-text-lg">Transfert @{{ transfert.ajoute }}/@{{ transfert.nombreAjouts }}</p>
                </div>
                <div class="tw-w-1/3 tw-text-center">
                    <a href="/commande/{{ $commande->id }}" class="tw-btn tw-btn-dark tw-leading-none">Précédent</a>
                    <a href="/commande/{{ $commande->id }}/demandes"
                        class="tw-btn tw-btn-dark tw-leading-none tw-ml-5">Suivant</a>
                </div>
                <div class="tw-w-1/3">

                </div>
            </div>`

            <div class="modal fade" id="ajouter-demande-modal" tabindex="-1" role="dialog"
                aria-labelledby="modelTitleId" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">AJOUTER A DEMANDE</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="tw-px-5 tw-flex tw-items-center tw-h-6" v-for="demande in commande.demandes">
                                <input type="checkbox" :value="demande" v-model="selected_demandes"
                                    class="tw-mx-5">
                                <label for="">@{{ demande.nom }}</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" @click="addProductsToDemandes">Save</button>
                        </div>
                    </div>
                </div>
            </div>


            <!-- This example requires Tailwind CSS v2.0+ -->
            <div ref="modal" v-show="magicSelectorModal" class="tw-relative tw-z-10" aria-labelledby="modal-title"
                role="dialog" aria-modal="true" @keydown.escape="closeMagicSelectorModal" tabindex="-1">

                <div class="tw-fixed tw-inset-0 tw-bg-gray-500 tw-opacity-75 tw-transition-opacity"></div>

                <div class="tw-fixed tw-z-10 tw-inset-0 tw-overflow-y-auto">
                    <div
                        class="tw-flex tw-items-end tw-justify-center tw-min-h-screen tw-pt-4 tw-px-4 tw-pb-20 tw-text-center sm:tw-block sm:tw-p-0">
                        <!-- This element is to trick the browser into centering the modal tw-contents. -->
                        <span class="tw-hidden sm:tw-inline-block sm:tw-align-middle sm:tw-h-screen"
                            aria-hidden="true">&#8203;</span>
                        <div
                            class="tw-relative tw-inline-block tw-align-bottom tw-bg-white tw-rounded-lg tw-px-4 tw-pt-5 tw-pb-4 tw-text-left tw-overflow-hidden tw-shadow-xl tw-transform tw-transition-all sm:tw-my-8 sm:tw-align-middle sm:tw-max-w-lg sm:tw-w-full sm:tw-p-6">
                            <div>
                                <div
                                    class="tw-mx-auto tw-flex tw-items-center tw-justify-center tw-h-12 tw-w-12 tw-rounded-full tw-bg-green-100">
                                    <!-- Heroicon name: outline/check -->
                                    <select name="" id="">
                                        <option value="" v-for="section in commande.sections">
                                            @{{ section.nom }}</option>
                                    </select>
                                </div>
                                <div class="tw-mt-3 tw-text-center sm:tw-mt-5">
                                    {{-- TITRE --}}
                                    <h3 class="tw-text-lg tw-leading-6 tw-font-medium tw-text-gray-900" id="modal-title">
                                        Payment
                                        successful</h3>
                                    <div class="tw-mt-2">
                                        <!-- This example requires Tailwind CSS v2.0+ -->
                                        <ul role="list" class="divide-y divide-gray-200">
                                            <li class="flex py-4" v-for="">
                                                <img class="w-10 h-10 rounded-full"
                                                    src="https://images.unsplash.com/photo-1491528323818-fdd1faba62cc?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
                                                    alt="">
                                                <div class="ml-3">
                                                    <p class="text-sm font-medium text-gray-900">Calvin Hawkins</p>
                                                    <p class="text-sm text-gray-500">calvin.hawkins@example.com</p>
                                                </div>
                                            </li>


                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="tw-mt-5 sm:tw-mt-6 sm:tw-grid sm:tw-grid-cols-2 sm:tw-gap-3 sm:tw-grid-flow-row-dense">
                                <button type="button"
                                    class="tw-w-full tw-inline-flex tw-justify-center tw-rounded-md tw-border tw-border-transparent tw-shadow-sm tw-px-4 tw-py-2 tw-bg-indigo-600 tw-text-base tw-font-medium tw-text-white hover:tw-bg-indigo-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-indigo-500 sm:tw-col-start-2 sm:tw-text-sm">Deactivate</button>
                                <button type="button" @click="closeMagicSelectorModal"
                                    class="tw-mt-3 tw-w-full tw-inline-flex tw-justify-center tw-rounded-md tw-border tw-border-gray-300 tw-shadow-sm tw-px-4 tw-py-2 tw-bg-white tw-text-base tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-indigo-500 sm:tw-mt-0 sm:tw-col-start-1 sm:tw-text-sm">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



        </div>

    </prepa-demande>
@endsection
