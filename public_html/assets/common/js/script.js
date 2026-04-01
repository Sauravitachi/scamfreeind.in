jQuery.fn.outerHtml = function () {
    return jQuery("<div />").append(this.eq(0).clone()).html();
};

$.ajaxSetup({
    complete: function (xhr) {
        const location = xhr.getResponseHeader('Location');
        if (location) {
            window.location.href = location;
        }

        // Handle Session Expiration / CSRF Mismatch
        if (xhr.status === 419) {
            location.reload();
        }

        const loginUrl = $('meta[name="login-url"]').attr('content');
        if (loginUrl && [401].includes(xhr.status)) {
            window.location.href = loginUrl;
        }
    }
});

const HtmlTag = {
    div: (content, classname) => {
        return `<div class="${classname ?? ""}">${content}</div>`;
    },
    span: (content, classname) => {
        return `<span class="${classname ?? ""}">${content}</span>`;
    },
    icon: (icon, classname) => {
        return `<i class="${icon} ${classname ?? ""}"></i>`;
    },
};

function showValidationErrors(xhr) {
    if (xhr.status === 419) {
        location.reload();
        return;
    }
    if (xhr.status === 422) {
        const errors = xhr.responseJSON.errors;

        // Clear previous errors
        $(".form-control").removeClass("is-invalid");
        $(".invalid-feedback").remove();

        // Iterate through each error and display it
        $.each(errors, function (field, messages) {
            const element = $('[name="' + field + '"]');

            element.addClass("is-invalid");

            element.parent().find(".invalid-feedback").remove();

            element
                .parent()
                .append(
                    '<div class="invalid-feedback">' + messages[0] + "</div>"
                );

            const inputGroupText = $(element)
                .parent()
                .find(".input-group-text");
            if (inputGroupText.length) {
                inputGroupText.addClass("border-danger");
            }
        });
    }
}

function clearInputs(container) {
    $(container)
        .find(":input, .select2, .flatpickr-input")
        .each(function () {
            var type = this.type;
            var tag = this.tagName.toLowerCase();

            if (
                ["text", "password", "number", "file"].includes(type) ||
                tag === "textarea"
            ) {
                this.value = "";
            } else if (["checkbox", "radio"].includes(type)) {
                this.checked = false;
            } else if (tag === "select") {
                $(this).val("").trigger("change"); // Handles normal & Select2
            } else if ($(this).hasClass("select2")) {
                $(this).val(null).trigger("change"); // Clear Select2
            } else if (this._flatpickr) {
                this._flatpickr.clear(); // Clear Flatpickr
            }
        });
}

function ajaxForm(formSelector, options = {}) {
    $(formSelector)
        .off("submit")
        .on("submit", function (e) {
            e.preventDefault();
            const form = $(this);
            if ($(formSelector).valid()) {
                let isSuccess = false;
                let redirectTo = null;

                let data = {};

                if (options.formData) {
                    if (typeof options.formData === "function") {
                        data = options.formData();
                    } else {
                        data = options.formData;
                    }
                } else {
                    data = new FormData(this);
                }

                $.ajax({
                    type: "POST",
                    url: $(this).attr("action"),
                    data: data,
                    processData: false,
                    contentType: false,
                    beforeSend: function () {
                        disable_form(form);
                        options.beforeSend && options.beforeSend();
                        options.showOverlayLoader && overlayLoader.show();
                    },
                    success: function (response) {
                        if (response.success) isSuccess = true;
                        if (options.responseRedirect && response.redirectTo) {
                            redirectTo = response.redirectTo;
                            redirect(redirectTo);
                            return;
                        }
                        options.handleToast &&
                            response.toast &&
                            toast.open(response.toast);
                        options.success && options.success(response);
                    },
                    error: function (xhr) {
                        showValidationErrors(xhr);
                        options.error && options.error(xhr);
                    },
                    complete: function () {
                        if (
                            !(
                                options.disableFormAfterSuccess &&
                                isSuccess &&
                                redirectTo
                            )
                        ) {
                            enable_form(form);
                        }
                        options.complete && options.complete();
                        options.showOverlayLoader && overlayLoader.hide();
                    },
                });
            }
        });
}

