let ajaxCall = function (url, data, method) {
    return new Promise(function (resolve, reject) {
        $.ajax({
            type: method,
            url: url,
            data: data,
            success: function (payload) {
                resolve(payload);
            },
            error: function (error) {
                reject(error);
            }
        });
    });
};

const alertPlaceholder = document.getElementById('alertPlaceholder')
const appendAlert = (message, type) => {
    const wrapper = document.createElement('div')
    wrapper.innerHTML = [
        `<div id="appAlert" class="alert alert-${type} flash alert-dismissible" role="alert">`,
        `   <div>${message}</div>`,
        '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
        '</div>'
    ].join('')

    alertPlaceholder.append(wrapper)
}
