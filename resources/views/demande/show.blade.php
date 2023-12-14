@extends('layouts.welcome')


@section('content')
    <demande-show :demande_prop="{{ $demande }}" :demandes_prop="{{ $demandes }}" inline-template>
        <div class="tw-container-fluid tw-mx-10">
            <en-tete bg-color="tw-bg-green-500" nom=""></en-tete>


            <h1 class="tw-text-5xl tw-text-center tw-mt- tw-uppercase">Demande @{{ demande.nom }}</h1>

            <div class="tw-mt-10">
                <a :href="'/demande/export/' + demande.id" class="tw-btn tw-btn-dark">Télécharger .xlsx</a>
                <button class="tw-btn tw-btn-dark" @click="creerBonCommande(demande)">Créer Bon Commande</button>
            </div>

            <add-product :products="{{ $products }}" location="demande" :document="{{ $demande }}"></add-product>

            <ul class="tw-flex tw-justify-between tw-bg-gray-300 tw-py-6 tw-px-4 tw-mt-6">
                <li>
                    <label for="">ID</label>
                    <input type="checkbox" v-model="toDisplay.id">
                </li>
                <li>
                    <label for="">Produit (Francais)</label>
                    <input type="checkbox" value="name" v-model="toDisplay.product">
                </li>
                <li>
                    <label for="">Product (English)</label>
                    <input type="checkbox" v-model="toDisplay.traduction">
                </li>
                <li>
                    <label for="">Différente Offre</label>
                    <input type="checkbox" v-model="toDisplay.differentOffer">
                </li>
                <li>
                    <label for="">Quantité</label>
                    <input type="checkbox" v-model="toDisplay.quantite">
                </li>
                <li>
                    <label for="">Quantité Offerte</label>
                    <input type="checkbox" v-model="toDisplay.offeredQuantity">
                </li>
                <li>
                    <label for="">Offre (AED)</label>
                    <input type="checkbox" v-model="toDisplay.offerAed">
                </li>
                <li>
                    <label for="">Total (AED)</label>
                    <input type="checkbox" v-model="toDisplay.totalAed">
                </li>
                <li>
                    <label for="">Offre (XAF)</label>
                    <input type="checkbox" v-model="toDisplay.offerXaf">
                </li>
                <li>
                    <label for="">Total (XAF)</label>
                    <input type="checkbox" v-model="toDisplay.totalXaf">
                </li>
                <li>
                    <label for="">Actions</label>
                    <input type="checkbox" v-model="toDisplay.actions">
                </li>

            </ul>
            <table class="table">

                <thead>
                    <tr>
                        <th>
                            <i class="fas fa-chevron-down tw-cursor-pointer" @click="toggleAllDetails()"
                                v-if="detailsState"></i>
                            <i class="fas fa-chevron-right tw-cursor-pointer" @click="toggleAllDetails()" v-else></i>
                        </th>
                        <th v-show="toDisplay.id">Id</th>
                        <th v-show="toDisplay.product">Produit (Français)</th>
                        <th v-show="toDisplay.traduction">Product (English)</th>
                        <th v-show="toDisplay.differentOffer">Différente Offre?</th>
                        <th v-show="toDisplay.quantite">Quantité</th>
                        <th v-show="toDisplay.offeredQuantity">Quantité Offerte</th>
                        <th v-show="toDisplay.offerAed">Offre (AED)</th>
                        <th v-show="toDisplay.totalAed">Total (AED)</th>
                        <th v-show="toDisplay.offerXaf">Offre (XAF)</th>
                        <th v-show="toDisplay.totalXaf">Total (XAF)</th>
                        <th v-show="toDisplay.actions">Actions</th>
                    </tr>
                </thead>


                <tbody>
                    {{-- PRODUCT --}}
                    <template v-for="(sectionnable, index) in demande.sectionnables" v-if="sectionnable.product">
                        {{--  --}}
                        <tr class="">
                            {{-- Toggle --}}
                            <td>
                                <i class="fas fa-chevron-down tw-cursor-pointer" @click="toggleDetails(sectionnable)"
                                    v-if="sectionnable.displayDetails"></i>
                                <i class="fas fa-chevron-right tw-cursor-pointer" @click="toggleDetails(sectionnable)"
                                    v-if="! sectionnable.displayDetails"></i>
                            </td>
                            {{-- ID --}}
                            <td v-show="toDisplay.id">
                                @{{ sectionnable.pivot.id }}
                            </td>
                            {{-- Produit Français --}}
                            <td v-show="toDisplay.product">
                                @{{ sectionnable.product.variant_name }}
                            </td>
                            {{-- Product (Traduction) --}}
                            <td v-if="sectionnable.pivot.traduction" v-show="toDisplay.traduction">
                                <span class="tw-flex tw-items-center tw-justify-between">
                                    <span class="tw-w-full">
                                        <span v-if="! sectionnable.editing">
                                            @{{ sectionnable.pivot.traduction }}
                                        </span>
                                        <input v-else type="text" class="form-control tw-w-full tw-inline-block"
                                            v-model="sectionnable.pivot.traduction">
                                    </span>
                                    <span>
                                        <i v-if="! sectionnable.editing"
                                            class="fas fa-pen tw-mx-3 tw-cursor-pointer tw-text-blue-600"
                                            @click="editTraduction(sectionnable)"></i>
                                        <i v-else class="fas fa-save tw-mx-3 tw-cursor-pointer tw-text-blue-600"
                                            @click="saveTraduction(sectionnable)"></i>
                                    </span>

                                </span>
                            </td>
                            <td v-else-if="sectionnable.traduction" v-show="toDisplay.traduction">
                                <span class="tw-flex tw-items-center tw-justify-between">
                                    <span class="tw-w-full">
                                        <span v-if="! sectionnable.editing">
                                            @{{ sectionnable.traduction }}
                                        </span>
                                        <input v-else type="text" class="form-control tw-w-full tw-inline-block"
                                            v-model="sectionnable.pivot.traduction">
                                    </span>
                                    <span>
                                        <i v-if="! sectionnable.editing"
                                            class="fas fa-pen tw-mx-3 tw-cursor-pointer tw-text-blue-600"
                                            @click="editTraduction(sectionnable)"></i>
                                        <i v-else class="fas fa-save tw-mx-3 tw-cursor-pointer tw-text-blue-600"
                                            @click="saveTraduction(sectionnable)"></i>
                                    </span>

                                </span>
                            </td>
                            <td scope="row" v-else-if="sectionnable.product.handle_data" v-show="toDisplay.traduction">

                                <span class="tw-flex tw-items-center tw-justify-between">
                                    <span>
                                        @{{ sectionnable.product.handle_data.translation }}
                                        <span v-if="sectionnable.product.handle_data.display1">/
                                            @{{ sectionnable.product[sectionnable.product.handle_data.display1] }}</span>
                                        <span v-if="sectionnable.product.handle_data.display2">/
                                            @{{ sectionnable.product[sectionnable.product.handle_data.display2] }}</span>
                                        <span v-if="sectionnable.product.handle_data.display3">/
                                            @{{ sectionnable.product[sectionnable.product.handle_data.display3] }}</span>
                                    </span>
                                    <i class="fas fa-pen tw-mx-3 tw-cursor-pointer tw-text-blue-600"
                                        @click="editTraduction(sectionnable)"></i>
                                </span>
                            </td>
                            <td v-else v-show="toDisplay.traduction">

                            </td>
                            {{-- Différente Offre --}}
                            <td v-show="toDisplay.differentOffer">

                                <input class="form-check-input" type="checkbox"
                                    v-model="sectionnable.pivot.differente_offre"
                                    @change="updateSectionnable(sectionnable, 'differente_offre' , sectionnable.pivot.differente_offre)">
                                <input v-if="sectionnable.pivot.differente_offre" class="form-control tw-w-2/3"
                                    type="text" v-model="sectionnable.pivot.reference_differente_offre"
                                    @change="updateSectionnable(sectionnable, 'reference_differente_offre' ,sectionnable.pivot.reference_differente_offre)">
                            </td>
                            {{-- QUANTITE --}}
                            <td v-show="toDisplay.quantite">@{{ sectionnable.quantite }} </td>
                            {{-- QUANTITE OFFERTE --}}
                            <td v-show="toDisplay.offeredQuantity" class="tw-flex-col tw-items-center">
                                <div class="tw-flex">
                                    <i class="fas fa-arrow-down tw-text-red-500 tw-px-5"
                                        v-if="sectionnable.pivot.quantite_offerte < sectionnable.quantite"></i>
                                    <i class="fas fa-arrow-up tw-text-yellow-600 tw-px-5"
                                        v-if="sectionnable.pivot.quantite_offerte > sectionnable.quantite"></i>
                                    <i class="fas fa-thumbs-up tw-text-green-500 tw-px-5"
                                        v-if="sectionnable.pivot.quantite_offerte === sectionnable.quantite"></i>
                                    <input type="number" class="form-control" min="0" step="1"
                                        v-model.number="sectionnable.pivot.quantite_offerte"
                                        @input="enregisterOffre(sectionnable)">
                                </div>
                                <div class="tw-text-center tw-mt-5">
                                    <span
                                        :class="sectionnable.transfer_state === 'Sauvegarde Réussie' ? 'tw-text-green-500' :
                                            'tw-text-red-500'">@{{ sectionnable.transfer_state }}</span>
                                </div>
                            </td>
                            {{-- OFFRE (AED) --}}
                            <td v-show="toDisplay.offerAed">
                                <input type="number" class="form-control"
                                    :class="{
                                        'tw-border-red-500': sectionnable
                                            .hasError,
                                        'focus:tw-border-red-500': sectionnable.hasError
                                    }"
                                    min="0" step="1" v-model.number="sectionnable.pivot.offre"
                                    @input="enregisterOffre(sectionnable)" @keyup="convertToXaf(sectionnable, index)">
                            </td>
                            {{-- TOTAL (AED) --}}
                            <td v-show="toDisplay.totalAed">
                                <span v-if="sectionnable.hasError" class="tw-text-red-500"> Erreur! Vérifiez vos
                                    entrées!!!</span>
                                <span v-else>@{{ sectionnable.pivot.quantite_offerte * sectionnable.pivot.offre | currency }}</span>
                            </td>
                            {{-- OFFRE (AED) --}}
                            <td v-show="toDisplay.offerXaf">
                                <input type="number" class="form-control"
                                    :class="{
                                        'tw-border-red-500': sectionnable
                                            .hasError,
                                        'focus:tw-border-red-500': sectionnable.hasError
                                    }"
                                    min="0" step="1" :ref="'offerXaf_' + index"
                                    :value="sectionnable.pivot.offre * demande.commande.currency_exchange_rate"
                                    @keyup="convertToXaf(sectionnable, index)">
                            </td>
                            {{-- TOTAL (XAF) --}}
                            <td v-show="toDisplay.totalXaf">
                                <span v-if="sectionnable.hasError" class="tw-text-red-500"> Erreur! Vérifiez vos
                                    entrées!!!</span>
                                <span v-else>@{{ sectionnable.pivot.quantite_offerte * (sectionnable.pivot.offre * demande.commande.currency_exchange_rate).toFixed(0) | currency }}</span>
                            </td>
                            {{-- Actions --}}
                            <td v-show="toDisplay.actions">
                                <i class="fas fa-trash tw-text-red-500 tw-cursor-pointer"
                                    @click="openDeleteModal(sectionnable)"></i>
                                <i class="fa fa-share-square tw-cursor-pointer tw-text-green-500 tw-ml-2"
                                    aria-hidden="true" @click="openMoveModal(sectionnable, index)"></i>
                                <i class="fas fa-exclamation-triangle tw-text-yellow-600 tw-cursor-pointer tw-ml-2"
                                    v-if="sectionnable.pivot.checked === -1"></i>
                            </td>
                        </tr>
                        {{-- Ligne des Détails --}}
                        <tr v-show="sectionnable.displayDetails" class=" tw-bg-gray-400 tw-text-black tw-ml-6">
                            <td colspan="10">
                                {{-- Bons Commandes --}}
                                <h3 class="tw-mt-2 tw-font-bold tw-text-xl tw-bg-green-300 tw-block tw-py-3 tw-px-2"
                                    v-if="sectionnable.bon_commande.length > 0">
                                    Bon Commande Correspondants :
                                </h3>

                                <div class="tw-mt-3 tw-mb-6" v-if="sectionnable.bon_commande"
                                    v-for="bc in sectionnable.bon_commande">
                                    <a :href="'/commande/' + demande.commande_id + '/bons-commandes/' + bc.id">
                                        @{{ bc.nom }}
                                    </a>
                                </div>

                                <h3 class="tw-mt-2 tw-font-bold tw-text-xl tw-bg-red-300 tw-block tw-py-3 tw-px-2"
                                    v-if="sectionnable.demandes.length > 0">
                                    Demandes Correspondantes :
                                </h3>
                                <div class="tw-mt-5">
                                    <table>
                                        <thead>
                                            <th>Fournisseur</th>
                                            <th>Quantité</th>
                                            <th>Offre</th>
                                            <th>Reference Differente Offre</th>
                                            <th>Action</th>
                                            <th>État Transfert</th>
                                        </thead>
                                        <tbody>
                                            <tr v-if="sectionnable.demandes.length > 0"
                                                v-for="(dem, idx) in sectionnable.demandes">
                                                <td>
                                                    <i class="fas fa-exclamation-triangle tw-orange-500"
                                                        v-if="dem.pivot.differente_offre"></i>
                                                    <a :href="'/demande/' + dem.id">
                                                        @{{ dem.nom }}
                                                    </a>
                                                </td>
                                                <td>@{{ dem.pivot.quantite_offerte }}</td>
                                                <td>@{{ dem.pivot.offre }}</td>
                                                <td>
                                                    <span
                                                        v-if="dem.pivot.differente_offre && dem.pivot.reference_differente_offre"
                                                        class="tw-ml-5">@{{ dem.pivot.reference_differente_offre }}</span>
                                                </td>
                                                <td>
                                                    <button class="tw-px-5 tw-bg-gray-500 tw-ml-5 tw-rounded"
                                                        v-if="dem.pivot.quantite_offerte !== 0 && dem.pivot.offre !== 0 && sectionnable.bon_commande.length  === 0"
                                                        @click="ajouterSectionnableABonCommande(sectionnable, idx, dem)">Ajouter</button>

                                                    <button class="tw-px-5 tw-text-md tw-ml-5 tw-rounded tw-bg-blue-300 "
                                                        v-if="dem.pivot.quantite_offerte !== 0 && dem.pivot.offre !== 0 && sectionnable.bon_commande.length  !== 0 && dem.id !== sectionnable.bon_commande[0].demande_id"
                                                        @click="transfererSectionnableABonCommande(sectionnable, idx, dem)">Transférer</button>
                                                </td>
                                                <td>
                                                    <span class="tw-ml-5"
                                                        v-if="dem.transfer_state">@{{ dem.transfer_state }}</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="tw-mt-3" v-if="sectionnable.demandes.length > 0"
                                    v-for="(dem, idx) in sectionnable.demandes">






                                </div>

                            </td>

                        </tr>
                        {{-- Option Branding 2.0 <tr v-show="sectionnable.displayDetails" class=" tw-bg-gray-700 tw-text-white">
                        <td colspan=9 class="tw-py-10 tw-mx-10">
                            <div class="tw-flex tw-items-center">
                                <span v-for="brand in sectionnable.product.handle_data.brands " class="tw-w-1/3 tw-flex tw-items-center">
                                    Reference @{{ brand.nom }}
                                    <input name="" id="" class="form-control tw-inline-block tw-w-1/2 tw-mx-3" type="text">
                                    <i class="fas fa-check-circle tw-text-green-700 fa-lg"></i>
                                    <i class="fas fa-times-circle fa-lg tw-text-red-500 tw-mx-3"></i>
                                </span>

                            </div>
                        </td>
                    </tr> --}}
                    </template>

                    {{-- ARTICLES --}}
                    <template v-for="(sectionnable, index) in demande.sectionnables" v-if="sectionnable.article">
                        <tr>
                            <td>
                                <i class="fas fa-chevron-down tw-cursor-pointer" @click="toggleDetails(sectionnable)"
                                    v-if="sectionnable.displayDetails"></i>
                                <i class="fas fa-chevron-right tw-cursor-pointer" @click="toggleDetails(sectionnable)"
                                    v-if="! sectionnable.displayDetails"></i>
                            </td>
                            <td>@{{ sectionnable.pivot.id }}</td>
                            <td scope="row">@{{ sectionnable.article.nom }}</td>
                            <td>
                                <span v-if="sectionnable.pivot.traduction && ! sectionnable.editing">
                                    @{{ sectionnable.pivot.traduction }}
                                    <i class="fas fa-pen tw-mx-3 tw-cursor-pointer tw-text-blue-600"
                                        @click="editMode(sectionnable)"></i>
                                </span>
                                <span v-else-if="sectionnable.traduction && ! sectionnable.editing">
                                    @{{ sectionnable.traduction }}
                                    <i class="fas fa-pen tw-mx-3 tw-cursor-pointer tw-text-blue-600"
                                        @click="editMode(sectionnable)"></i>
                                </span>

                                <span v-if="sectionnable.editing" class="tw-flex tw-items-center">
                                    <input type="text" class="form-control tw-inline-block"
                                        v-model="sectionnable.pivot.traduction" @focus="sectionnable.editing = true" />
                                    <i class="fas fa-save tw-mx-2 tw-text-green-700 tw-cursor-pointer"
                                        @click="updateSectionnable(sectionnable, 'traduction', sectionnable.pivot.traduction)"></i>
                                </span>
                            </td>
                            <td>

                                <input class="form-check-input" type="checkbox"
                                    v-model="sectionnable.pivot.differente_offre"
                                    @change="updateSectionnable(sectionnable, 'differente_offre' , sectionnable.pivot.differente_offre)">

                                <input v-if="sectionnable.pivot.differente_offre" class="form-control tw-w-2/3"
                                    type="text" v-model="sectionnable.pivot.reference_differente_offre"
                                    @change="updateSectionnable(sectionnable, 'reference_differente_offre' ,sectionnable.pivot.reference_differente_offre)">
                            </td>
                            <td>@{{ sectionnable.quantite }} </td>
                            <td class="tw-flex-col tw-items-center">
                                <div class="tw-flex">
                                    <i class="fas fa-arrow-down tw-text-red-500 tw-px-5"
                                        v-if="sectionnable.pivot.quantite_offerte < sectionnable.quantite"></i>
                                    <i class="fas fa-arrow-up tw-text-yellow-600 tw-px-5"
                                        v-if="sectionnable.pivot.quantite_offerte > sectionnable.quantite"></i>
                                    <i class="fas fa-thumbs-up tw-text-green-500 tw-px-5"
                                        v-if="sectionnable.pivot.quantite_offerte === sectionnable.quantite"></i>
                                    <input type="number" class="form-control" min="0" step="1"
                                        v-model.number="sectionnable.pivot.quantite_offerte"
                                        @input="enregisterOffre(sectionnable)">
                                </div>
                                <div class="tw-text-center tw-mt-5">
                                    <span
                                        :class="sectionnable.transfer_state === 'Sauvegarde Réussie' ? 'tw-text-green-500' :
                                            'tw-text-red-500'">@{{ sectionnable.transfer_state }}</span>
                                </div>
                            </td>
                            <td>
                                <input type="text" class="form-control" v-model.number="sectionnable.pivot.offre"
                                    @input="enregisterOffre(sectionnable)">
                            </td>
                            <td>
                                @{{ sectionnable.pivot.quantite_offerte * sectionnable.pivot.offre | currency }}
                            </td>
                            <td>
                                <i class="fas fa-trash tw-text-red-500 tw-cursor-pointer"
                                    @click="openDeleteModal(sectionnable)"></i>
                                <i class="fa fa-share-square tw-cursor-pointer tw-text-green-500 tw-ml-2"
                                    aria-hidden="true" @click="openMoveModal(sectionnable, index)"></i>
                                <i class="fas fa-exclamation-triangle tw-text-red-500 tw-cursor-pointer"
                                    v-if="sectionnable.pivot.checked === -1"></i>
                            </td>
                        </tr>
                        {{-- <tr v-show="sectionnable.displayDetails" class=" tw-bg-gray-700 tw-text-white">
                        <td colspan=10>
                            <div class="tw-w-1/4" :class="sectionnable.bon_commande.length < 1 ? 'tw-bg-red-500' : 'tw-bg-green-500'">
                                <span v-if="sectionnable.bon_commande.length > 0">
                                    Bon Commande Correspondants :
                                </span>
                                <span v-if="sectionnable.bon_commande.length <= 0">
                                    Demandes Correspondantes :
                                </span>
                            </div>

                            <div class="tw-mt-5">
                                <a :href="'/commande/' + demande.commande_id + '/bons-commandes/' + bc.id" v-if="sectionnable.bon_commande" v-for="bc in sectionnable.bon_commande">
                                    @{{ bc.nom }}
                                </a>
                                <div class="tw-mt-3" v-if="sectionnable.bon_commande.length === 0 && sectionnable.demandes.length > 0" v-for="(dem, idx) in sectionnable.demandes">
                                    <i class="fas fa-exclamation-triangle tw-orange-500" v-if="dem.pivot.differente_offre"></i>
                                    <a :href="'/demande/' + dem.id">
                                        @{{ dem.nom }}
                                    </a>
                                    <span>@{{ dem.pivot.quantite_offerte }} x @{{ dem.pivot.offre }}</span>
                                    <span v-if="dem.pivot.differente_offre && dem.pivot.reference_differente_offre" class="tw-ml-5">@{{ dem.pivot.reference_differente_offre }}</span>
                                    <button
                                        class="tw-px-5 tw-bg-gray-500 tw-ml-5 tw-rounded"
                                        v-if="dem.pivot.quantite_offerte !== 0 && dem.pivot.offre !== 0"
                                        @click="ajouterSectionnableABonCommande(sectionnable, idx, dem)"
                                    >Ajouter</button>
                                    <span class="tw-ml-5" v-if="dem.transfer_state">@{{ dem.transfer_state }}</span>
                                </div>
                            </div>
                        </td>
                    </tr> --}}
                        {{-- Ligne des Détails --}}
                        <tr v-show="sectionnable.displayDetails" class=" tw-bg-gray-700 tw-text-white">
                            <td colspan=10>

                                <div class="tw-w-1/4"
                                    :class="sectionnable.bon_commande.length < 1 ? 'tw-bg-red-500' : 'tw-bg-green-500'">
                                    <span v-if="sectionnable.bon_commande.length > 0">
                                        Bon Commande Correspondants :
                                    </span>
                                </div>

                                <div v-if="sectionnable.bon_commande" v-for="bc in sectionnable.bon_commande">
                                    <a :href="'/commande/' + demande.commande_id + '/bons-commandes/' + bc.id">
                                        @{{ bc.nom }}
                                    </a>
                                </div>

                                <div class="tw-w-1/4 tw-mt-5 tw-bg-red-500">
                                    <span v-if="sectionnable.demandes.length > 0">
                                        Demandes Correspondantes :
                                    </span>
                                </div>

                                <div class="tw-mt-3" v-if="sectionnable.demandes.length > 0"
                                    v-for="(dem, idx) in sectionnable.demandes">

                                    <i class="fas fa-exclamation-triangle tw-orange-500"
                                        v-if="dem.pivot.differente_offre"></i>
                                    <a :href="'/demande/' + dem.id">
                                        @{{ dem.nom }}
                                    </a>
                                    <span>@{{ dem.pivot.quantite_offerte }} x @{{ dem.pivot.offre }}</span>
                                    <span v-if="dem.pivot.differente_offre && dem.pivot.reference_differente_offre"
                                        class="tw-ml-5">@{{ dem.pivot.reference_differente_offre }}</span>
                                    <button class="tw-px-5 tw-bg-gray-500 tw-ml-5 tw-rounded"
                                        v-if="dem.pivot.quantite_offerte !== 0 && dem.pivot.offre !== 0 && sectionnable.bon_commande.length  === 0"
                                        @click="ajouterSectionnableABonCommande(sectionnable, idx, dem)">Ajouter</button>
                                    <button class="tw-px-5 tw-bg-gray-500 tw-ml-5 tw-rounded"
                                        v-if="dem.pivot.quantite_offerte !== 0 && dem.pivot.offre !== 0 && sectionnable.bon_commande.length  !== 0 && dem.id !== sectionnable.bon_commande[0].demande_id"
                                        @click="transfererSectionnableABonCommande(sectionnable, idx, dem)">Transférer</button>

                                    <span class="tw-ml-5" v-if="dem.transfer_state">@{{ dem.transfer_state }}</span>
                                </div>

                            </td>

                        </tr>
                    </template>


                    <tr>
                        <td colspan="3" class="tw-text-right"></td>
                        <td class="tw-flex tw-justify-center">
                            <button class="tw-btn tw-btn-dark" @click="normaliserQuantités()">Normaliser
                                Quantités</button>
                        </td>
                        <td class="tw-text-right">Total</td>
                        <td>@{{ totalDemande | currency }}</td>
                    </tr>

                </tbody>


            </table>

            <!-- Modal Supprimé -->
            <div class="modal fade" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="modelTitleId"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Modal title</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p class="">Êtes-vous sûr de vouloir supprimer cet élement. Cette action est irréversible
                            </p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                            <button type="button" class="btn btn-primary" @click="removeSectionnable()">Oui,
                                Supprimer</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="demande-move-modal" tabindex="-1" role="dialog"
                aria-labelledby="modelTitleId" aria-hidden="true" @keydown.enter="saveDemande">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">DÉPLACER VERS UNE DEMANDE</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="">Nom de la Demande</label>
                                <multiselect v-model="demande_to_move_to" :options="demandes" label="nom"
                                    :searchable="true" :close-on-select="true" :show-labels="true"
                                    placeholder="Pick a value"></multiselect>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                            <button type="button" class="btn btn-primary"
                                @click="deplacerSectionnable()">Déplacer</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </demande-show>
@endsection
