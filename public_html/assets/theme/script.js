const overlayLoader = {
    show: () => $("#overlay-loader").fadeIn(200),
    hide: () => $("#overlay-loader").fadeOut(200),
};

const toast = typeof Notyf == 'undefined' ? null : new Notyf({
    duration: 5000,
    position: { x: "right", y: "bottom" },
    types: [
        {
            type: "warning",
            background: "orange",
            icon: {
                className: "ti ti-alert-circle fs-2 text-white",
                tagName: "i",
                text: "warning",
            },
        },
        {
            type: "primary",
            background: "#0054a6",
            icon: {
                className: "ti ti-info-circle fs-2 text-white",
                tagName: "i",
                text: "info",
            },
        },
    ],
});
// how to use
// toast.open({ type: "success", message: "hello world" });

const Loader = {
    spinner: '<div class="_spinner_loader"></div>',

    centerSpinnerLoader: function (text) {
        const textHtml = text
            ? `<div class="fs-2 ms-3">${text}</div>`
            : ``;
        const html = `<div class="d-flex justify-content-center align-items-center h-100"><div class="d-flex align-items-center"><div class="_spinner_loader"></div> ${textHtml}</div></div>`;
        return html;
    },
};

const FFSound = {
    urls: {},
    register: function (options) {
        options.notificationUrl && (FFSound.notificationAudio = new Audio(options.notificationUrl));
        options.clickUrl && (FFSound.clickAudio = new Audio(options.clickUrl));
        options.cashierUrl && (FFSound.cashierAudio = new Audio(options.cashierUrl));
        FFSound.urls = options; // Store all URLs for reference
    },
    notify: function (url) {
        if (url && url === FFSound.urls.cashierUrl && FFSound.cashierAudio) {
            FFSound.cashierAudio.currentTime = 0;
            FFSound.cashierAudio.play();
        } else if (FFSound.notificationAudio) {
            FFSound.notificationAudio.currentTime = 0;
            FFSound.notificationAudio.play();
        }
    },
    click: function () {
        FFSound.clickAudio && FFSound.clickAudio.play();
    }
}

function noContentText() {
    return `<span class="text-secondary">N/A</span>`;
}

function avatarField(avatarUrl, classname = "") {
    return `<img class="avatar avatar-sm avatar-rounded ${classname}" src="${avatarUrl}"></img>`;
}

function withFilterData(dtData) {
    const $form = $("#filter-offcanvas-form"); // filter form selector

    // Loop through all form elements
    $form.find("input, select, textarea").each(function () {
        const name = $(this).attr("name");
        const value = $(this).val();

        // Check if it's a multi-select (select2 or other multiple select)
        if ($(this).is("select[multiple]")) {
            // For multi-select, values will be an array
            const selectedValues = $(this).val();
            dtData[name] = selectedValues;
        } else if ($(this).is(":checkbox, :radio")) {
            // For checkboxes or radio buttons, check if it's checked
            if ($(this).is(":checked")) {
                dtData[name] = value;
            }
        } else {
            // For normal input fields, just set the value
            dtData[name] = value;
        }
    });

    return dtData;
}


function clearFilterData() {
    const $form = $("#filter-offcanvas-form");
    clearInputs($form);
}

