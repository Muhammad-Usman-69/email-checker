<?php
session_start();

//check is session closing
if (isset($_GET["close"]) && $_GET["close"] == true) {
    session_destroy();
    header("location:./php/verify.php");
    exit();
}

if (!isset($_SESSION["allow"]) || $_SESSION["allow"] != true) {
    header("location:./php/verify.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Checker</title>
    <meta name="author" content="Muhammad Usman">
    <link rel="stylesheet" href="./side/style.css">
    <link rel="shortcut icon"
        href="data:image/png;base64,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"
        type="image/x-icon">
</head>

<body class="bg-[#e4eaf2] text-[#1F2937] flex flex-col min-h-screen relative">

    <header class="p-3 border-b border-gray-800 flex justify-between items-center bg-white">
        <a href="/" class="flex flex-row items-center space-x-3">
            <img src="data:image/png;base64,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"
                alt="logo" width="56px" height="56px">
            <p class="text-lg">Email Checker</p>
        </a>
        <a href="/?close=true" class="font-semibold hover:underline">Close Session</a>
    </header>

    <!-- checkers -->
    <div class="flex flex-col space-y-4 md:space-y-0 md:grid md:grid-cols-[1fr_2px_1fr] gap-4 p-4 text-sm">
        <!-- single checker -->
        <div class="w-full space-y-4">
            <h3 class="text-xl font-semibold">Single Email Checker</h3>
            <form action="" class="flex" id="single-email-form">
                <input name="email" id="single-email" type="email" placeholder="example@example.com"
                    class="bg-white rounded-l-md outline-none border border-[#1F2937] py-2 px-3 w-full" minlength="12"
                    required>
                <button type="submit" id="single-button"
                    class="bg-[#1F2937] border border-[#1F2937] rounded-r-md text-white outline-none py-2 px-3 submit-button">Submit</button>
            </form>
        </div>

        <!-- line -->
        <hr class="w-0.5 h-full hidden md:block text-gray-700 py-4 bg-gray-700">

        <!-- file checker -->
        <div class="w-full space-y-4">
            <h3 class="text-xl font-semibold">File Checker (Max. limit 200)</h3>
            <form action="" class="flex flex-col" id="multiple-email-file">
                <div class="flex">
                    <input name="file" id="email-file" type="file"
                        class="bg-white rounded-l-md outline-none border border-[#1F2937] py-2 px-3 resize-none w-full"
                        required>
                    <button type="submit"
                        class="bg-[#1F2937] border border-[#1F2937] rounded-r-md text-white outline-none py-2 px-3 submit-button">Submit</button>
                </div>
            </form>
        </div>

        <!-- multiple checker -->
        <div class="col-span-3 space-y-4">
            <h3 class="text-xl font-semibold">Multiple Email Checker (Max. limit 200)</h3>
            <form action="" id="multiple-email-form">
                <div class="flex w-full">
                    <textarea name="email" id="multiple-email"
                        class="bg-white rounded-l-md outline-none border border-[#1F2937] py-2 px-3 resize-none w-full"
                        rows="5" required minlength="12" placeholder></textarea>
                    <button type="submit"
                        class="bg-[#1F2937] border border-[#1F2937] rounded-r-md text-white outline-none py-2 px-3 submit-button">Submit</button>
                </div>
            </form>

            <div id="info-container">
                <div class="rounded-md bg-blue-200 border-blue-700 border flex items-center space-x-2 p-2">
                    <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 48 48" style="width: 24px; min-width: 24px">
                        <path fill="#2196f3"
                            d="M44,24c0,11.045-8.955,20-20,20S4,35.045,4,24S12.955,4,24,4S44,12.955,44,24z">
                        </path>
                        <path fill="#fff"
                            d="M22 22h4v11h-4V22zM26.5 16.5c0 1.379-1.121 2.5-2.5 2.5s-2.5-1.121-2.5-2.5S22.621 14 24 14 26.5 15.121 26.5 16.5z">
                        </path>
                    </svg>
                    <p class="text-blue-600 font-semibold">One Email Per Line</p>
                </div>
            </div>
        </div>
    </div>

    <!-- result -->
    <div class="hidden" id="result">
        <p class="bg-white text-lg p-4 py-2 font-semibold">Result</p>
        <div class="m-4 p-3 text-sm border-[#1F2937] border bg-white space-y-3 rounded-md">
            <!-- basic info -->
            <div class="flex items-center justify-between">
                <div class="text-base mx-2">
                    <p class="font-semibold space-x-2.5">
                        <span id="result-id"></span>
                        <span>-</span>
                        <span id="result-check" class="capitalize"></span>
                    </p>
                </div>
                <form action="php/download.php" class="flex items-center space-x-2 hidden" id="download-form"
                    method="post" target="_blank">
                    <select name="select" class="p-1.5 border border-[#1F2937]" required>
                        <option value="all">All</option>
                        <option value="valid">Valid / Safe</option>
                    </select>
                    <input type="hidden" name="id" id="download-id">
                    <button type="submit"
                        class="bg-[#1F2937] hover:bg-gray-800 border border-[#1F2937] rounded-md text-white outline-none p-1"
                        id="download">
                        <img class="rounded-full invert p-0.5 min-w-6 w-6"
                            src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAA7AAAAOwBeShxvQAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAAJESURBVFiFxdfNi01hHAfwzxymMe54i4Tc0rikaCxEakJRaMZGWcqCf0JKEaWmLJCN1OwsZolRLLwspjFsEAs7GS9J8jIz7jSlsTjn1ul075nn3CvzrV/nOec8v+/39/ye9zbhKOEIerEba7ESC/ED7/ACDzGMiQLcuejGDfzCbKBN4SY2tSK8CJcwXUA4azMYSLgKoYyxFoSzNirusiBsxdcA0t84iVOoBtQfR89c4hV8CmzV7ZTfnUCfcaxJC0apcjtuCU/VdKpcDfRZj7vorBfABewMJGoFO3A6+7Gs+GgfSvkPFfSdlGS6loEz6PhXTQxACWdrAXTi+H8Ur+EElkQ4jK55CKCEvggH50G8hv0Rts1jAD0RNgdUfIzl4t1wKqfeJPqxCiMBvN0RFgdUHMbP5NnfIIhJ9OEeviXPubCMsPn/Ubwt17APg6n3QexJvVfwOYB3SkIesni8zwSxokG5Il7zQzmNBFauF0QWRcRnMRrhVQ5hFmXxgKwXRAWPxBtOKMbgaIGIG2Vio2Itr9kxWCo+QDYTRK948DUj/l1qBl5rgqBVuw5tqRS+0XhH/IP78hehNEo4hAUN/lexRTILajifE+1AoHAal3P4LtZz6MDLBg5XmgigUbc+k8p0W8apjKdYl/k+iyfi03IIVmNvHf4v2CWT+ix6hK+OReyDuN+DUBbf8/6V+HP5K2hddOCc1q5mVfGAay8qnkY3rip2OZ0Qz/M5l+XsIMlDFw6Ij3DbsUF8SJlJ7C1e40FiQZeVv9B8oAsh+Fn5AAAAAElFTkSuQmCC"
                            alt="">
                    </button>
                </form>
            </div>

            <hr class="text-gray-700 h-0.5 bg-gray-700">

            <div class="flex flex-col space-y-1" id="email-cont">
            </div>
        </div>
    </div>

    <!-- history -->
    <div class="text-sm hidden" id="history">
        <p class="bg-white text-lg p-4 py-2 font-semibold">History</p>
        <div class="m-4" id="history-container">
        </div>
    </div>

    <footer class="bg-white border-gray-800 border-t mt-auto">
        <p class="text-center py-4">Email Checker | &copy; 2024 All Rights Reserved</p>
    </footer>

    <div id="dummy" class="hidden border-red-700 bg-red-200 text-red-600 border-green-700 bg-green-200 text-green-600"></div>

    <script src="side/dev.js"></script>
    <script>
        document.getElementById("multiple-email").placeholder =
            "example@example.com\nexample@example.com\nexample@example.com\nexample@example.com\nexample@example.com";
    </script>
    
</body>

</html>