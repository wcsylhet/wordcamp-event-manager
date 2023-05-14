const request = function (method, route, data = {}) {
    const url = `${window.WordCampEntryAdmin.rest.url}/${route}`;

    const headers = {'X-WP-Nonce': window.WordCampEntryAdmin.rest.nonce};

    if (['PUT', 'PATCH', 'DELETE'].indexOf(method.toUpperCase()) !== -1) {
        headers['X-HTTP-Method-Override'] = method;
        method = 'POST';
    }

    return new Promise((resolve, reject) => {
        window.jQuery.ajax({
            url: url,
            type: method,
            data: data,
            headers: headers
        })
            .then(response => resolve(response))
            .fail(errors => reject(errors.responseJSON));
    });
}

export default {
    get(route, data = {}) {
        return request('GET', route, data);
    },
    post(route, data = {}) {
        return request('POST', route, data);
    },
    delete(route, data = {}) {
        return request('DELETE', route, data);
    },
    put(route, data = {}) {
        return request('PUT', route, data);
    },
    patch(route, data = {}) {
        return request('PATCH', route, data);
    },
    rawRequest(route, data, method = 'POST') {
        const url = `${window.WordCampEntryAdmin.rest.url}/${route}`;

        const headers = {'X-WP-Nonce': window.WordCampEntryAdmin.rest.nonce};

        return new Promise((resolve, reject) => {
            window.jQuery.ajax({
                url: url,
                type: method,
                data: data,
                headers: headers,
                cache: false,
                contentType: false,
                processData: false
            })
                .then(response => resolve(response))
                .fail(errors => reject(errors.responseJSON));
        });
    }
};

jQuery(document).ajaxSuccess((event, xhr, settings) => {
    const nonce = xhr.getResponseHeader('X-WP-Nonce');
    if (nonce) {
        window.WordCampEntryAdmin.rest.nonce = nonce;
    }
});
