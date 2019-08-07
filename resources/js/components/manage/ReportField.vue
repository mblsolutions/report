<template>
    <div :id="'report-field-' + index" class="report-field" v-if="loaded && data.deleted_at === null">

        <hr class="col-xs-12">

        <h4>
            {{ fieldLabel }} <span class="badge badge-primary"><small>#</small>{{ (index + 1) }}</span>
            <div class="pull-right float-right">
                <button class="btn btn-sm btn-danger mr-2" @click.prevent="removeField(index)">Remove Field</button>
                <button v-if="show_add_button" class="btn btn-sm btn-success" @click.prevent="addField">Add Field</button>
            </div>
        </h4>

        <div class="form-group">
            <label for="field_label">Field Label</label>
            <input id="field_label" type="text" name="label" class="form-control" placeholder="Label" v-model="data.label">
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-6">
                <div class="form-group">
                    <label for="criteria_alias">Criteria Alias</label>
                    <input id="criteria_alias" type="text" name="alias" class="form-control" placeholder="Criteria Alias" v-model="data.alias">

                    <small class="text-muted">
                        The Criteria Alias <i>start_date</i> could be used in the query builder
                        to filter users created after the specified date e.g. <code>users.created_at >= <b>&#123;start_date&#125;</b></code>
                    </small>
                </div>
            </div>

            <div class="col-xs-12 col-md-6">
                <div class="form-group">
                    <label for="criteria_type">Criteria Type</label>
                    <select name="type" id="criteria_type" class="form-control" v-model="data.type">
                        <option :value="type.value" v-for="type in types">{{ type.name }}</option>
                    </select>
                </div>
            </div>
        </div>

        <transition name="fade">
            <div v-if="data.type === 'select'">
                <h5>Select Options</h5>

                <small class="text-muted">
                    The Model used to populate the select options when selecting the parameters of the report e.g. Users would
                    display all available <code>User</code> models to filter by using the <code>`id`</code> attribute.
                </small>

                <div class="form-group">
                    <label for="model">Model</label>
                    <select name="type" id="model" class="form-control" v-model="data.model">
                        <option :value="null">Select Model</option>
                        <option :value="model.value" v-for="model in models">{{ model.name }}</option>
                    </select>
                </div>

                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="model_select_value">Value </label>
                            <input id="model_select_value" type="text" name="model_select_value" class="form-control" placeholder="Select Value" v-model="data.model_select_value">

                            <small class="text-muted">
                                The attribute on the model use for the selects value e.g. <code><b>`id`</b> on the <b>Users</b> model</code>
                            </small>
                        </div>
                    </div>

                    <div class="col-xs-12 col-md-6">
                        <div class="form-group">
                            <label for="model_select_name">Select Name</label>
                            <input id="model_select_name" type="text" name="model_select_name" class="form-control" placeholder="Select Name" v-model="data.model_select_name">

                            <small class="text-muted">
                                The attribute on the model use for the selects value e.g. <code><b>`name`</b> on the <b>Users</b> model</code>
                            </small>
                        </div>
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
            'show_add_button': {
                type: Boolean,
                default: false
            }
        },
        data() {
            return {
                data: null,
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
    .badge {
        font-size: 0.9rem;
        font-weight: 600;
    }
    .badge small {
        font-size: 0.7rem;
        font-weight: 100;
    }
</style>