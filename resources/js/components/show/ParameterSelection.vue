<template>
    <div>
        <div v-if="loaded">
            <div class="form-group" v-for="field in data.report.fields">
                <label :for="'field_' + field.alias">{{ field.label }}</label>

                <div v-if="field.type === 'select'">
                    <select :name="field.alias" :id="'field_' + field.alias" class="form-control" v-model="data.data[field.alias]">
                        <option value="null">Select {{ field.label }}</option>
                        <option :value="option[field.model_select_value]" v-for="option in field.options">
                            {{ option[field.model_select_name] }}
                        </option>
                    </select>
                </div>
                <div v-else>
                    <input :id="'field_' + field.alias" :type="field.type" :name="field.alias" class="form-control" :placeholder="field.label" v-model="data.data[field.alias]">
                </div>
            </div>

            <button class="btn btn-primary" @click.prevent="submitParameters">Submit</button>
        </div>
        <div v-else>
            <div v-if="hasLoadingSlot">
                <slot name="loading"></slot>
            </div>
        </div>

    </div>
</template>

<script>
    export default {
        name: "ParameterSelection",
        props: {
            value: {
                type: Object
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
             * Check if a loading slot exists
             *
             * @return {boolean}
             */
            hasLoadingSlot() {
                return !!this.$slots.loading;
            },
            /**
             * Submit Parameters to Report
             */
            submitParameters() {
                this.$emit('submit-report-parameters', true);
            }
        },
        mounted() {
            let vm = this;

            new Promise(resolve => {
                vm.data = vm.value;

                for (var i = 0; i < vm.data.report.fields.length; i++) {
                    let field = vm.data.report.fields[i];

                    if (!vm.data.data[field.alias]) {
                        vm.data.data[field.alias] = null;
                    }
                }

                resolve(true);
            }).then(response => {
                vm.loaded = response;
            });
        }
    }
</script>

<style scoped>

</style>
