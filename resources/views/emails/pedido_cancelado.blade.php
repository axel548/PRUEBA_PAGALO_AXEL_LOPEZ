<!DOCTYPE html>
<html>

<head>
    <title>Pedido Cancelado</title>
</head>

<body>
    <h1>Hola, {{ $pedido->cliente->nombre }}</h1>
    <p>Lamentamos informarte que tu pedido con ID PD-{{ $pedido->id }} ha sido cancelado debido a falta de stock.</p>
    <p>Gracias por tu comprensión.</p>
    <p>Si tienes dudas, contáctanos.</p>
</body>

</html>
