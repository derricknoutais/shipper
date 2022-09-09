

<script>
export default {
    data(){
        return {
            name: null,
            template: null
        }
    },
    methods:{
        sendForm(){
            if(this.template){
                var products = []; 
                if(this.template.products){
                    this.template.products.forEach( product => {
                        products.push({
                            "type" : "product",
                            "value" : product.id
                        })
                    });
                }
                
            }
            axios.post('/api/inventory', {nom : this.name, prods: products} ).then(response => {
                console.log(response.data);
                // window.location.reload(true)
            }).catch(error => {
                console.log(error);
            });
        }
    },
    mounted(){

    }
}
</script>