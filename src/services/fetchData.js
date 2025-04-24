const ajax_url = lbm_settings.ajax_url;

export const fetchData = (action, callback, additionalParams = {}) => {
    const params = {
        _ajax_nonce: lbm_settings.nonce,
        action: action,
        ...additionalParams,
    };

    jQuery.post(ajax_url, params, function(response) {
        if (typeof callback === "function") {
            callback(response);
        }
    });
};