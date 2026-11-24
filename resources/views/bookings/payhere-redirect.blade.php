<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>PayHere</title>
</head>
<body>
    <form id="bookingPayment__payhere_form" method="post" action="{{ $checkoutUrl }}">
        @foreach ($fields as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach
    </form>
    <script>
        document.getElementById('bookingPayment__payhere_form').submit();
    </script>
</body>
</html>
