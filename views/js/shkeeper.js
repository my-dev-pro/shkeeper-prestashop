$("#get-address").on('click', function () {

    // reset current data
    $('#wallet-address').text('');
    $('#amount').text('');
    $('#qrcode').text('');

    new QRCode(document.getElementById("qrcode"), {
        text: 'MY-Dev',
        width: 128,
        height: 128,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });

});