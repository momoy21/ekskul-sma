// Run this in Tinker to find duplicates:
// php artisan tinker
// Then paste:

// Check for duplicate ekskul
\App\Models\Ekskul::select('nama_ekskul', \Illuminate\Support\Facades\DB::raw('COUNT(*) as count'))
    ->groupBy('nama_ekskul')
    ->having('count', '>', 1)
    ->get();

// Or check all ekskul count
\App\Models\Ekskul::count();

// Check if there are duplicate active ekskul
\App\Models\Ekskul::aktif()->count();

// To remove duplicates (if any), run:
// Get all aktif ekskul and delete duplicates (keep first, delete rest)
$ekskul = \App\Models\Ekskul::aktif()->get();
$seen = [];
foreach ($ekskul as $e) {
    if (isset($seen[$e->nama_ekskul])) {
        $e->delete();
    } else {
        $seen[$e->nama_ekskul] = true;
    }
}
