
<script>
export default {
    props: ['commandes_prop'],
    data(){
        return {
            commandes: null,
            editingExchangeRate : false
        }
    },
    methods: {
        updateCurrencyRate(commande){
            axios.post('/commande/'+ commande.id + '/update-exchange-rate', {rate : commande.currency_exchange_rate})
            .then((response) => {
            })
        },
        total(commande){
            var total = 0;
            if(commande.bons_commandes.length > 0){
                commande.bons_commandes.forEach( bon => {
                    if(bon.sectionnables){
                        bon.sectionnables.forEach(sectionnable => {
                            total += (sectionnable.pivot.prix_achat * sectionnable.pivot.quantite)
                        });
                    }
                });
            }
            return total;
        },
        nombreProduits(commande){
            var total = 0;
            if(commande.sections.length > 0){
                commande.sections.forEach( section => {
                    if(section.articles){
                        total += section.articles.length
                    }
                    if(section.products){
                        total +=  section.products.length
                    }
                });
            }
            return total;
        },
        editExchangeRate(commande){
            commande.editingExchangeRate = true
            this.$forceUpdate()
        },
        saveExchangeRate(commande){
            axios.post('/commande/'+ commande.id + '/update-exchange-rate', {rate : commande.currency_exchange_rate})
            .then((response) => {
                commande.editingExchangeRate = false
                this.$forceUpdate()
            })
        }
    },
    created(){
        this.commandes = this.commandes_prop
        this.commandes.forEach(commande => {
            commande.editingExchangeRate = false
        });
    }
}
</script>
