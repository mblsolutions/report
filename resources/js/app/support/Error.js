export class Error {

    /**
     * Error
     */
    constructor() {
        this.message = null;
        this.errors = null;
    }

    /**
     * Add Error Message
     *
     * @param message
     */
    addMessage(message) {
        this.message = message;
    }

    /**
     * Add Errors
     *
     * @param errors
     */
    addErrors(errors) {
        this.errors = errors;
    }

    /**
     * Check if Error Exists
     *
     * @param key
     * @return {boolean}
     */
    exists(key) {
        if (this.errors) {
            return this.errors.hasOwnProperty(key);
        }

        return false;
    }

    /**
     * Get the Error
     *
     * @param key
     * @return {boolean|string}
     */
    get(key) {
        if (this.exists(key)) {
            return this.errors[key][0];
        }

        return false;
    }

    /**
     * Reset Error Key
     *
     * @param key
     * @return this
     */
    resetErrorKey(key) {
        if (this.exists(key)) {
            delete this.errors[key];
        }

        return this;
    }

    /**
     * Reset all Errors
     *
     * @return this
     */
    reset() {
        this.message = null;
        this.errors = null;

        return this;
    }

}