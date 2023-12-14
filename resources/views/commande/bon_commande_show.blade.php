@extends('layouts.welcome')

@section('content')
    <bon-commande-show :bc_prop="{{ $bc }}" :commande_prop="{{ $commande }}" inline-template>
        <div class="tw-container tw-mx-auto tw-flex tw-flex-col">
            {{-- En Tete --}}
            <en-tete></en-tete>
            {{-- Titre Document --}}
            <h1 class="tw-text-center tw-text-5xl">@{{ bc.nom }}</h1>
            {{-- Boutons de Fonction --}}
            <div class="tw-mt-5">
                <a href="/bons-commandes/export/{{ $bc->id }}" class="tw-btn tw-inline-block tw-btn-dark">Export
                    .xlsx</a>
                <a class="tw-btn tw-inline-block tw-btn-dark" :href="'/facture/' + bc.facture_id" v-if="bc.facture_id">Voir
                    Facture</a>
                <button class="tw-btn tw-inline-block tw-btn-dark" @click="createInvoice()" v-else>Créer Facture</button>
                {{-- Voir Facture --}}


                {{-- <button v-if="! editMode" class="tw-btn tw-inline-block tw-btn-dark tw-mt-5" @click="toggleEditMode()">Modifier</button>
                <button v-else class="tw-btn tw-inline-block tw-btn-dark tw-mt-5" @click="updateAllEdited()">Enregistrer</button> --}}
            </div>

            {{-- Sous-Titre --}}
            <h2 class="tw-text-xl tw-mt-20">Liste des Produits</h2>
            {{-- Selecteur --}}
            <div class="tw-flex tw-items-center tw">
                <multiselect class="tw-cursor-text" v-model="newProduct" :options="{{ $products }}"
                    :searchable="true" :close-on-select="true" :show-labels="false" placeholder="Pick a value"
                    label="name"></multiselect>

                <div>
                    <input v-if="newProduct" type="number" v-model.number="newProduct.quantite"
                        class="tw-input tw-ml-5 tw-bg-white tw-h-full" placeholder="Quantité">
                </div>



                <input v-if="newProduct" type="number" v-model.number="newProduct.prix_achat"
                    class="tw-input tw-ml-5 tw-bg-white tw-h-full" placeholder="Prix Achat (AED)">
            </div>
            {{-- Boutton Ajouter Nouveau Produit --}}
            <button v-if="! editMode" class="tw-btn tw-inline-block tw-bg-green-800 tw-text-white tw-mt-5 "
                @click="addNewProduct('bon-commande')">Ajouter Produit</button>

            {{-- Tableau  --}}
            <table class="table tw-mt-10">
                <thead>
                    <tr>
                        <th>Ligne Nº</th>
                        <th>Nom du Produit</th>
                        <th>Quantité</th>
                        <th>Prix Achat (AED)</th>
                        <th>Total (AED)</th>
                        <th>Prix Achat (XAF)</th>
                        <th>Total (XAF)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Produits --}}
                    <tr v-for="(sectionnable, index) in bc.sectionnables" v-if="sectionnable.product">
                        {{-- Index --}}
                        <td>@{{ index + 1 }}</td>

                        {{-- Nom du Produit --}}
                        <td class="tw-bg-gray-300 tw-border tw-border-gray-400" scope="row">@{{ sectionnable.product.variant_name }} </td>

                        {{-- Quantité du Produit --}}
                        <td>
                            <input v-if="editMode || sectionnable.editMode" @input="addEdited(sectionnable)"
                                class="tw-input focus:tw-border-gray-600" type="text"
                                v-model="sectionnable.pivot.quantite">
                            <span v-else>@{{ sectionnable.pivot.quantite }}</span>
                        </td>

                        {{-- Prix Achat (AED) --}}
                        <td class="tw-bg-teal-600 tw-text-white">
                            {{-- En Mode Edit --}}
                            <span class="tw-mr-1">AED</span>

                            <input v-if="editMode || sectionnable.editMode" @keyup="convertToXaf(sectionnable,index)"
                                class="tw-input tw-text-black focus:tw-border-gray-600 tw-rounded-sm" type="text"
                                v-model="sectionnable.pivot.prix_achat">
                            {{-- En Mode Normal --}}
                            <span v-else>@{{ sectionnable.pivot.prix_achat }}</span>
                        </td>

                        {{-- Total AED --}}
                        <td class="tw-bg-teal-700 tw-text-white">
                            <span>@{{ (sectionnable.pivot.quantite * sectionnable.pivot.prix_achat).toFixed(0) }}
                            </span>
                        </td>
                        {{-- Prix Achat (XAF) --}}
                        <td class=" tw-bg-indigo-600 tw-text-white">
                            <input v-show="editMode || sectionnable.editMode" {{-- @input="addEdited(sectionnable)" --}}
                                class="tw-input focus:tw-border-gray-600 tw-text-black" type="number"
                                @keyup="convertToAed(sectionnable, index)" :ref="'prix_achat_xaf_' + index">
                            <span
                                v-if="! (editMode || sectionnable.editMode) && (sectionnable.pivot.prix_achat % commande.currency_exchange_rate !== 0 )">XAF
                                @{{ (sectionnable.pivot.prix_achat * commande.currency_exchange_rate).toFixed(1) }}</span>
                            <span
                                v-if=" ! (editMode || sectionnable.editMode) && (sectionnable.pivot.prix_achat % commande.currency_exchange_rate === 0)">XAF
                                @{{ (sectionnable.pivot.prix_achat * commande.currency_exchange_rate).toFixed(0) }}</span>
                        </td>

                        {{-- Total XAF --}}
                        <td class=" tw-bg-indigo-700 tw-text-white">
                            XAF <span>@{{ (sectionnable.pivot.quantite * (sectionnable.pivot.prix_achat * commande.currency_exchange_rate)).toFixed(0) }}
                            </span>
                        </td>
                        <td class="tw-bg-gray-200">
                            {{-- Edit Mode --}}
                            <i v-if="! sectionnable.editMode && !editMode "
                                class="fas fa-edit tw-text-blue-700 tw-cursor-pointer"
                                @click="enableSectionnableEditMode(sectionnable, index)">
                            </i>

                            {{-- Enregistrer --}}
                            <i v-if="sectionnable.editMode" class="fas fa-save tw-text-green-700 tw-cursor-pointer"
                                @click="updateSectionnable(sectionnable, 'bon-commande')"></i>

                            <i class="fas fa-trash tw-text-red-700 tw-ml-5 tw-cursor-pointer"
                                @click="deleteSectionnable(sectionnable, 'bon-commande')"></i>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="7">
                            <h3 class="tw-text-xl tw-text-center">Nouveaux Produits</h3>
                        </td>
                    </tr>
                    {{-- Articles --}}
                    <tr v-for="(sectionnable, index) in bc.sectionnables" v-if="sectionnable.article">
                        {{-- Index --}}
                        <td>@{{ index + 1 }}</td>
                        <td scope="row" class="tw-bg-gray-300 tw-border tw-border-gray-400">
                            @{{ sectionnable.article.nom }}
                        </td>
                        <td class="tw-input focus:tw-border-gray-600">
                            @{{ sectionnable.pivot.quantite }}
                        </td>
                        <td class="tw-bg-teal-600 tw-text-white">AED @{{ sectionnable.pivot.prix_achat }}</td>
                        <td class="tw-bg-teal-700 tw-text-white">AED @{{ sectionnable.pivot.prix_achat * sectionnable.pivot.quantite }}</td>
                        <td class=" tw-bg-indigo-600 tw-text-white">XAF @{{ sectionnable.pivot.prix_achat * commande.currency_exchange_rate }}</td>
                        <td class=" tw-bg-indigo-700 tw-text-white">
                            XAF
                            <animated-number
                                :value="sectionnable.pivot.quantite * (sectionnable.pivot.prix_achat / commande
                                    .currency_exchange_rate)"
                                :duration="500" />
                        </td>
                        <td class="tw-bg-gray-200">
                            {{-- Edit Mode --}}
                            <i v-if="! sectionnable.editMode && !editMode "
                                class="fas fa-edit tw-text-blue-700 tw-cursor-pointer"
                                @click="enableSectionnableEditMode(sectionnable, index)">
                            </i>

                            {{-- Enregistrer --}}
                            <i v-if="sectionnable.editMode" class="fas fa-save tw-text-green-700 tw-cursor-pointer"
                                @click="updateSectionnable(sectionnable, 'bon-commande')"></i>

                            <i class="fas fa-trash tw-text-red-700 tw-ml-5 tw-cursor-pointer"
                                @click="deleteSectionnable(sectionnable, 'bon-commande')"></i>
                        </td>
                    </tr>
                    {{-- Total --}}
                    <tr>
                        <td colspan="2"></td>
                        <td scope="row"class="tw-text-right tw-bg-teal-600 tw-text-teal-100">MONTANT TOTAL</td>
                        <td class="tw-bg-teal-700 tw-text-teal-100">
                            <animated-number :value="montantTotal" :format-value="formatToPrice"
                                :duration="500" />
                        </td>
                        <td class="tw-bg-indigo-600 tw-text-indigo-100">MONTANT TOTAL</td>
                        <td class="tw-bg-indigo-700 tw-text-indigo-100">
                            AED <animated-number :value="(montantTotal / commande.currency_exchange_rate).toFixed(0)"
                                :duration="500" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </bon-commande-show>
@endsection
