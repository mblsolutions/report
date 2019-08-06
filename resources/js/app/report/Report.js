import {Form} from "../support/Form";

export class Report extends Form {

    constructor() {
        super();

        let report = null;
        let results = [];
    }

    /**
     * Get the Report Index
     *
     * @return {Promise}
     */
    index() {
        let self = this;

        return new Promise(resolve => {
            self.request('/api/report', 'get')
                .then(response => {
                    resolve(response.data);
                }).catch(error => {
                    resolve(error);
                });
        });

    }

    /**
     * Get the Report
     *
     * @return {Promise}
     */
    getReport(id) {
        let self = this;

        return new Promise(resolve => {
            self.request('/api/report/' + id, 'get')
                .then(response => {
                    self.report = response.data;

                    resolve(response.data);
                }).catch(error => {
                resolve(error);
            });
        })
    }

    /**
     * Render a Report
     *
     * @return {Promise}
     */
    render(id) {
        let self = this;

        return new Promise(resolve => {
            self.request('/api/report/' + id, 'post')
                .then(response => {
                    self.results = response.data;

                    resolve(self.results);
                }).catch(error => {
                resolve(error);
            });
        });
    }

}