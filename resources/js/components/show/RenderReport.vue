<template>
    <div id="render-report">

        <div id="report-result-error" class="col-12 col-xs-12" v-if="error">
            <div class="text-center">
                <h1>Report Error</h1>
                <p class="text-muted">
                    An error occurred while rendering report, please contact support.
                </p>
                <p class="code-block" v-if="error.response">
                    <code>{{ error.response.message }}</code>
                </p>
            </div>
        </div>
        <div v-else-if="loaded">

            <div id="report-parameters" class="col-12 col-xs-12 offset-md-6 col-md-6" v-if="data.report.fields.length > 0">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th scope="col" colspan="2">
                                Parameters
                                <div class="pull-right float-right">
                                    <button class="btn btn-sm btn-primary" @click.prevent="resetParameters">Reset Parameters</button>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(field, index) in data.report.fields">
                            <td class="parameter-title">{{ field.label }}</td>
                            <td class="parameter-value">{{ getParameterName(index, data.data[field.alias]) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="report-results" class="col-12 col-xs-12" v-if="data.results && data.results.length > 0">
                <div class="table-responsive report-results-table">
                    <table class="table table-sm table-striped table-hover" :key="page" >
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

            <div class="index-no-results p-5 align-self-center text-center">
                <h4 class="text-primary">No Results</h4>
                <p class="text-muted">
                    This report generated no results.
                    <span v-if="data.report.fields.length > 0">Try adjusting your <a href="#" @click.prevent="resetParameters">report parameters</a>.</span>
                </p>
            </div>

            <hr class="col-xs col-xs-12">

            <div class="row">
                <div class="col-xs col-xs-12 col-md-4 text-left">
                    <button class="btn btn-block btn-primary" :disabled="page <= 1" @click.prevent="previousPage">Previous Page</button>
                </div>
                <div class="col-xs col-xs-12 col-md-4 text-center">
                    <p class="report-meta">
                        {{ data.results.data.total }} results found ({{ data.results.data.per_page }} per page)<br>
                        <small>Displaying {{ data.results.data.from }} to {{ data.results.data.to }}</small>
                    </p>
                </div>
                <div class="col-xs col-xs-12 col-md-4 text-right">
                    <button class="btn btn-block btn-primary" :disabled="page >= data.results.data.last_page" @click.prevent="nextPage">Next Page</button>
                </div>
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
                page: 1,
                error: false,
                loaded: false
            }
        },
        methods: {
            /**
             * Get the Parameters formatted name
             *
             * @return {string}
             */
            getParameterName(index, value) {
                try {
                    let field = this.data.report.fields[index];
                    let alias_key = field.model_select_value;

                    let name = Object.keys(field.options).find(function (key) {
                        return field.options[key][alias_key] == value;
                    });

                    if (name) {
                        return field.options[name][field.model_select_name];
                    }

                    return '-';
                } catch (error) {
                    return value;
                }
            },
            /**
             * Reset Report Parameters
             */
            resetParameters() {
                this.$emit('reset-report-parameters');
            },
            /**
             * Go to Previous page of Results
             *
             * @return {Promise}
             */
            previousPage() {
                let vm = this;

                return new Promise(resolve => {

                    vm.loaded = false;

                    vm.page -= 1;

                    resolve(vm.page);
                }).then(page => {
                    vm.data.render(vm.data.report.id, page).then(results => {
                        vm.$emit('previous-report-results', results);
                        vm.$emit('report-rendered', results);

                        vm.loaded = true;
                    });
                }).catch(error => {
                    vm.error = error;
                    vm.$emit('report-render-error', error);
                })
            },
            /**
             * Go to Next page of results
             *
             * @return {Promise}
             */
            nextPage() {
                let vm = this;

                return new Promise(resolve => {
                    vm.loaded = false;

                    vm.page += 1;

                    resolve(vm.page);
                }).then(page => {
                   vm.data.render(vm.data.report.id, page).then(results => {
                        vm.$emit('next-report-results', results);
                        vm.$emit('report-rendered', results);

                        vm.loaded = true;
                   });
                }).catch(error => {
                    vm.error = error;
                    vm.$emit('report-render-error', error);
                })
            }
        },
        mounted() {
            let vm = this;

            new Promise((resolve, reject) => {
                vm.data = vm.value;

                vm.data.render(vm.data.report.id, vm.page).then(results => {
                    vm.$emit('report-rendered', results);
                    resolve(true);
                }).catch(error => {
                    reject(error);
                });
            }).then(response => {
                vm.loaded = response;
            }).catch(error => {
                vm.error = error;
                vm.$emit('report-render-error', error);
            });
        }
    }
</script>

<style scoped>
    .raw-query {
        padding-bottom: 40px;
    }
</style>