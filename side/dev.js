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
    // document.getElementById("email-file").value = "";
    //declaring endpoint
    let endpoint = "../php/filemail.php";
    //submitting data
    submitData(endpoint, formData);
})

async function submitData(endpoint, data) {
    let res = await fetch(endpoint, {
        method: "POST",
        body: data
    });

    let json = await res.json();

    loadData(json);
}

async function loadData(data) {
    if (data.error != undefined) {
        console.log(data);
        return;
    }

    //showing result
    let result = document.getElementById("result");
    result.classList.remove("hidden");
    document.getElementById("result-check").innerHTML = data["check"];

    //showing data by fetching it
    let res = await fetch(data["url"]);
    let json = await res.json();


    //showing result only if multiple
    if (data["check"] == "multiple") {
        document.getElementById("result-download").classList.remove("hidden");
        document.getElementById("result-id").innerHTML = data["id"];
        showMultiple(json);
        return;
    }

    showSingle(json);
}

function showMultiple(data) {
    console.log(data);
    const resultEmailCont = document.getElementById("email-cont");

    //taking result
    let result = data["results"];
    let emails = Object.keys(result);

    //looping through email
    emails.forEach(email => {
        let status = result[email]["status"];
        if (status == "safe" || status == "valid") {
            resultEmailCont.innerHTML +=
                `<div class="flex items-center justify-between rounded-md p-2 bg-green-300 border-green-700 border">
                    <p>${email}</p>
                    <p class="capitalize">${status}</p>
                </div>`;
        } else {
            resultEmailCont.innerHTML +=
                `<div class="flex items-center justify-between rounded-md p-2 bg-red-300 border-red-700 border">
                    <p>${email}</p>
                    <p class="capitalize">${status}</p>
                </div>`;
        }
    })
}





