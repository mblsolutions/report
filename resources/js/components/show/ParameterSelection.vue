<template>
    <div>
        <h1>PARAMETER SELECTION</h1>
    </div>
</template>

<script>
    export default {
        name: "ParameterSelection",
        props: {
            value: {
                type: Object
            }
        },
        data() {
            return {
                data: null,
                loaded: false
            }
        },
        mounted() {
            new Promise(resolve => {
                this.data = this.value;
                resolve(true);
            }).then(response => {
                this.loaded = response;
            });
        }
    }
</script>

<style scoped>

</style>