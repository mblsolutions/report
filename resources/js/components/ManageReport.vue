<template>
    <div id="manage-report">

        <form :action="action" :method="method" enctype="multipart/form-data" v-if="loaded">

            <div class="my-3 p-3 bg-white rounded shadow-sm">
                <h3>Report Information</h3>

                <p class="text-muted">
                    Settings pertaining to how a report is rendered and the report information. When a report is rendered two primary sources of information are available,
                    the report data (individual rows of information based on the report query) and the report totals (the counts/sums of
                    columns on the report). Columns are configured by the report selects and can be configured to count and sum relevant columns
                    (please note these columns are limited to numeric data types only).
                </p>

                <div class="form-group">
                    <label for="report_name">Report Name</label>
                    <input id="report_name" type="text" name="name" class="form-control" placeholder="Report Name" v-model="report.data.name">
                </div>

                <hr class="col-xs-12">

                <div class="form-group">
                    <div class="form-check">
                        <input id="show_data" type="checkbox" name="show_data" class="form-check-input" v-model="report.data.show_data">
                        <label class="form-check-label" for="show_data">
                            Display Report Data
                        </label>
                    </div>

                    <small class="text-muted">
                        Should this report display the result data when viewing the report.
                    </small>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input id="show_totals" type="checkbox" name="show_totals" class="form-check-input" v-model="report.data.show_totals">
                        <label class="form-check-label" for="show_totals">
                            Display Report Totals
                        </label>
                    </div>

                    <small class="text-muted">
                        Should this report display the result totals when viewing the report.
                    </small>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input id="active" type="checkbox" name="active" class="form-check-input" v-model="report.data.active">
                        <label class="form-check-label" for="active">
                            Active
                        </label>
                    </div>
                </div>
            </div>

            <div class="my-3 p-3 bg-white rounded shadow-sm">
                <h3>
                    Report Fields
                    <button class="btn btn-sm btn-primary pull-right float-right" @click.prevent="addNewReportField">Add New Field</button>
                </h3>

                <p class="text-muted">
                    Report fields are the parameters passed to the report, these parameters are used to further refine rendered report data e.g.
                    passing a start_date and end_date to limit results between two dates.
                </p>

                <div v-for="(field, index) in report.data.fields">
                    <transition name="fade">
                        <ReportField
                                :index="index" v-model="report.data.fields[index]" :show_add_button="isLastField(index)"
                                @remove-field="removeReportField" @add-field="addNewReportField"
                        ></ReportField>
                    </transition>
                </div>
            </div>

            <div class="my-3 p-3 bg-white rounded shadow-sm">
                <h3>Report Query</h3>

                <div class="form-group">
                    <button class="btn btn-primary" @click.prevent="submitReport">
                        Submit Report
                    </button>
                </div>
            </div>
        </form>
        <div v-else>
            <div class="my-3 p-3 bg-white rounded shadow-sm" v-if="hasLoadingSlot">
                <slot name="loading"></slot>
            </div>
        </div>
    </div>
</template>

<script>
    import {ManageReport} from "../app/manage/ManageReport";
    import ReportField from "./manage/ReportField";

    export default {
        name: "ManageReport",
        props: {
            id: {
                type: Number,
                required: false,
                default: null
            },
            action: {
                type: String,
                default: '/api/report/manage/create'
            },
            method: {
                type: String,
                default: 'post'
            }
        },
        components: {
            ReportField
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
             * Remove Report Field
             */
            removeReportField(index) {
                this.report.removeField(index);

                this.$emit('remove-report-field', index);
            },
            /**
             * Add New Report Field
             */
            addNewReportField() {
                let field = this.report.addNewField();

                this.$emit('add-report-field', field);
            },
            /**
             * Submit Report
             */
            submitReport() {
                alert('report submitted');
            },
            /**
             * Check if this is Last Field Row
             *
             * @return {Boolean}
             */
            isLastField(index) {
                return Number(index + 1) === this.report.getNextFieldIndex()
            }
        },
        mounted() {
            let vm = this;

            new Promise((resolve) => {

                vm.report = new ManageReport();

                vm.report.load(vm.id).then(response => {
                    resolve(true);
                });
            }).then(report => {
                vm.loaded = true;
            });
        }
    }
</script>

<style scoped>
    .fade-enter-active, .fade-leave-active {
        transition: opacity 0.7s;
    }
    .fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
        opacity: 0;
    }
</style>