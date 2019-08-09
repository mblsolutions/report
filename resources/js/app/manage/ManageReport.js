import {Form} from "../support/Form";

export class ManageReport extends Form {

    /**
     * ManageReport
     */
    constructor() {
        super();

        this.connections = [];
        this.middleware = [];
        this.models = [];
        this.data_types = [];
    }

    /**
     * Load Report Manager
     *
     * @return {Promise}
     */
    load(id = null) {
        let self = this;

        return Promise.all([
            self.loadSettings(),
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

        return new Promise((resolve, reject) => {
            self.request(action, method)
                .then(response => {
                    resolve(response.data);
                }).catch(error => {
                    reject(error);
                });
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
     * Load Report Settings
     * e.g. Connections, Middleware, Models and Select Types
     *
     * @return {Promise}
     */
    loadSettings() {
        let self = this;

        return new Promise(resolve => {
            self.request('/api/report/manage/settings', 'get')
                .then(response => {
                    self.connections = response.data.connections;
                    self.middleware = response.data.middleware;
                    self.models = response.data.models;
                    self.data_types = response.data.data_types;

                    resolve([
                        self.connections,
                        self.middleware,
                        self.models,
                        self.data_types
                    ]);
                });
        });
    }

    /**
     * Load the Report Selectable Models
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
     * Load the Report Middleware
     *
     * @return {Promise}
     */
    loadMiddleware() {
        let self = this;

        return new Promise(resolve => {
            self.request('/api/report/middleware', 'get')
                .then(response => {
                    self.models = response.data;

                    resolve(self.models);
                });
        });
    }

    /**
     * Load the Report Select Types
     *
     * @return {Promise}
     */
    loadSelectTypes() {
        let self = this;

        return new Promise(resolve => {
            self.request('/api/report/data/type', 'get')
                .then(response => {
                    self.models = response.data;

                    resolve(self.models);
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

    /**
     * Load Report
     *
     * @param id
     * @return {Promise}
     */
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
    if (this.data.fields[index].id) {
        this.data.fields[index].deleted_at = ManageReport.getTimestamp();
        } else {
        this.data.fields.splice(index, 1);
        }
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
                model_select_name: null,
                deleted_at: null
            });

            resolve(id);
        }).then(id => {
            setTimeout(function() {

                setTimeout(function() {
                    let element = document.getElementById('report-field-' + id);

                    if (element) {
                        element.scrollIntoView();
                    }
                }, 100);
            }, 100);
        })
    }

    /**
     * Remove the report Select
     */
    removeSelect(index) {
    if (this.data.selects[index].id) {
            this.data.selects[index].deleted_at = ManageReport.getTimestamp();
        } else {
            this.data.selects.splice(index, 1);
        }
    }

    /**
     * Move Report Select Up
     *
     * @param index
     */
    moveSelectUp(index) {
        let previousSelect = this.data.selects[index - 1];
        let select = this.data.selects[index];

        if (previousSelect && select) {
            this.data.selects[index - 1].column_order = previousSelect.column_order + 1;
            this.data.selects[index].column_order = select.column_order - 1;

            let element = this.data.selects[index];
            this.data.selects.splice(index, 1);
            this.data.selects.splice(index - 1, 0, element);
        }
    }

    /**
     * Move Report Select Down
     *
     * @param index
     */
    moveSelectDown(index) {
        let nextSelect = this.data.selects[index + 1];
        let select = this.data.selects[index];

        if (nextSelect && select) {
            this.data.selects[index + 1].column_order = nextSelect.column_order - 1;
            this.data.selects[index].column_order = select.column_order + 1;

            let element = this.data.selects[index];
            this.data.selects.splice(index, 1);
            this.data.selects.splice(index + 1, 0, element);

            return this.data.selects[index + 1];
        }
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
                type: null,
                column_order: null,
                deleted_at: null
            });

            resolve(id);
        }).then(id => {
            setTimeout(function() {
                let element = document.getElementById('report-select-' + id);

                if (element) {
                    element.scrollIntoView();
                }
            }, 100);
        })
    }

    /**
     * Remove the report Join
     */
    removeJoin(index) {
        if (this.data.joins[index].id) {
            this.data.joins[index].deleted_at = ManageReport.getTimestamp();
        } else {
            this.data.joins.splice(index, 1);
        }
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
                second: null,
                deleted_at: null
            });

            resolve(id);
        }).then(id => {
            setTimeout(function() {
                let element = document.getElementById('report-join-' + id);

                if (element) {
                    element.scrollIntoView();
                }
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

    /**
     * Get Timestamp
     *
     * @return {string}
     */
    static getTimestamp() {
        return new Date().toISOString().slice(0, 19).replace('T', ' ');
    }


}