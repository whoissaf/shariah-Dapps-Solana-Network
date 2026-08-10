set -e
cd /home/ilham/Documents/hackathon/backend
php -d memory_limit=-1 /usr/local/bin/composer require laravel/sanctum --no-interaction

cat <<'MIGRATION' > database/migrations/2024_01_01_000008_add_role_and_email_verified_at_to_users_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user');
            $table->timestamp('email_verified_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'email_verified_at']);
        });
    }
};
MIGRATION

cat <<'MODEL' > app/Models/User.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
MODEL

mkdir -p app/Services

cat <<'SERVICE' > app/Services/AuthenticationService.php
<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthenticationService
{
    public function register(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'user',
        ]);

        $this->sendOtp($user->email);

        return $user;
    }

    public function verifyEmail(string $email, string $otp): User
    {
        $cached = Cache::get('otp:' . $email);

        if (!$cached || $cached !== $otp) {
            throw ValidationException::withMessages([
                'otp' => ['OTP tidak valid atau sudah kedaluwarsa.'],
            ]);
        }

        $user = User::where('email', $email)->firstOrFail();
        $user->update(['email_verified_at' => now()]);
        Cache::forget('otp:' . $email);

        return $user;
    }

    public function login(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial tidak valid.'],
            ]);
        }

        if (!$user->email_verified_at) {
            throw ValidationException::withMessages([
                'email' => ['Email belum diverifikasi.'],
            ]);
        }

        $token = $user->createToken('api')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    private function sendOtp(string $email): void
    {
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put('otp:' . $email, $otp, now()->addMinutes(10));
        Mail::raw('Kode OTP verifikasi Anda: ' . $otp, function ($message) use ($email) {
            $message->to($email)->subject('OTP Verifikasi Email');
        });
    }
}
SERVICE

cat <<'CONTROLLER' > app/Http/Controllers/AuthController.php
<?php

namespace App\Http\Controllers;

use App\Services\AuthenticationService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private AuthenticationService $service)
    {
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $this->service->register($data);

        return response()->json([
            'message' => 'Registrasi berhasil. OTP dikirim ke email.',
            'user' => $user,
        ], 201);
    }

    public function verifyEmail(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $this->service->verifyEmail($data['email'], $data['otp']);

        return response()->json([
            'message' => 'Email berhasil diverifikasi.',
        ]);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $result = $this->service->login($data['email'], $data['password']);

        return response()->json([
            'message' => 'Login berhasil.',
            'user' => $result['user'],
            'token' => $result['token'],
        ]);
    }
}
CONTROLLER

cat <<'ROUTES' > routes/api.php
<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $database = \Illuminate\Support\Facades\DB::connection()->getDatabaseName();
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'service' => 'backend',
            'database' => false
        ], 500);
    }

    return response()->json([
        'status' => 'ok',
        'service' => 'backend',
        'database' => $database
    ]);
});

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/auth/login', [AuthController::class, 'login']);
ROUTES

php artisan migrate --force
