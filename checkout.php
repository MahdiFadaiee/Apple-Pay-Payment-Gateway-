<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Apple pay</title>
</head>
<body>
<a id="applePayButton">
    <img src="apple-pay.webp" width="100" height="100">
</a>
</body>


<!--script code-->
<script src="https://applepay.cdn-apple.com/jsapi/v1/apple-pay-sdk.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    //    Apple Pay
    $(document).ready(function () {
        $('#applePayButton').on('click', function () {
            const applePayBtn = $(this); // cache this for future use
            applePayBtn.prop('disabled', true); // disable on click
            const paymentRequest = {
                countryCode: 'US',
                currencyCode: 'USD',
                domainName: 'cutiebeauti.com',
                supportedNetworks: ["amex", "masterCard", "visa", "discover"],
                merchantCapabilities: ['supports3DS', 'supportsCredit', 'supportsDebit'],
                requiredShippingContactFields: ['postalAddress', 'name'],
                merchantIdentifier: 'merchant.cutiebeauti',
                displayName: 'cutiebeauti',
                total: {
                    label: 'cutiebeauti ApplePay',
                    amount: '<?php echo $total; ?>'
                }
            };

            const session = new ApplePaySession(1, paymentRequest);

            if (window.ApplePaySession) {
                if (ApplePaySession.canMakePayments) {
                } else {
                    applePayBtn.prop('disabled', false); // re-enable button
                    return;
                }
            } else {
                applePayBtn.prop('disabled', false); // re-enable button
                return;
            }


            session.onvalidatemerchant = function (event) {

                const validationURL = event.validationURL;
                const requestData = {
                    validationUrl: validationURL,
                    paymentData: paymentRequest
                };
                fetch("https://www.cutiebeauti.com/api/applepay/validateMerchant.php", {
                    method: "POST",
                    body: JSON.stringify(requestData),
                    headers: {
                        "Content-Type": "application/json"
                    }
                }).then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                }).then(data => {
                    if (data.merchantSessionIdentifier) {
                        session.completeMerchantValidation(data);

                    } else {
                        applePayBtn.prop('disabled', false); // re-enable button if validation fails
                        session.abort();
                    }

                }).catch(error => {
                    applePayBtn.prop('disabled', false); // re-enable button if there's an error
                    session.abort();
                });
            };


            session.onpaymentauthorized = function (event) {
                const payment = event.payment;
                const shippingContact = payment.shippingContact;
                const token = payment.token;
                const paymentmethod = payment.token.paymentMethod;
                const total = <?php echo $total; ?>;
                const shippingCost = <?php echo $shipping_cost; ?>;

                //send All date we received from Apple Pay and send to your own database
                $.post("ApplePay.php", {
                    shippingContact: shippingContact,
                    payment: payment,
                    token: token,
                    total: total,
                    shippingCost: shippingCost,
                    paymentmethod: paymentmethod,
                }).done(function (data) {
                    data = String(data).trim();
                    window.location.replace("special-offer.php?done&order_id=" + data + "&method=apl");
                }).fail(function (jqXHR, textStatus, errorThrown) {
                    applePayBtn.prop('disabled', false); // re-enable button
                    session.abort();
                });

                session.completePayment(ApplePaySession.STATUS_SUCCESS);
            };

            session.oncancel = function (event) {

                applePayBtn.prop('disabled', false); // re-enable button if session is canceled
            };

            session.begin();
        });
    });
</script>
</html>