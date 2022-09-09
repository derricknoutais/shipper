@extends('layouts.welcome')

@section('content')

<template-show :template_prop="{{ $template }}" inline-template>

    <section>
        <header class="tw-flex tw-flex-col tw-items-center tw-bg-gray-800 tw-text-white">
            <p class="tw-text-4xl tw-text-bold tw-my-4">Template {{ $template->name }}</p>
            <p class="tw-mb-4">
                Les Templates vous permettent d'enregistrer un groupe de produits que vous pouvez réutiliser dans vos commandes
            </p>

            <div class="tw-flex tw-w-screen tw-justify-center tw-items-center tw--ml-2">
                <div class="tw-w-1/4 tw-mr-4">
                    {{-- <multiselect v-model="selected_product" :options="{{ $products }}" ref="SearchBar" :multiple="true" :searchable="true" :close-on-select="false" :show-labels="false"
                    placeholder="Pick a value" label="name" @input="addProduct()"></multiselect> --}}
                    <multiselect v-model="selected_product" :options="{{ $products }}" :multiple="true" :close-on-select="false" :clear-on-select="false" :preserve-search="true" placeholder="Pick some" label="name" track-by="name" :preselect-first="true"></multiselect>



                </div>
                <input type="number" v-model.number="quantite" class="form-control tw-w-1/12" v-if="selected_product.length < 2">
                <button class="tw-btn tw-ml-5 tw-shadow-outline tw-leading-none" @click="addProduct()">Ajouter Produit</button>
            </div>



            <p class="tw-text-lg my-4">
                @{{ template.products.length }}
                @{{ template.products.length <= 1 ? 'Produit' : 'Produits' }}
            </p>
            {{-- <button class="tw-btn" data-toggle="modal" data-target="#modelId">Créer Template</button> --}}
            <!-- Modal -->
        </header>

        <main class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-w-screen tw-mt-5">
            <table class="table tw-w-1/2 ">
                <thead class="thead-inverse">
                    <tr>
                        <th>SKU</th>
                        <th>Nom</th>
                        <th>Quantité</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                            <tr v-for="(product, index) in template.products ">
                                <td>@{{ product.sku }}</td>
                                <td scope="row">@{{ product.name }}</td>
                                <td>
                                    <input type="number" class="form-control" v-model.number="product.pivot.quantite">
                                </td>
                                <td>@{{ product.price }}</td>
                                <td>
                                    <i class="fas fa-times tw-text-red-500 tw-mr-2 tw-cursor-pointer" @click="removeProduct(index)"></i>
                                </td>
                            </tr>
                    </tbody>
            </table>
            <div class="tw-w-1/2 tw-flex tw-justify-end tw-my-5">
                <button type="button" class="btn tw-bg-gray-300 tw-ml-5 hover:tw-bg-gray-700">Cancel</button>
                <button type="button" class="btn tw-bg-gray-800 tw-text-white tw-ml-5 hover:tw-bg-gray-700" @click="enregistrer">Enregistrer</button>
            </div>
        </main>

    </section>


</template-show>
@endsection
