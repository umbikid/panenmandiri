<?php

$files = [
    'Product' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected \$guarded = ['id'];

    public function category() { return \$this->belongsTo(Category::class); }
    public function subcategory() { return \$this->belongsTo(Subcategory::class); }
    public function partner() { return \$this->belongsTo(User::class, 'partner_id'); }
    public function photos() { return \$this->hasMany(ProductPhoto::class); }
    public function primaryPhoto() { return \$this->hasOne(ProductPhoto::class)->where('is_primary', true); }
    public function coverages() { return \$this->hasMany(ProductCoverage::class); }
}
EOT,
    'Category' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected \$guarded = ['id'];

    public function subcategories() { return \$this->hasMany(Subcategory::class)->orderBy('sort_order'); }
}
EOT,
    'Subcategory' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subcategory extends Model
{
    protected \$guarded = ['id'];
}
EOT,
    'Regency' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Regency extends Model
{
    protected \$guarded = ['id'];
    
    public function province() { return \$this->belongsTo(Province::class); }
}
EOT,
    'Province' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected \$guarded = ['id'];
    
    public function regencies() { return \$this->hasMany(Regency::class); }
}
EOT,
    'ProductPhoto' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPhoto extends Model
{
    protected \$guarded = ['id'];
}
EOT,
    'ProductCoverage' => <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCoverage extends Model
{
    protected \$guarded = ['id'];
    
    public function regency() { return \$this->belongsTo(Regency::class); }
}
EOT,
];

foreach ($files as $name => $content) {
    file_put_contents(__DIR__ . "/app/Models/{$name}.php", $content);
}
