import {Form} from "../support/Form";

export class ManageReport extends Form {

    /**
     * ManageReport
     */
    constructor() {
        super();
    }

    /**
     * Load Report
     *
     * @return {Promise}
     */
    load(id = null) {
        let self = this;

        return new Promise(resolve => {
            self.request('/api/report/manage/' + id, 'get')
                .then(response => {
                    self.data = response.data.data;

                    resolve(self.data);
                });
        });
    }

    /**
     * Remove a Report Field
     */
    removeField(index) {
        this.data.fields.splice(index,1)
    }

    /**
     * Add a new Report Field
     */
    addNewField() {
        return new Promise(resolve => {
            let id = this.getNextFieldIndex();

            this.data.fields.push({
                id: null,
                label: null,
                type: 'text',
                model: null,
                alias: null,
                model_select_value: null,
                model_select_name: null
            });

            resolve(id);
        }).then(id => {
            setTimeout(function() {
                document.getElementById('report-field-' + id).scrollIntoView();
            }, 100);
        })
    }

    /**
     * Get the Index of Next Field
     *
     * @return {Number}
     */
    getNextFieldIndex() {
        if (this.data.fields) {
            return this.data.fields.length;
        }

        return 0;
    }

}