<?php
foreach (['Create.vue', 'Edit.vue'] as $f) {
    $p = 'resources/js/Pages/Mitra/Product/' . $f;
    $c = file_get_contents($p);
    $c = str_replace('@change="form.subcategory_id = \'\'; form.unit = \'\'"', '@change="form.subcategory_id = \'\'"', $c);
    file_put_contents($p, $c);
}
