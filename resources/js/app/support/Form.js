let axios = require('axios');
let {Error} = require('./Error');

export class Form {

    /**
     * Ajax Form
     *
     * @param data
     */
    constructor(data = null) {
        this.data = data ? data : {};

        this.response = null;
        this.error = new Error;
    }

    /**
     * Get Axios
     *
     * @return Promise
     */
    request(action, method, params = null) {
        let self = this;

        return new Promise((resolve, reject) => {

            self.error.reset();

            axios({
                url: action,
                method: method,
                data: params ? params : self.data,
                headers: {
                    'Accept': 'application/json'
                }
            }).then(response => {
                self.response = response;

                resolve(self.response);
            }).catch(error => {
                let response = error.response.data;

                if (response.message === '') {
                    self.error.addMessage('An Error Occurred');
                } else {
                    self.error.addMessage(response.message);
                }

                if (response.errors) {
                    self.error.addErrors(response.errors);
                }

                reject(response);
            })
        });
    }

    /**
     * Check if there are Errors
     *
     * @return {null|boolean}
     */
    hasErrors() {
        return this.error.message || this.error.errors;
    }

    /**
     * Check if Form has Error
     *
     * @param key
     * @param index
     * @param pre_key
     * @return {boolean}
     */
    hasError(key, index = null, pre_key = null) {

        if (index !== null && pre_key !== null) {
            key = pre_key + '.' + index + '.' + key;
        }

        return this.error.exists(key);
    }

    /**
     * Get Error
     *
     * @param key
     * @param index
     * @param pre_key
     * @return {boolean|string}
     */
    getError(key, index = null, pre_key = null) {

        if (index !== null && pre_key !== null) {
            key = pre_key + '.' + index + '.' + key;
        }

        return this.error.get(key);
    }

    /**
     * Get the Error Message
     *
     * @return {null|string}
     */
    getErrorMessage() {
        return this.error.message;
    }

    /**
     * Reset Error Key
     *
     * @param key
     * @return this
     */
    resetErrorKey(key) {
        this.error.resetErrorKey(key);

        return this;
    }

    /**
     * Convert Date to ISO8601
     *
     * @param date
     * @return {*}
     */
    convertDateToISO8601(date = null) {
        if (date) {
            let parsed_date = moment(date);

            date = parsed_date.format('YYYY-MM-DDTHH:mm:ssZ');
        }

        return date;
    }

}