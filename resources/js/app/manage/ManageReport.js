import {Form} from "../support/Form";

export class ManageReport extends Form {

    /**
     * ManageReport
     */
    constructor() {
        super();

        this.connections = [];
        this.models = [];
    }

    /**
     * Load Report Manager
     *
     * @return {Promise}
     */
    load(id = null) {
        let self = this;

        return Promise.all([
            self.loadConnections(),
            self.loadModels(),
            self.loadReport(id)
        ]).then(function (results) {
            return results;
        });
    }

    /**
     * Submit Report
     *
     * @return {Promise}
     */
    submit(action, method = 'post') {
        let self = this;

        return new Promise(resolve => {
            self.request(action, method)
                .then(response => {
                    resolve(response.data);
                }).catch(error => {
                    resolve(error);
                });

            resolve(true);
        });
    }

    /**
     * Test Report
     *
     * @return {Promise}
     */
    test() {
        let self = this;

        return new Promise(resolve => {
            self.request('/api/report/manage/test', 'post')
                .then(response => {
                    resolve(response.data);
                }).catch(error => {
                    resolve(error);
                });
        });
    }

    /**
     * Load the Report Select Models
     *
     * @return {Promise}
     */
    loadConnections() {
        let self = this;

        return new Promise(resolve => {
            self.request('/api/report/connection', 'get')
                .then(response => {
                    self.connections = response.data;

                    resolve(self.connections);
                });
        });
    }

    /**
     * Load the Report Select Models
     *
     * @return {Promise}
     */
    loadModels() {
        let self = this;

        return new Promise(resolve => {
            self.request('/api/report/model', 'get')
                .then(response => {
                    self.models = response.data;

                    resolve(self.models);
                });
        });
    }

    loadReport(id) {
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
     * Remove the report Select
     */
    removeSelect(index) {
        this.data.selects.splice(index,1)
    }

    /**
     * Add a new Report Select
     */
    addNewSelect() {
        return new Promise(resolve => {
            let id = this.getNextSelectIndex();

            this.data.selects.push({
                id: null,
                column: null,
                alias: null,
                type: 'string',
                column_order: null
            });

            resolve(id);
        }).then(id => {
            setTimeout(function() {
                document.getElementById('report-select-' + id).scrollIntoView();
            }, 100);
        })
    }

    /**
     * Remove the report Join
     */
    removeJoin(index) {
        this.data.joins.splice(index,1)
    }

    /**
     * Add a new Report Join
     */
    addNewJoin() {
        return new Promise(resolve => {
            let id = this.getNextJoinIndex();

            this.data.joins.push({
                id: null,
                type: 'inner_join',
                table: null,
                first: null,
                operator: null,
                second: null
            });

            resolve(id);
        }).then(id => {
            setTimeout(function() {
                document.getElementById('report-join-' + id).scrollIntoView();
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

    /**
     * Get the index of the Next Select
     *
     * @return {Number}
     */
    getNextSelectIndex() {
        if (this.data.selects) {
            return this.data.selects.length;
        }

        return 0;
    }

    /**
     * Get the index of the Next Join
     *
     * @return {Number}
     */
    getNextJoinIndex() {
        if (this.data.joins) {
            return this.data.joins.length;
        }

        return 0;
    }


}