var Popup = {
    alert: function ({
        type = "primary",
        title = "",
        content = "",
        icon = null,
    } = {}) {
        $modal = $("#modal-alert");

        $modal.modal("hide");

        $modal
            .find(".modal-status")
            .removeClass()
            .addClass(`modal-status bg-${type}`);
        $modal.find(".main-title").html(title);
        $modal.find(".main-content").html(content);

        if (icon) {
            $modal
                .find(".main-icon")
                .removeClass()
                .addClass(`main-icon text-${type} ${icon}`)
                .show();
        } else {
            $modal.find(".main-icon").hide();
        }

        $modal.modal("show");
    },
    askConfirmation: async function (options = {}) {
        const {
            title = "Are you sure?",
            message = "If you procede, you won't be able to revert this.",
            confirmButtonText = "Yes, procede",
            processingText = "Processing",
            variant = "danger",
            onConfirm = null, // callback
            onCancel = null, //callback
            icon = "ti ti-alert-triangle",
        } = options;

        $("#modal-confirmation").modal("hide");

        $("#modal-confirmation .modal-title").html(title);
        $("#modal-confirmation .modal-message").html(message);
        const confirmButton = $("#modal-confirmation .confirm-btn");
        const cancelButton = $("#modal-confirmation .cancel-btn");
        const mainIcon = $("#modal-confirmation .main-icon");
        const modalStatus = $("#modal-confirmation .modal-status");
        confirmButton.off("click");
        cancelButton.off("click");
        confirmButton.find(".spinner-border").hide();
        confirmButton.removeClass().addClass("btn confirm-btn btn-" + variant);
        mainIcon.removeClass().addClass(`main-icon ${icon} text-${variant}`);
        modalStatus.removeClass().addClass("modal-status bg-" + variant);
        $("#modal-confirmation .confirm-btn .btn-text").text(confirmButtonText);

        confirmButton.on("click", async function () {
            confirmButton.find(".spinner-border").show();
            confirmButton.prop("disabled", true);
            cancelButton.prop("disabled", true);
            confirmButton.find(".btn-text").text(processingText);
            onConfirm && (await onConfirm());
            confirmButton.find(".btn-text").text(confirmButtonText);
            confirmButton.prop("disabled", false);
            cancelButton.prop("disabled", false);
            confirmButton.find(".spinner-border").hide();
            $("#modal-confirmation").modal("hide");
        });

        cancelButton.on("click", async function () {
            onCancel && (await onCancel());
        });

        $("#modal-confirmation").modal("show");
    },
};
var AdminHtml = {
    alert: function (options = {}) {
        let html = `<div class="alert alert-${options.variant ?? 'primary'} ${options.important ? 'alert-important' : ''}" role="alert">`;
        html += `<div class="d-flex">`;
        html += options.icon ? `<i class="${options.icon} fs-2 me-1"></i>` : '';
        html += `<div class="message">${options.message ?? ''}</div>`;
        html += `</div>`;
        html += `</div>`;
        return html;
    }
};

function changeResourceStatus({
    url,
    body,
    dtTable = null,
    alertMessage = "You are about to change the status of a record.",
} = {}) {
    Popup.askConfirmation({
        variant: "warning",
        icon: "ti ti-alert-triangle",
        message: alertMessage,
        onConfirm: async function () {
            await $.ajax({
                url,
                type: "POST",
                data: body,
                success: function (res) {
                    if (res.toast) {
                        toast.open(res.toast);
                    } else if (res.message) {
                        toast.open({
                            type: "success",
                            message: res.message,
                        });
                    }
                    dtTable && dtTable.draw(false);
                },
            });
        },
    });
}

function badge({
    title,
    color,
    textColor = "white",
    classname = "",
    dataId = null,
    dataValueId = null,
} = options) {
    let styles = color ? `background-color: ${color};` : ``;
    styles += textColor ? `color: ${textColor};` : ``;
    const dataIdText = dataId ? `data-id="${dataId}"` : ``;
    const dataValueIdText = dataValueId ? `data-value="${dataValueId}"` : ``;
    return `<span class="badge ${classname}" style="${styles}" ${dataIdText} ${dataValueIdText} ${dataIdText ? 'role="button"' : ""
        }>${title}</span>`;
}

function dtSelectedRows(dtTable, keyName) {
    const data = dtTable.rows({ selected: true }).data().toArray();
    if (!keyName) return data;
    return data.map((obj) => obj[keyName]);
}

function dtAllRows(dtTable) {
    return dtTable.rows().data().toArray();
}

function dtSerialNumber(meta) {
    return parseInt(meta.row) + parseInt(meta.settings._iDisplayStart + 1);
}

function findObjectInArrayByKey(array, keyName, value) {
    return array.find((obj) => obj[keyName] == value) || null;
}

function copyTextToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        toast.open({ type: "success", message: "Copied to clipboard!" });
    });
}

function initLabelDropdown(selector, options = {}) {
    const $selector = $(selector);

    const menuItems = options.menu || [];
    const defaultValue = options.default;

    // Find default item by value
    const defaultItem = menuItems.find(item => item.value === defaultValue) || menuItems[0] || {};
    const defaultLabel = defaultItem.label || 'Select';

    const $dropdown = $('<div>').addClass('dropdown');

    const $toggle = $('<a>')
        .addClass('dropdown-toggle text-secondary')
        .attr({
            href: '#',
            'data-bs-toggle': 'dropdown',
            'aria-haspopup': 'true',
            'aria-expanded': 'false'
        })
        .text(defaultLabel);

    const $menu = $('<div>')
        .addClass('dropdown-menu dropdown-menu-end');

    menuItems.forEach(item => {
        const $item = $('<a>')
            .addClass('dropdown-item')
            .text(item.label)
            .attr({
                href: item.url || 'javascript:;',
                value: item.value || undefined
            });

        if (item.className) {
            $item.addClass(item.className);
        }

        if (item.value === defaultValue) {
            $item.addClass('active');
        }

        // Store the full item object
        $item.data('menuItem', item);

        $menu.append($item);
    });

    // Handle click on items
    $menu.on('click', '.dropdown-item', function (e) {
        e.preventDefault();

        const $clicked = $(this);

        // Do nothing if already selected
        if ($clicked.hasClass('active')) return;

        const selectedItem = $clicked.data('menuItem');

        // Update label
        $toggle.text(selectedItem.label);

        // Update active class
        $menu.find('.dropdown-item').removeClass('active');
        $clicked.addClass('active');

        // Trigger onChange with full item
        if (typeof options.onChange === 'function') {
            options.onChange(selectedItem);
        }
    });

    $dropdown.append($toggle).append($menu);
    $selector.append($dropdown);

    // 🔥 Trigger onChange immediately with defaultItem
    if (typeof options.onChange === 'function' && defaultItem.value !== undefined) {
        options.onChange(defaultItem);
    }
}

function handleSidebarMenu() {

    var currentUrl = window.location.href.split('?')[0]; // Strip query parameters
    var currentPathname = window.location.pathname; // Get current path

    // Reset all active states
    $('.nav-item').removeClass('active');
    $('.sidebar-menu').removeClass('show');
    $('.nav-link').removeClass('active');
    $('.nav-link').attr('aria-expanded', 'false');

    // Track the active item
    var $activeItem = null;

    // Activate the top-level menu item and handle dropdown
    $('.nav-item').each(function () {
        var itemUrl = $(this).data('url');
        if (itemUrl) {
            itemUrl = itemUrl.split('?')[0]; // Strip query parameters

            if (currentUrl === itemUrl || currentPathname.startsWith(itemUrl)) {
                $(this).addClass('active');
                $(this).find('.nav-link').addClass('active');

                // Handle dropdowns
                if ($(this).hasClass('dropdown')) {
                    $(this).find('.sidebar-menu').addClass('show');
                    $(this).find('.nav-link').attr('aria-expanded', 'true');
                }

                $activeItem = $(this);
            }
        }
    });

    // Activate submenu items
    $('.sidebar-menu a').each(function () {
        var submenuUrl = $(this).attr('href');
        if (submenuUrl) {
            submenuUrl = submenuUrl.split('?')[0]; // Strip query parameters

            if (currentUrl === submenuUrl || currentUrl.startsWith(submenuUrl)) {
                $(this).addClass('active text-white');
                $(this).closest('.sidebar-menu').addClass('show');
                $(this).closest('.nav-item').addClass('active');
                $(this).closest('.nav-item').find('.nav-link').attr('aria-expanded', 'true');

                if (!$activeItem) {
                    $activeItem = $(this).closest('.nav-item');
                }
            }
        }
    });

    // Scroll the parent 'navbar' container to the active item
    if ($activeItem) {
        var $scrollParent = $('.navbar-vertical'); // Select the navbar parent container

        // Scroll the navbar to the active item
        $scrollParent.animate({
            scrollTop: $activeItem.position().top + $scrollParent.scrollTop() -
                100 // Adjust offset as needed
        }, 500);
    }
}

