<template>
    <div id="render-report">

        <div v-if="loaded">
            <div class="table-responsive report-results-table">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col" v-for="column in data.results.headings">
                            {{ column }}
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="result in data.results.data.data">
                        <td scope="row" v-for="column in data.results.headings">
                            {{ result[column] }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <slot v-else></slot>

    </div>
</template>

<script>
    export default {
        name: "RenderReport",
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
            let vm = this;

            new Promise(resolve => {
                vm.data = vm.value;

                vm.data.render(vm.data.report.id).then(results => {
                    vm.$emit('report-rendered', results);
                    resolve(true);
                });
            }).then(response => {
                vm.loaded = response;
            });
        }
    }
</script>

<style scoped>
    .raw-query {
        padding-bottom: 40px;
    }
</style>