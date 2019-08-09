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

                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="report_name">Report Name</label>
                            <input id="report_name" type="text" name="name" class="form-control" placeholder="Report Name" :class="{ 'is-invalid': report.hasError('name') }" v-model="report.data.name">

                            <div v-if="report.hasError('name')" class="invalid-feedback">{{ report.getError('name') }}</div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-6">
                        <label for="connection">Connection</label>
                        <select name="type" id="connection" class="form-control" :class="{ 'is-invalid': report.hasError('connection') }" v-model="report.data.connection">
                            <option :value="null">Select Connection</option>
                            <option :value="connection.value" v-for="connection in report.connections">{{ connection.name }}</option>
                        </select>

                        <div v-if="report.hasError('connection')" class="invalid-feedback">{{ report.getError('connection') }}</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 col-md-8">
                        <div class="form-group">
                            <label for="report_description">Report Description</label>
                            <input id="report_description" type="text" name="description" class="form-control" placeholder="Description" :class="{ 'is-invalid': report.hasError('description') }" v-model="report.data.description">

                            <div v-if="report.hasError('description')" class="invalid-feedback">{{ report.getError('description') }}</div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-4">
                        <div class="form-group">
                            <label for="report_display_limit">Display Limit</label>
                            <input id="report_display_limit" type="text" name="display_limit" class="form-control" placeholder="25" :class="{ 'is-invalid': report.hasError('display_limit') }" v-model="report.data.display_limit">

                            <div v-if="report.hasError('display_limit')" class="invalid-feedback">{{ report.getError('display_limit') }}</div>
                        </div>
                    </div>
                </div>

                <hr class="col-xs-12">

                <div class="form-group">
                    <div class="form-check">
                        <input id="show_data" type="checkbox" name="show_data" class="form-check-input" v-model="report.data.show_data">
                        <label class="form-check-label" for="show_data">
                            Display Report Data
                        </label>

                        <div v-if="report.hasError('show_data')" class="invalid-feedback">{{ report.getError('show_data') }}</div>
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

                        <div v-if="report.hasError('show_totals')" class="invalid-feedback">{{ report.getError('show_totals') }}</div>
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

                        <div v-if="report.hasError('active')" class="invalid-feedback">{{ report.getError('active') }}</div>
                    </div>
                </div>
            </div>

            <div class="my-3 p-3 bg-white rounded shadow-sm">
                <h3>
                    Report Fields
                    <button class="btn btn-sm btn-primary pull-right float-right" @click.prevent="addNewReportField">Add Field</button>
                </h3>

                <p class="text-muted">
                    Report fields are the parameters passed to the report, these parameters are used to further refine rendered report data e.g.
                    passing a start_date and end_date to limit results between two dates.
                </p>

                <div v-if="report.data.fields.length > 0">
                    <div v-for="(field, index) in report.data.fields">
                        <transition name="fade">
                            <ReportField
                                    :index="index" :show_add_button="isLastField(index)"
                                    :models="report.models"
                                    v-model="report.data.fields[index]"
                                    @remove-field="removeReportField" @add-field="addNewReportField"
                            ></ReportField>
                        </transition>
                    </div>
                </div>
                <div v-else>
                    <div class="alert alert-primary" role="alert">
                        There are no fields added to this report. <a href="#" class="alert-link" @click.prevent="addNewReportField">Add a field</a> to allow users to
                        further refine report results.
                    </div>
                </div>
            </div>

            <div class="my-3 p-3 bg-white rounded shadow-sm">
                <h3>Report Query</h3>

                <p class="text-muted">
                    Build up the report using the report query builder.
                </p>

                <h3>
                    Selects
                    <button class="btn btn-sm btn-primary pull-right float-right" @click.prevent="addNewReportSelect">Add Column</button>
                </h3>

                <p class="text-muted">
                    Each <b>select</b> in the report will be turned into a column on the report, the <b>alias</b> will be the column title when rendering reports.
                    The select <b>type</b> will determine how the rendered data is formatted, and if the data should display a total.
                </p>

                <div  v-if="report.data.selects.length > 0">
                    <div v-for="(select, index) in report.data.selects">
                        <transition name="fade">
                            <ReportSelect
                                    :key="select.id ? select.id : select" :index="index" :show_add_button="isLastSelect(index)"
                                    :types="report.data_types" v-model="report.data.selects[index]"
                                    @move-select-up="moveReportSelectUp" @move-select-down="moveReportSelectDown"
                                    @remove-select="removeReportSelect" @add-select="addNewReportSelect"
                            ></ReportSelect>
                        </transition>
                    </div>
                </div>
                <div v-else>
                    <div class="alert alert-primary" role="alert">
                        <a href="#" class="alert-link" @click.prevent="addNewReportSelect">Add selected columns</a> to show data results on the report.
                    </div>
                </div>

                <hr class="col-xs-12">

                <h3>
                    From
                </h3>

                <p class="text-muted">
                    The primary table the report will be run from.
                </p>

                <div class="form-group">
                    <label for="report_table">Table</label>
                    <input id="report_table" type="text" name="table" class="form-control" placeholder="Table" :class="{ 'is-invalid': report.hasError('table') }" v-model="report.data.table">

                    <div v-if="report.hasError('table')" class="invalid-feedback">{{ report.getError('table') }}</div>
                </div>

                <hr class="col-xs-12">

                <h3>
                    Joins
                    <button class="btn btn-sm btn-primary pull-right float-right" @click.prevent="addNewReportJoin">Add Join</button>
                </h3>

                <p class="text-muted">
                    Join additional tables into the query to access data relational data.
                </p>

                <div v-if="report.data.joins.length > 0">
                    <div v-for="(join, index) in report.data.joins">
                        <transition name="fade">
                            <ReportJoin
                                    :index="index" :show_add_button="isLastJoin(index)"
                                    v-model="report.data.joins[index]"
                                    @remove-join="removeReportJoin" @add-join="addNewReportJoin"
                            ></ReportJoin>
                        </transition>
                    </div>
                </div>
                <div v-else>
                    <div class="alert alert-primary" role="alert">
                        <a href="#" class="alert-link" @click.prevent="addNewReportField">Add joins</a> to access relational data associated
                        with the primary table.
                    </div>
                </div>

                <hr class="col-xs-12">

                <h3>
                    Where
                </h3>

                <p class="text-muted">
                    Columns on the primary table or any joined tables can be used in this section e.g. <code><b>users.active</b> = 1</code>.
                    Field parameters can be used by adding the criteria alias in curly braces e.g. <code>users.created_at >= <b>&#123;start_date&#125;</b></code>.
                    We recommend always appending the columns table to avoid ambiguous column names e.g. <code><b>users</b>.id = 1 AND <b>logs</b>.id > 100</code>
                </p>

                <div class="form-group">
                    <label for="report_where">Where</label>
                    <div class="autocomplete">
                        <transition name="fade">
                            <div class="autocomplete-list" v-if="show_suggestions && report.data.fields.length > 0">
                                <div class="list-group">
                                    <a class="list-group-item" @click="dismissSuggestions">Available Fields <small>(click to dismiss suggestions)</small></a>
                                    <span v-for="(field, index) in report.data.fields">
                                        <a href="#" class="list-group-item list-group-item-action"
                                           v-if="field.alias" @click.prevent="selectSuggestion(report.data.fields[index].alias, 'report_where')"
                                        >{{ field.alias }} ({{ field.type }})</a>
                                    </span>
                                </div>
                            </div>
                        </transition>
                        <textarea id="report_where" name="where" rows="8" class="form-control"
                                  :class="{ 'is-invalid': report.hasError('where') }" v-model="report.data.where" @keyup="checkForSuggestions"
                        ></textarea>
                    </div>

                    <div v-if="report.hasError('where')" class="invalid-feedback">{{ report.getError('where') }}</div>
                </div>

                <hr class="col-xs-12">

                <h3>
                    Result Sorting
                </h3>

                <p class="text-muted">
                    Sort you results.
                </p>

                <div class="form-group">
                    <label for="report_groupby">Group By</label>
                    <input id="report_groupby" type="text" name="groupby" class="form-control" placeholder="Group By (e.g. users.role)" :class="{ 'is-invalid': report.hasError('groupby') }" v-model="report.data.groupby">

                    <div v-if="report.hasError('groupby')" class="invalid-feedback">{{ report.getError('groupby') }}</div>
                </div>

                <div class="form-group">
                    <label for="report_having">Having</label>
                    <input id="report_having" type="text" name="having" class="form-control" placeholder="Having (e.g. users.id > 100)" :class="{ 'is-invalid': report.hasError('having') }" v-model="report.data.having">

                    <div v-if="report.hasError('having')" class="invalid-feedback">{{ report.getError('having') }}</div>
                </div>

                <div class="form-group">
                    <label for="report_orderby">Order By</label>
                    <input id="report_orderby" type="text" name="orderby" class="form-control" placeholder="Order By (e.g. users.created_at DESC)" :class="{ 'is-invalid': report.hasError('orderby') }" v-model="report.data.orderby">

                    <div v-if="report.hasError('orderby')" class="invalid-feedback">{{ report.getError('orderby') }}</div>
                </div>

                <hr class="col-xs-12">

                <div class="form-group">
                    <button class="btn btn-primary" @click.prevent="submitReport">
                        Submit Report
                    </button>
                    <button class="btn btn-success" @click.prevent="testReport">
                        Test Report
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
    import ReportSelect from "./manage/ReportSelect";
    import ReportJoin from "./manage/ReportJoin";

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
                required: true
            },
            method: {
                type: String,
                required: true
            },
            redirect_location: {
                type: String,
                default: '/report/manage'
            }
        },
        components: {
            ReportField,
            ReportSelect,
            ReportJoin
        },
        data() {
            return {
                report: null,
                loaded: false,
                show_suggestions: false,
                suggestions: [
                    'sug 1',
                    'sug 4',
                    'sug 3',
                    'sug 2',
                ]
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
             * Remove Select
             */
            removeReportSelect(index) {
                this.report.removeSelect(index);

                this.$emit('remove-report-select', index);
            },
            /**
             * Move Report Select Column Up
             */
            moveReportSelectUp(index) {
                let select = this.report.moveSelectUp(index);

                this.$emit('move-report-select-up', select);
            },
            /**
             * Move Report Select Column Down
             */
            moveReportSelectDown(index) {
                let select = this.report.moveSelectDown(index);

                this.$emit('move-report-select-down', select);
            },
            /**
             * Add New Select
             */
            addNewReportSelect() {
                let select = this.report.addNewSelect();

                this.$emit('add-report-select', select);
            },
            /**
             * Remove Join
             */
            removeReportJoin(index) {
                this.report.removeJoin(index);

                this.$emit('remove-report-join', index);
            },
            /**
             * Add New Join
             */
            addNewReportJoin() {
                let join = this.report.addNewJoin();

                this.$emit('add-report-join', join);
            },
            /**
             * Check if this is Last Field Row
             *
             * @return {Boolean}
             */
            isLastField(index) {
                return Number(index + 1) === this.report.getNextFieldIndex()
            },
            /**
             * Check if this is Last Select Row
             *
             * @return {Boolean}
             */
            isLastSelect(index) {
                return Number(index + 1) === this.report.getNextSelectIndex()
            },
            /**
             * Check if this is Last Join Row
             *
             * @return {Boolean}
             */
            isLastJoin(index) {
                return Number(index + 1) === this.report.getNextJoinIndex()
            },
            /**
             * Submit Report
             */
            submitReport() {
                let vm = this;

                vm.report.submit(vm.action, vm.method).then(result => {
                    vm.$emit('report-submitted', result);

                    window.location.href = this.redirect_location;
                }).catch(error => {
                    console.error(error)
                });
            },
            /**
             * Test Report
             */
            testReport() {
                let vm = this;

                vm.report.test().then(result => {
                    vm.$emit('report-test-results', result);
                });
            },
            /**
             * Check for suggestions when typing
             *
             * @param event
             */
            checkForSuggestions(event) {
                if (event.key === '{') {
                    this.show_suggestions = true;
                }
            },
            /**
             * Select a Suggestion
             */
            selectSuggestion(suggestion, id) {
                let vm = this;
                let where = vm.report.data.where;

                if (where != null) {
                    vm.report.data.where = where + suggestion + '} ';
                } else {
                    vm.report.data.where = suggestion + '} ';
                }

                this.dismissSuggestions();

                document.getElementById(id).focus();
            },
            /**
             * Dismiss Suggestions
             */
            dismissSuggestions() {
                this.show_suggestions = false;
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
    .autocomplete {
        position: relative;
    }

    .autocomplete-list {
        position: absolute;
        display: block;
        width: 100%;
    }

    .fade-enter-active, .fade-leave-active {
        transition: opacity 0.7s;
    }
    .fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
        opacity: 0;
    }
</style>