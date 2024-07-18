//for single email
document.getElementById("single-email-form").addEventListener("submit", (e) => {
    //preventing submission
    e.preventDefault();
    //taking element
    const input = document.getElementById("single-email");
    //taking data
    const data = input.value;
    //clearing input
    input.value = "";
    //declaring endpoint
    let endpoint = "../php/singlemail.php";
    // Create a FormData object to hold the data
    let formData = new FormData();
    formData.append("email", data);
    //submitting array data
    submitData(endpoint, formData);
})

//for multiple email
document.getElementById("multiple-email-form").addEventListener("submit", (e) => {
    //preventing submission
    e.preventDefault();
    //taking element
    const input = document.getElementById("multiple-email");
    //taking data
    const data = input.value;
    //clearing input
    input.value = "";
    //splitting to arr
    let arr = data.split("\n");
    // Create a FormData object to hold the array
    let formData = new FormData();
    //clearing any unusual data and appending data
    arr.forEach((email, index) => {
        arr[index] = email.replace(/[^.@a-z0-9]/g, "");
        // formData.append(`emails[${index}]`, arr[index]);
        formData.append("emails[]", arr[index]);
    })
    //declaring endpoint
    let endpoint = "../php/multiplemail.php";
    //submitting array data
    submitData(endpoint, formData);
})

//for file
document.getElementById("multiple-email-file").addEventListener("submit", (e) => {
    //preventing submission
    e.preventDefault();
    //taking element
    const input = document.getElementById("email-file").files[0];
    // Create a FormData object to hold the file
    let formData = new FormData();
    formData.append("file", input);
    //clearing file
    document.getElementById("email-file").value = "";
    //declaring endpoint
    let endpoint = "../php/filemail.php";
    //submitting data
    submitData(endpoint, formData);
})

async function submitData(endpoint, data) {
    //clearing previous data
    document.getElementById("email-cont").innerHTML = "";
    document.getElementById("result").classList.add("hidden");
    document.getElementById("download-form").classList.add("hidden");

    //changing info container
    changeInfoContainer("Request has been sent.", "green", "009933");

    let res = await fetch(endpoint, {
        method: "POST",
        body: data
    });

    let json = await res.json();

    loadData(json);
}

async function loadData(data) {
    if (data.error != undefined) {
        //changing info container
        changeInfoContainer(data.error, "red", "ff0000");
        return;
    }

    //changing info container
    changeInfoContainer("Data has been recieved.", "blue", "2196f3");

    //showing result
    let result = document.getElementById("result");
    result.classList.remove("hidden");
    document.getElementById("result-check").innerHTML = data["check"];

    //showing data by fetching it
    let res = await fetch(data["url"]);
    let json = await res.json();


    document.getElementById("result-id").innerHTML = data["id"];

    //showing result only if multiple
    if (data["check"] == "multiple") {
        document.getElementById("download-form").classList.remove("hidden");
        document.getElementById("download-id").value = data["id"];
        showMultiple(json);
        return;
    }

    showSingle(json);
}

function showMultiple(data) {
    const resultEmailCont = document.getElementById("email-cont");

    //taking result
    let result = data["results"];
    let emails = Object.keys(result);

    //looping through email
    emails.forEach(email => {
        let status = result[email]["status"];
        if (status == "safe" || status == "valid") {
            resultEmailCont.innerHTML +=
                `<div class="flex items-center justify-between rounded-md p-2 bg-green-300 border-green-700 border text-green-700 font-semibold">
                    <p>${email}</p>
                    <p class="capitalize">${status}</p>
                </div>`;
        } else {
            resultEmailCont.innerHTML +=
                `<div class="flex items-center justify-between rounded-md p-2 bg-red-300 border-red-700 border text-red-700 font-semibold">
                    <p>${email}</p>
                    <p class="capitalize">${status}</p>
                </div>`;
        }
    })
}

function showSingle(data) {
    const resultEmailCont = document.getElementById("email-cont");

    //taking result and status
    let email = data["email"];
    let status = data["status"];

    if (status == "safe" || status == "valid") {
        resultEmailCont.innerHTML =
            `<div class="flex items-center justify-between rounded-md p-2 bg-green-300 border-green-700 border text-green-700 font-semibold">
                <p>${email}</p>
                <p class="capitalize">${status}</p>
            </div>`;
    } else {
        resultEmailCont.innerHTML =
            `<div class="flex items-center justify-between rounded-md p-2 bg-red-300 border-red-700 border text-red-700 font-semibold">
                <p>${email}</p>
                <p class="capitalize">${status}</p>
            </div>`;
    }
}

async function history() {
    //for history
    let res = await fetch("./php/history.php");
    let data = await res.json();

    if (data.error != undefined) {
        return;
    }

    //taking history container
    let container = document.getElementById("history-container");

    //showing container
    document.getElementById("history").classList.remove("hidden");

    //putting data to history contain by looping though data got
    data.forEach(history => {
        let id = history.id;
        let time = history.time;
        let method = history.method;
        let url = history.url;

        //if single method
        if (method == "Single") {
            showSingleHistory(container, id, time, url);
            return;
        }

        //if multiple method
        showMultipleHistory(container, id, time);
    })
}

document.addEventListener("load", history());

//showing single history email
async function showSingleHistoryEmail(id) {
    document.getElementById(id).classList.toggle("hidden");
}

