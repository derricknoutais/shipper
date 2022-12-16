
<script>

export default {
    props: [ 'bc_prop', 'commande_prop' ],

    data(){
        return {
            commande : this.commande_prop,
            bc: null,
            editMode : false,
            newProduct: null,
        }
    },
    computed:{
        montantTotal(){
            var total = 0
            this.bc.sectionnables.forEach( sect => {
                total += (sect.pivot.quantite * sect.pivot.prix_achat)
            })
            return total
        },
        convert(amount, currency){
            switch (currency) {
                case 'XAF':
                    amount = amount / 165
                    break;
                case 'AED':
                    amount = amount * 165
                    break;
                default:
                    break;
            }
        },

    },
    methods : {
        convertToXaf(sectionnable, index){
            this.$refs['prix_achat_xaf_' + index ][0].value = sectionnable.pivot.prix_achat * this.commande.currency_exchange_rate
            this.$forceUpdate()
        },
        convertToAed(sectionnable, index){
            console.log(this.$refs['prix_achat_xaf_' + index ][0].value)
            sectionnable.pivot.prix_achat = this.$refs['prix_achat_xaf_' + index ][0].value / this.commande.currency_exchange_rate;
            this.$$forceUpdate
        },

        Edited(sectionnable){
            sectionnable.edited = true
        },
        toggleEditMode(){
            this.editMode = ! this.editMode
        },
        updateAllEdited(){

            axios.put('/bon-commande/sectionnables', ['hi', 'hello', 'bjr'] ).then(response => {
                console.log(response)
                // this.$swal({
                //     position: 'top-end',
                //     icon: 'success',
                //     title:  'Votre produit a été modifié avec succès',
                //     showConfirmButton: false,
                //     timer: 1000
                // })
            }).catch(error => {
                console.log(error);
            });
        },
        enableSectionnableEditMode(sectionnable, index){
            this.$refs['prix_achat_xaf_' + index ][0].value = (sectionnable.pivot.prix_achat * this.commande.currency_exchange_rate).toFixed(0)
            sectionnable.editMode = true;
            this.$forceUpdate()
        },
        formatToPrice(value) {
          return `AED ${value.toFixed(0)}`;
        },
        updateSectionnable(sectionnable, location){
            axios.put('/' + location + '/' + sectionnable.pivot.id, sectionnable).then(response => {
                this.$swal({
                    position: 'top-end',
                    width: 300,
                    height: 300,
                    icon: 'success',
                    title:  'Votre produit a été modifié avec succès',
                    showConfirmButton: false,
                    timer: 1000
                })
                sectionnable.editMode = false;
                this.$forceUpdate();
            }).catch(error => {
                console.log(error);
            });
        },
        deleteSectionnable(sectionnable, location){
            console.log('hello')
            this.$swal({
                title: 'Êtes-vous sûr(e)?',
                  text: "Cette action est irréversible!",
                  icon: 'warning',
                  showCancelButton: true,
                  confirmButtonColor: '#3085d6',
                  cancelButtonColor: '#d33',
                  confirmButtonText: 'Oui, Supprimer!',
                  cancelButtonText: 'Annuler',
                }).then((result) => {
                  if (result.value) {
                      axios.delete('/' + location + '/sectionnable/' + sectionnable.pivot.id ).then( response => {
                          this.$swal(
                            'Deleted!',
                            'Your file has been deleted.',
                            'success'
                          )
                          console.log(response.data);
                      }).catch( error =>{
                          console.log(error)
                      })


                  }
            })
        },
        createInvoice(){
            axios.get('/bon-commande/' + this.bc.id + '/create-invoice').then(response => {
                console.log(response.data);
                this.bc.facture_id = response.data
            }).catch(error => {
                console.log(error);
            });
        },
        creerBonLivraison(){
            axios.post('/creer-bl/', this.bc ).then(response => {
                console.log(response.data);

            }).catch(error => {
                console.log(error);
            });
        }
    },
    created(){
        this.bc = this.bc_prop
        this.bc.sectionnables.map( sectionnable => {
            sectionnable.editMode = false
            sectionnable.edited = false
        })

        this.bc.sectionnables.forEach( sect => {
            // console.log('hello')
            if(sect.sectionnable_type === "App\\Article" ){
                console.log('hello')
                axios.get('https://azimuts.gq/article/api/' + sect.sectionnable_id ).then(response => {

                    sect.article = response.data
                    this.$forceUpdate()
                }).catch( error => {
                    console.log(error);
                });
            }



        });
    }
}
</script>