async function runAjax({
    url,
    method = "POST",
    data = {},
    showOverlayLoader = false,
    beforeSend = null,
    success = null,
    complete = null,
    responseRedirect = false,
    handleToast = false,
    ajaxOptions = {},
} = {}) {
    await $.ajax({
        url,
        method,
        data,
        beforeSend: function () {
            showOverlayLoader && overlayLoader.show();
            beforeSend && beforeSend();
        },
        success: function (response) {
            if (
                responseRedirect &&
                response.redirectTo &&
                redirect(response.redirectTo)
            ) {
                return;
            }

            handleToast && response.toast && toast.open(response.toast);

            success && success(response);
        },
        complete: function () {
            showOverlayLoader && overlayLoader.hide();
            complete && complete();
        },
        ...ajaxOptions,
    });
}

function redirect(url, newTab = false) {
    if (!url) return;

    if (newTab) {
        window.open(url, '_blank');
    } else {
        window.location.href = url;
    }
}

function reload() {
    location.reload();
}

function disable_form(selector, isId = true) {
    const container = $(selector);
    container.find("input, select, textarea, button").prop("disabled", true);
    container.children().each(function () {
        disable_form($(this));
    });
}

function enable_form(selector) {
    const container = $(selector);
    container.find("input, select, textarea, button").each(function () {
        if (!$(this).hasClass("keep-disabled")) {
            $(this).prop("disabled", false);
        }
    });
    container.children().each(function () {
        enable_form($(this));
    });
}

function getCsrfToken() {
    const tokenMeta = document.getElementById("csrf");
    return tokenMeta ? tokenMeta.getAttribute("content") : null;
}

function debounce(func, wait) {
    let timeout;
    return function (...args) {
        const context = this;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), wait);
    };
}

function getRandomColor() {
    const letters = '0123456789ABCDEF';
    let color = '#';
    for (let i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}

function $getBackgroundImageUrl($elem) {
    const backgroundImage = $elem.css("background-image");

    if (backgroundImage && backgroundImage.startsWith("url")) {
        return backgroundImage
            .replace(/^url\(["']?/, "")
            .replace(/["']?\)$/, "");
    }
    return null;
}

function replaceUrl(url) {
    window.history.replaceState({}, '', url);
}

function downloadFileFromUrl(url, { outputFileName = "" }) {
    var a = document.createElement("a");
    a.href = url;
    a.download = outputFileName;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

function wrap_anchor(str, url, newTab = false) {
    return `<a href="${url}" ${newTab ? 'target="_blank"' : ""}>${str}</a>`;
}

function ucFirst(str) {
    if (!str) return str;
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function delay(callback, ms) {
    setTimeout(callback, ms);
}

function close_all_modals() {
    $(".modal").modal("hide");
}

function nl2br(str) {
    return str.replace(/\n/g, "<br>");
}

function parseBoolean01(boolData) {
    return boolData ? 1 : 0;
}

function enableActionBlocker(value = true) {
    if (value) {
        $('#action-blocker').show();
    } else {
        $('#action-blocker').hide();
    }
}

function parseJsonString(data) {
    if (typeof data === "string") {
        try {
            data = JSON.parse(data);
        } catch (e) {
            console.error("Invalid JSON string:", data);
        }
    }
    return data;
}

function trimOrNull(str) {
    if (!str) return null;
    const t = str.trim();
    return t === '' ? null : t;
}

$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content"),
        },
    });

    // applying trim on values before jquery validation
    if (typeof jQuery !== 'undefined' && jQuery.validator && jQuery.validator.methods) {
        jQuery.each(jQuery.validator.methods, function (key, value) {
            jQuery.validator.methods[key] = function () {
                if (arguments.length > 0) {
                    arguments[0] = jQuery.trim(arguments[0]);
                }
                return value.apply(this, arguments);
            };
        });
    }
});
