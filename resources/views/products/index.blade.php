@extends('layouts.welcome')


@section('content')
    <product-index :products_prop="{{ $products }}" :fournisseurs_prop="{{ $fournisseurs }}" inline-template>
        <div class="tw-container tw-mx-auto tw-mt-10">
            <form action="/product" method="GET" class="tw-flex tw-w-full">
                <select name="handle" class="form-control tw-w-1/4">
                    @foreach ($handles as $handle)

                        <option
                        @isset($handle_filter)
                            @if ($handle->id == $handle_filter)
                                selected
                            @endif
                        @endisset
                        value="{{ $handle->id }}">{{ $handle->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary tw-ml-5 ">Filtrer</button>
            </form>
            <div class="tw-w-full tw-flex tw-mt-5" >
                <multiselect
                    class="tw-w-2/4"
                    v-if="selected_products.length > 0"
                    v-model="selected_fournisseur" label="nom"
                    track-by="nom" :options="fournisseurs" :multiple="true"
                    :taggable="true" ></multiselect>
                <button v-if="selected_products.length > 0 && selected_fournisseur.length > 0" class="btn btn-primary tw-ml-1 tw-w-1/4" @click="addTagsToMultipleProducts">Ajouter @{{ selected_fournisseur.length }} Fournisseurs à @{{ selected_products.length }} Produits</button>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Groupe</th>
                        <th>Nom</th>
                        <th>SKU</th>
                        <th>Price</th>
                        <th>Fournisseurs</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="product in products">
                        <td scope="row">
                            <input type="checkbox" :value="product.id" v-model="selected_products">
                        </td>
                        <td>@{{ product.handle }}</td>
                        <td>@{{ product.name }}</td>
                        <td>@{{ product.sku }}</td>
                        <td>@{{ product.price }}</td>
                        <td class="tw-w-64">
                            <multiselect v-model="product.fournisseurs" label="nom"
                                track-by="nom" :options="fournisseurs" :multiple="true" :taggable="true" @input="addTag(product)"></multiselect>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </product-index>

@endsection
