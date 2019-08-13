<template>
    <div :id="'report-middleware-' + index" class="report-middleware" v-if="loaded && data.deleted_at === null">

        <hr class="col-xs-12">

        <h4>
            {{ middlewareLabel }} <span class="badge badge-primary"><small>#</small>{{ (index + 1) }}</span>
            <div class="pull-right float-right">
                <button class="btn btn-sm btn-danger mr-2" @click.prevent="removeMiddleware(index)">Remove Middleware</button>
                <button v-if="show_add_button" class="btn btn-sm btn-success" @click.prevent="addMiddleware">Add Middleware</button>
            </div>
        </h4>


        <div class="form-group">
            <div class="form-group">
                <label for="column_type">Middleware</label>
                <select name="type" id="column_type" class="form-control" v-model="data.middleware">
                    <option :value="null">Select Middleware</option>
                    <option :value="object.value" v-for="object in middleware">{{ object.name }}</option>
                </select>
            </div>
        </div>

    </div>
</template>

<script>
    export default {
        name: "ReportMiddleware",
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
            middleware: {
                type: Array,
                required: true
            },
            show_add_button: {
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
             * Get the Middleware Label
             *
             * @return {string}
             */
            middlewareLabel() {
                let vm = this;

                if (!vm.data.middleware) {
                    return 'Label';
                }

                let middleware = vm.middleware.find(function (element) {
                    return element.value === vm.data.middleware;
                });

                return middleware.name;
            }
        },
        methods: {
            /**
             * Remove Middleware
             */
            removeMiddleware(index) {
                this.$emit('remove-middleware', index);
            },
            /**
             * Add New Middleware
             */
            addMiddleware() {
                this.$emit('add-middleware');
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