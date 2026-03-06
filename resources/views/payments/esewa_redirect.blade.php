<!DOCTYPE html>
<html>
<head>
    <title>Redirecting to eSewa...</title>
</head>
<body onload="document.forms['esewa_form'].submit()">
    <div style="text-align: center; margin-top: 50px;">
        <h2>Redirecting to eSewa Payment Gateway...</h2>
        <p>Please do not refresh the page.</p>
        
        <form name="esewa_form" action="{{ $gatewayUrl }}" method="POST">
            @foreach($paymentData as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <noscript>
                <p>If you are not redirected, please click the button below:</p>
                <button type="submit">Pay with eSewa</button>
            </noscript>
        </form>
    </div>
</body>
</html>
