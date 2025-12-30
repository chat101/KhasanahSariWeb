## AI Coding Agent Instructions

### Stack & Architecture
- **Framework**: Laravel 12 + Livewire 3 (real-time web app), Blade templates + Flux UI, TailwindCSS 4, Vite 6.
- **Language**: PHP 8.2+, SQLite (development), MySQL (testing/production).
- **Key packages**: `livewire/flux`, `livewire/volt`, `maatwebsite/excel`, `laravel-notification-channels/webpush`, `sanctum`.
- **Domain**: Manufacturing/distribution ERP with sales tracking (kontribusi), production orders, inventory, finance, accounting.

### Component Architecture (Critical Pattern)
Every major feature is a **Livewire component** housed in `app/Livewire/{Module}/` with paired Blade view at `resources/views/livewire/{module}/`. Routes wire directly to components in `routes/web.php` (no separate controllers).

**Typical component structure**:
```php
class Wilayah extends Component {
    use WithPagination;
    public $search = '';
    public $modal = false;   // modal toggle
    public $editId = null;   // edit mode tracker
    
    protected function rules() { /* validation rules */ }
    
    public function openModal() { $this->resetForm(); $this->modal = true; }
    public function store() { $this->validate(); Model::updateOrCreate(...); $this->closeModal(); }
    public function delete($id) { /* check constraints, dispatch swal event */ }
}
```

### Service Layer & Business Logic
Heavy calculations/integrations live in `app/Services/`:
- **`KontribusiHarianTokoService`** (465+ lines): HPP calculations (base 0.56, return 0.42), inflation adjustments from `MasterTrendInflasi`, target comparison, batch snapshots via `batch_id`.
- **`ExpoPushService`**: Delivers push notifications to mobile via Expo; channels: `work_order_alerts`, `alerts`.

Inject services into components; keep public properties lightweight.

### Data Model & Domain Language
**Regional hierarchy**: `Wilayah` (region) → `Area` → `Toko` (store). **Key models**:
- `MasterToko`: stores with `produksi_sendiri` flag, `area_id` FK.
- `TargetKontribusi`: sales targets with `tipe`, `rule_produksi` (conditional logic for `nilai_produksi_sendiri` vs `nilai_non_produksi_sendiri`).
- `Operasional\*`: LossBahan, KurangSetoran, MasterProyeksiKontribusi (batch snapshots), MasterTrendInflasi (inflation by month/year).
- `Produksi\PerintahProduksi`: work orders; `PerintahProduksiCreated` event triggers notifications.

Preserve Indonesian terms (kontribusi, barang, kurang_setoran, loss_bahan).

### Livewire Patterns
- **State management**: Public properties + `wire:model` binding. Reset after action with `$this->resetForm()` + `$this->resetPage()`.
- **Modal interaction**: Toggle `$modal` boolean; `$editId` tracks edit vs. create. Call `resetForm()` before opening.
- **Validation**: Define `rules()` method; call `$this->validate()` before store.
- **Events/notifications**: Dispatch SweetAlert via `$this->dispatch('swal', [...])`. Eager-load relationships to avoid N+1.
- **Pagination**: Use `WithPagination` trait with `protected $paginationTheme = 'tailwind'`.

### Async & Notifications
- **Queue**: `QUEUE_CONNECTION=database` (sync during testing). Long operations dispatch `ShouldQueue` jobs.
- **Example**: `SendExpoPush` job receives token array, title, body, data, channelId; dispatched by listeners like `SendPerintahProduksiPush` on `PerintahProduksiCreated` event.
- **Push filters**: Events filter by role (`admin`, `leaderproduksi`, `gudang`, etc.); use `User::whereIn('role', [...])` + `whereHas('pushTokens')`.

### Frontend & UI
- **Flux components**: `<flux:button>`, `<flux:input>`, `<flux:modal>`, `<flux:checkbox>`. Icons in `resources/views/flux/icon/`.
- **Exports**: Use `maatwebsite/excel` classes under `app/Exports/{Module}/`; call `->download()` from component methods.
- **Loading states**: Wrap with `wire:loading` for async feedback.

### Development & Operations
**Setup**:
```bash
composer install && npm install
composer run dev          # concurrent: php artisan serve, php artisan queue:listen, npm run dev
php artisan migrate       # SQLite by default
```

**Manual commands**:
```bash
php artisan serve                      # localhost:8000
php artisan queue:listen --tries=1     # process async jobs
npm run dev                            # Vite watch
npm run build                          # production build
phpunit                                # tests use MySQL from phpunit.xml
./vendor/bin/pint                      # format code
```

**Logs**: `storage/logs/laravel.log`. Queue debug: `php artisan queue:work --verbose`.

### Access Control & Role-Based Views
`User::allowedMenu()` returns role-based group/item whitelist. Roles: `admin`, `manager_finance`, `adminproduksi`, `leaderproduksi`, `gudang`, `area`, `wilayah`, `kasir`, `teknisi`. Each has `divisi_id`, `area_id`, `wilayah_id`, `toko_id` for scope restriction.