function handleNavbarMenu() {

    var currentUrl = window.location.href.split('?')[0]; // Strip query parameters
    var currentPathname = window.location.pathname; // Get current path

    // Reset all active states
    $('.nav-item, .nav-link').removeClass('active');
    $('.dropdown-menu').removeClass('show');
    $('.nav-link').attr('aria-expanded', 'false');

    var $activeItem = null;

    // Activate the top-level menu item and handle dropdown
    $('.nav-item').each(function () {
        var itemUrl = $(this).data('url');
        if (itemUrl) {
            itemUrl = itemUrl.split('?')[0]; // Strip query parameters

            if (currentUrl === itemUrl || currentPathname.startsWith(itemUrl)) {
                $(this).addClass('active');
                $(this).find('.nav-link').addClass('active');

                // Handle dropdowns
                if ($(this).hasClass('dropdown')) {
                    $(this).find('.dropdown-menu').addClass('show');
                    $(this).find('.nav-link').attr('aria-expanded', 'true');
                }

                $activeItem = $(this);
            }
        }
    });

    // Activate submenu items
    $('.dropdown-menu a').each(function () {
        var submenuUrl = $(this).attr('href');
        if (submenuUrl) {
            submenuUrl = submenuUrl.split('?')[0]; // Strip query parameters

            if (currentUrl === submenuUrl || currentPathname.startsWith(submenuUrl)) {
                $(this).addClass('active');
                $(this).closest('.nav-item').addClass('active');
                $(this).closest('.nav-item').find('.nav-link').attr('aria-expanded', 'true');

                if (!$activeItem) {
                    $activeItem = $(this).closest('.nav-item');
                }
            }
        }
    });

    // Scroll to active item
    if ($activeItem) {
        var $scrollParent = $('.navbar'); // Adjust based on your layout
        $scrollParent.animate({
            scrollTop: $activeItem.position().top + $scrollParent.scrollTop() - 100
        }, 500);
    }
}

function setPanelTheme(theme) {
    const themeKey = "tablerTheme";
    document.body[theme === "dark" ? "setAttribute" : "removeAttribute"]("data-bs-theme", "dark");
    localStorage.setItem(themeKey, theme);
}


$(document).ready(function () {
    // if ($.fn.dataTable) {
    //     $.extend($.fn.dataTable.defaults, {
    //         pageLength: 15,
    //         lengthMenu: [15, 30, 50, 100],
    //     });
    // }

    if ($.fn.dataTable && $.fn.dataTable.Buttons && $.fn.dataTable.Buttons.defaults) {
        $.extend($.fn.dataTable.Buttons.defaults.dom.button, {
            className: 'btn'
        });
    }


    $(
        "body"
    ).append(`<div class="modal modal-blur fade" id="modal-confirmation" data-bs-backdrop="static" data-bs-keyboard="false"
        tabindex="-1" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-status"></div>
                <div class="modal-body text-center py-4">
                    <i class="main-icon" style="font-size: 60px;"></i>
                    <div class="modal-title mt-3"></div>
                    <div class="modal-message"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary me-auto cancel-btn"
                        data-bs-dismiss="modal">Cancel</button>
                    <button style="min-width: 160px;" role="button" type="button" class="confirm-btn">
                        <span style="display: none;" class="spinner-border spinner-border-sm me-2"
                            role="status"></span>
                        <span class="btn-text"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>`)
        .append(`<div class="modal modal-blur fade" id="modal-alert" tabindex="-1" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-status"></div>
                <div class="modal-body text-center py-4">
                    <i class="main-icon ti ti-alert-triangle text-success" style="font-size: 50px;"></i>
                    <h3 class="main-title mt-2"></h3>
                    <div class="main-content"></div>
                </div>
            </div>
        </div>
    </div>`);
});
