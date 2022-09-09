
<script>

export default {
    props: ['handles_prop'],
    data(){
        return {
            handles: null,
        }
    },
    methods:{
        updateDisplay(handle, field){
            console.log(handle[field])
            axios.put('/handle/' + handle.id, {handle: handle, field : field} ).then(response => {
                console.log(response.data);

            }).catch(error => {
                console.log(error);
            });
        }
    },
    created(){
        this.handles = this.handles_prop
    }
}
</script>
