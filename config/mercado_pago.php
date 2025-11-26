<?php
return [
    'access_token' => getenv('MERCADO_PAGO_ACCESS_TOKEN') ?: 'APP_USR-8496996522435031-112521-284fb3826ce63d5d7176432e29143f22-3017105420',
    'public_key' => getenv('MERCADO_PAGO_PUBLIC_KEY') ?: 'APP_USR-0f0f9ef1-805b-489c-8d73-2a0f1a405ba8',
    'integrator_id' => getenv('MERCADO_PAGO_INTEGRATOR_ID') ?: '',
    'notification_url' => getenv('MERCADO_PAGO_NOTIFICATION_URL') ?: '',
];
