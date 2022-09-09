
<script>
export default {
    props: ['fournisseurs_prop'],
    data(){
        return {
            fournisseurs: null,

            nouveau_fournisseur: {
                nom: null,
                email: null,
                phone: null
            },

            isUpdating: false,
            fournisseur_being_updated: null,

            isDeleting: false,
            fournisseur_being_deleted: null,
        }
    },
    methods:{
        ajouterFournisseur(){
            axios.post('/fournisseur', this.nouveau_fournisseur).then(response => {
                this.fournisseurs.push(response.data)
                $('#ajouter-fournisseur-modal').modal('hide')
                this.$forceUpdate()
                
            }).catch(error => {
                console.log(error);
            });
        },
        openEditModal(fournisseur){
            $('#update-fournisseur-modal').modal('show')
            this.isUpdating = true;
            this.fournisseur_being_updated = fournisseur
        },
        openDeleteModal(fournisseur){
            $('#delete-fournisseur-modal').modal('show')
            this.isDeleting = true;
            this.fournisseur_being_deleted = fournisseur
        },
        updateFournisseur(){
            axios.put('/fournisseur/' + this.fournisseur_being_updated.id, this.fournisseur_being_updated ).then(response => {
                $('#update-fournisseur-modal').modal('hide')

            }).catch(error => {
                console.log(error);
            });
        },
        deleteFournisseur(){
            axios.delete('/fournisseur/' + this.fournisseur_being_deleted.id).then(response => {
                console.log(response.data);
                
                this.isDeleting = false
                var index = this.fournisseurs.indexOf(this.fournisseur_being_deleted)
                this.fournisseurs.splice(index, 1)
                this.fournisseur_being_deleted = null
                $('#delete-fournisseur-modal').modal('hide')
                
            }).catch(error => {
                console.log(error);
            });
        }
    },
    created(){
        this.fournisseurs = this.fournisseurs_prop
    }
}
</script>