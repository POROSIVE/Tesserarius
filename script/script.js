

// I don't wanna deal with remaking script that works
function dialogOpen(Ids) {
    const elem = document.getElementById(Ids);
    if (!elem) return;
    const computed = window.getComputedStyle(elem).display;
    if (computed === 'none') {
        elem.style.display = 'flex';
        elem.classList.remove('hidden', 'dp-none');
    } else {
        elem.style.display = 'none';
    }
}

function formLoadFile(event, ids) {
    const output = document.getElementById(ids);
    const file = event.target && event.target.files ? event.target.files[0] : null;
    if (!output || !file) return;
    output.src = URL.createObjectURL(file);
    output.onload = function() {
        URL.revokeObjectURL(output.src);
    };
}

function formReloadFile(imgObj, ids) {
    const output = document.getElementById(ids);
    if (!output) return;
    output.src = imgObj;
}

function formLoad(ReqstData, ids) {
    const form = document.getElementById(ids) || document.forms[ids];
    if (!form) return;
    const values = ReqstData.dataset || {};
    Object.keys(values).forEach((key) => {
        const field = form.elements[key];
        if (!field) return;
        if (field.type === "checkbox") {
            const value = String(values[key]).toLowerCase();
            field.checked = (value === "1" || value === "true" || value === "yes" || value === "on");
        } else {
            field.value = values[key] ?? "";
        }
    });
}
