<template>
    <div id="show-report">

        <div v-if="loaded">
            <ParameterSelection
                    @submit-report-parameters="submitReport" v-model="report"
                    v-if="parameters_submitted === false && report.report.fields.length > 0"
            >
                <slot name="loading"></slot>
            </ParameterSelection>

            <div v-else>
                <RenderReport
                        @reset-report-parameters="resetReportParameters"  @report-rendered="reportRendered" v-model="report"
                >
                    <slot name="loading"></slot>
                </RenderReport>
            </div>
        </div>
        <div v-else>
            <div v-if="hasLoadingSlot">
                <slot name="loading"></slot>
            </div>
        </div>

    </div>
</template>

<script>
    import {Report} from "../app/report/Report";
    import ParameterSelection from "./show/ParameterSelection";
    import RenderReport from "./show/RenderReport";

    export default {
        name: "ShowReport",
        props: {
            id: {
                type: Number,
                required: true
            }
        },
        components: {
            ParameterSelection,
            RenderReport
        },
        data() {
            return {
                report: null,
                parameters_submitted: false,
                loaded: false
            }
        },
        methods: {
            /**
             * Check if a loading slot exists
             *
             * @return {boolean}
             */
            hasLoadingSlot() {
                return !! this.$slots.loading;
            },
            /**
             * Report Rendered Event
             *
             * @param results
             */
            reportRendered(results) {
                this.$emit('render-show-report', {
                    results: results,
                    params: this.report.data
                });
            },
            /**
             * Submit Report Render
             */
            submitReport() {
                this.parameters_submitted = true;
            },
            /**
             * Reset Report Parameters
             */
            resetReportParameters() {
                this.parameters_submitted = false;
            }
        },
        mounted() {
            let vm = this;

            new Promise((resolve) => {
                vm.report = new Report();

                vm.report.getReport(vm.id).then(report => {
                    vm.$emit('load-show-report', report);
                    resolve(true);
                })
            }).then(loaded => {
                vm.loaded = loaded;
            });
        }
    }
</script>

<style scoped>

</style>