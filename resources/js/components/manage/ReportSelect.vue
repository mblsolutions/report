<template>
    <div :id="'report-select-' + index" class="report-select" v-if="loaded">

        <hr class="col-xs-12">

        <h4>
            {{ columnLabel }} <span class="badge badge-primary"><small>#</small>{{ (index + 1) }}</span>
            <div class="pull-right float-right">
                <button class="btn btn-sm btn-danger mr-2" @click.prevent="removeSelect">Remove Column</button>
                <button v-if="show_add_button" class="btn btn-sm btn-success" @click.prevent="addSelect">Add Column</button>
            </div>
        </h4>

        <div class="form-group">
            <label for="select_column">Select Column</label>
            <input id="select_column" type="text" name="column" class="form-control" placeholder="Select Column (e.g. users.name)" v-model="data.column">
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-5">
                <div class="form-group">
                    <label for="column_alias">Column Alias</label>
                    <input id="column_alias" type="text" name="alias" class="form-control" placeholder="Column Alias (e.g. Name)" v-model="data.alias">
                </div>
            </div>
            <div class="col-8 col-xs-8 col-md-4">
                <div class="form-group">
                    <div class="form-group">
                        <label for="column_type">Column Type</label>
                        <select name="type" id="column_type" class="form-control" v-model="data.type">
                            <option :value="type.value" v-for="type in types">{{ type.name }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-4 col-xs-4 col-md-3">
                <div class="form-group">
                    <label for="column_order">Column Order</label>
                    <input id="column_order" type="text" name="column_order" class="form-control text-center" placeholder="Order" readonly v-model="data.column_order">
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    export default {
        name: "ReportSelect",
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
                        { value: 'string', name: 'String' },
                        { value: 'string_count', name: 'String (COUNT)' },
                        { value: 'integer', name: 'Integer' },
                        { value: 'integer_sum', name: 'Integer (SUM)' },
                        { value: 'integer_count', name: 'Integer (COUNT)' },
                        { value: 'decimal', name: 'Decimal' },
                        { value: 'decimal_sum', name: 'Decimal (SUM)' },
                        { value: 'currency', name: 'Currency' },
                        { value: 'currency_sum', name: 'Currency (SUM)' },
                        { value: 'date', name: 'date' },
                        { value: 'datetime', name: 'datetime' },
                    ];
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
             * Get the Column Label
             *
             * @return {string}
             */
            columnLabel() {
                return 'Column';
            }
        },
        methods: {
            /**
             * Remove Select
             */
            removeSelect(index) {
                this.$emit('remove-select', index);
            },
            /**
             * Add New Select
             */
            addSelect() {
                this.$emit('add-select');
            }
        },
        mounted() {
            new Promise(resolve => {
                this.data = this.value;

                this.data.column_order = this.index;

                resolve(true);
            }).then(response => {
                this.loaded = response;
            });
        }
    }
</script>

<style scoped>

</style>