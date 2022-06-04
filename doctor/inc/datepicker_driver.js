const picker = MCDatepicker.create({
    el: '#datepicker',
    disableWeekends: true,
    firstWeekday: 1,
    customMonths: ['Leden', 'Únor', 'Březen', 'Duben', 'Květen', 'Červen', 'Červenec', 'Srpen', 'Září', 'Říjen', 'Listopad', 'Prosinec'],
    customWeekDays: ['Neděle', 'Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek', 'Sobota'],
    customClearBTN: 'Vyčistit výběr',
    customCancelBTN: 'Zrušit',
    customOkBTN: 'Zvolit',
    minDate: new Date(Date.now()),
    dateFormat: 'dd-mmmm-yyyy'
    //https://mcdatepicker.netlify.app/docs/theme
});

const appointmentsByDate = (date) => {
    let newDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
    console.log(newDate);

    let url = new URL(window.location.href);
    url.searchParams.set(`timestamp`, `${newDate.getTime() / 1000}`);

    location.href = `${url.href}`;
}

function removeParam(key, sourceURL) {
    var rtn = sourceURL.split("?")[0],
        param,
        params_arr = [],
        queryString = (sourceURL.indexOf("?") !== -1) ? sourceURL.split("?")[1] : "";
    if (queryString !== "") {
        params_arr = queryString.split("&");
        for (var i = params_arr.length - 1; i >= 0; i -= 1) {
            param = params_arr[i].split("=")[0];
            if (param === key) {
                params_arr.splice(i, 1);
            }
        }
        if (params_arr.length) rtn = rtn + "?" + params_arr.join("&");
    }
    return rtn;
}

const clearDateConstraint = () => {
    let url = new URL(window.location.href);
    url = removeParam(`timestamp`, url.href);

    location.href = `${url}`;
}

let datePickerOkBtn = document.getElementById('mc-btn__ok');
datePickerOkBtn.addEventListener("click", () => appointmentsByDate(picker.getFullDate()));

let datePickerClearBtn = document.getElementById('mc-btn__clear');
datePickerClearBtn.addEventListener("click", () => clearDateConstraint());

//opens MCDatepicker
btn.onclick = () => picker.open();
