<?php

$models = [
    'Province', 'Regency', 'District',
    'Category', 'Subcategory',
    'PartnerProfile', 'Product', 'ProductPhoto', 'ProductCoverage',
    'StockLog', 'PriceLog', 'Order', 'OrderItem', 'Inquiry', 'Setting'
];

foreach ($models as $model) {
    echo "Creating $model...\n";
    shell_exec("D:\\laragon\\bin\\php\\php-8.3.33-Win32-vs16-x64\\php.exe artisan make:model $model -m");
}
echo "Done.\n";
