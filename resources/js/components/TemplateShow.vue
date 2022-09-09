

<script>
export default {
    props: ['template_prop', ],
    data(){
        return {
            selected_product: false,
            template: null,
            quantite: null,
        }
    },
    methods:{
        addProduct(){
            var data = [];
            this.selected_product.forEach(product => {
                if(data.length > 1){
                    data.push({
                        template_id : this.template.id,
                        product_id : product.id,
                        quantite: 1
                    })
                } else {

                    data.push({
                        template_id : this.template.id,
                        product_id : product.id,
                        quantite: this.quantite

                    })
                }

            });
            console.log(data)
            axios.post('/product-template', data ).then(response => {
                console.log('response' + response.data)
                if(response.data === 'OK'){
                    this.selected_product.forEach( element => {
                        element.pivot = {
                            quantite: this.quantite
                        }
                        this.template.products.unshift(element)
                    })

                    // this.selected_product = false
                    // this.$refs.searchBar.focus()
                }

            }).catch(error => {
                console.log(error);
            });
        },
        removeProduct(index){
            axios.post('/product-template/delete', {template_id : this.template.id, product_id : this.template.products[index].id } ).then(response => {
                if(response.data === 'OK'){
                    this.template.products.splice(index, 1)
                }
            }).catch(error => {
                console.log(error);
            });
        },
        enregistrer(){
            var data = [];
            this.template.products.forEach( product => {
                data.push({
                    'product_id':  product.id,
                    'template_id': this.template.id,
                    'quantite' : product.pivot.quantite
                })
            })
            axios.put('/product-template' , data).then(response => {
                data.forEach( datum => {

                });
                console.log(response.data);

            }).catch(error => {
                console.log(error);
            });
        }
    },
    created(){
        this.template = this.template_prop
    }
}
</script>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
