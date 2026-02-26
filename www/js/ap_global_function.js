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

function escapeHtml(text) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}

const alertPlaceholder = document.getElementById('alertPlaceholder')
const appendAlert = (message, type) => {
    const wrapper = document.createElement('div')
    const alertDiv = document.createElement('div')
    alertDiv.id = 'appAlert'
    alertDiv.className = 'alert alert-' + escapeHtml(type) + ' flash alert-dismissible'
    alertDiv.setAttribute('role', 'alert')

    const messageDiv = document.createElement('div')
    messageDiv.textContent = message

    const closeBtn = document.createElement('button')
    closeBtn.type = 'button'
    closeBtn.className = 'btn-close'
    closeBtn.setAttribute('data-bs-dismiss', 'alert')
    closeBtn.setAttribute('aria-label', 'Close')

    alertDiv.appendChild(messageDiv)
    alertDiv.appendChild(closeBtn)
    wrapper.appendChild(alertDiv)

    alertPlaceholder.append(wrapper)
}
