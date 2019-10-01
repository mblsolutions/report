<template>
    <div :id="'report-field-' + index" class="report-field" v-if="loaded && data.deleted_at === null">

        <hr class="my-5">


        <h4 class="text-xl text-brand-blue-500 px-2">
            {{ fieldLabel }}
            <div class="pull-right float-right">
                <button class="brand-btn-alt mr-2" @click.prevent="removeField(index)">Remove Field</button>
                <button v-if="show_add_button" class="brand-btn" @click.prevent="addField">Add Field</button>
            </div>
        </h4>

        <div class="form-group">
            <label class="form-label" for="field_label">Field Label</label>
            <input id="field_label" type="text" name="label" class="form-control" :class="{ 'is-invalid': report.hasError('label', index, 'fields') }" placeholder="Label" v-model="data.label">

            <div v-if="report.hasError('label', index, 'fields')" class="invalid-feedback">{{ report.getError('label', index, 'fields') }}</div>
        </div>

        <div class="md:flex">
            <div class="form-group md:w-1/2">
                <label class="form-label" for="criteria_alias">Criteria Alias</label>
                <input id="criteria_alias" type="text" name="alias" :class="{ 'is-invalid': report.hasError('alias', index, 'fields') }" class="form-control" placeholder="Criteria Alias" v-model="data.alias">

                <small class="text-muted">
                    The Criteria Alias <i>start_date</i> could be used in the query builder
                    to filter users created after the specified date e.g. <code>users.created_at >= <b>&#123;start_date&#125;</b></code>
                </small>

                <div v-if="report.hasError('alias', index, 'fields')" class="invalid-feedback">{{ report.getError('alias', index, 'fields') }}</div>
            </div>
            <div class="form-group md:w-1/2">
                <label class="form-label" for="criteria_type">Criteria Type</label>
                <select name="type" id="criteria_type" class="form-control" :class="{ 'is-invalid': report.hasError('type', index, 'fields') }" v-model="data.type">
                    <option :value="type.value" v-for="type in types">{{ type.name }}</option>
                </select>

                <div v-if="report.hasError('type', index, 'fields')" class="invalid-feedback">{{ report.getError('type', index, 'fields') }}</div>
            </div>
        </div>

        <transition name="fade">
            <div v-if="data.type === 'select'">
                <h5 class="text-lg text-brand-blue-500 px-2">Select Options</h5>

                <p class="text-muted p-2">
                    The Model used to populate the select options when selecting the parameters of the report e.g. Users would
                    display all available <code>User</code> models to filter by using the <code>`id`</code> attribute.
                </p>

                <div class="form-group">
                    <label class="form-label" for="report_model">Model</label>
                    <select name="model" id="report_model" class="form-control" :class="{ 'is-invalid': report.hasError('model', index, 'fields') }" v-model="data.model">
                        <option :value="null">Select Model</option>
                        <option :value="model.value" v-for="model in models">{{ model.name }}</option>
                    </select>

                    <div v-if="report.hasError('model', index, 'fields')" class="invalid-feedback">{{ report.getError('model', index, 'fields') }}</div>
                </div>

                <div class="md:flex">
                    <div class="form-group  md:w-1/2">
                        <label class="form-label" for="model_select_value">Value </label>
                        <input id="model_select_value" type="text" name="model_select_value" class="form-control" :class="{ 'is-invalid': report.hasError('model_select_value', index, 'fields') }" placeholder="Select Value" v-model="data.model_select_value">

                        <small class="text-muted">
                            The attribute on the model use for the selects value e.g. <code><b>`id`</b> on the <b>Users</b> model</code>
                        </small>

                        <div v-if="report.hasError('model_select_value', index, 'fields')" class="invalid-feedback">{{ report.getError('model_select_value', index, 'fields') }}</div>
                    </div>

                    <div class="form-group  md:w-1/2">
                        <label class="form-label" for="model_select_name">Select Name</label>
                        <input id="model_select_name" type="text" name="model_select_name" class="form-control" :class="{ 'is-invalid': report.hasError('model_select_name', index, 'fields') }" placeholder="Select Name" v-model="data.model_select_name">

                        <small class="text-muted">
                            The attribute on the model use for the selects value e.g. <code><b>`name`</b> on the <b>Users</b> model</code>
                        </small>

                        <div v-if="report.hasError('model_select_name', index, 'fields')" class="invalid-feedback">{{ report.getError('model_select_name', index, 'fields') }}</div>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
    export default {
        name: "ReportField",
        props: {
            index: {
                type: Number,
                required: true
            },
            value: {
                type: Object
            },
            errors: {
                default: function () {
                    return [];
                }
            },
            types: {
                type: Array,
                default: function () {
                    return [
                        { value: 'text', name: 'Text' },
                        { value: 'number', name: 'Number' },
                        { value: 'date', name: 'Date' },
                        { value: 'time', name: 'Time' },
                        { value: 'datetime', name: 'DateTime' },
                        { value: 'select', name: 'Select' },
                    ];
                }
            },
            models: {
                type: Object,
                default: function () {
                    return {};
                }
            },
            show_add_button: {
                type: Boolean,
                default: false
            }
        },
        data() {
            return {
                data: null,
                report: null,
                loaded: false
            }
        },
        computed: {
            /**
             * Get the Field Label
             *
             * @return {string}
             */
            fieldLabel() {
                if (!this.data.label) {
                    return 'Label';
                }

                return this.data.label;
            }
        },
        methods: {
            /**
             * Remove Field
             */
            removeField(index) {
                this.$emit('remove-field', index);
            },
            /**
             * Add New Field
             */
            addField() {
                this.$emit('add-field');
            }
        },
        mounted() {
            let vm = this;

            new Promise(resolve => {
                vm.report = vm.value;
                vm.data = vm.value.data.fields[vm.index];
                resolve(true);
            }).then(response => {
                vm.loaded = response;
            });
        }
    }
</script>

<style scoped>
    .badge {
        font-size: 0.9rem;
        font-weight: 600;
    }
    .badge small {
        font-size: 0.7rem;
        font-weight: 100;
    }
</style>
