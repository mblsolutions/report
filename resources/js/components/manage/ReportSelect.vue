<template>
    <div :id="'report-select-' + index" class="report-select" v-if="loaded && data.deleted_at === null">

        <hr class="my-5">

        <h4 class="text-base text-brand-blue-500 px-2">
            {{ columnLabel }} <span class="bg-brand-blue-500 text-white p-1 text-base"><small>#</small>{{ (index + 1) }}</span>
            <div class="pull-right float-right">
                <button class="brand-btn-alt mr-2" @click.prevent="removeSelect(index)">Remove Column</button>
                <button v-if="show_add_button" class="brand-btn" @click.prevent="addSelect">Add Column</button>
            </div>
        </h4>

        <div class="form-group">
            <label class="form-label" for="select_column">Select Column</label>
            <input id="select_column" type="text" name="column" class="form-control" :class="{ 'is-invalid': report.hasError('column', index, 'selects') }" placeholder="Select Column (e.g. users.name)" v-model="data.column">

            <div v-if="report.hasError('column', index, 'selects')" class="invalid-feedback">{{ report.getError('column', index, 'selects') }}</div>
        </div>

        <div class="md:flex">
            <div class="form-group md:w-5/12">
                <label class="form-label" for="column_alias">Column Alias</label>
                <input id="column_alias" type="text" name="alias" class="form-control" :class="{ 'is-invalid': report.hasError('alias', index, 'selects') }" placeholder="Column Alias (e.g. Name)" v-model="data.alias">

                <div v-if="report.hasError('alias', index, 'selects')" class="invalid-feedback">{{ report.getError('alias', index, 'selects') }}</div>
            </div>
            <div class="form-group w-7/12 md:4/12">
                <label class="form-label" for="column_type">Column Type</label>
                <select name="type" id="column_type" class="form-control" :class="{ 'is-invalid': report.hasError('type', index, 'selects') }" v-model="data.type">
                    <option :value="null">Select Column Type</option>
                    <option :value="type.value" v-for="type in types">{{ type.name }}</option>
                </select>

                <div v-if="report.hasError('type', index, 'selects')" class="invalid-feedback">{{ report.getError('type', index, 'selects') }}</div>
            </div>
            <div class="form-group w-3/12 md:3/12 md:text-right">
                <label class="form-label">Column Order</label>
                <button class="rounded-sm border-2 border-brand-blue-500 text-sm bg-brand-blue-500 text-brand-blue-100 py-2 px-3 shadow ml-1" @click.prevent="moveUp(index)" :disabled="index === 0">
                    <i class="material-icons">expand_less</i>
                </button>
                <button class="rounded-sm border-2 border-brand-blue-500 text-sm bg-brand-blue-500 text-brand-blue-100 py-2 px-3 shadow" @click.prevent="moveDown(index)" :disabled="show_add_button">
                    <i class="material-icons">expand_more</i>
                </button>
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
            let vm = this;

            new Promise(resolve => {
                vm.report = vm.value;
                vm.data = vm.value.data.selects[vm.index];

                vm.data.column_order = vm.index;

                resolve(true);
            }).then(response => {
                vm.loaded = response;
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
