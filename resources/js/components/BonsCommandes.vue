<script>
export default {
    props: ['bc_prop'],
    data(){
        return {
            commande : null,
            bcs: null
        }
    },
    computed: {
        montantTotal(){
            var total = 0
            this.bcs.forEach( bc => {
                total += this.totalBC(bc)
            })
            return total;
        }
    },
    methods:{
        totalBC(bc){
            console.log(bc)
            var total = 0
            bc.sectionnables.forEach(sectionnable => {
                total += (sectionnable.pivot.quantite * sectionnable.pivot.prix_achat)
            });
            return total
        }
    },
    created(){
        this.commande = this.bc_prop
        this.bcs = this.bc_prop.bons_commandes
    }
}
</script>