<template>
    <div id="show-report">

        <div v-if="loaded">
            <ParameterSelection v-model="report" v-if="report.report.fields.length > 0">
                <slot name="loading"></slot>
            </ParameterSelection>

            <div v-else>
                <RenderReport v-model="report" @report-rendered="reportRendered">
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
    import ParameterSelection from "./show/ParameterSelection";
    import RenderReport from "./show/RenderReport";
    import {Report} from "../app/report/Report";

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
                this.$emit('render-show-report', results);
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