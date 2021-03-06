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

        return new Promise((resolve, reject) => {
            self.request('/api/report', 'get')
                .then(response => {
                    resolve(response.data);
                }).catch(error => {
                    reject(error);
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

        return new Promise((resolve, reject) => {
            self.request('/api/report/' + id, 'get')
                .then(response => {
                    self.report = response.data.data;

                    resolve(self.report);
                }).catch(error => {
                    reject(error);
                });
        })
    }

    /**
     * Render a Report
     *
     * @return {Promise}
     */
    render(id, page = 1) {
        let self = this;

        self.results = [];

        return new Promise((resolve, reject) => {
            self.request('/api/report/' + id + '?page=' + page, 'post')
                .then(response => {
                    self.results = response.data;

                    resolve(self.results);
                }).catch(error => {
                    reject(error);
                });
        });
    }

}