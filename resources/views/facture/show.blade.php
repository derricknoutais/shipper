@extends('layouts.welcome')


@section('content')
<bon-commande-show :bc_prop="{{$facture}}" inline-template>
    <div class="tw-container tw-mx-auto tw-flex tw-flex-col">
        {{-- En Tete --}}
        <en-tete></en-tete>
        {{-- Titre Document --}}
        <h1 class="tw-text-center tw-text-5xl">@{{ bc.nom }}</h1>
        {{-- Boutons de Fonction --}}
        <div class="tw-mt-5">
            {{-- <a href="/factures/export/{{ $facture->id }}" class="tw-btn tw-inline-block tw-btn-dark">Export .xlsx</a> --}}
            <button class="tw-btn tw-btn-dark" @click="creerBonLivraison()">Créer B.L dans Vend</button>
            <a v-if="bc.bon_livraison_id" class="tw-btn tw-btn-dark" :href="'https://stapog.vendhq.com/consignment/' + bc.bon_livraison_id" target="_blank">Voir B.L dans Vend</a>

            {{-- <button v-if="! editMode" class="tw-btn tw-inline-block tw-btn-dark tw-mt-5" @click="toggleEditMode()">Modifier</button>
            <button v-else class="tw-btn tw-inline-block tw-btn-dark tw-mt-5" @click="updateAllEdited()">Enregistrer</button> --}}
        </div>

        {{-- Sous-Titre --}}
        <h2 class="tw-text-3xl tw-mt-20">Liste des Produits</h2>
        {{-- Selecteur --}}
        <div class="tw-flex tw-items-center tw">
            <multiselect class="tw-cursor-text" v-model="newProduct" :options="{{ $products }}" :searchable="true"
                :close-on-select="true" :show-labels="false" placeholder="Pick a value" label="name"></multiselect>

            <input v-if="newProduct" type="number" v-model.number="newProduct.quantite"
                class="tw-input tw-ml-5 tw-bg-white tw-h-full" placeholder="Quantité">

            <input v-if="newProduct" type="number" v-model.number="newProduct.prix_achat"
                class="tw-input tw-ml-5 tw-bg-white tw-h-full" placeholder="Prix">
        </div>
        {{-- Boutton Ajouter Nouveau Produit --}}
        <button v-if="! editMode"
            class="tw-btn tw-inline-block tw-bg-green-800 tw-text-white tw-mt-5 "
            @click="addNewProduct('facture')">Ajouter Produit
        </button>

        <table class="table tw-mt-10">
            <thead>
                <tr class="tw-bg-gray-800 tw-text-gray-100">
                    <th>Nom du Produit</th>
                    <th>Quantité</th>
                    <th>Prix Achat (XAF)</th>
                    <th>Total (XAF)</th>
                    <th>Prix Achat (AED)</th>
                    <th>Total (AED)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                {{-- Produits --}}
                <tr v-for="(sectionnable, index) in bc.sectionnables" v-if="sectionnable.product">

                    {{-- Nom du Produit --}}
                    <td
                        class="tw-bg-gray-300 tw-border tw-border-gray-400"
                        scope="row"
                    >@{{ sectionnable.product.name }} </td>

                    {{-- Quantité du Produit --}}
                    <td>
                        <input v-if="editMode || sectionnable.editMode" @input="addEdited(sectionnable)"
                            class="tw-input focus:tw-border-gray-600" type="text" v-model="sectionnable.pivot.quantite">
                        <span v-else>@{{ sectionnable.pivot.quantite }}</span>
                    </td>
                    {{-- Prix Achat (XAF) --}}
                    <td class="tw-bg-teal-600 tw-text-white">
                        {{-- En Mode Edit --}}
                        <span class="tw-mr-1">XAF</span>

                        <input v-if="editMode || sectionnable.editMode" @input="addEdited(sectionnable)"
                            class="tw-input tw-text-black focus:tw-border-gray-600 tw-rounded-sm" type="text"
                            v-model="sectionnable.pivot.prix_achat">
                        {{-- En Mode Normal --}}
                        <span v-else>@{{ sectionnable.pivot.prix_achat }}</span>
                    </td>
                    {{-- Total (XAF) --}}
                    <td class="tw-bg-teal-700 tw-text-white">
                        <animated-number :value="sectionnable.pivot.quantite * sectionnable.pivot.prix_achat"
                            :format-value="formatToPrice" :duration="500" />
                    </td>
                    {{-- Prix Achat (AED) --}}
                    <td class=" tw-bg-indigo-600 tw-text-white">
                        <input
                            v-show="editMode || sectionnable.editMode" @input="addEdited(sectionnable)"
                            class="tw-input focus:tw-border-gray-600 tw-text-black" type="number"
                            @keyup="convertToXaf(sectionnable, index)" :ref="'prix_achat_aed_' + index"
                        >
                        <span v-if="! (editMode || sectionnable.editMode) && sectionnable.pivot.prix_achat%165 !== 0">AED @{{ (sectionnable.pivot.prix_achat / 165 ).toFixed(1) }}</span>
                        <span v-if=" ! (editMode || sectionnable.editMode) && sectionnable.pivot.prix_achat%165 === 0">AED @{{ (sectionnable.pivot.prix_achat / 165 ).toFixed(0) }}</span>
                    </td>
                    {{-- Total AED --}}
                    <td class=" tw-bg-indigo-700 tw-text-white">
                        AED <animated-number :value="(sectionnable.pivot.quantite * (sectionnable.pivot.prix_achat / 165)).toFixed(0)"
                             :duration="500" />
                    </td>
                    {{-- Actions --}}
                    <td class="tw-bg-gray-200">
                        {{-- Edit Mode --}}
                        <i v-if="! sectionnable.editMode && !editMode "
                            class="fas fa-edit tw-text-blue-700 tw-cursor-pointer"
                            @click="enableSectionnableEditMode(sectionnable, index)">
                        </i>

                        {{-- Enregistrer --}}
                        <i v-if="sectionnable.editMode" class="fas fa-save tw-text-green-700 tw-cursor-pointer"
                            @click="updateSectionnable(sectionnable, 'facture')"></i>

                        <i class="fas fa-trash tw-text-red-700 tw-ml-5 tw-cursor-pointer"
                            @click="deleteSectionnable(sectionnable, 'facture')"></i>
                    </td>
                </tr>



                {{-- Articles --}}
                <tr v-for="(sectionnable, index) in bc.sectionnables" v-if="sectionnable.article">
                    {{-- Nom du Produit --}}
                    <td scope="row" class="tw-bg-indigo-300 tw-border tw-border-gray-400">@{{ sectionnable.article.nom }} </td>
                    {{-- Quantité --}}
                    <td class="tw-border tw-border-gray-400">
                        <input v-if="editMode || sectionnable.editMode" @input="addEdited(sectionnable)"
                            class="tw-input focus:tw-border-gray-600" type="text" v-model="sectionnable.pivot.quantite">
                        <span v-else>@{{ sectionnable.pivot.quantite }}</span>
                    </td>
                    {{-- Prix Achat XAF --}}
                    <td class="tw-border tw-bg-orange-300 tw-border-orange-500">
                        {{-- En Mode Edit --}}
                        <span class="tw-mr-1">XAF</span>

                        <input v-if="editMode || sectionnable.editMode" @input="addEdited(sectionnable)"
                            class="tw-input tw-text-black focus:tw-border-gray-600 tw-rounded-sm" type="text"
                            v-model="sectionnable.pivot.prix_achat">
                        {{-- En Mode Normal --}}
                        <span v-else>@{{ sectionnable.pivot.prix_achat }}</span>
                    </td>
                    {{-- Prix Achat Total XAF --}}
                    <td class="tw-border tw-bg-orange-400 tw-border-orange-500">
                        XAF @{{ sectionnable.pivot.prix_achat * sectionnable.pivot.quantite }}
                    </td>
                    {{-- Prix Achat AED --}}
                    <td class="tw-border tw-bg-gray-500 tw-border-gray-400 tw-text-dark "
                        @keyup="convertToXaf(sectionnable, index)" :ref="'prix_achat_aed_' + index"
                    >
                        <input
                            v-show="editMode || sectionnable.editMode" @input="addEdited(sectionnable)"
                            class="tw-input focus:tw-border-gray-600 tw-text-black" type="number"
                            @keyup="convertToXaf(sectionnable, index)" :ref="'prix_achat_aed_' + index"
                        >
                        <span v-if="! (editMode || sectionnable.editMode) && sectionnable.pivot.prix_achat%165 !== 0">AED @{{ (sectionnable.pivot.prix_achat / 165 ).toFixed(2) }}</span>
                        <span v-if=" ! (editMode || sectionnable.editMode) && sectionnable.pivot.prix_achat%165 === 0">AED @{{ (sectionnable.pivot.prix_achat / 165 ).toFixed(0) }}</span>
                    </td>
                    {{-- Prix Achat Total XAF --}}
                    <td class="tw-border tw-bg-gray-600 tw-border-gray-400 tw-text-white">
                        AED
                        <animated-number :value="sectionnable.pivot.quantite * (sectionnable.pivot.prix_achat/165)"
                             :duration="1000" />
                    </td>
                    <td class="tw-bg-gray-200">
                        {{-- Edit Mode --}}
                        <i v-if="! sectionnable.editMode && !editMode "
                            class="fas fa-edit tw-text-blue-700 tw-cursor-pointer"
                            @click="enableSectionnableEditMode(sectionnable, index)">
                        </i>

                        {{-- Enregistrer --}}
                        <i v-if="sectionnable.editMode" class="fas fa-save tw-text-green-700 tw-cursor-pointer"
                            @click="updateSectionnable(sectionnable, 'facture')"></i>

                        <i class="fas fa-trash tw-text-red-700 tw-ml-5 tw-cursor-pointer"
                            @click="deleteSectionnable(sectionnable, 'facture')"></i>
                    </td>
                </tr>
                {{-- Total --}}
                <tr>
                    <td colspan="2"></td>
                    <td scope="row"class="tw-text-right tw-bg-teal-600 tw-text-teal-100">MONTANT TOTAL</td>
                    <td class="tw-bg-teal-700 tw-text-teal-100">
                        <animated-number :value="montantTotal" :format-value="formatToPrice" :duration="500" />
                    </td>
                    <td class="tw-bg-indigo-600 tw-text-indigo-100">MONTANT TOTAL</td>
                    <td class="tw-bg-indigo-700 tw-text-indigo-100">
                        AED <animated-number :value="(montantTotal/165).toFixed(0)"  :duration="500" />
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</bon-commande-show>
@endsection
