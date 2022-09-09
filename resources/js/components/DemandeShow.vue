<script>
export default {
    props : ['demande_prop', 'demandes_prop', ],
    data(){
        return {
            demande: null,
            demandes: this.demandes_prop,
            cardNumber: null,
            options: {
              creditCard: true,
              delimiter: '-',
            },
            toDisplay : {
                id : true,
                product : false,
                traduction : true,
                differentOffer : false,
                quantite : false,

                offeredQuantity : true,
                offerXaf : true,
                totalXaf : true,
                offerAed : true,
                totalAed : true,
                actions : false,
            },

            sectionnable_being_deleted: null,
            sectionnable_being_moved: null,
            demande_to_move_to: null,
            detailsState: false
        }
    },
    computed: {
        totalDemande(){
            var total = 0;
            this.demande.sectionnables.forEach(sectionnable => {
                total += (sectionnable.pivot.quantite_offerte * sectionnable.pivot.offre)
            });
            return total;
        }
    },
    methods:{

        convertToXaf(sectionnable, index){
            sectionnable.pivot.offre = this.$refs['offerAed_' + index ][0].value * demande.commande.currency_exchange_rate
            this.$forceUpdate()
            this.enregisterOffre(sectionnable)
        },
        convertToAed(sectionnable, index){
            this.$refs['offerAed_' + index ][0].value = sectionnable.pivot.offre / demande.commande.currency_exchange_rate;
            this.$forceUpdate
        },
        creerBonCommande(demande){
            axios.post('/creer-bon-commande', demande ).then(response => {
                console.log(response.data);

            }).catch(error => {
                console.log(error);
            });
        },
        enregisterOffre(sectionnable){

                sectionnable.hasError = false
                sectionnable.transfer_state = 'Sauvegarde en Cours ...'
                this.$forceUpdate()
                axios.put('/demande/' + this.demande.id + '/update-product', sectionnable).then(response => {
                    sectionnable.transfer_state = 'Sauvegarde Réussie'
                    this.$forceUpdate()
                }).catch(error => {
                    sectionnable.transfer_state = 'Sauvegarde Échouée. Veuillez verifier votre connexion'
                    this.$forceUpdate()
                    console.log(error);
                });

        },
        openDeleteModal(sectionnable){
            this.sectionnable_being_deleted = sectionnable
            $('#delete-modal').modal('show')
        },
        removeSectionnable(){
            axios.delete('/demande-sectionnable/' + this.sectionnable_being_deleted.pivot.id).then(response => {
                console.log(response.data);
                var index = this.demande.sectionnables.indexOf(this.sectionnable_being_deleted)
                this.demande.sectionnables.splice(index, 1)
                this.sectionnable_being_deleted = null
                $('#delete-modal').modal('hide')
            }).catch(error => {
                console.log(error);
            });
        },
        normaliserQuantités(){
            this.demande.sectionnables.forEach( sectionnable => {
                this.enregisterOffre(sectionnable)
                sectionnable.pivot.quantite_offerte = sectionnable.quantite
            })
            this.$forceUpdate()
        },

        editMode(sectionnable){
            sectionnable.editing = true
            this.$forceUpdate()
        },
        editTraduction(sectionnable){
            if (! sectionnable.pivot.traduction) {

                axios.put('/demande-sectionnable', sectionnable ).then(response => {

                    sectionnable.pivot.traduction = [
                        sectionnable.product.handle.translation, sectionnable.product[sectionnable.product.handle.display1],
                        sectionnable.product[sectionnable.product.handle.display2], sectionnable.product[sectionnable.product.handle.display3]
                    ].filter(Boolean).join(' / ', '');

                    this.$forceUpdate()

                }).catch(error => {
                    console.log(error);
                });
            }
            sectionnable.editing = true
            this.$forceUpdate()
        },
        saveTraduction(sectionnable){
            axios.patch('/demande-sectionnable-traduction', sectionnable)
            .then(response => {
                sectionnable.editing= false
                this.$forceUpdate()
            })
            .catch(error => {
                console.log(error);
            });
        },
        toggleDetails(sectionnable){
            sectionnable.displayDetails = ! sectionnable.displayDetails;
            this.$forceUpdate()
        },
        ajouterSectionnableABonCommande(element, index, dem){
            dem.transfer_state = 'Enregistrement en Cours ...'
            this.$forceUpdate()
            console.log(element.demandes[index])
            element.demande_id = dem.id
            element.offre = dem.pivot.offre
            element.quantite_offerte = dem.pivot.quantite_offerte
            element.sectionnable_id = element.pivot.sectionnable_id

            axios.post('/commande/' + this.demande.commande_id + '/résoudre-conflit',
                {element : element}
            ).then(response => {
                dem.transfer_state = 'Fournisseur Sélectionné'
                this.$forceUpdate()
            }).catch(error => {
                sectionnable.transfer_state = 'Sauvegarde Échouée. Veuillez verifier votre connexion'
                console.log(error);
            });
        },
        openMoveModal(sectionnable, index){
            // this.sectionnable_being_moved = sectionnable
            // $('#demande-move-modal').modal('show')
            sectionnable.transfer_state = 'Déplacement En Cours ...'
            this.$forceUpdate()

            axios.patch('/demande-sectionnable', {id: sectionnable.pivot.id, field: 'demande_id', value: 190}).then(response => {
                console.log(response.data);
                sectionnable.editing = false
                sectionnable.transfer_state = 'Produit Déplacé ...'
                this.$forceUpdate()
                setTimeout(() => {
                    this.demande.sectionnables.splice(index, 1);
                    this.$forceUpdate()
                }, 2000);

            }).catch(error => {
                console.log(error);
            });
        },
        toggleAllDetails(){
            this.detailsState = ! this.detailsState
            this.demande.sectionnables.map( sect => {
                sect.displayDetails = this.detailsState;
                this.$forceUpdate()
            })
            this.$forceUpdate()
        },
        deplacerSectionnable(){
            this.updateSectionnable(this.sectionnable_being_moved, 'demande_id', this.demande_to_move_to.id )
        },
        updateSectionnable(sectionnable, field, value){
            axios.patch('/demande-sectionnable', {id: sectionnable.pivot.id, field: field, value: value}).then(response => {
                console.log(response.data);
                sectionnable[field] = value;
                sectionnable.editing = false
                this.$forceUpdate()

            }).catch(error => {
                console.log(error);
            });
        },
        transfererSectionnableABonCommande(sectionnable, index, dem){
            dem.transfer_state = 'Enregistrement en Cours ...'
            this.$forceUpdate()
            axios.patch('/transfer-sectionnable-to-bon-commandes', { sectionnable : sectionnable , dem: dem, index: index} ).then(response => {
                console.log(response.data);
                dem.transfer_state = 'Fournisseur Sélectionné'
                this.$forceUpdate()
            }).catch(error => {
                console.log(error);
                sectionnable.transfer_state = 'Sauvegarde Échouée. Veuillez verifier votre connexion'
                this.$forceUpdate()
            });
        }

    },
    created(){
        this.demande = this.demande_prop
        this.demande.sectionnables.map( sect => {
            sect.transfer_state = ''
            sect.editing = false
            sect.displayDetails = false;
        })
    }
}
</script>
