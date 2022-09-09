
<script>
export default {
    props : ['commande_prop'],
    data(){
        return {
            commande: null
        }
    },
    computed: {

    },
    methods:{
        totalDemande(demande){
            var total = 0
            demande.sectionnables.forEach(sectionnable => {
                total += (sectionnable.pivot.quantite_offerte * sectionnable.pivot.offre)
            });
            return total
        },
        générerBons(){
            // console.log('Hello')
            axios.get('/commande/' + this.commande.id + '/générer-bons').then(response => {
                console.log(response.data);

            }).catch(error => {
                console.log(error);
            });
        },
        uploadFiles(){
            let formData = new FormData();
            for( var i = 0; i < this.$refs.myFiles.files.length; i++ ){
              let file = this.$refs.myFiles.files[i];
              formData.append('files[' + i + ']', file);
            }
            console.log(this.$refs.myFiles.files)
            axios.post('/import', formData, {
                headers : {
                    'Content-Type' : 'multipart/form-data'
                }
            } ).then(response => {

                this.$swal({
                    icon : 'success',
                    title: 'Yaay!!!',
                    text: 'Votre Fichier a été télechargé avec succès'
                })
            }).catch(error => {
                this.$swal({
                    icon : 'error',
                    title: 'Oops...',
                    text: 'Une erreur est survenue lors du téleversement de votre fichier. Appellez Ricko'
                })
            });
        }
    },
    created(){
        this.commande = this.commande_prop
    }
}
</script>
