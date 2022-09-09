<template>
    <div >
        <h2 class="tw-text-xl tw-mt-20">Liste des Produits</h2>

        <div class="tw-flex tw-items-center ">

                <multiselect class="tw-cursor-text" v-model="newProduct" :options="products" :searchable="true"
                :close-on-select="true" :show-labels="false" placeholder="Pick a value" label="name"></multiselect>


            <input v-if="newProduct" type="number" v-model.number="newProduct.quantite"
                class="tw-input tw-ml-5 tw-bg-white tw-py-2 tw-rounded" placeholder="Quantité">

            <button class=" tw-bg-green-800 tw-text-white tw-rounded tw-px-4 tw-ml-3" @click="addNewProduct(location)">Ajouter Produit</button>
        </div>
    </div>
</template>
<script>
    export default {
        props: ['products', 'location', 'document' ],
        data(){
            return {
                editMode : false,
                newProduct : null,

            }
        },
        methods : {
            addNewProduct(location){
                axios.post('/' + location + '/sectionnable', { product: this.newProduct, document: this.document, }).then(response => {
                    console.log(response.data);
                    // window.location.reload()
                }).catch(error => {
                    console.log(error);
                });
            }
        }
    }
</script>
