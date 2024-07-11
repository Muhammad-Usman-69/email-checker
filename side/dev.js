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

    let json = await res.text();
    
    console.log(json);
}