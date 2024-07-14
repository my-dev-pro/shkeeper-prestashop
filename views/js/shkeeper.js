$("#get-address").on('click', function () {

    // reset current data
    $('#wallet-address').text('');
    $('#amount').text('');
    $('#qrcode').text('');

    // send ajex request
    $.ajax({
        type: 'POST',
        headers: { "cache-control": "no-cache" },
        url: carrierComparisonUrl + '?rand=' + new Date().getTime(),
        data: 'currency=' + $('#shkeeper-currency').val(),
        dataType: 'json',
        success: function (json) {
            console.log(json)
            if (json.length) {
                //
            }
        }
    });

    new QRCode(document.getElementById("qrcode"), {
        text: 'MY-Dev',
        width: 128,
        height: 128,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });

});