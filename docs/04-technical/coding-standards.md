# 📝 Coding Standards

## TixKita - Developer Guidelines

---

## 1. PHP Standards

### PSR-12 Compliance
Kode PHP harus mengikuti PSR-12 Extended Coding Style.

### Namespace Convention
```php
// Controllers
namespace App\Http\Controllers\Admin;

// Models
namespace App\Models;

// Services
namespace App\Services;
```

### Class Naming
```php
// PascalCase for classes
class EventController extends Controller {}
class OrderService {}
class PaymentCallbackController {}

// Singular names for models
class Event extends Model {}
class Ticket extends Model {}
```

### Method Naming
```php
// camelCase for methods
public function showEventDetail($id) {}
public function processCheckout(Request $request) {}

// CRUD methods
public function index() {}    // List
public function create() {}   // Show create form
public function store() {}    // Save new
public function show() {}     // Show single
public function edit() {}     // Show edit form
public function update() {}   // Save update
public function destroy() {}  // Delete
```

### Variable Naming
```php
// camelCase for variables
$eventData = [];
$orderItems = [];
$totalPrice = 0;

// snake_case for database columns
$event->tgl_mulai;
$order->email_pembeli;
```

---

## 2. Laravel Conventions

### Controllers
```php
<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of events.
     */
    public function index()
    {
        $events = Event::with(['location', 'categories'])
            ->where('status', 'active')
            ->orderBy('tgl_mulai')
            ->paginate(12);

        return view('events.index', compact('events'));
    }

    /**
     * Store a newly created event.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tgl_mulai' => 'required|date',
        ]);

        $event = Event::create($validated);

        return redirect()
            ->route('admin.events')
            ->with('success', 'Event berhasil dibuat');
    }
}
```

### Models
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    // Custom primary key
    protected $primaryKey = 'event_id';

    // Mass assignable attributes
    protected $fillable = [
        'judul',
        'deskripsi',
        'tgl_mulai',
        'tgl_selesai',
    ];

    // Attribute casting
    protected $casts = [
        'tgl_mulai' => 'datetime',
        'tgl_selesai' => 'datetime',
    ];

    // Relationships
    public function products()
    {
        return $this->hasMany(Product::class, 'event_id', 'event_id');
    }

    // Accessors
    public function getMinPriceAttribute()
    {
        return $this->products->min('harga') ?? 0;
    }
}
```

### Migrations
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id('event_id');
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->datetime('tgl_mulai');
            $table->datetime('tgl_selesai');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
```

---

## 3. Blade Templates

### File Naming
```
resources/views/
├── admin/
│   ├── events/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   └── edit.blade.php
│   └── layout.blade.php
├── public/
│   ├── home.blade.php
│   └── event_detail.blade.php
└── components/
    ├── alert.blade.php
    └── card.blade.php
```

### Blade Conventions
```blade
{{-- Escaping output (default) --}}
{{ $event->judul }}

{{-- Unescaped output (be careful!) --}}
{!! $event->deskripsi !!}

{{-- Directives --}}
@if($events->isEmpty())
    <p>Tidak ada event</p>
@else
    @foreach($events as $event)
        <div>{{ $event->judul }}</div>
    @endforeach
@endif

{{-- Components --}}
<x-alert type="success" message="Berhasil!" />

{{-- Slots --}}
@section('content')
    <h1>Title</h1>
@endsection
```

---

## 4. JavaScript Standards

### ES6+ Syntax
```javascript
// Use const/let, not var
const events = [];
let currentPage = 1;

// Arrow functions
const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID').format(price);
};

// Template literals
const message = `Total: Rp ${formatPrice(total)}`;

// Destructuring
const { id, judul, harga } = eventData;
```

### jQuery Conventions (if used)
```javascript
$(document).ready(function() {
    // Cache selectors
    const $form = $('#checkout-form');
    const $submitBtn = $('#submit-btn');

    // Event handlers
    $form.on('submit', function(e) {
        e.preventDefault();
        handleSubmit();
    });
});
```

---

## 5. CSS/SCSS Standards

### Class Naming (BEM)
```css
/* Block */
.event-card {}

/* Element */
.event-card__title {}
.event-card__image {}
.event-card__price {}

/* Modifier */
.event-card--featured {}
.event-card--soldout {}
```

### Responsive Design
```css
/* Mobile first approach */
.event-card {
    width: 100%;
}

@media (min-width: 768px) {
    .event-card {
        width: 50%;
    }
}

@media (min-width: 1024px) {
    .event-card {
        width: 33.33%;
    }
}
```

---

## 6. Git Conventions

### Branch Naming
```
feature/add-payment-qris
bugfix/fix-qr-code-error
hotfix/critical-payment-fix
release/v1.5.0
```

### Commit Messages
```
feat: add QRIS payment method
fix: resolve QR code generation error
docs: update API documentation
refactor: simplify checkout logic
test: add order creation tests
chore: update dependencies
```

### Pull Request Template
```markdown
## Description
Brief description of changes

## Type
- [ ] Feature
- [ ] Bug Fix
- [ ] Documentation
- [ ] Refactor

## Checklist
- [ ] Tests pass
- [ ] Code reviewed
- [ ] Documentation updated
```

---

## 7. Security Guidelines

### Input Validation
```php
// Always validate input
$validated = $request->validate([
    'email' => 'required|email',
    'amount' => 'required|numeric|min:0',
]);

// Sanitize when needed
$cleanHtml = strip_tags($input);
```

### SQL Injection Prevention
```php
// Use Eloquent (safe)
Event::where('judul', $title)->get();

// Use query builder (safe)
DB::table('events')->where('judul', '=', $title)->get();

// NEVER do this
DB::raw("SELECT * FROM events WHERE judul = '$title'"); // UNSAFE!
```

### XSS Prevention
```blade
{{-- Always escape output --}}
{{ $userInput }}

{{-- Only use unescaped for trusted HTML --}}
{!! $trustedHtml !!}
```

---

## 8. Documentation

### PHPDoc Comments
```php
/**
 * Process ticket checkout.
 *
 * @param  \Illuminate\Http\Request  $request
 * @return \Illuminate\Http\RedirectResponse
 *
 * @throws \App\Exceptions\PaymentException
 */
public function processCheckout(Request $request)
{
    // ...
}
```

### Inline Comments
```php
// Calculate discount
$discount = $this->calculateDiscount($code, $subtotal);

// Apply tax if applicable
if ($event->has_tax) {
    $tax = $subtotal * 0.1; // 10% tax
}
```

---

*Last Updated: January 2026*
