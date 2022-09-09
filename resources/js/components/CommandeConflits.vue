
<script>
export default {
    props: ['commande_prop'],
    data(){
        return {
            commande: null,
            isLoading: {
                non_disponible : false
            },
            displayNotAvailable: false,
            sectionnablesNotAvailable: []
        }
    },
    methods:{
        toggleNotAvailable(){
            this.displayNotAvailable != this.displayNotAvailable
        },
        showNotAvailable(){
            this.toggleNotAvailable = true

        },
        selectionnerElementConflictuel(element, index, elements_conflictuels){
            axios.post('/commande/' + this.commande.id + '/résoudre-conflit',
                {element : element, elements_conflictuels : elements_conflictuels}
            ).then(response => {
                this.commande.conflits.splice(index, 1);
                this.$forceUpdate()
            }).catch(error => {
                console.log(error);
            });
        },
        selectionnerPrixBas(){
            console.log('selecting')
            this.commande.conflits.forEach( (conflit, i) => {
                if(conflit.pivot.conflit !== 0){
                    var index = -1
                    var moinsCher = 9999999999999999999999999999999999
                    conflit.elements_conflictuels.forEach( (element, idx) => {
                        if(element.offre !== 0 && element.offre < moinsCher){
                            moinsCher = element.offre
                            index = idx
                        }
                    })
                }
                if(index !== -1 && moinsCher !== 9999999999999999999999999999999999 )
                    this.selectionnerElementConflictuel(conflit.elements_conflictuels[index], index, conflit.elements_conflictuels)
            })
        },
        selectionnerChoixUnique(){
            this.commande.conflits.forEach( (conflit, i) => {
                if(conflit.pivot.conflit !== 2){
                    var nb_elements_conflictuels = conflit.elements_conflictuels.length
                    var idx = null
                    conflit.elements_conflictuels.forEach( (element, index) => {
                        if( element.offre === 0 && element.quantite_offerte === 0){
                            nb_elements_conflictuels -= 1
                        } else {
                            idx = index
                        }
                    })
                    if(nb_elements_conflictuels === 1){
                        this.selectionnerElementConflictuel(conflit.elements_conflictuels[idx], i, conflit.elements_conflictuels)
                    }
                }
            })
        },
        definirNonDisponible(){
            this.isLoading.non_disponible = true
            var conflits_length = this.commande.conflits.length
            this.commande.conflits.forEach( conflit => {
                var non_disponible = conflit.elements_conflictuels.length
                conflit.elements_conflictuels.forEach( element  => {
                    if(element.offre === 0 && element.quantite_offerte === 0){
                        non_disponible -= 1
                    }
                })
                if(non_disponible === 0){
                    this.updateSectionnable(conflit, 'conflit', 2)
                }
                conflits_length -= 1
                // if( conflits_length === 0 ){
                //     this.isLoading.non_disponible = false
                //     this.$swal({
                //         text: 'Good Job'
                //     })
                // }
            })
        },
        updateSectionnable(sectionnable, field, value){
            axios.patch('/sectionnable', {id: sectionnable.pivot.id, field: field, value: value}).then(response => {
                console.log(response.data);
                sectionnable.editing = false
                this.$forceUpdate()

            }).catch(error => {
                console.log(error);
            });
        }
    },
    created(){
        //
        this.commande = this.commande_prop

        this.commande.sections.forEach( section => {
            var filtres = section.products.filter( product => {
                if (product.pivot.conflit === 2){
                    this.sectionnablesNotAvailable.push(product)
                }
            })
            section.articles.filter(article => {
                if(article.pivot.conflit === 2){
                    this.sectionnablesNotAvailable.push(article)
                }
            })
        })

        // Pour chaque Conflit
        this.commande.conflits.forEach( conflit => {
            // Map tous les éléments conflictuels
            conflit.elements_conflictuels.map( (element) => {
                var found = this.commande.demandes.find( demande => {
                    return element.demande_id === demande.id
                } )
                element.demande = found
            } )
        });
    }
}
</script>
