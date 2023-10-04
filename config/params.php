<?php

return [
    'isApi' => false,
    'apiType' => null,
    'external' => false,
    'customerAppUrl' => '/app',
    'adminAppUrl' => '/admin',
    'managerAppUrl' => '/manager',
    'designerAppUrl' => '/designer',
    'restrictedUploadsFolder' => '@app/uploads',
    // Maximum number of uploaded files
    'maxFilesUpload' => 8,
    // Maximum size of uploaded files in bytes
    'maxFilesUploadSize' => 10 * 1024 * 1024,

    // User module params
    'user.passwordResetTokenExpire' => 3600,
    'user.confirmationTokenExpire' => 3600,
    'user.rememberMeDuration' => 3600 * 24 * 90,

    // Project module params
    'project.uploadFolder' => 'projects',

    // Set gear producers in config/local.php
    // e.g. ...'params' => ['gearProducers' => ['Sony', 'Samsung'...]], ...
    'gearProducers' => []
];
