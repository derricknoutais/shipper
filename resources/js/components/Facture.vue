
<script>
export default {
    props: [ 'data'],
    data(){
        return {

        }
    },
    methods:{
        total(bc){
            console.log(bc)
            var total = 0
            bc.sectionnables.forEach(sectionnable => {
                total += (sectionnable.pivot.quantite * sectionnable.pivot.prix_achat)
            });
            return total
        }
    },
    mounted(){

    }
}
</script>