//showing single history
async function showSingleHistory(container, id, time, url) {

    //fetching email and status from res
    const res = await fetch(url);
    const data = await res.json();

    const email = data["email"];
    const status = data["status"];

    //initializing color var
    let color;

    //changing color according to status
    if (status == "valid" || status == "safe") {
        color = "bg-green-300 border-green-700 text-green-700";
    } else {
        color = "bg-red-300 border-red-700 text-red-700";
    }

    container.innerHTML +=
        `<div
        class="space-y-2 border-[#1F2937] border border-b-0 last:border-b bg-white p-2 first:rounded-t-md last:rounded-b-md">
        <div class="flex items-center justify-between">
            <div class="text-base mx-2">
                <p class="font-semibold space-x-2.5">
                    <span>${id}</span>
                    <span>-</span>
                    <span>${time}</span>
                </p>
            </div>
            <button onclick="showSingleHistoryEmail('${id}')" class="bg-[#1F2937] rounded-md p-1">
                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="24" viewBox="0 0 48 48">
                    <path fill="#fff"
                        d="M44,24c0,11.045-8.955,20-20,20S4,35.045,4,24S12.955,4,24,4S44,12.955,44,24z">
                    </path>
                    <path fill="#000"
                        d="M22 22h4v11h-4V22zM26.5 16.5c0 1.379-1.121 2.5-2.5 2.5s-2.5-1.121-2.5-2.5S22.621 14 24 14 26.5 15.121 26.5 16.5z">
                    </path>
                </svg>
            </button>
        </div>

        <div class="space-y-2 hidden" id="${id}">
            <hr class="text-gray-700 h-0.5 bg-gray-700">

            <div class="flex flex-col space-y-1">
                <div
                    class="flex items-center justify-between rounded-md p-2 ${color} border font-semibold">
                    <p>${email}</p>
                    <p class="capitalize">${status}</p>
                </div>
            </div>
        </div>
    </div>`;
}

function showMultipleHistory(container, id, time) {
    container.innerHTML +=
        `<div
        class="flex items-center justify-between border-[#1F2937] border border-b-0 last:border-b bg-white p-2 first:rounded-t-md last:rounded-b-md">
        <div class="text-base mx-2">
            <p class="font-semibold space-x-2.5">
                <span>${id}</span>
                <span>-</span>
                <span>${time}</span>
            </p>
        </div>
        <form action="php/download.php" class="flex items-center space-x-2" id="download-form" method="post"
            target="_blank">
            <select name="select" class="p-1.5 border border-[#1F2937]" required>
                <option value="all">All</option>
                <option value="valid">Valid / Safe</option>
            </select>
            <input type="hidden" name="id" id="download-id" value="${id}">
            <button type="submit"
                class="bg-[#1F2937] hover:bg-gray-800 border border-[#1F2937] rounded-md text-white outline-none p-1"
                id="download">
                <img class="rounded-full invert p-0.5 min-w-6 w-6"
                    src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAA7AAAAOwBeShxvQAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAAJESURBVFiFxdfNi01hHAfwzxymMe54i4Tc0rikaCxEakJRaMZGWcqCf0JKEaWmLJCN1OwsZolRLLwspjFsEAs7GS9J8jIz7jSlsTjn1ul075nn3CvzrV/nOec8v+/39/ye9zbhKOEIerEba7ESC/ED7/ACDzGMiQLcuejGDfzCbKBN4SY2tSK8CJcwXUA4azMYSLgKoYyxFoSzNirusiBsxdcA0t84iVOoBtQfR89c4hV8CmzV7ZTfnUCfcaxJC0apcjtuCU/VdKpcDfRZj7vorBfABewMJGoFO3A6+7Gs+GgfSvkPFfSdlGS6loEz6PhXTQxACWdrAXTi+H8Ur+EElkQ4jK55CKCEvggH50G8hv0Rts1jAD0RNgdUfIzl4t1wKqfeJPqxCiMBvN0RFgdUHMbP5NnfIIhJ9OEeviXPubCMsPn/Ubwt17APg6n3QexJvVfwOYB3SkIesni8zwSxokG5Il7zQzmNBFauF0QWRcRnMRrhVQ5hFmXxgKwXRAWPxBtOKMbgaIGIG2Vio2Itr9kxWCo+QDYTRK948DUj/l1qBl5rgqBVuw5tqRS+0XhH/IP78hehNEo4hAUN/lexRTILajifE+1AoHAal3P4LtZz6MDLBg5XmgigUbc+k8p0W8apjKdYl/k+iyfi03IIVmNvHf4v2CWT+ix6hK+OReyDuN+DUBbf8/6V+HP5K2hddOCc1q5mVfGAay8qnkY3rip2OZ0Qz/M5l+XsIMlDFw6Ij3DbsUF8SJlJ7C1e40FiQZeVv9B8oAsh+Fn5AAAAAElFTkSuQmCC"
                    alt="">
            </button>
        </form>
    </div>`;
}

//changing color of info container
function changeInfoContainer(message, color, fill) {
    document.getElementById("info-container").innerHTML =
        `<div class="rounded-md bg-${color}-200 border-${color}-700 border flex items-center space-x-2 p-2">
        <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="24" viewBox="0 0 48 48">
            <path fill="#${fill}"
                d="M44,24c0,11.045-8.955,20-20,20S4,35.045,4,24S12.955,4,24,4S44,12.955,44,24z">
            </path>
            <path fill="#fff"
                d="M22 22h4v11h-4V22zM26.5 16.5c0 1.379-1.121 2.5-2.5 2.5s-2.5-1.121-2.5-2.5S22.621 14 24 14 26.5 15.121 26.5 16.5z">
            </path>
        </svg>
        <p class="text-${color}-600 font-semibold">${message}</p>
    </div>`;
}