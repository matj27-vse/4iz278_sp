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

const getJSON = async url => {
    const response = await fetch(url);
    if (!response.ok) {
        throw new Error(response.statusText);
    }

    return response.json();
}

const prepareTable = (doctor_id, freeTimeSlots) => {
    let tableDiv = document.getElementById('timeslots-table');
    tableDiv.innerHTML = '';

    let table = document.createElement('table');
    table.classList.add("table");
    table.innerHTML = `
        <thead>
            <tr>
                <th scope="col">Čas vyšetření</th>
            </tr>
        </thead>
    `;

    for (let i = 0; i < freeTimeSlots.length; i++) {
        const dateTime = new Date(freeTimeSlots[i] * 1000);
        const humanReadableDate = String(dateTime.getDate()).padStart(2, '0') + '. ' +
            String(dateTime.getMonth()).padStart(2, '0') + '. ' +
            String(dateTime.getFullYear()) + ', ' +
            String(dateTime.getHours()).padStart(2, '0') + ':' +
            String(dateTime.getMinutes()).padStart(2, '0') + ':' +
            String(dateTime.getSeconds()).padStart(2, '0');

        var tr = document.createElement('tr');
        tr.innerHTML = `
            <th scope="col">
                <a href="reservation_confirmation.php?doctor_id=${doctor_id}&timestamp=${freeTimeSlots[i]}">${humanReadableDate}</a>
            </th>
        `;
        table.appendChild(tr);
    }

    tableDiv.appendChild(table);
}

const loadFreeTimeSlots = (date) => {
    const doctorId = new URLSearchParams(window.location.search).get("doctor_id");

    //todo nějakej spinner

    const formattedDate = `${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()}`;
    const url = `https://eso.vse.cz/~matj27/4iz278/semestralni_prace/api/doctor/free_time_slots/?doctor_id=${doctorId}&date=${formattedDate}`;
    getJSON(url).then(data => {
        prepareTable(doctorId, data)
    }).catch(error => {
        alert(error);
    });
}

let datePickerOkBtn = document.getElementById('mc-btn__ok');
datePickerOkBtn.addEventListener("click", () => loadFreeTimeSlots(picker.getFullDate()));

//opens MCDatepicker
btn.onclick = () => picker.open();
