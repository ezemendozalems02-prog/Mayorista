<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WSAA – Web Service de Autenticación y Autorización
    |--------------------------------------------------------------------------
    |
    | Endpoints for ARCA's authentication service. Use TESTING during
    | development; switch to PRODUCTION only with a valid ARCA certificate
    | issued for the production environment.
    |
    */
    'wsaa' => [
        'testing_url'    => env('ARCA_WSAA_TESTING_URL', 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms'),
        'production_url' => env('ARCA_WSAA_PRODUCTION_URL', 'https://wsaa.afip.gov.ar/ws/services/LoginCms'),

        // Service name sent in the TRA (Ticket de Requerimiento de Acceso).
        // For electronic invoicing this is always 'wsfe'.
        'service' => env('ARCA_WSAA_SERVICE', 'wsfe'),

        // SOAP connection timeout in seconds
        'timeout' => (int) env('ARCA_WSAA_TIMEOUT', 30),

        // Token is considered expired when it has less than this many minutes left.
        // This buffer prevents race conditions between check and actual SOAP call.
        'expiry_buffer_minutes' => (int) env('ARCA_TOKEN_BUFFER_MINUTES', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | WSFEv1 – Web Service de Facturación Electrónica v1
    |--------------------------------------------------------------------------
    |
    | Endpoints for electronic invoice authorization (CAE retrieval).
    | Uses WSDL URLs — SoapClient fetches the full schema automatically.
    |
    */
    'wsfe' => [
        'testing_url'    => env('ARCA_WSFE_TESTING_URL', 'https://wswhomo.afip.gov.ar/wsfev1/service.asmx?WSDL'),
        'production_url' => env('ARCA_WSFE_PRODUCTION_URL', 'https://servicios1.afip.gov.ar/wsfev1/service.asmx?WSDL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Temporary file storage for signing operations
    |--------------------------------------------------------------------------
    |
    | OpenSSL signing requires writing certificate/key content to disk
    | temporarily. Files are created with 0600 permissions and deleted
    | immediately after the signing operation (even on failure).
    |
    */
    'tmp_path' => storage_path('app/arca/tmp'),

];
