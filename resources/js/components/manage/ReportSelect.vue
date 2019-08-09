<template>
    <div :id="'report-select-' + index" class="report-select" v-if="loaded && data.deleted_at === null">

        <hr class="col-xs-12">

        <h4>
            {{ columnLabel }} <span class="badge badge-primary"><small>#</small>{{ (index + 1) }}</span>
            <div class="pull-right float-right">
                <button class="btn btn-sm btn-danger mr-2" @click.prevent="removeSelect(index)">Remove Column</button>
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
                            <option :value="null">Select Column Type</option>
                            <option :value="type.value" v-for="type in types">{{ type.name }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-4 col-xs-4 col-md-3">
                <div class="form-group">
                    <label>Column Order {{ data.column_order }}</label>
                    <div class="block-buttons">
                        <div class="btn-group">
                            <button class="btn btn-sm btn-primary btn-up" @click.prevent="moveUp(index)" :disabled="index === 0">Move Up</button>
                            <button class="btn btn-sm btn-primary btn-down" @click.prevent="moveDown(index)" :disabled="show_add_button">Move Down</button>
                        </div>
                    </div>
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
                    return [];
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
                if (!this.data.alias) {
                    return 'Label';
                }

                return this.data.alias;
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
             * Move Column Up
             */
            moveUp(index) {
                this.$emit('move-select-up', index);
            },
            /**
             * Move Column Down
             */
            moveDown(index) {
                this.$emit('move-select-down', index);
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
    .block-buttons {
        position: relative;
    }
    .block-buttons .btn-group {
        width: 100%;
    }

    .block-buttons button {
        width: 50%;
    }

    .btn-down {
        border-left: 2px solid white !important;
    }
</style>