<template>
    <div  :class="this.bgcolor ">
        <div class="tw-flex tw-justify-between">
            <div class="tw-text-xl">
                <img src="/img/stalogo-image.png">
                <p>
                    <i class="fas fa-envelope"></i>
                    B.P: 1268
                </p>
                <p>
                    <i class="fas fa-phone"></i>
                    (+241)77.15.82.15 / (+241)11.56.08.55
                </p>
                <p>
                    <i class="fas fa-map-marked "></i>
                    Ngadi à la Case d'Écoute (Port-Gentil)
                </p>

            </div>
            <div>
                <h3 class="tw-text-2xl">{{ todaysDate }}</h3>
            </div>

        </div>

    </div>
</template>

<script>
export default {
    props: ['textcolor', 'bgcolor', 'nom'],
    data(){
        return {
            today : null
        }
    },
    computed:{
        todaysDate(){
            var today = new Date(Date.now())
            var date = today.getDate()
            var mois = today.getMonth()
            var year = today.getFullYear()
            // return today;
            if(date < 10 ){
                date =  '0' + date
            }
            if(mois < 10 ){
                mois = '0' + mois
            }
            if(year < 10 ){
                year = '0' + year
            }
            return (date + ' / ' + mois + ' / ' + year)
        }
    },
    mounted(){

    }
}
</script>
