<template>
    <div class='container'>

    </div>
</template>

<script>
export default {
    props: ['fournisseur_prop'],
    data(){
        return {
            fournisseur: null
        }
    },
    methods:{
        total(demande, type){
            var total = 0;
            if(demande.sectionnables){
                    if(type === 'demande'){
                        demande.sectionnables.forEach( sectionnable => {
                            total += (sectionnable.pivot.offre * sectionnable.pivot.quantite_offerte)
                        });
                    } else if( type === 'bc') {
                        demande.sectionnables.forEach( sectionnable => {
                            total += (sectionnable.pivot.prix_achat * sectionnable.pivot.quantite)
                        });
                    }
            }
            return total;
        }
    },
    created(){
        this.fournisseur = this.fournisseur_prop
    }
}
</script>