<template>
    <div :id="'report-join-' + index" class="join" v-if="loaded && data.deleted_at === null">

        <hr class="my-5">

        <h4 class="text-xl text-brand-blue-500 px-2">
            Join <span class="bg-brand-blue-500 text-white p-1 text-base"><small>#</small>{{ (index + 1) }}</span>
            <div class="pull-right float-right">
                <button class="brand-btn-alt mr-2" @click.prevent="removeJoin(index)">Remove Join</button>
                <button v-if="show_add_button" class="brand-btn" @click.prevent="addJoin">Add Join</button>
            </div>
        </h4>

        <div class="md:flex">
            <div class="form-group md:w-1/2">
                <label class="form-label" for="join_type">Join Type</label>
                <select name="type" id="join_type" class="form-control" :class="{ 'is-invalid': report.hasError('type', index, 'joins') }" v-model="data.type">
                    <option :value="type.value" v-for="type in types">{{ type.name }}</option>
                </select>

                <div v-if="report.hasError('type', index, 'joins')" class="invalid-feedback">{{ report.getError('type', index, 'joins') }}</div>
            </div>
            <div class="form-group md:w-1/2">
                <label class="form-label" for="join_table">Join Table</label>
                <input id="join_table" type="text" name="table" class="form-control" :class="{ 'is-invalid': report.hasError('table', index, 'joins') }" placeholder="Table" v-model="data.table">

                <div v-if="report.hasError('table', index, 'joins')" class="invalid-feedback">{{ report.getError('table', index, 'joins') }}</div>
            </div>
        </div>

        <div class="md:flex">
            <div class="form-group md:w-5/12">
                <label class="form-label" for="join_first">Column 1</label>
                <input id="join_first" type="text" name="first" class="form-control" :class="{ 'is-invalid': report.hasError('first', index, 'joins') }" placeholder="First Column (e.g. users.team_id)" v-model="data.first">

                <div v-if="report.hasError('first', index, 'joins')" class="invalid-feedback">{{ report.getError('first', index, 'joins') }}</div>
            </div>
            <div class="form-group md:w-2/12">
                <label class="form-label" for="join_condition">Condition</label>
                <input id="join_condition" type="text" name="operator" class="form-control" :class="{ 'is-invalid': report.hasError('operator', index, 'joins') }" placeholder="Condition (e.g. =, !=)" v-model="data.operator">

                <div v-if="report.hasError('operator', index, 'joins')" class="invalid-feedback">{{ report.getError('operator', index, 'joins') }}</div>
            </div>
            <div class="form-group md:w-5/12">
                <label class="form-label" for="join_second">Column 2</label>
                <input id="join_second" type="text" name="second" class="form-control" :class="{ 'is-invalid': report.hasError('second', index, 'joins') }" placeholder="Second Column (e.g. teams.id)" v-model="data.second">

                <div v-if="report.hasError('second', index, 'joins')" class="invalid-feedback">{{ report.getError('second', index, 'joins') }}</div>
            </div>
        </div>
    </div>

</template>

<script>
    export default {
        name: "ReportJoin",
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
                        { value: 'inner_join', name: 'INNER' },
                        { value: 'left_join', name: 'LEFT' },
                        { value: 'right_join', name: 'RIGHT' },
                    ];
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
        methods: {
            /**
             * Remove Join
             */
            removeJoin(index) {
                this.$emit('remove-join', index);
            },
            /**
             * Add New Join
             */
            addJoin() {
                this.$emit('add-join');
            }
        },
        mounted() {
            let vm = this;

            new Promise(resolve => {
                vm.report = vm.value;
                vm.data = vm.value.data.joins[vm.index];
                resolve(true);
            }).then(response => {
                vm.loaded = response;
            });
        }
    }
</script>
