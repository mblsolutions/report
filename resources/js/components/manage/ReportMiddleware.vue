<template>
    <div :id="'report-middleware-' + index" class="report-middleware" v-if="loaded && data.deleted_at === null">

        <hr class="my-5">

        <h4 class="text-xl text-brand-blue-500 px-2">
            {{ middlewareLabel }}
            <span class="bg-brand-blue-400 text-white px-1 font-bold rounded-sm">
                <span class="text-sm font-normal">#</span>{{ (index + 1) }}
            </span>
            <div class="pull-right float-right">
                <button class="brand-btn-alt mr-2" @click.prevent="removeMiddleware(index)">Remove Middleware</button>
                <button v-if="show_add_button" class="brand-btn" @click.prevent="addMiddleware">Add Middleware</button>
            </div>
        </h4>

        <div class="form-group">
            <label class="form-label" for="column_type">Middleware</label>
            <select name="type" id="column_type" class="form-control" :class="{ 'is-invalid': report.hasError('middleware', index, 'middleware') }" v-model="data.middleware">
                <option :value="null">Select Middleware</option>
                <option :value="object.value" v-for="object in middleware">{{ object.name }}</option>
            </select>

            <div v-if="report.hasError('middleware', index, 'middleware')" class="invalid-feedback">{{ report.getError('middleware', index, 'middleware') }}</div>
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
                report: null,
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
            let vm = this;

            new Promise(resolve => {
                vm.report = vm.value;
                vm.data = vm.value.data.middleware[vm.index];

                resolve(true);
            }).then(response => {
                vm.loaded = response;
            });
        }
    }
</script>

<style scoped>

</style>
