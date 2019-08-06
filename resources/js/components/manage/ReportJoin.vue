<template>
    <div :id="'report-join-' + index" class="join" v-if="loaded">

        <hr class="col-xs-12">

        <h4>
            Join <span class="badge badge-primary"><small>#</small>{{ (index + 1) }}</span>
            <div class="pull-right float-right">
                <button class="btn btn-sm btn-danger mr-2" @click.prevent="removeJoin">Remove Join</button>
                <button v-if="show_add_button" class="btn btn-sm btn-success" @click.prevent="addJoin">Add Join</button>
            </div>
        </h4>

        <div class="row">
            <div class="col-xs-12 col-md-6">
                <div class="form-group">
                    <label for="join_type">Join Type</label>
                    <select name="type" id="join_type" class="form-control" v-model="data.type">
                        <option :value="type.value" v-for="type in types">{{ type.name }}</option>
                    </select>
                </div>
            </div>
            <div class="col-xs-12 col-md-6">
                <div class="form-group">
                    <label for="join_table">Join Table</label>
                    <input id="join_table" type="text" name="table" class="form-control" placeholder="Table" v-model="data.table">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-5">
                <div class="form-group">
                    <label for="join_first">Column 1</label>
                    <input id="join_first" type="text" name="first" class="form-control" placeholder="First Column (e.g. users.team_id)" v-model="data.first">
                </div>
            </div>
            <div class="col-xs-12 col-md-2">
                <div class="form-group">
                    <label for="join_condition">Condition</label>
                    <input id="join_condition" type="text" name="first" class="form-control" placeholder="Condition (e.g. =, !=)" v-model="data.first">
                </div>
            </div>
            <div class="col-xs-12 col-md-5">
                <div class="form-group">
                    <label for="join_second">Column 2</label>
                    <input id="join_second" type="text" name="second" class="form-control" placeholder="Second Column (e.g. teams.id)" v-model="data.second">
                </div>
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

</style>