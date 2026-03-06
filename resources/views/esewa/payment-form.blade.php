<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting to eSewa...</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #60a917;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        h2 {
            color: #333;
            margin-bottom: 10px;
        }
        p {
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Redirecting to eSewa Payment Gateway...</h2>
        <div class="spinner"></div>
        <p>Please wait while we redirect you to complete your payment.</p>
        <p><strong>Amount: Rs. {{ number_format($sale->final_amount, 2) }}</strong></p>
    </div>

    <!-- eSewa Payment Form (Auto-submits) -->
    <form id="esewaForm" action="{{ $gatewayUrl }}" method="POST" style="display: none;">
        @foreach($paymentData as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach
    </form>

    <script>
        // Auto-submit form after 1 second
        setTimeout(function() {
            document.getElementById('esewaForm').submit();
        }, 1000);
    </script>
</body>
</html>
